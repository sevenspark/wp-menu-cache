<?php

function wpmenucache_get_transient_key( $args ){

	//TODO: Check for "Ignore Cache" flag by menu ID

	//CACHE ON TWO AXES:
	//- Menu ID
	//- Theme Location
	//
	//Trim to 45 chars

	//Cache based on passed arg 'menu_cache_key'


	$key = '';
	$theme_location = '';
	$menu_id = '';

	//Manual Key
	if( isset( $args->menu_cache_key ) && $args->menu_cache_key ){
		$key = $args->menu_cache_key;
	}
	else{
	
		//Theme Location
		if( isset( $args->theme_location ) && $args->theme_location ){
			$theme_location = $args->theme_location;
		}

		//Menu
		if( isset( $args->menu ) && $args->menu ){
			$menu_id = $args->menu;
		}
		else{
			if( $theme_location && has_nav_menu( $theme_location ) ){
				$menus = get_nav_menu_locations();
				$menu_id = $menus[$theme_location];
			}
		}

		//if neither is set, don't cache
		if( !$theme_location && !$menu_id ){
			return false;
		}

		$key = "[$menu_id][$theme_location]";
	}

	$key = WPMENUCACHE_TRANSIENT_PREFIX.$key;

	$key = substr( $key , 0 , 45 );	//45 is the max length of a transient

	return $key;
}

add_filter( 'pre_wp_nav_menu' , 'wpmenucache_get_cached_menu' , 10 , 2 );
function wpmenucache_get_cached_menu( $nav_menu , $args ){

	//up( get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() ) );
	
	//Get the key for this menu
	$key = wpmenucache_get_transient_key( $args );
	if( !$key ) return null;	//Don't cache
	
	//Get the cached menu
	$menu = get_transient( $key );

	//If the menu is cached, return it
	if( $menu ){
		$menu = 	"\n<!-- Cached by WP Menu Cache [$key] -->\n" .
					$menu .
					"\n<!-- end WP Menu Cache -->\n";
		return $menu;
	}

	//If the menu was not cached, return null so wp_nav_menu can do its thang
	return null;
}

add_filter( 'wp_nav_menu' , 'wpmenucache_cache_menu' , 10 , 2 );
function wpmenucache_cache_menu( $nav_menu , $args ){

	//Get the key for this menu
	$key = wpmenucache_get_transient_key( $args );
	//No key?  Just return what was passed without caching
	if( !$key ) return $nav_menu;		

	//Cache the menu / store transient
	set_transient( $key , $nav_menu );

	//Add key to transients key list
	$keys = get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
	$keys[$key] = 'cached';

	update_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , $keys );
	
	//Return what was passed
	return $nav_menu;
}


function wpmenucache_update_menu( $menu_id ){
	wpmenucache_clear_transients();
}
 
add_action( 'wp_update_nav_menu', 'wpmenucache_update_menu' , 10 , 1 );


function wpmenucache_clear_transients(){
	//Clear all the transients
	$keys = get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
	//up( $keys );
	foreach( $keys as $key => $cached ){
		//echo 'delete ' . $key . '<br/>';
		delete_transient( $key );
	}
	//Reset Keys
	update_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
}