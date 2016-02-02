<?php
if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}
/**
 * ************************************************************************
 *       _____  ______          _____      __  __ ______
 *      |  __ \|  ____|   /\   |  __ \    |  \/  |  ____|
 *      | |__) | |__     /  \  | |  | |   | \  / | |__
 *      |  _  /|  __|   / /\ \ | |  | |   | |\/| |  __|
 *      | | \ \| |____ / ____ \| |__| |   | |  | | |____
 *      |_|  \_\______/_/    \_\_____/    |_|  |_|______|
 *
 * ************************************************************************
 *
 * This file contains all deprecated actions, filters, functions, and classes in EE.
 * DO NOT ADD NEW CODE TO THE TOP OF THIS FILE !!!
 * PLEASE ADD ALL NEW CODE TO THE BOTTOM THIS FILE !!!
 * IF YOU ADD CODE TO THIS FILE, WHY NOT TRY ADDING IT TO THE BOTTOM ?
 * THE WHITE ZONE IS FOR LOADING AND UNLOADING ONLY,
 * IF YOU HAVE TO LOAD OR UNLOAD, GO TO THE WHITE ZONE !!!
 *
 * @package     Event Espresso
 * @subpackage  helpers
 * @since       4.5.0
 */
/**
 * ee_deprecated__registration_checkout__button_text
 *
 * @param string       $submit_button_text
 * @param \EE_Checkout $checkout
 * @return string
 */
function ee_deprecated__registration_checkout__button_text( $submit_button_text, EE_Checkout $checkout ) {
	// list of old filters
	$deprecated_filters = array(
		'update_registration_details' => true,
		'process_payment' => true,
		'finalize_registration' => true,
		'and_proceed_to_payment' => true,
		'proceed_to' => true,
	);
	// loop thru and call doing_it_wrong() or remove any that aren't being used
	foreach ( $deprecated_filters as $deprecated_filter => $on ) {
		// was this filter called ?
		if ( has_action( 'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__' . $deprecated_filter )) {
			// only display doing_it_wrong() notice to Event Admins during non-AJAX requests
			if ( EE_Registry::instance()->CAP->current_user_can( 'ee_read_ee', 'hide_doing_it_wrong_for_deprecated_SPCO_filter' ) && ! defined( 'DOING_AJAX' ) ) {
				EE_Error::doing_it_wrong(
					'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__' . $deprecated_filter,
					sprintf(
						__( 'The %1$s filter is deprecated.  It *may* work as an attempt to build in backwards compatibility.  However, it is recommended to use the following new filter: %2$s"%3$s" found in "%4$s"', 'event_espresso' ),
						'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__' . $deprecated_filter,
						'<br />',
						'FHEE__EE_SPCO_Reg_Step__set_submit_button_text___submit_button_text',
						'/modules/single_page_checkout/inc/EE_SPCO_Reg_Step.class.php'
					),
					'4.6.10',
					E_USER_DEPRECATED
				);
			}
		} else {
			unset( $deprecated_filters[ $deprecated_filter ] );
		}
	}
	if ( ! empty( $deprecated_filters )) {

		if ( $checkout->current_step->slug() == 'attendee_information' && $checkout->revisit && isset( $deprecated_filters[ 'update_registration_details' ] )) {
			$submit_button_text = apply_filters( 'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__update_registration_details', $submit_button_text );
		} else if ( $checkout->current_step->slug() == 'payment_options' && $checkout->revisit && isset( $deprecated_filters[ 'process_payment' ] ) ) {
			$submit_button_text = apply_filters( 'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__process_payment', $submit_button_text );
		} else if ( $checkout->next_step instanceof EE_SPCO_Reg_Step && $checkout->next_step->slug() == 'finalize_registration' && isset( $deprecated_filters[ 'finalize_registration' ] ) ) {
			$submit_button_text = apply_filters( 'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__finalize_registration', $submit_button_text );
		}
		if ( $checkout->next_step instanceof EE_SPCO_Reg_Step ) {
			if ( $checkout->payment_required() && $checkout->next_step->slug() == 'payment_options' && isset( $deprecated_filters[ 'and_proceed_to_payment' ] ) ) {
				$submit_button_text .= apply_filters( 'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__and_proceed_to_payment', $submit_button_text );
			}
			if ( $checkout->next_step->slug() != 'finalize_registration' && ! $checkout->revisit && isset( $deprecated_filters[ 'proceed_to' ] ) ) {
				$submit_button_text = apply_filters( 'FHEE__EED_Single_Page_Checkout__registration_checkout__button_text__proceed_to', $submit_button_text ) . $checkout->next_step->name();
			}
		}

	}
	return $submit_button_text;

}
add_filter( 'FHEE__EE_SPCO_Reg_Step__set_submit_button_text___submit_button_text', 'ee_deprecated__registration_checkout__button_text', 10, 2 );




