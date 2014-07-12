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


function wpmenucache_do_clear_cache(){

	if( isset( $_GET['page'] ) && $_GET['page'] == 'wpmenucache-settings' ){
		
		if( !current_user_can( 'manage_options' ) ){
			die( 'You need to be an admin to do that' );
		}

		if( isset( $_GET['do'] ) && $_GET['do'] == 'clear_cache' ){
			wpmenucache_clear_transients();
			$notice =  "Menu cache cleared";
			add_settings_error( 'clear_cache' , 'clear-cache' , $notice , 'updated' );
		}

	}

}
add_action( 'admin_init' , 'wpmenucache_do_clear_cache' , 100 );

/**
 * Display the plugin settings options page
 */
function wpmenucache_settings_panel() {
	
	$settings_api = _WPMENUCACHE()->settings_api();
 
	?>

	<div class="wrap">
	
	<?php //settings_errors(); ?>

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

function wpmenucache_get_settings_sections(){

	$prefix = WPMENUCACHE_PREFIX;

	$sections = array(
		/*array(
			'id' => $prefix.'basics',
			'title' => __( 'Basic Configuration', 'shiftnav' )
		),*/
		array(
			'id' => $prefix.'cache',
			'title' => __( 'Cache Settings', 'shiftnav' )
		),
	);

	$sections = apply_filters( 'wpmenucache_settings_panel_sections' , $sections );

	return $sections;

}


function wpmenucache_get_settings_fields(){

	$prefix = WPMENUCACHE_PREFIX;

	$fields = array(		

		$prefix.'cache' => array(

			array(
				'name' => 'clear_transients_on_save',
				'label' => __( 'Clear Cache on Save', 'wpmenucache' ),
				'desc' => __( 'Clear the menu transients when a menu is saved.  If you disable this, you\'ll need to clear the cache manually in order to see any changes.', 'shiftnav' ),
				'type' => 'checkbox',
				'default' => 'on'
			),

			array(
				'name' => 'transient_expiration',
				'label' => __( 'Cache Expiration', 'wpmenucache' ),
				'desc' => __( 'Time in seconds until the menu will refresh.  To make this take effect immediately, don\'t forget to clear the existing cache.', 'shiftnav' ),
				'type' => 'text',
				'default' => 0,
			),

			array(
				'name'	=> 'clear_transients',
				'label'	=> __( 'Clear Cache' , 'wpmenucache' ),
				'desc'	=> '<a class="button button-primary" href="'.admin_url('options-general.php?page=wpmenucache-settings&do=clear_cache').'">'.__( 'Clear Cache' , 'ubermenu' ).'</a><br/><p>'.__( 'Clear all menu transients.', 'ubermenu' ).'</p>',
				'type'	=> 'html',
			),
		)
	);

	return $fields;

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



function wpmenucache_admin_panel_assets( $hook ){

	if( $hook == 'settings_page_wpmenucache-settings' ){
		//wp_enqueue_script( 'wpmenucache' , SHIFTNAV_URL . 'admin/assets/admin.settings.js' );
		wp_enqueue_style( 'wpmenucache-settings-styles' , WPMENUCACHE_URL.'admin/assets/admin.control-panel.css' );
		//wp_enqueue_style( 'shiftnav-font-awesome' , SHIFTNAV_URL.'assets/css/fontawesome/css/font-awesome.min.css' );
	}
}
add_action( 'admin_enqueue_scripts' , 'wpmenucache_admin_panel_assets' );




/**
 * Get the value of a settings field
 *
 * @param string $option settings field name
 * @param string $section the section name this field belongs to
 * @param string $default default text if it's not found
 * @return mixed
 */
function wpmenucache_op( $option, $section = 'cache', $default = null ) {
 
	$options = get_option( WPMENUCACHE_PREFIX.$section );

	if ( isset( $options[$option] ) ) {
		return $options[$option];
	}

	if( $default == null ){
		//$default = _SHIFTNAV()->settings_api()->get_default( $option, SHIFTNAV_PREFIX.$section );
		$default = _WPMENUCACHE()->get_default( $option, WPMENUCACHE_PREFIX.$section );
	}

	return $default;
}

/*
function wpmenucache_get_settings_fields(){

}
*/