<?php

function wpmenucache_get_transient_key( $args , $check_select = false ){

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
	// $cache_type = wpmenucache_op( 'cache_type' );
	// if( $cache_type == 'select_global' || $cache_type == 'select_only' ){		
	// 	$key.= wpmenucache_get_individual_page_key();

	//}

	//If individual caching is a possibility - select_global, select_only, individual
	if( wpmenucache_cache_individually() ){

		$page_key = wpmenucache_get_individual_page_key( $check_select );
		
		//If there is an individual key here
		if( $page_key ){
			$key.= '|'.$page_key;
		}
		//If there's no individual key, and we're select only, return false
		else if( wpmenucache_op( 'cache_type' ) == 'select_only' ){
			return false;
		}
	}	

	$key = WPMENUCACHE_TRANSIENT_PREFIX.$key;
	$key = substr( $key , 0 , 45 );	//45 is the max length of a transient
	
	return $key;
}

function wpmenucache_cache_state(){
	$cache_type = wpmenucache_op( 'cache_type' );

	switch( $cache_type ){
		case 'none':
			return 0;
		
		case 'global':
		case 'all_individual':
			return 1;

		case 'select_only':
		case 'select_global':
			return 2;

		default:
			return 0;
	}
}

function wpmenucache_cache_individually(){

	$cache_type = wpmenucache_op( 'cache_type' );

	switch( $cache_type ){
		case 'global':
		case 'none':
			return false;
		case 'all_individual':
		case 'select_only':
		case 'select_global':
			return true;
	}
}

/*
function wpmenucache_cache_individually(){

	$cache_type = wpmenucache_op( 'cache_type' );

	switch( $cache_type ){

		case 'global':
			return false;
		case 'all_individual':
			return true;

		case 'select_only':
		case 'select_global':
			return wpmenucache_matches_select();

		case 'none':
			return false;
	}
}
*/



function wpmenucache_get_individual_page_key( $check_select = false ){

	$select_pages = $select_tax = false;

	if( $check_select ){
		//echo 'selects:';
		$select_pages = wpmenucache_op( 'cache_select_page' );
		if( $select_pages === '' ) $select_pages = array();
		//uberp( $select_pages );

		$select_tax = wpmenucache_op( 'cache_select_taxonomy' );
		if( $select_tax === '' ) $select_tax = array();
		//uberp( $select_tax );
	}
//echo '//'.get_post_type().'//';

	$pid = '';
	if( is_home() ){
		if( !$check_select || 
			isset( $select_pages['_home'] ) ){
			return 'blog';
		}
	}
	if( is_front_page() ){
		if( !$check_select || 
			isset( $select_pages['_front'] ) ){
			return 'front';
		}
	}

	if( is_singular() ){
		$id = get_the_id();

		if( !$check_select || 
			isset( $select_pages[$id] ) ||
			isset( $select_pages['_all_'.get_post_type()] ) ){
			return 'p'.$id;
		}
	}
	
	if( is_archive() ){
		if( is_category() || is_tag() || is_tax() ){
			$q = get_queried_object();
			$term_id = $q->term_id;
			if( !$check_select || 
				isset( $select_tax[$term_id] ) ||
				isset( $select_tax['_all_'.$q->taxonomy] ) ){
				return substr( $q->taxonomy , 0, 3 ).'_'.$term_id;
			}
		}
		else if( is_author() ){
			if( !$check_select ){
				return 'a'.get_the_author_meta( "ID" );
			}
		}
		//Dates
		else if( is_day() ){
			if( !$check_select ){
				return get_the_date( 'Y_m_d' );
			}
		}
		else if( is_month() ){
			if( !$check_select ){
				return get_the_date( 'Y_m' );
			}
		}
		else if( is_year() ){
			if( !$check_select ){
				return get_the_date( 'Y' );
			}
		}
		//Custom Post Type
		else if( true ){
			if( !$check_select || 
				isset( $select_tax[get_post_type()] ) ){
				return get_post_type();
			}
		}
	}
	else if( is_search() ){
		if( !$check_select ||
			isset( $select_pages['_search'] ) ){
			$pid = 'search';
		}
	}
	else if( is_404() ){
		if( !$check_select ||
			isset( $select_pages['_404'] )){
			$pid = '404';
		}
	}

	return $pid;
}

add_filter( 'pre_wp_nav_menu' , 'wpmenucache_get_cached_menu' , 10 , 2 );
function wpmenucache_get_cached_menu( $nav_menu , $args ){

	$check_select = false;
	switch( wpmenucache_cache_state() ){
		case 0: return null;
		case 1:	break;
		case 2:	$check_select = true;
				break;
	}

	//Ignore menu segments
	if( isset( $args->uber_segment ) ){
		return $nav_menu;
	}

	//up( get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() ) );
	
	//Get the key for this menu
	$key = wpmenucache_get_transient_key( $args , $check_select );
	//echo $key;
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

	$check_select = false;
	switch( wpmenucache_cache_state() ){
		case 0: return null;				//No cache
		case 1:	break;						//Global cache only
		case 2:	$check_select = true;	//Possible individual cache
				break;
	}

	//Ignore menu segments
	if( isset( $args->uber_segment ) ){
		return $nav_menu;
	}

	//Get the key for this menu
	$key = wpmenucache_get_transient_key( $args , $check_select );
	//No key?  Just return what was passed without caching
	if( !$key ) return $nav_menu;

	$expiration = wpmenucache_op( 'transient_expiration' );
	if( !is_numeric( $expiration ) ) $expiration = 0;

	//Cache the menu / store transient
	set_transient( $key , $nav_menu , $expiration );

	/*
	//Add key to transients key list
	$keys = get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
	$keys[$key] = 'cached';

	update_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , $keys );
	*/

	//Return what was passed
	return $nav_menu;
}


function wpmenucache_update_menu( $menu_id ){
	if( wpmenucache_op( 'clear_transients_on_save' ) == 'on' ){
		wpmenucache_clear_transients();
	}
} 
add_action( 'wp_update_nav_menu', 'wpmenucache_update_menu' , 10 , 1 );
add_action( 'ubermenu_after_menu_item_save' , 'wpmenucache_update_menu' , 20 , 1 );

function wpmenucache_clear_transients(){
	
	do_action( 'wpmenucache-clear-transients' );

	global $wpdb;
	$query = "DELETE FROM {$wpdb->prefix}options ".
				"WHERE option_name LIKE ('\_transient\_".WPMENUCACHE_TRANSIENT_PREFIX."%') OR ".
						"option_name LIKE ('\_transient\_timeout\_".WPMENUCACHE_TRANSIENT_PREFIX."%')";
	//echo $query;

	$wpdb->query( $query );

	/*
	//Clear all the transients
	$keys = get_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
	//up( $keys );
	foreach( $keys as $key => $cached ){
		//echo 'delete ' . $key . '<br/>';
		delete_transient( $key );
	}
	//Reset Keys
	update_option( WPMENUCACHE_TRANSIENTS_KEYS_OP , array() );
	*/
}
