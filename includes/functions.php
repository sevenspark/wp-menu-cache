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

		$key = "|$menu_id|$theme_location";
	}

	//Cache per page
	if( wpmenucache_op( 'cache_per_page' ) == 'on' ){
		$pid = '';
		if( is_singular() ){
			$pid = 'p'.get_the_id();
		}
		else if( is_home() ){
			$pid = 'blog';
		}
		else if( is_front_page() ){
			$pid = 'front';
		}
		else if( is_archive() ){
			if( is_category() || is_tag() || is_tax() ){
				$q = get_queried_object();
				$pid = substr( $q->taxonomy , 0, 3 ).'_'.$q->term_id;
			}
			else if( is_author() ){
				$pid = 'a'.get_the_author_meta( "ID" );
			}
			//Dates
			else if( is_day() ){
				$pid = get_the_date( 'Y_m_d' );
			}
			else if( is_month() ){
				$pid = get_the_date( 'Y_m' );
			}
			else if( is_year() ){
				$pid = get_the_date( 'Y' );
			}
			//Custom Post Type
			else if( true ){
				$pid = get_post_type();
			}
		}
		else if( is_search() ){
			$pid = 'search';
		}
		else if( is_404() ){
			$pid = '404';
		}

		$key.= "|$pid";

	}

	

	$key = WPMENUCACHE_TRANSIENT_PREFIX.$key;

	$key = substr( $key , 0 , 45 );	//45 is the max length of a transient
	
	return $key;
}

add_filter( 'pre_wp_nav_menu' , 'wpmenucache_get_cached_menu' , 10 , 2 );
function wpmenucache_get_cached_menu( $nav_menu , $args ){

	//Ignore menu segments
	if( isset( $args->uber_segment ) ){
		return $nav_menu;
	}

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

	//Ignore menu segments
	if( isset( $args->uber_segment ) ){
		return $nav_menu;
	}

	//Get the key for this menu
	$key = wpmenucache_get_transient_key( $args );
	//No key?  Just return what was passed without caching
	if( !$key ) return $nav_menu;

	$expiration = wpmenucache_op( 'transient_expiration' );
	if( !is_numeric( $expiration ) ) $expiration = 0;

	//Cache the menu / store transient
	set_transient( $key , $nav_menu , $expiration );

	//Add key to transients key list
	$keys = get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
	$keys[$key] = 'cached';

	update_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , $keys );
	
	//Return what was passed
	return $nav_menu;
}


function wpmenucache_update_menu( $menu_id ){
	if( wpmenucache_op( 'clear_transients_on_save' ) == 'on' ){
		wpmenucache_clear_transients();
	}
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