<?php
/**
 * Appel des TPE 
 */
require_once( dirname(__FILE__).'/bankwire/bankwire.php' );
require_once( dirname(__FILE__).'/cheque/cheque.php' );
require_once( dirname(__FILE__).'/CMCIC/cmcic.php' );

/**
 * Récupération de la liste des plateformes de paiement actives pour un formulaire
 */
function mktzr_forms_cart_get_gateways( $form ){
	global $mktzr_forms;
	// Bypass
	if( ! $_form = $mktzr_forms->forms->get( $form ) )
	return;
	
	if( $gateways = $mktzr_forms->addons->get_form_option('gateway', 'shopping-cart', $_form) )
		return array_keys($gateways);
	
	
}