/**
 * ee_deprecated_finalize_transaction
 *
 * @param \EE_Checkout $checkout
 * @param boolean $status_updates
 */
function ee_deprecated_finalize_transaction( EE_Checkout $checkout, $status_updates ) {
	$action_ref = NULL;
	$action_ref = has_action( 'AHEE__EE_Transaction__finalize__new_transaction' ) ? 'AHEE__EE_Transaction__finalize__new_transaction' : $action_ref;
	$action_ref = has_action( 'AHEE__EE_Transaction__finalize__all_transaction' ) ? 'AHEE__EE_Transaction__finalize__all_transaction' : $action_ref;
	if ( $action_ref ) {

		EE_Error::doing_it_wrong(
			$action_ref,
			sprintf(
				__( 'This action is deprecated.  It *may* work as an attempt to build in backwards compatibility.  However, it is recommended to use one of the following new actions: %1$s"%3$s" found in "%2$s" %1$s"%4$s" found in "%2$s" %1$s"%5$s" found in "%2$s" %1$s"%6$s" found in "%2$s"', 'event_espresso' ),
				'<br />',
				'/core/business/EE_Transaction_Processor.class.php',
				'AHEE__EE_Transaction_Processor__finalize',
				'AHEE__EE_Transaction_Processor__manually_update_registration_statuses',
				'AHEE__EE_Transaction_Processor__toggle_registration_statuses_for_default_approved_events',
				'AHEE__EE_Transaction_Processor__toggle_registration_statuses_if_no_monies_owing'
			),
			'4.6.0',
			E_USER_DEPRECATED
		);
		switch ( $action_ref ) {
			case 'AHEE__EE_Transaction__finalize__new_transaction' :
				do_action( 'AHEE__EE_Transaction__finalize__new_transaction', $checkout->transaction, $checkout->admin_request );
				break;
			case 'AHEE__EE_Transaction__finalize__all_transaction' :
				do_action( 'AHEE__EE_Transaction__finalize__new_transaction', $checkout->transaction, array( 'new_reg' => ! $checkout->revisit, 'to_approved' => $status_updates ), $checkout->admin_request );
				break;
		}
	}
}
add_action( 'AHEE__EE_SPCO_Reg_Step_Finalize_Registration__process_reg_step__completed', 'ee_deprecated_finalize_transaction', 10, 2 );
/**
 * ee_deprecated_finalize_registration
 *
 * @param EE_Registration $registration
 */
function ee_deprecated_finalize_registration( EE_Registration $registration ) {
	$action_ref = has_action( 'AHEE__EE_Registration__finalize__update_and_new_reg' ) ? 'AHEE__EE_Registration__finalize__update_and_new_reg' : NULL;
	if ( $action_ref ) {
		EE_Error::doing_it_wrong(
			$action_ref,
			sprintf(
				__( 'This action is deprecated.  It *may* work as an attempt to build in backwards compatibility.  However, it is recommended to use the following new action: %1$s"%3$s" found in "%2$s"', 'event_espresso' ),
				'<br />',
				'/core/business/EE_Registration_Processor.class.php',
				'AHEE__EE_Registration_Processor__trigger_registration_status_changed_hook'
			),
			'4.6.0',
			E_USER_DEPRECATED
		);
		do_action( 'AHEE__EE_Registration__finalize__update_and_new_reg', $registration, ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )));
	}
}
add_action( 'AHEE__EE_Registration_Processor__trigger_registration_update_notifications', 'ee_deprecated_finalize_registration', 10, 1 );




/** Messages System deprecated things **/


/**
 * wrapper for deprecated FHEE__*___create_new_templates__templates filter.  Note depending on how this was used, it may or may not degrade gracefully for clients using the existing filter because the old Default classes are NOT present anymore.
 *
 * @deprecated 4.5.0
 * @deprecated Use FHEE__EE_Template_Pack___get_templates__templates filter instead.
 *
 * @param array                     $templates      array of generated templates
 * @param EE_messenger              $messenger
 * @param EE_message_type           $message_type
 * @param EE_Messages_Template_Pack $template_pack
 *
 * @return array
 */
