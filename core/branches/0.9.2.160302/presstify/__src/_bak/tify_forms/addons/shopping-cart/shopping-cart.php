<?php
/**
 * @name FORMS - CART
 * @description Gestion de panier
 * 
 * @package Milk_Thematzr
 * @subpackage Forms
 */

// Initialisation des controleurs
require_once( dirname(__FILE__).'/inc/admin.php' );
require_once( dirname(__FILE__).'/inc/cart.php' );
require_once( dirname(__FILE__).'/inc/cookie.php' );
require_once( dirname(__FILE__).'/inc/order.php' );
require_once( dirname(__FILE__).'/inc/template-loader.php' );
require_once( dirname(__FILE__).'/gateways/gateways.php');
 
/**
 * Initialisation de l'addon
 */ 
function mktzr_forms_cart_init(){
	global $wpdb, $mktzr_forms;
	
	//Bypass
	if( ! $mktzr_forms )
		return;
		
	// Déclaration des tables
	$wpdb->tables = array_merge( $wpdb->tables, array( 'carts', 'cartmeta' ) );
	$wpdb->set_prefix( $wpdb->base_prefix );		

	// Déclaration des options par défaut de l'add-on	
	$mktzr_forms->addons->set_default_options( 'shopping-cart', mktzr_forms_cart_default_options( ) );
	// Lancement du traitement de paiement 
	mktzr_forms_cart_order_handle();
	// Fonction de traitement des formulaires
	$mktzr_forms->addons->set_handle_callback( 'shopping-cart', 'mktzr_forms_cart_handle' );	
	// Comportement avant l'affichage du formulaire
	$mktzr_forms->addons->set_form_before_display_callback( 'shopping-cart', 'mktzr_forms_cart_before_form_display' );
	// Fonction d'affichage de formulaire
	$mktzr_forms->addons->set_form_output_display_callback( 'shopping-cart', 'mktzr_forms_cart_form_output_display' );
	// Fonction d'affichage champ de formulaire
	$mktzr_forms->addons->set_field_before_display_callback( 'shopping-cart', 'mktzr_forms_cart_field_before_display' );
} 
add_action( 'wp', 'mktzr_forms_cart_init' );

/**
 * Options par défaut de l'addon pour les formulaire
 */
function mktzr_forms_cart_default_options(){
	$defaults = array(
		'ref' => '%1$s-%2$s-%3$s', // Référence du panier stockée en BDD 
		'max_products' => 0,
		'price' => array(
			'html' => '<div id="mksc_price"><span class="mksc_label_before">Total : </span><span class="mksc_price">%1$s</span> <span class="mksc_currency">%2$s</span> <span class="mksc_label_taxe">%3$s</span></div>',
			'value' => 0,
			'currency' => '€',
			'tax' => 0.2,
			'tax_label' => 'TTC'
		),
		'summary_location' => add_query_arg( array( 'mktzr_forms_cart' => 'display_summary' ), site_url() ),
		'gateway' => array(
			'bankwire'
		)
	);
	$options = apply_filters( 'mktzr_forms_shopping-cart_options', array() );
	
	return wp_parse_args( $options, $defaults );
}

/**
 * TRAITEMENT DU FORMULAIRE
 */
function mktzr_forms_cart_handle(){
	global $wpdb, $mktzr_forms;
	
	// Bypass
	if( ! $_form = $mktzr_forms->forms->get_current() )
		return;

	$_submit = $mktzr_forms->handle->parsed_request;
	// Récupération du panier pour le formulaire
	$cart = ( $_cart = mktzr_forms_cart_get_from_cookie() )? $_cart : null;

	//Vérifie si un utilisateur est connecté
	if( $current_user = wp_get_current_user() )
		$user_id = $current_user->ID;
	else
		$user_id = null;
	
	if( ! $price = mktzr_forms_cart_calculate_price( $_submit['form_id'] ) )
		$price = null;
	
	$url_edit = site_url().$_SERVER['REQUEST_URI'];
	// Insertion du panier dans la BDD
	$cartdata = array(
		'form_id' => $_submit['form_id'],
		'client_id' => $user_id,
		'date_create' => current_time( 'mysql' ),
		'date_update' => current_time( 'mysql' ),
		'status' => 'progress',
		'order_reference' => mktzr_forms_cart_make_reference( $_submit['form_id'] ),
		'price' => $price,
		'currency' => mktzr_forms_cart_get_currency( $_submit['form_id'] ),
		'url_edit' => $url_edit
	);
	
	if( isset( $cart ) ) :
		$_method = $mktzr_forms->handle->get_method();
		
		$cartdata['id'] = $cart->id;
		$cartdata['date_create'] = $cart->date_create;
		if( isset( $_method[ "{$_form['prefix']}_price_value" ] ) AND $_method[ "{$_form['prefix']}_price_value" ] == $price)
			$cartdata['status'] = 'calculated';
		$cart_id = mktzr_forms_cart_update( $cartdata );
	else :		
		$cartdata['date_update'] = null;
		$cartdata['salt'] = uniqid();
		$cart_id = mktzr_forms_cart_create( $cartdata );
	endif;
	
	// Récupération du panier
	$cart = mktzr_forms_cart_get( $cart_id );	
	
	// Insertion des données du panier
	foreach( $_submit['fields'] as $slug => $field )
		update_metadata( 'cart', $cart_id, $slug, $field['value'] );
	
	// Création du cookie
	mktzr_forms_cart_create_cookie( $cart );	
	
	// Redirection	
	if( $cart->status == 'calculated' )
		$location = $mktzr_forms->addons->get_form_option( 'summary_location', 'shopping-cart', $_form );
	else
		$location = $_REQUEST['_wp_http_referer'];
	
	wp_redirect( $location );
	exit;
};

