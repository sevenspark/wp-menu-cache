<?php

/**
 * Register the plugin page
 */
function wpmenucache_admin_menu() {
	add_submenu_page(
		'options-general.php',
		'WP Menu Cache',
		'WP Menu Cache',
		'manage_options',
		'wpmenucache-settings',
		'wpmenucache_settings_panel'
	);
	//add_options_page( 'Settings API', 'Settings API', 'manage_options', 'settings_api_test', 'shiftnav_plugin_page' );
}
 
add_action( 'admin_menu', 'wpmenucache_admin_menu' );


/**
 * Display the plugin settings options page
 */
function wpmenucache_settings_panel() {
	
	$settings_api = _WPMENUCACHE()->settings_api();
 
	?>

	<div class="wrap">
	
	<?php settings_errors(); ?>

	<?php /*<div class="shiftnav-settings-links">
		<?php do_action( 'shiftnav_settings_before_title' ); ?>
	</div>*/ ?>

	<h2>WP Menu Cache </h2>

	<?php

	do_action( 'wpmenucache_settings_before' );	
 
	$settings_api->show_navigation();
	$settings_api->show_forms();

	do_action( 'wpmenucache_settings_after' );
 
	?>

	</div>

	<?php
}

/**
 * Registers settings section and fields
 */
function wpmenucache_admin_init() {

	$prefix = WPMENUCACHE_PREFIX;
 
 	$sections = wpmenucache_get_settings_sections();
 	$fields = wpmenucache_get_settings_fields();

 	//set up defaults so they are accessible
	//_SHIFTNAV()->set_defaults( $fields );

	
	$settings_api = _WPMENUCACHE()->settings_api();

	//set sections and fields
	$settings_api->set_sections( $sections );
	$settings_api->set_fields( $fields );

	//initialize them
	$settings_api->admin_init();

}
add_action( 'admin_init', 'wpmenucache_admin_init' );