function ee_deprecated_get_templates( $templates, EE_messenger $messenger, EE_message_type $message_type, EE_Messages_Template_Pack $template_pack ) {
	$old_default_classnames = array(
		'EE_Messages_Email_Cancelled_Registration_Defaults',
		'EE_Messages_Email_Declined_Registration_Defaults',
		'EE_Messages_Email_Not_Approved_Registration_Defaults',
		'EE_Messages_Email_Payment_Declined_Defaults',
		'EE_Messages_Email_Payment_Defaults',
		'EE_Messages_Email_Payment_Reminder_Defaults',
		'EE_Messages_Email_Pending_Approval_Defaults',
		'EE_Messages_Email_Registration_Defaults',
		'EE_Messages_Email_Newsletter_Defaults',
		'EE_Message_Template_Defaults'
		);

	$old_class_instance = new stdClass();

	foreach ( $old_default_classnames as $classname ) {
		$filter_ref = 'FHEE__' . $classname . '___create_new_templates___templates';
		if ( has_filter( $filter_ref ) ) {
			EE_Error::doing_it_wrong( $filter_ref, __('This filter is deprecated.  It *may* work as an attempt to build in backwards compatibility.  However, it is recommended to use the new filter provided which is "FHEE__EE_Template_Pack___get_templates__templates" found in the EE_Messages_Template_Pack class.', 'event_espresso'), '4.5.0', E_USER_DEPRECATED );
		}
		$templates = apply_filters( $filter_ref, $templates, $old_class_instance );
	}

	return $templates;
}
add_filter( 'FHEE__EE_Template_Pack___get_templates__templates', 'ee_deprecated_get_templates', 10, 4 );

/**
 * Called after EED_Module::set_hooks() and EED_Module::set_admin_hooks() was called.
 * Checks if any deprecated hooks were hooked-into and provide doing_it_wrong messages appropriately.
 */
function ee_deprecated_hooks(){
	/**
	 * @var $hooks array where keys are hook names, and their values are array{
	 *			@type string $version  when deprecated
	 *			@type string $alternative  saying what to use instead
	 *			@type boolean $still_works  whether or not the hook still works
	 *		}
	 */
	$hooks = array(
		'AHEE__EE_System___do_setup_validations' => array(
			'version' => '4.6.0',
			'alternative' => __( 'Instead use "AHEE__EEH_Activation__validate_messages_system" which is called after validating messages (done on every new install, upgrade, reactivation, and downgrade)', 'event_espresso' ),
			'still_works' => FALSE
		)
	);
	foreach( $hooks as $name => $deprecation_info ){
		if( has_action( $name ) ){
			EE_Error::doing_it_wrong(
				$name,
				sprintf(
					__('This filter is deprecated. %1$s%2$s','event_espresso'),
					$deprecation_info[ 'still_works' ] ?  __('It *may* work as an attempt to build in backwards compatibility.', 'event_espresso') : __( 'It has been completely removed.', 'event_espresso' ),
					isset( $deprecation_info[ 'alternative' ] ) ? $deprecation_info[ 'alternative' ] : __( 'Please read the current EE4 documentation further or contact Support.', 'event_espresso' )
				),
				isset( $deprecation_info[ 'version' ] ) ? $deprecation_info[ 'version' ] : __( 'recently', 'event_espresso' ),
				E_USER_DEPRECATED
			);
		}
	}
}
add_action( 'AHEE__EE_System__set_hooks_for_shortcodes_modules_and_addons', 'ee_deprecated_hooks' );






/**
 * wrapper for the now deprecated FHEE__*__get_default_field_content filter.  Note depending on how this was used, it may or may not degrade gracefully for clients using the  filter format that is "FHEE__EE_Messages_Base__get_default_field_content" as that format might have had either a EE_messenger or EE_message_type object as a param.  The backward_compat implementation assumed EE_message_type.
 *
 * @deprecated 4.5.0
 * @deprecated Use FHEE__EE_Messages_Template_Pack__get_specific_template__contents filter instead.
 *
 * @param string                    $contents        The template contents being used.
 * @param string                    $actual_path   The actual path for the template contents.
 * @param EE_messenger              $messenger
 * @param EE_message_type           $message_type
 * @param string                    $field         The field the contents are for.
 * @param string                    $context       The context the contents are for.
 * @param EE_Messages_Template_Pack $template_pack
 *
 * @return string                    The default contents for the messenger, message type, context and field.
 */
function ee_deprecated_get_default_field_content( $contents, $actual_path, EE_messenger $messenger, EE_message_type $message_type, $field, $context, EE_Messages_Template_Pack $template_pack ) {

	$classnames_to_try = array(
		get_class( $messenger ) => $messenger,
		get_class( $message_type ) => $message_type,
		'EE_Messages_Base' => $message_type
		);

	foreach ( $classnames_to_try as $classname => $obj ) {
		$filter_ref = 'FHEE__' . $classname . '__get_default_field_content';
		if ( has_filter( $filter_ref ) ) {
			EE_Error::doing_it_wrong( $filter_ref, __('This filter is deprecated.  It *may* work as an attempt to build in backwards compatibility.  However, it is recommended to use the new filter provided which is "FHEE__EE_Messages_Template_Pack__get_specific_template__contents" found in the EE_Messages_Template_Pack class.', 'event_espresso'), '4.5.0', E_USER_DEPRECATED );
		}
		$contents = apply_filters( $filter_ref, $contents, $obj );
	}

	return $contents;
}
add_filter( 'FHEE__EE_Messages_Template_Pack__get_specific_template__contents', 'ee_deprecated_get_default_field_content', 10, 7 );





