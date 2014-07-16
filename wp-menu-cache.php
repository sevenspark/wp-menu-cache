<?php
/*
Plugin Name: WP Menu Cache
Plugin URI: http://sevenspark.com
Description: Speed up menu generation by caching your menu output to minimze your database queries
Author: Chris Mavricos, SevenSpark
Author URI: http://sevenspark.com
Version: 0.1
*/

/* Copyright 2014 Chris Mavricos, SevenSpark */

if ( !defined( 'ABSPATH' ) ) exit;

//SETTINGS PANEL
//  Clear Transients on Menu Save (on)
//  Clear Transients button
//  Don't cache (multicheck of menu IDs) - note this would need to be done by [slug/ID] (pick one and determine proper identifier in check)
//  Cache expiration

//TODO: clear transients when saving UberMenu settings?



//wpmenucache_clear_transients();
//delete_transient( 'menucache_uber[main][primary]' );
//delete_transient( 'menucache_uber[main][primary]' );
//return;


if ( !class_exists( 'WPMenuCache' ) ) :

final class WPMenuCache {
	/** Singleton *************************************************************/

	private static $instance;
	private static $settings_api;
	private static $settings_defaults;

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WPMenuCache;
			self::$instance->setup_constants();
			self::$instance->includes();
		}
		return self::$instance;
	}

	/**
	 * Setup plugin constants
	 *
	 * @since 1.0
	 * @access private
	 * @uses plugin_dir_path() To generate plugin path
	 * @uses plugin_dir_url() To generate plugin url
	 */
	private function setup_constants() {
		// Plugin version

		if( ! defined( 'WPMENUCACHE_VERSION' ) )
			define( 'WPMENUCACHE_VERSION', '.1' );

		if( ! defined( 'WPMENUCACHE_BASENAME' ) )
			define( 'WPMENUCACHE_BASENAME' , plugin_basename( __FILE__ ) );

		// Plugin Folder URL
		if( ! defined( 'WPMENUCACHE_URL' ) )
			define( 'WPMENUCACHE_URL', plugin_dir_url( __FILE__ ) );

		// Plugin Folder Path
		if( ! defined( 'WPMENUCACHE_DIR' ) )
			define( 'WPMENUCACHE_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Root File
		if( ! defined( 'WPMENUCACHE_FILE' ) )
			define( 'WPMENUCACHE_FILE', __FILE__ );


		define( 'WPMENUCACHE_PREFIX' , 'wpmenucache_' );
		define( 'WPMENUCACHE_TRANSIENT_PREFIX' , 'navcache_' );
		define( 'WPMENUCACHE_TRANSIENTS_KEYS_OP' , 'wpmenucache_keys' );
	}

	private function includes() {
		
		require_once WPMENUCACHE_DIR . 'includes/functions.php';
		require_once WPMENUCACHE_DIR . 'admin/admin.php';

	}

	public function settings_api(){
		if( self::$settings_api == null ){
			self::$settings_api = new WPMenuCache_Settings_API();
		}
		return self::$settings_api;
	}


	
	public function set_defaults( $fields ){

		if( self::$settings_defaults == null ) self::$settings_defaults = array();

		foreach( $fields as $section_id => $ops ){

			self::$settings_defaults[$section_id] = array();

			foreach( $ops as $op ){
				self::$settings_defaults[$section_id][$op['name']] = isset( $op['default'] ) ? $op['default'] : '';
			}
		}

		//shiftp( $this->settings_defaults );

	}

	function get_defaults( $section = null ){
		if( self::$settings_defaults == null ) self::set_defaults( wpmenucache_get_settings_fields() );

		if( $section != null && isset( self::$settings_defaults[$section] ) ) return self::$settings_defaults[$section];
		
		return self::$settings_defaults;
	}
	

	function get_default( $option , $section ){

		if( self::$settings_defaults == null ) self::set_defaults( wpmenucache_get_settings_fields() );

		$default = '';

		//echo "[[$section|$option]]  ";
		if( isset( self::$settings_defaults[$section] ) && isset( self::$settings_defaults[$section][$option] ) ){
			$default = self::$settings_defaults[$section][$option];
		}
		return $default;
	}
	

}
endif;

if( !function_exists( '_WPMENUCACHE' ) ){
	function _WPMENUCACHE() {
		return WPMenuCache::instance();
	}
	_WPMENUCACHE();
}
