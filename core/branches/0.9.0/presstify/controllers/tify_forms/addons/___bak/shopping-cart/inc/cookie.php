<?php
/**
 * Création du cookie
 * 
 * @param object $cart Objet panier
 */
function mktzr_forms_cart_create_cookie( $cart ){	
	setcookie( "mktzr_forms_shopping-cart", $cart->salt, 3 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
}

/**
 * Récupération de la valeur du cookie
 *
 * @return string Valeur du cookie
 */
function mktzr_forms_cart_get_cookie( ){
	if( isset( $_COOKIE[ "mktzr_forms_shopping-cart" ] ) )
		return $_COOKIE[ "mktzr_forms_shopping-cart" ];
}

/**
 * Requête de suppression du cookie
 */
function mktzr_forms_cart_kill_cart_cookie(){
	if( ! isset( $_REQUEST['killcookie'] ) )
		return;
	
	mktzr_forms_cart_remove_from_cookie( );	
	setcookie( "mktzr_forms_shopping-cart", ' ', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	unset( $_COOKIE["mktzr_forms_shopping-cart"] );
	
	$location =  add_query_arg( uniqid(), remove_query_arg( 'killcookie', wp_get_referer() ) );
	wp_redirect(  wp_get_referer() );
	exit;
}
add_action( 'wp', 'mktzr_forms_cart_kill_cart_cookie', 11 );


function mktzr_form_cart_no_cache() {
	// Bypass
	if( ! isset( $_REQUEST['killcookie'] ) )
		return;
 
	echo '<meta http-Equiv="cache-control" content="no-cache">';
	echo '<meta http-Equiv="pragma" content="no-cache">';
	echo '<meta http-Equiv="expires" content="0">';
}
add_filter( 'wp_head' , 'mktzr_form_cart_no_cache' );