/**
 * wrapper for the now deprecated *__get_inline_css_template__css_url and path filters.
 * Filters deprecated are:
 * 	- FHEE__EE_Email_messenger__get_inline_css_template__css_url
 * 	- FHEE__EE_Email_messenger__get_inline_css_template__css_path
 * 	- FHEE__EE_Html_messenger__get_inline_css_template__css_url
 * 	- FHEE__EE_Html_messenger__get_inline_css_template__css_path
 *
 * @deprecated 4.5.0
 * @deprecated Use the new FHEE__EE_Messages_Template_Pack__get_variation filter instead.
 *
 * @param string                    $variation_path The current css path.
 * @param string                    $messenger      EE_messenger slug.
 * @param string                    $messenger      EE_message_type slug
 * @param string                    $type                The type of css file being returned (wpeditor, default etc.)
 * @param string                    $variation         Introduced by the new template pack system. The variation slug.
 * @param string                    $file_extension Defaults to css.  The file extension for the file being retrieved.
 * @param bool                      $url            Whether this is a directory path or url path.
 * @param EE_Messages_Template_Pack $template_pack
 *
 * @return string                    The path to the file being used.
 */
function ee_deprecated_get_inline_css_template_filters( $variation_path, $messenger, $message_type, $type, $variation, $file_extension, $url,  EE_Messages_Template_Pack $template_pack ) {

	if ( $messenger == 'email' ) {
		$filter_ref = $url ? 'FHEE__EE_Email_messenger__get_inline_css_template__css_url' : 'FHEE__EE_Email_messenger__get_inline_css_template__css_path';
	} elseif ( $messenger == 'html' ) {
		$filter_ref = $url ? 'FHEE__EE_Html_messenger__get_inline_css_template__css_url' : 'FHEE__EE_Html_messenger__get_inline_css_template__css_path';
	} else {
		return $variation_path;
	}

	if ( has_filter( $filter_ref ) ) {
		EE_Error::doing_it_wrong( $filter_ref, __('This filter is deprecated.  It is recommended to use the new filter provided which is "FHEE__EE_Messages_Template_Pack__get_variation" found in the EE_Messages_Template_Pack class.', 'event_espresso'), '4.5.0', E_USER_DEPRECATED );
	}

	return apply_filters( $filter_ref, $variation_path, $url, $type );
}
add_filter( 'FHEE__EE_Messages_Template_Pack__get_variation', 'ee_deprecated_get_inline_css_template_filters', 10, 8 );




/**
 * EE_Messages_Init
 * This was the old controller for the Messages system which has now been moved into a proper EED Module
 *
 * @deprecated 4.5.0
 * @deprecated Use the new EED_Messages module
 *
 */
class EE_Messages_Init extends EE_Base {

	public function __construct() {
		self::doing_it_wrong_call( __METHOD__ );
	}

