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
			$time_start = microtime();
			wpmenucache_clear_transients();
			$time_end = microtime();
			$time = round( $time_end - $time_start , 4 );

			$notice =  "Menu cache cleared in $time seconds";
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

	<script type="text/javascript">
		jQuery( 'document' ).ready( function($){
			$( '.wpmc-op-cache_type' ).on( 'change' , 'input[type="radio"]' , function(){
				update_cache_type_ops( $(this) );
			});

			var $page = $( '.wpmc-op-cache_select_page' ).closest( 'tr' );
			var $tax = $('.wpmc-op-cache_select_taxonomy' ).closest( 'tr' );
			$selections = $page.add( $tax );

			//console.log( $selections.size() );

			function update_cache_type_ops( $selected ){
				console.log( $selected.val() );
				if( $selected.val() == 'select_global' ||
					$selected.val() == 'select_only' ){
						$selections.fadeIn();
				}
				else{
					$selections.fadeOut();
				}
			}

			update_cache_type_ops( $( '.wpmc-op-cache_type input[type="radio"]:checked' ) );
		});
	</script>

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
				'name' => 'cache_type',
				'label' => __( 'Cache Strategy', 'wpmenucache' ),
				'desc' => __( '', 'shiftnav' ),
				'options'	=> array(
					'group'	=> array(
						'select_global'	=> array(
							'name' 		=> __( 'Selected Pages with Global Fallback', 'wpmenucache' ),
							'desc'		=> __( 'Select the pages below to cache individually.  Unselected pages will use the global cache.' , 'ubermenu' ),
						),
						'select_only'	=> array(
							'name'		=> __( 'Selected Pages Only', 'wpmenucache' ),
							'desc'		=> __( 'Only cache the pages individually selected below.' , 'ubermenu' ),
						),
						'global'		=> array(
							'name'		=> __( 'Global', 'wpmenucache' ),
							'desc'		=> __( 'Cache each menu globally (no dynamic items per page like current menu items)' , 'ubermenu' ),
						),
						'all_individual'=> array(
							'name'		=> __( 'All Pages Individually', 'wpmenucache' ),
							'desc'		=> __( 'Cache each page independently (will result in lots of transients in the databse if you have many pages)' , 'ubermenu' ),
						),						
						'none'			=> array(
							'name'		=> __( 'None', 'wpmenucache' ),
							'desc'		=> __( 'Disable caching' , 'ubermenu' ),
						),

					),
				),
				'type' => 'radio_advanced',
				'default' => 'select_global'
			),

			array(
				'name' => 'cache_select_page',
				'label' => __( 'Select Page Cache', 'wpmenucache' ),
				'desc' => __( 'Cache the menus for these pages individually.', 'shiftnav' ),
				'type' => 'multicheck_groups',
				'options'	=> 'wpmenucache_select_page_ops'
			),

			array(
				'name' => 'cache_select_taxonomy',
				'label' => __( 'Select Taxonomy Cache', 'wpmenucache' ),
				'desc' => __( 'Cache the menus for these pages individually.', 'shiftnav' ),
				'type' => 'multicheck_groups',
				'options'	=> 'wpmenucache_select_tax_ops'
			),

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
				'desc' => __( 'Time in seconds until the menu will refresh.  To make this take effect immediately, don\'t forget to clear the existing cache.  A value of <code>0</code> means the cache will never expire unless cleared.', 'shiftnav' ),
				'type' => 'text',
				'default' => 0,
			),

			array(
				'name' => 'cache_per_page',
				'label' => __( 'Cache Menu for each Page', 'wpmenucache' ),
				'desc' => __( 'Cache the menu independently for each page.  This is important if you want things like current menu item highlighting to work.', 'shiftnav' ),
				'type' => 'checkbox',
				'default' => 'on'
			),

			array(
				'name'	=> 'clear_transients',
				'label'	=> __( 'Clear Cache' , 'wpmenucache' ),
				'desc'	=> '<a class="button button-primary" href="'.admin_url('options-general.php?page=wpmenucache-settings&do=clear_cache').'">'.__( 'Clear Cache' , 'ubermenu' ).'</a><br/><p>'.__( 'Clear all menu transients.', 'ubermenu' ).'</p>',
				'type'	=> 'html',
			),
		)
	);
//wpmenucache_clear_transients();
	return $fields;

}

function wpmenucache_select_tax_ops(){
	$ops = array();
	 
	$taxonomies = get_taxonomies( array( 
			'public' => true , 
			//'hierarchical'	=> true,
			//'publicly_queryable' => true 
		) , 
		'objects' );

	$ops['quick'] = array(
	 	'name'	=> 'Taxonomy Groups',
	 	'ops'	=> array()
	 );
	
	foreach( $taxonomies as $slug => $tax ){
		$ops['quick']['ops']['_all_'.$slug] = 'All '.$tax->label;
	}


	$taxonomies = get_taxonomies( array( 
			'public' => true , 
			'hierarchical'	=> true,
			//'publicly_queryable' => true 
		) , 
		'objects' );

	//uberp( $types );

	foreach( $taxonomies as $slug => $tax ){

		$terms = get_terms( $slug, array(
	 		'number'  => 200,
	 		'orderby' => 'name',
	 	) );

	 	if( count( $terms ) == 0 ) continue;
//uberp( $tax );
		$ops[$slug] = array( 'name' => $tax->label , 'ops' => array() );


	 	foreach( $terms as $t ){
	 		$ops[$slug]['ops'][$t->term_id] = $t->name;
	 	}
		
	}

	return $ops;
}

function wpmenucache_select_page_ops(){

	$ops = array();
	 
	$types = get_post_types( array( 
			'public' => true , 
			//'publicly_queryable' => true 
		) , 
		'objects' );

	$ops['special'] = array(
	 	'name'	=> 'Special',
	 	'ops'	=> array()
	 );
	
	$ops['special']['ops']['_front'] = 'Front Page';
	$ops['special']['ops']['_home'] = 'Home (Blog) Page';
	$ops['special']['ops']['_search'] = 'Search Results';
	$ops['special']['ops']['_404'] = '404';

	foreach( $types as $slug => $type ){
		$ops['special']['ops']['_all_'.$slug] = 'All '.$type->label;	
	}

	//uberp( $types );

	foreach( $types as $slug => $type ){
//uberp( $type );
		if( $slug == 'attachment' ) continue;

		$posts = get_posts( array(
	 		'post_type'	=> $slug,
	 		'posts_per_page' => -1,
	 		'show_in_nav_menus'	=> true,
	 		'orderby' => 'name',
	 	) );

	 	if( count( $posts ) == 0 ) continue;

		$ops[$slug] = array( 'name' => $type->label , 'ops' => array() );

	 	foreach( $posts as $p ){
	 		$ops[$slug]['ops'][$p->ID] = $p->post_title;
	 	}
		
	}

	return $ops;
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