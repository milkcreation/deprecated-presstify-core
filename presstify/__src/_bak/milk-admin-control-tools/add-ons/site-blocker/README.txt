/**
 * Example of Customize options management capabilities
 */

/**
 * Define new capability option page access 
 */
function mkbkr_custom_capability(){
	return 'manage_mkbr';
}
add_filter( 'mkbkr_capability', 'mkbkr_custom_capability' );

/**
 * Define page access only for user "mastermilk" 
 */
function mkbkr_custom_map_meta_cap( $caps, $cap, $user_id, $args ){
	if( $cap ==  'manage_mkbr' ) :
		$caps = array();
		if( wp_get_current_user()->data->user_login == 'mastermilk' )
			$caps[] = 'manage_options';
		else 
			$caps[] = 'do_not_allow';
	endif;
	return $caps;		
}
add_filter( 'map_meta_cap', 'mkbkr_custom_map_meta_cap', 10, 4 );