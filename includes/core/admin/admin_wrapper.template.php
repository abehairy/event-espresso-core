<div class="wrap">

	<div id="icon-options-event" class="icon32"></div>	
		
	<h2><?php esc_attr_e( 'Event Espresso', 'event_espresso' );?>&nbsp;-&nbsp;<?php esc_attr_e( $admin_page_title, 'event_espresso' ); ?></h2>

	<h2 class="nav-tab-wrapper">
		<?php foreach ( $nav_tabs as $nav_tab ) : ?>
			<a class="nav-tab<?php echo $nav_tab['css_class'];?>" href="<?php echo $nav_tab['url'];?>"><?php echo $nav_tab['link_text'];?></a>
		<?php endforeach; ?>
	</h2>

	<?php echo $admin_page_content; ?>

</div>
