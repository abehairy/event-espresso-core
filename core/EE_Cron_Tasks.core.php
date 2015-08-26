<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/**
 *
 * Class EE_Cron_Tasks
 *
 * @package 			Event Espresso
 * @subpackage 	core
 * @author 				Brent Christensen
 *
 */
class EE_Cron_Tasks extends EE_BASE {


	/**
	 * @var EE_Cron_Tasks
	 */
	private static $_instance;



	/**
	 * @return EE_Cron_Tasks
	 */
	public static function instance() {
		if ( ! self::$_instance instanceof EE_Cron_Tasks ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}



	/**
	 * @access private
	 * @return EE_Cron_Tasks
	 */
	private function __construct() {
		// UPDATE TRANSACTION WITH PAYMENT
		add_action(
			'AHEE__EE_Cron_Tasks__update_transaction_with_payment',
			array( 'EE_Cron_Tasks', 'setup_update_for_transaction_with_payment' ),
			10, 2
		);
		// FINALIZE ABANDONED TRANSACTIONS
		add_action(
			'AHEE__EE_Cron_Tasks__finalize_abandoned_transactions',
			array( 'EE_Cron_Tasks', 'check_for_abandoned_transactions' ),
			10, 1
		);
		// EXPIRED TRANSACTION CHECK
		add_action(
			'AHEE__EE_Cron_Tasks__expired_transaction_check',
			array( 'EE_Cron_Tasks', 'expired_transaction_check' ),
			10, 1
		);
		// CLEAN OUT JUNK TRANSACTIONS AND RELATED DATA
		add_action(
				'AHEE__EE_Cron_Tasks__clean_up_junk_transactions',
				array( 'EE_Cron_Tasks', 'clean_out_junk_transactions' )
		);
	}



	/**
	 * reschedule_cron_for_transactions_if_maintenance_mode
	 *
	 * if Maintenance Mode is active, this will reschedule a cron to run again in 10 minutes
	 *
	 * @param string $cron_task
	 * @param array  $TXN_IDs
	 * @return bool
	 */
	public static function reschedule_cron_for_transactions_if_maintenance_mode( $cron_task, $TXN_IDs ) {
		// reschedule the cron if we can't hit the db right now
		if ( ! EE_Maintenance_Mode::instance()->models_can_query() ) {
			foreach( $TXN_IDs as $TXN_ID => $some_unused_var ) {
				// reset cron job for finalizing the TXN
				EE_Cron_Tasks::$cron_task(
					time() + ( 10 * MINUTE_IN_SECONDS ),
					$TXN_ID
				);
			}
			return true;
		}
		return false;
	}




	/****************  UPDATE TRANSACTION WITH PAYMENT ****************/


	/**
	 * array of TXN IDs and the payment
	 * @var array
	 */
	protected static $_update_transactions_with_payment = array();



	/**
	 * schedule_update_transaction_with_payment
	 *
	 * sets a wp_schedule_single_event() for updating any TXNs that may
	 * require updating due to recently received payments
	 *
	 * @param int $timestamp
	 * @param int $TXN_ID
	 * @param EE_Payment | null $payment
	 */
	public static function schedule_update_transaction_with_payment(
		$timestamp,
		$TXN_ID,
		$payment
	) {
		// validate $TXN_ID and $timestamp
		$TXN_ID = absint( $TXN_ID );
		$timestamp = absint( $timestamp );
		if ( $TXN_ID && $timestamp ) {
			wp_schedule_single_event(
				$timestamp,
				'AHEE__EE_Cron_Tasks__update_transaction_with_payment',
				array( $TXN_ID, $payment )
			);
		}
	}



	/**
	 * setup_update_for_transaction_with_payment
	 *
	 * this is the callback for the action hook:
	 * 'AHEE__EE_Cron_Tasks__update_transaction_with_payment'
	 * which is setup by EE_Cron_Tasks::schedule_update_transaction_with_payment().
	 * The passed TXN_ID and associated payment gets added to an array, and then
	 * the EE_Cron_Tasks::update_transaction_with_payment() function is hooked into
	 * 'shutdown' which will actually handle the processing of any
	 * transactions requiring updating, because doing so now would be too early
	 * and the required resources may not be available
	 *
	 * @param int  $TXN_ID
	 * @param null $payment
	 */
	public static function setup_update_for_transaction_with_payment( $TXN_ID = 0, $payment = null ) {
		if ( absint( $TXN_ID )) {
			self::$_update_transactions_with_payment[ $TXN_ID ] = $payment;
			add_action(
				'shutdown',
				array( 'EE_Cron_Tasks', 'update_transaction_with_payment' ),
				5
			);
		}
	}



	/**
	 * update_transaction_with_payment
	 *
	 * loops through the self::$_abandoned_transactions array
	 * and attempts to finalize any TXNs that have not been completed
	 * but have had their sessions expired, most likely due to a user not
	 * returning from an off-site payment gateway
	 */
	public static function update_transaction_with_payment() {

		if (
			// are there any TXNs that need cleaning up ?
			! empty( self::$_update_transactions_with_payment ) &&
			// reschedule the cron if we can't hit the db right now
			! EE_Cron_Tasks::reschedule_cron_for_transactions_if_maintenance_mode(
				'schedule_update_transaction_with_payment',
				self::$_update_transactions_with_payment
			)
		) {
			/** @type EE_Payment_Processor $payment_processor */
			$payment_processor = EE_Registry::instance()->load_core( 'Payment_Processor' );
			// set revisit flag for payment processor
			$payment_processor->set_revisit( false );
			// load EEM_Transaction
			EE_Registry::instance()->load_model( 'Transaction' );
			foreach ( self::$_update_transactions_with_payment as $TXN_ID => $payment ) {
				$transaction = EEM_Transaction::instance()->get_one_by_ID( $TXN_ID );
				// verify transaction
				if ( $transaction instanceof EE_Transaction ) {
					// now try to update the TXN with any payments
					$payment_processor->update_txn_based_on_payment( $transaction, $payment, true, true );
				}
				unset( self::$_update_transactions_with_payment[ $TXN_ID ] );
			}
		}
	}



	/************  END OF UPDATE TRANSACTION WITH PAYMENT  ************/



	/*****************  FINALIZE ABANDONED TRANSACTIONS *****************/



	/**
	 * array of TXN IDs
	 * @var array
	 */
	protected static $_abandoned_transactions = array();



	/**
	 * schedule_finalize_abandoned_transactions_check
	 *
	 * sets a wp_schedule_single_event() for finalizing any TXNs that may
	 * have been abandoned during the registration process
	 *
	 * @param int $timestamp
	 * @param int $TXN_ID
	 */
	public static function schedule_finalize_abandoned_transactions_check(
		$timestamp,
		$TXN_ID
	) {
		// validate $TXN_ID and $timestamp
		$TXN_ID = absint( $TXN_ID );
		$timestamp = absint( $timestamp );
		if ( $TXN_ID && $timestamp ) {
			wp_schedule_single_event(
				$timestamp,
				'AHEE__EE_Cron_Tasks__finalize_abandoned_transactions',
				array( $TXN_ID )
			);
		}
	}



	/**
	 * check_for_abandoned_transactions
	 *
	 * this is the callback for the action hook:
	 * 'AHEE__EE_Cron_Tasks__espresso_finalize_abandoned_transactions'
	 * which is utilized by wp_schedule_single_event()
	 * in EE_SPCO_Reg_Step_Payment_Options::_post_payment_processing().
	 * The passed TXN_ID gets added to an array, and then the
	 * espresso_finalize_abandoned_transactions() function is hooked into
	 * 'AHEE__EE_System__core_loaded_and_ready' which will actually handle the
	 * processing of any abandoned transactions, because doing so now would be
	 * too early and the required resources may not be available
	 *
	 * @param int $TXN_ID
	 */
	public static function check_for_abandoned_transactions(	$TXN_ID = 0 ) {
		if ( absint( $TXN_ID )) {
			self::$_abandoned_transactions[ $TXN_ID ]  = $TXN_ID;
			add_action(
				'shutdown',
				array( 'EE_Cron_Tasks', 'finalize_abandoned_transactions' ),
				5
			);
		}
	}



	/**
	 * finalize_abandoned_transactions
	 *
	 * loops through the self::$_abandoned_transactions array
	 * and attempts to finalize any TXNs that have not been completed
	 * but have had their sessions expired, most likely due to a user not
	 * returning from an off-site payment gateway
	 */
	public static function finalize_abandoned_transactions() {
		if (
			// are there any TXNs that need cleaning up ?
			! empty( self::$_abandoned_transactions ) &&
			// reschedule the cron if we can't hit the db right now
			! EE_Cron_Tasks::reschedule_cron_for_transactions_if_maintenance_mode(
				'schedule_finalize_abandoned_transactions_check',
				self::$_abandoned_transactions
			)
		) {
			/** @type EE_Transaction_Processor $transaction_processor */
			$transaction_processor = EE_Registry::instance()->load_class( 'Transaction_Processor' );
			// set revisit flag for txn processor
			$transaction_processor->set_revisit( false );
			/** @type EE_Transaction_Payments $transaction_payments */
			$transaction_payments = EE_Registry::instance()->load_class( 'Transaction_Payments' );
			// load EEM_Transaction
			EE_Registry::instance()->load_model( 'Transaction' );
			foreach ( self::$_abandoned_transactions as $TXN_ID ) {
				$transaction = EEM_Transaction::instance()->get_one_by_ID( $TXN_ID );
				// verify transaction
				if ( $transaction instanceof EE_Transaction ) {
					// or have had all of their reg steps completed
					if ( $transaction_processor->all_reg_steps_completed(	$transaction ) === true ) {
						continue;
					}
					// maybe update status, but don't save transaction just yet
					$transaction_payments	->update_transaction_status_based_on_total_paid( $transaction, false );
					do_action( 'AHEE__EE_Cron_Tasks__finalize_abandoned_transactions__after_status_update_based_on_total_paid', $transaction );
					// check if enough Reg Steps have been completed to warrant finalizing the TXN
					if ( $transaction_processor->all_reg_steps_completed_except_final_step( $transaction ) ) {
						// and if it hasn't already been set as being started...
						$transaction_processor->set_reg_step_initiated( $transaction, 'finalize_registration' );
					}
					// now update the TXN and trigger notifications
					$transaction_processor->update_transaction_and_registrations_after_checkout_or_payment(
						$transaction,
						$transaction->last_payment()
					);
				}
				unset( self::$_abandoned_transactions[ $TXN_ID ] );
			}
		}
	}



	/*************  END OF FINALIZE ABANDONED TRANSACTIONS  *************/

	/*****************  EXPIRED TRANSACTION CHECK *****************/



	/**
	 * array of TXN IDs
	 * @var array
	 */
	protected static $_expired_transactions = array();



	/**
	 * schedule_expired_transaction_check
	 *
	 * sets a wp_schedule_single_event() for following up on TXNs after their session has expired
	 *
	 * @param int $timestamp
	 * @param int $TXN_ID
	 */
	public static function schedule_expired_transaction_check(
		$timestamp,
		$TXN_ID
	) {
		// validate $TXN_ID and $timestamp
		$TXN_ID = absint( $TXN_ID );
		$timestamp = absint( $timestamp );
		if ( $TXN_ID && $timestamp ) {
			wp_schedule_single_event(
				$timestamp,
				'AHEE__EE_Cron_Tasks__expired_transaction_check',
				array( $TXN_ID )
			);
		}
	}



	/**
	 * expired_transaction_check
	 *
	 * this is the callback for the action hook:
	 * 'AHEE__EE_Cron_Tasks__transaction_session_expiration_check'
	 * which is utilized by wp_schedule_single_event()
	 * in \EED_Single_Page_Checkout::_initialize_transaction().
	 * The passed TXN_ID gets added to an array, and then the
	 * process_expired_transactions() function is hooked into
	 * 'AHEE__EE_System__core_loaded_and_ready' which will actually handle the
	 * processing of any failed transactions, because doing so now would be
	 * too early and the required resources may not be available
	 *
	 * @param int $TXN_ID
	 */
	public static function expired_transaction_check(	$TXN_ID = 0 ) {
		if ( absint( $TXN_ID )) {
			self::$_expired_transactions[ $TXN_ID ]  = $TXN_ID;
			add_action(
				'shutdown',
				array( 'EE_Cron_Tasks', 'process_expired_transactions' ),
				5
			);
		}
	}



	/**
	 * process_expired_transactions
	 *
	 * loops through the self::$_expired_transactions array and processes any failed TXNs
	 */
	public static function process_expired_transactions() {
		if (
			// are there any TXNs that need cleaning up ?
			! empty( self::$_expired_transactions ) &&
			// reschedule the cron if we can't hit the db right now
			! EE_Cron_Tasks::reschedule_cron_for_transactions_if_maintenance_mode(
				'schedule_expired_transaction_check',
				self::$_expired_transactions
			)
		) {
			/** @type EE_Transaction_Processor $transaction_processor */
			$transaction_processor = EE_Registry::instance()->load_class( 'Transaction_Processor' );
			// set revisit flag for txn processor
			$transaction_processor->set_revisit( false );
			// load EEM_Transaction
			EE_Registry::instance()->load_model( 'Transaction' );
			foreach ( self::$_expired_transactions as $TXN_ID ) {
				$transaction = EEM_Transaction::instance()->get_one_by_ID( $TXN_ID );
				// verify transaction and whether it is failed or not
				if ( $transaction instanceof EE_Transaction) {
					switch( $transaction->status_ID() ) {
						// Completed TXNs
						case EEM_Transaction::complete_status_code :
							do_action( 'AHEE__EE_Cron_Tasks__process_expired_transactions__completed_transaction', $transaction );
							break;
						// Overpaid TXNs
						case EEM_Transaction::overpaid_status_code :
							do_action( 'AHEE__EE_Cron_Tasks__process_expired_transactions__overpaid_transaction', $transaction );
							break;
						// Incomplete TXNs
						case EEM_Transaction::incomplete_status_code :
							do_action( 'AHEE__EE_Cron_Tasks__process_expired_transactions__incomplete_transaction', $transaction );
							// todo : merge the finalize_abandoned_transactions cron into this one...
							// todo : move business logic into EE_Transaction_Processor for finalizing abandoned transactions
							break;
						// Failed TXNs
						case EEM_Transaction::failed_status_code :
							do_action( 'AHEE__EE_Cron_Tasks__process_expired_transactions__failed_transaction', $transaction );
							// todo : perform garbage collection here and remove clean_out_junk_transactions()
							//$registrations = $transaction->registrations();
							//if ( ! empty( $registrations ) ) {
							//	foreach ( $registrations as $registration ) {
							//		if ( $registration instanceof EE_Registration ) {
							//$delete_registration = true;
							//if ( $registration->attendee() instanceof EE_Attendee ) {
							//	$delete_registration = false;
							//}
							//if ( $delete_registration ) {
							//	$registration->delete_permanently();
							//	$registration->delete_related_permanently();
							//}
							//		}
							//	}
							//}
							break;
					}

				}
				unset( self::$_expired_transactions[ $TXN_ID ] );
			}
		}
	}



	/*************  END OF FINALIZE ABANDONED TRANSACTIONS  *************/

	/************* START CLEAN UP BOT TRANSACTIONS **********************/

	//when a transaction is initially made, schedule this check.
	//if it has NO REG data by the time it has expired, forget about it
	public static function clean_out_junk_transactions() {
		if( EE_Maintenance_Mode::instance()->models_can_query() ) {
			EEM_Transaction::instance('')->delete_junk_transactions();
			EEM_Registration::instance('')->delete_registrations_with_no_transaction();
			EEM_Line_Item::instance('')->delete_line_items_with_no_transaction();
		}
	}


}
// End of file EE_Cron_Tasks.core.php
// Location: /EE_Cron_Tasks.core.php