	/**
	 * @param $method_name
	 */
	public static function doing_it_wrong_call( $method_name ) {
		EE_Error::doing_it_wrong( __CLASS__, sprintf( __('The %s in this class is deprecated as of EE4.5.0.  All functionality formerly in this class is now in the EED_Messages module.', 'event_espresso'), $method_name ), '4.5.0', E_USER_DEPRECATED );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public static function set_autoloaders() {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::set_autoloaders();
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function payment_reminder( $transaction ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::payment_reminder( $transaction );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function payment( $transaction, $payment ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::payment( $transaction, $payment );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function cancelled_registration( $transaction ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::cancelled_registration( $transaction );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function maybe_registration( $transaction, $reg_msg, $from_admin ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::maybe_registration( $transaction, $reg_msg, $from_admin );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function process_resend( $success, $req_data ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::process_resend( $req_data );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function process_admin_payment( $success, $payment ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::process_admin_payment( $payment );
	}

	/**
	 * @deprecated 4.5.0
	 */
	public function send_newsletter_message( $contacts, $grp_id ) {
		self::doing_it_wrong_call( __METHOD__ );
		EED_Messages::send_newsletter_message( $contacts, $grp_id );
	}


} //end deprecated EE_Messages_Init


/**
 * Deprecated EE_Register_CPTs filters
 */

/**
 * wrapper for deprecated 'FHEE__EE_Register_CPTs__construct__CPTs' filter.
 *
 * @deprecated 4.5.0
 * @deprecated Use FHEE__EE_Register_CPTs__get_CPTs__cpts filter instead
 *
 * @param array $cpts The cpts being filtered
 *
 * @return array additional cpts.
 */
function ee_deprecated_get_cpts( $cpts ) {
	if ( has_filter( 'FHEE__EE_Register_CPTs__construct__CPTs' ) ) {
		EE_Error::doing_it_wrong( 'FHEE__EE_Register_CPTs__construct__CPTs', __('This filter is deprecated. It will still work for the time being.  However, it is recommended to use the new filter provided which is "FHEE__EE_Register_CPTs__get_CPTs__cpts" found in EE_Register_CPTs::get_CPTs()', 'event_espresso'), '4.5.0', E_USER_DEPRECATED );
	}
	return apply_filters( 'FHEE__EE_Register_CPTs__construct__CPTs', $cpts );
}
add_filter( 'FHEE__EE_Register_CPTs__get_CPTs__cpts', 'ee_deprecated_get_cpts', 10 );



/**
 * wrapper for deprecated 'FHEE__EE_Register_CPTs__construct__taxonomies' filter.
 *
 * @deprecated 4.5.0
 * @deprecated Use FHEE__EE_Register_CPTs__get_taxonomies__taxonomies filter instead
 *
 * @param array $cts The custom taxonomies being filtered
 *
 * @return array additional custom taxonomies.
 */
function ee_deprecated_get_taxonomies( $cts ) {
	if ( has_filter( 'FHEE__EE_Register_CPTs__construct__taxonomies' ) ) {
		EE_Error::doing_it_wrong( 'FHEE__EE_Register_CPTs__construct__taxonomies', __('This filter is deprecated. It will still work for the time being.  However, it is recommended to use the new filter provided which is "FHEE__EE_Register_CPTs__get_taxonomies__taxonomies" found in EE_Register_CPTs::get_taxonomies()', 'event_espresso'), '4.5.0', E_USER_DEPRECATED );
	}
	return apply_filters( 'FHEE__EE_Register_CPTs__construct__taxonomies', $cts );
}
add_filter( 'FHEE__EE_Register_CPTs__get_taxonomies__taxonomies', 'ee_deprecated_get_taxonomies', 10 );


/**
 * Deprecated class for instantiating default templates.  This was deprecated because of a substantial change in the constructor
 * signature.
 *
 * @package    Event Espresso
 * @subpackage messages
 * @author     Darren Ethier
 * @since      4.1
 * @deprecated 4.9.0  Replaced by EE_Messages_Template_Defaults (note the plural s on Messages)
 */
class EE_Message_Template_Defaults extends EE_Base {

	/**
	 * EE_Message_Template_Defaults constructor.
	 *
	 * @param EE_messages $messages
	 * @param             $messenger_name
	 * @param             $message_type_name
	 * @param int         $GRP_ID
	 * @return EE_Messages_Template_Defaults
	 */
	public function __construct(
		EE_messages $messages,
		$messenger_name,
		$message_type_name,
		$GRP_ID = 0
	) {
		EE_Error::doing_it_wrong(
			__FUNCTION__,
			__(
				'The class EE_Message_Template_Defaults has been deprecated and replaced by EE_Messages_Template_Defaults.',
				'event_espresso'
			),
			'4.9.0'
		);
		/** @var EE_Message_Resource_Manager $message_resource_manager */
		$message_resource_manager = EE_Registry::instance()->load_lib( 'Message_Resource_Manager' );
		$messenger = $message_resource_manager->get_messenger( $messenger_name );
		$message_type = $message_resource_manager->get_message_type( $message_type_name );
		return EE_Registry::instance()->load_lib(
			'Messages_Template_Defaults',
			array(
				$GRP_ID,
				$messenger,
				$message_type,
			)
		);
	}
}



//end EE_Message_Template_Defaults class



/**
 * @deprecated     4.9.0
 * @package        Event Espresso
 * @subpackage     includes/core/messages
 * @author         Darren Ethier, Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class EE_messages {

	/** @type EE_messenger[] */
	protected $_active_messengers = array();

	/** @type array */
	protected $_active_message_types = array();

	/** @type EE_message_type[] */
	protected $_installed_message_types = array();

	/** @type EE_messenger */
	protected $_messenger;

	/** @type EE_message_type */
	protected $_message_type;

	/** @type array */
	protected $_contexts = array();

	/** @type EE_Message_Resource_Manager $_message_resource_manager */
	protected $_message_resource_manager;



	/**
	 * EE_messages constructor.
	 *
	 * @deprecated 4.9.0
	 */
	function __construct() {
	}



	/**
	 * @param string $method
	 */
	public function _class_is_deprecated( $method ) {
		// EE_messages has been deprecated
		EE_Error::doing_it_wrong(
			'EE_messages::' . $method,
			__( 'EE_messages has been deprecated.  Please use EE_Message_Resource_Manager instead.' ),
			'4.9.0'
		);
		// Please use EE_Message_Resource_Manager instead
		$this->_message_resource_manager = EE_Registry::instance()->load_lib( 'Message_Resource_Manager' );
	}



	/**
	 * @deprecated 4.9.0
	 * @param string $messenger_name
	 * @return boolean TRUE if it was PREVIOUSLY active, and FALSE if it was previously inactive
	 */
	public function ensure_messenger_is_active( $messenger_name ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->ensure_messenger_is_active( $messenger_name );
	}



	/**
	 * @deprecated 4.9.0
	 * @param string $message_type message type name
	 * @param        $messenger
	 * @return bool true if it got activated (or was active) and false if not.
	 * @throws \EE_Error
	 */
	public function ensure_message_type_is_active( $message_type, $messenger ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->ensure_message_type_is_active( $message_type, $messenger );
	}



	/**
	 * @deprecated 4.9.0
	 * @param string $messenger_name
	 * @param array  $mts_to_activate             (optional) An array of message types to activate with this messenger.  If
	 *                                            included we do NOT setup the default message types (assuming
	 *                                            they are already setup.)
	 * @return boolean an array of generated templates or false if nothing generated/activated.
	 */
	public function activate_messenger( $messenger_name, $mts_to_activate = array() ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->activate_messenger( $messenger_name, $mts_to_activate );
	}



	/**
	 * @deprecated 4.9.0
	 * @param EE_messenger    $messenger    messenger used in trigger
	 * @param EE_message_type $message_type message type used in trigger
	 *
	 * @return bool true is a generating messenger and can be sent OR FALSE meaning cannot send.
	 */
	public function is_generating_messenger_and_active( EE_messenger $messenger, EE_message_type $message_type ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->is_generating_messenger_and_active( $messenger, $message_type );
	}



	/**
	 * @deprecated 4.9.0
	 * @param string $messenger
	 * @return EE_messenger | null
	 */
	public function get_messenger_if_active( $messenger ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->get_active_messenger( $messenger );
	}



	/**
	 * @deprecated 4.9.0
	 * @param EE_Message $message
	 * @return array  An array with 'messenger' and 'message_type' as the index and the corresponding valid object if
	 *                  available.
	 *                  Eg. Valid Messenger and Message Type:
	 *                  array(
	 *                  'messenger' => new EE_Email_messenger(),
	 *                  'message_type' => new EE_Registration_Approved_message_type()
	 *                  )
	 *                  Valid Messenger and Invalid Message Type:
	 *                  array(
	 *                  'messenger' => new EE_Email_messenger(),
	 *                  'message_type' => null
	 *                  )
	 */
	public function validate_for_use( EE_Message $message ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return array(
			'messenger'    => $message->messenger_object(),
			'message_type' => $message->message_type_object(),
		);
	}



	/**
	 * @deprecated 4.9.0
	 * @param  string $type                 What type of message are we sending (corresponds to message types)
	 * @param  mixed  $vars                 Data being sent for parsing in the message
	 * @param  string $sending_messenger    if included then we ONLY use the specified messenger for delivery.  Otherwise we cycle through all active messengers.
	 * @param string  $generating_messenger if included then this messenger is used for generating the message templates (but not for sending).
	 * @param string  $context              If included then only a message type for a specific context will be generated.
	 * @param bool    $send                 Default TRUE.  If false, then this will just return the generated EE_messages objects which might be used by the trigger to setup a batch message (typically html messenger uses it).
	 * @return bool
	 */
	public function send_message(
		$type,
		$vars,
		$sending_messenger = '',
		$generating_messenger = '',
		$context = '',
		$send = true
	) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		/** @type EE_Messages_Processor $processor */
		$processor = EE_Registry::instance()->load_lib( 'Messages_Processor' );
		$error = false;
		//try to intelligently determine what method we'll call based on the incoming data.
		//if generating and sending are different then generate and send immediately.
		if ( ! empty( $sending_messenger ) && $sending_messenger != $generating_messenger && $send ) {
			//in the legacy system, when generating and sending were different, that means all the
			//vars are already in the request object.  So let's just use that.
			try {
				/** @type EE_Message_To_Generate_From_Request $mtg */
				$mtg = EE_Registry::instance()->load_lib( 'Message_To_Generate_From_Request' );
				$processor->generate_and_send_now( $mtg );
			} catch ( EE_Error $e ) {
				$error_msg = __(
					'Please note that a system message failed to send due to a technical issue.',
					'event_espresso'
				);
				// add specific message for developers if WP_DEBUG in on
				$error_msg .= '||' . $e->getMessage();
				EE_Error::add_error( $error_msg, __FILE__, __FUNCTION__, __LINE__ );
				$error = true;
			}
		} else {
			$processor->generate_for_all_active_messengers( $type, $vars, $send );
			//let's find out if there were any errors and how many successfully were queued.
			$count_errors = $processor->get_queue()->count_STS_in_queue( EEM_Message::status_failed );
			$count_queued = $processor->get_queue()->count_STS_in_queue( EEM_Message::status_incomplete );
			$count_retry = $processor->get_queue()->count_STS_in_queue( EEM_Message::status_retry );
			$count_errors = $count_errors + $count_retry;
			if ( $count_errors > 0 ) {
				$error = true;
				if ( $count_errors > 1 && $count_retry > 1 && $count_queued > 1 ) {
					$message = sprintf(
						__(
							'There were %d errors and %d messages successfully queued for generation and sending',
							'event_espresso'
						),
						$count_errors,
						$count_queued
					);
				} elseif ( $count_errors > 1 && $count_queued === 1 ) {
					$message = sprintf(
						__(
							'There were %d errors and %d message successfully queued for generation.',
							'event_espresso'
						),
						$count_errors,
						$count_queued
					);
				} elseif ( $count_errors === 1 && $count_queued > 1 ) {
					$message = sprintf(
						__(
							'There was %d error and %d messages successfully queued for generation.',
							'event_espresso'
						),
						$count_errors,
						$count_queued
					);
				} else {
					$message = sprintf(
						__(
							'There was %d message that failed to be queued for generation.',
							'event_espresso'
						),
						$count_errors
					);
				}
				EE_Error::add_error( $message, __FILE__, __FUNCTION__, __LINE__ );
			} else {
				if ( $count_queued === 1 ) {
					$message = sprintf(
						__(
							'%d message successfully queued for generation.',
							'event_espresso'
						),
						$count_queued
					);
				} else {
					$message = sprintf(
						__(
							'%d messages were successfully queued for generation.',
							'event_espresso'
						),
						$count_queued
					);
				}
				EE_Error::add_success( $message );
			}
		}
		//if no error then return the generated message(s).
		if ( ! $error && ! $send ) {
			$generated_queue = $processor->generate_queue( false );
			//get message and return.
			$generated_queue->get_queue()->rewind();
			$messages = array();
			while ( $generated_queue->get_queue()->valid() ) {
				$message = $generated_queue->get_queue()->current();
				if ( $message instanceof EE_Message ) {
					//set properties that might be expected by add-ons (backward compat)
					$message->content = $message->content();
					$message->template_pack = $message->get_template_pack();
					$message->template_variation = $message->get_template_pack_variation();
					$messages[] = $message;
				}
				$generated_queue->get_queue()->next();
			}
			return $messages;
		}
		return $error ? false
			: true; //yeah backwards eh?  Really what we're returning is if there is a total success for all the messages or not.  We'll modify this once we get message recording in place.
	}



	/**
	 * @deprecated 4.9.0
	 * @param  string $type      This should correspond with a valid message type
	 * @param  string $context   This should correspond with a valid context for the message type
	 * @param  string $messenger This should correspond with a valid messenger.
	 * @param bool    $send      true we will do a test send using the messenger delivery, false we just do a regular preview
	 * @return string          The body of the message.
	 */
	public function preview_message( $type, $context, $messenger, $send = false ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return EED_Messages::preview_message( $type, $context, $messenger, $send );
	}



	/**
	 * @since      4.5.0
	 * @deprecated 4.9.0   Moved to EED_Messages Module
	 * @param string   $messenger    a string matching a valid active messenger in the system
	 * @param string   $message_type Although it seems contrary to the name of the method, a message type name is still required to send along the message type to the messenger because this is used for determining what specific variations might be loaded for the generated message.
	 * @param stdClass $message      a stdClass object in the format expected by the messenger.
	 *
	 * @return bool          success or fail.
	 */
	public function send_message_with_messenger_only( $messenger, $message_type, $message ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		//setup for sending to new method.
		/** @type EE_Messages_Queue $queue */
		$queue = EE_Registry::instance()->load_lib( 'Messages_Queue' );
		//make sure we have a proper message object
		if ( ! $message instanceof EE_Message && is_object( $message ) && isset( $message->content ) ) {
			$msg = EE_Message_Factory::create(
				array(
					'MSG_messenger'    => $messenger,
					'MSG_message_type' => $message_type,
					'MSG_content'      => $message->content,
					'MSG_subject'      => $message->subject
				)
			);
		} else {
			$msg = $message;
		}
		if ( ! $msg instanceof EE_Message ) {
			return false;
		}
		//make sure any content in a content property (if not empty) is set on the MSG_content.
		if ( ! empty( $msg->content ) ) {
			$msg->set( 'MSG_content', $msg->content );
		}
		$queue->add( $msg );
		return EED_Messages::send_message_with_messenger_only( $messenger, $message_type, $queue );
	}



	/**
	 * @deprecated 4.9.0
	 * @param         $messenger
	 * @param  string $message_type message type that the templates are being created for
	 * @param int     $GRP_ID
	 * @param bool    $is_global
	 * @return array|object if creation is successful then we return an array of info, otherwise an error_object is returned.
	 * @throws \EE_Error
	 */
	public function create_new_templates( $messenger, $message_type, $GRP_ID = 0, $is_global = false ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		EE_Registry::instance()->load_helper( 'MSG_Template' );
		return EEH_MSG_Template::create_new_templates( $messenger, $message_type, $GRP_ID, $is_global );
	}



	/**
	 * @deprecated 4.9.0
	 * @param  string $messenger_name    name of EE_messenger
	 * @param  string $message_type_name name of EE_message_type
	 * @return array
	 */
	public function get_fields( $messenger_name, $message_type_name ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		EE_Registry::instance()->load_helper( 'MSG_Template' );
		return EEH_MSG_Template::get_fields( $messenger_name, $message_type_name );
	}



	/**
	 * @deprecated 4.9.0
	 * @access     public
	 * @param string $type                we can indicate just returning installed message types
	 *                                    or messengers (or both) via this parameter.
	 * @param bool   $skip_cache          if true then we skip the cache and retrieve via files.
	 * @return array                    multidimensional array of messenger and message_type objects
	 *                                    (messengers index, and message_type index);
	 */
	public function get_installed( $type = 'all', $skip_cache = false ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		if ( $skip_cache ) {
			$this->_message_resource_manager->reset_active_messengers_and_message_types();
		}
		switch ( $type ) {
			case 'messengers' :
				return array(
					'messenger' => $this->_message_resource_manager->installed_messengers(),
				);
				break;
			case 'message_types' :
				return array(
					'message_type' => $this->_message_resource_manager->installed_message_types(),
				);
				break;
			case 'all' :
			default :
				return array(
					'messenger'    => $this->_message_resource_manager->installed_messengers(),
					'message_type' => $this->_message_resource_manager->installed_message_types(),
				);
				break;
		}
	}



	/**
	 * @deprecated 4.9.0
	 * @return \EE_messenger[]
	 */
	public function get_active_messengers() {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->active_messengers();
	}



	/**
	 * @deprecated 4.9.0
	 * @return array array of message_type references (string)
	 */
	public function get_active_message_types() {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->list_of_active_message_types();
	}



	/**
	 * @deprecated 4.9.0
	 * @return EE_message_type[]
	 */
	public function get_active_message_type_objects() {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->get_active_message_type_objects();
	}



	/**
	 * @deprecated 4.9.0
	 * @since      4.5.0
	 * @param string $messenger The messenger being checked
	 * @return EE_message_type[]    (or empty array if none present)
	 */
	public function get_active_message_types_per_messenger( $messenger ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->get_active_message_types_for_messenger( $messenger );
	}



	/**
	 * @deprecated 4.9.0
	 * @param string $messenger    The string should correspond to the messenger (message types are
	 * @param string $message_type The string should correspond to a message type.
	 * @return EE_Message_Type|null
	 */
	public function get_active_message_type( $messenger, $message_type ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->get_active_message_type_for_messenger( $messenger, $message_type );
	}



	/**
	 * @deprecated 4.9.0
	 * @return array|\EE_message_type[]
	 */
	public function get_installed_message_types() {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->installed_message_types();
	}



	/**
	 * @deprecated 4.9.0
	 * @return array
	 */
	public function get_installed_messengers() {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->installed_messengers();
	}



	/**
	 * @deprecated 4.9.0
	 * @param   bool $slugs_only Whether to return an array of just slugs and labels (true) or all contexts indexed by message type.
	 * @return array
	 */
	public function get_all_contexts( $slugs_only = true ) {
		// EE_messages has been deprecated
		$this->_class_is_deprecated( __FUNCTION__ );
		return $this->_message_resource_manager->get_all_contexts( $slugs_only );
	}



}
//end EE_messages class