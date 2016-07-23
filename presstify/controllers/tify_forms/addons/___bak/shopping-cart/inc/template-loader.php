<?php
/**
 * Modification des requêtes
 */
function mktzr_forms_cart_pre_get_posts( &$query ){
	if(  ! isset( $_REQUEST['mktzr_forms_cart'] ) )
		return;
	switch( $_REQUEST['mktzr_forms_cart'] ) :
		case 'display_summary' :
			add_action( 'template_redirect', 'mktzr_forms_cart_display_summary' );
			break;
		case 'payment_error' :
			add_action( 'template_redirect', 'mktzr_forms_cart_payment_error' );
			break;
		case 'payment_success' :
			if( mktzr_check_cart_token( ) ):
				// mise à jour de la table cart ou creation de l'order$cart = mktzr_forms_cart_get_from_cookie();
				$cart = mktzr_forms_cart_get_from_cookie();				
				mktzr_forms_cart_update_status_to_paid( $cart );
				if( isset( $_COOKIE[ "mktzr_forms_shopping-cart" ] ) ) :
					setcookie( "mktzr_forms_shopping-cart", ' ', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
					unset( $_COOKIE["mktzr_forms_shopping-cart"] );
				endif;
			endif;			
				add_action( 'template_redirect', 'mktzr_forms_cart_payment_success' );			
			break;
		case 'get_pdf' :
			$user = wp_get_current_user();
			if( isset( $_REQUEST['commande'] ) )
				mktzr_forms_cart_get_pdf_summary( $user->ID, $_REQUEST['commande'] );
			break;
		default :
			add_action( 'template_redirect', 'mktzr_forms_cart_'.$_REQUEST['mktzr_forms_cart'] );
			break;
	endswitch;
}
add_action( 'pre_get_posts', 'mktzr_forms_cart_pre_get_posts' );

/**
 * Affichage du récapitulatif panier
 */
function mktzr_forms_cart_display_summary(){
	if( ! $cart = mktzr_forms_cart_get_from_cookie() )
		wp_die( __( 'Votre panier est vide', 'mktzr_forms' ) );
	
	$step = isset( $_REQUEST['step'] ) ? $_REQUEST['step'] : 1 ;
	
	switch( $step ) :
		// Récapitulatif panier
		default :
		case 1 :
			mktzr_forms_display( $cart->form_id, array( 'display' => 'summary' ) );
			break;
		// Authentification utilisateur
		case 2 :
			if( ! is_user_logged_in() ) :
				wp_login_form();
			else :
				mktzr_forms_display( 7, array( 'display' => 'summary' ) );
			endif;
			break;
		// Paiement
		case 3 :
			mktzr_forms_cart_cmcic_display_form( );
			break;
	endswitch;
		echo mktzr_forms_cart_display_summary_navigation( $step, $cart->form_id );
	exit;
}

/**
 * Affichage des erreurs de paiement
 */
function mktzr_forms_cart_payment_error(){
?>
	<h3><?php _e( 'Paiement en erreur', 'mktzr_forms' );?></h3>
<?php
	exit;	
}
 
/**
 * Affichage du paiement réussi
 */ 
function mktzr_forms_cart_payment_success(){
?>
	<h3><?php _e( 'Paiement effectué avec succès', 'mktzr_forms' );?></h3>
<?php
	exit;
}
  
/**
 * 
 */
function mktzr_forms_cart_display_summary_navigation( $step = 1, $form = null ){
	global $mktzr_forms; 
	
	$output  = "";
	$output  = "<div class=\"mktzr_forms_cart_navigation\">";
	if( $step == 1 )
		$output .= "<a class=\"modify-cart\" href=\"\">". __( 'Modifier', 'mktzr_forms' ) ."</a>"; 
	if( $step > 1 )
		$output .= "<a class=\"prev-step\" href=\"". add_query_arg( 'step', $step-1, $mktzr_forms->addons->get_form_option( 'summary_location', 'shopping-cart', $form ) )."\">".__( 'Précedent', 'mktzr_forms' )."</a>";
	if( $step < 3 )
		$output .= "<a class=\"next-step\" href=\"". add_query_arg( 'step', $step+1, $mktzr_forms->addons->get_form_option( 'summary_location', 'shopping-cart', $form ) )."\">".__( 'Suivant', 'mktzr_forms' )."</a>";
	$output .= "</div>";
	
	return $output;
}