/**
 * Modification de l'intitulé du submit si le prix est calculé
 */
function mktzr_forms_cart_before_form_display( &$form ){
	// Bypass
	if( ( ! $cart = mktzr_forms_cart_get_from_cookie( ) ) )
		return;
	if( $cart->form_id != $form['ID'] )
		return;
	
	$html = $form['add-ons']['shopping-cart']['price']['html'];
	
	foreach($form['fields'] as &$field)
		if( $value = get_metadata( 'cart', $cart->id, $field['slug'], true ) )
			$field['value'] = $value;

	$price = mktzr_forms_cart_calculate_price( $form );

	$currency = $form['add-ons']['shopping-cart']['price']['currency'];
	$tax_label = $form['add-ons']['shopping-cart']['price']['tax_label'];
	
	$form['options']['submit']['before'] .= sprintf( $html , $price , $currency ,$tax_label);
	$form['options']['submit']['before'] .= "<input type=\"hidden\" name=\"{$form['prefix']}_price_value\" value=\"$price\" >";
	$form['options']['submit']['label']  = __( 'Déposer ma marque' , 'easymarks' );
}

/**
 * Fonction de court-circuitage d'affichage de formulaire
 */
function mktzr_forms_cart_form_output_display( &$output, $form ){
	global $mktzr_forms;

	// Bypass
	if( ! $cart = mktzr_forms_cart_get_from_cookie( ) )
		return;
	
	// Affichage du court-circuitage de commande si un panier existe
		
	// Bypass : Le panier correspond au produit courant
	if( $cart->form_id == $form['ID'] )
		return;		
	
	$output  = "";
	$output .= "<div class=\"error\">";
	$output .= "<p>". __( 'Vous avez déjà une commande en cours.', 'mktzr_forms' ) ."</p>";
	$output .= "<p>". __( 'Avant d\'effectuer une nouvelle commande, veuillez terminer votre commande ou supprimez la.', 'mktzr_forms' ) . "</p>";
	$output .= "</div>";
	$output .= "<div class=\"mktzr_forms_cart_exist\">";
	$output .= "<a class=\"button-primary\" href=\"".$mktzr_forms->addons->get_form_option( 'summary_location', 'shopping-cart', $form )."\">" . __( 'Continuer ma commande', 'mktzr_forms' ) ."</a>"; 
	$output .= "<a class=\"button-quaternary\" href=\"". add_query_arg( array( 'killcookie' => mktzr_forms_cart_get_cookie( ) ), @$_SERVER['HTTP_REFERER'] ) ."\">". __( 'Supprimer ma commande', 'mktzr_forms' ) ."</a>";
	$output .= "</div>";

}

/**
 * Affichage des informations du panier si le cookie existe et qu'il correspond
 */
function mktzr_forms_cart_field_before_display( &$field ){
	global $mktzr_forms;

	// Bypass
	if( ( ! $cart = mktzr_forms_cart_get_from_cookie( ) ) || ( $cart->form_id != $field['form_id'] )  )
		return;	
	
	if( $value = get_metadata( 'cart', $cart->id, $field['slug'], true ) )
		$field['value'] = $value;
}
/**
 * Génération d'un pdf contenant le récapitulatif d'un cart 
 * @param $cart_id int : identifiant du cart 
 * @param $method string : méthode d'envoi du pdf 
 * ( forçage du téléchargement ('D'), affichage dans le navigateur (''), écriture du contenu dans un fichier sur le serveur ('F') ) || par défaut : affichage dans le navigateur.
 */
function mktzr_forms_cart_get_pdf_summary( $user_id, $cart_id , $method = null ){
	if( ! $cart = mktzr_forms_cart_get( $cart_id ) )
		return;
	if( ! $cart_meta_datas = mktzr_forms_cart_get_meta_datas( $cart_id ) )
		return;
	if( $user_id == 0 )
		return;
	ob_start(); ?>
		<!-- Style du pdf -->
		<style type="text/css">
			p{ color:#888888; }
		</style>
		<!-- Contenu du pdf -->	
			<p>This is Photoshop's version  of Lorem Ipsum. Iis igitur est difficilius satis facere, qui se Latina scripta dicunt contemnere. in quibus hoc primum est in quo admirer, cur in gravissimis rebus non delectet eos sermo patrius, cum idem fabellas Latinas ad verbum e Graecis expressas non inviti legant. quis enim tam inimicus paene nomini Romano est, qui Ennii Medeam aut Antiopam Pacuvii spernat aut reiciat, quod se isdem Euripidis fabulis delectari dicat, Latinas litteras oderit ?</p>
			<p>Utque aegrum corpus quassari etiam levibus solet offensis, ita animus eius angustus et tener, quicquid increpuisset, ad salutis suae dispendium existimans factum aut cogitatum, insontium caedibus fecit victoriam luctuosam.</p>		
	<?php 
	$content = ob_get_clean();
	$content = apply_filters( 'mktzr_forms_cart_pdf_content', $content, $cart, $cart_meta_datas, $user_id );
	require_once MKTZR_DIR.'/assets/html2pdf/html2pdf.class.php';
	$mktzr_forms_pdf = new HTML2PDF('P','A4','fr');
	$mktzr_forms_pdf->WriteHTML( $content );
	$mktzr_forms_pdf->Output( $cart->order_reference.'.pdf', $method );
}
