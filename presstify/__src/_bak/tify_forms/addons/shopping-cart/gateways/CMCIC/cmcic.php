<?php
// API CMCIC
require_once( dirname(__FILE__).'/lib/CMCIC_Tpe.inc.php' );

/**
 * Options par défaut du TPE
 */
function mktzr_forms_cart_cmcic_default_options(){	
	return array(		
		'cle' 				=> '12345678901234567890123456789012345678P0', // Clé 
		'TPE' 				=> '0000001', // TPE
		'serveur' 			=> '',	// https://ssl.paiement.cic-banques.fr/test/, https://paiement.creditmutuel.fr/test/ ; https://ssl.paiement.banque-obc.fr/test/	
		'url_retour' 		=> site_url(),
		'url_retour_ok' 	=> add_query_arg( array( 'mktzr_forms_cart' => 'payment_success' ), home_url() ),
		'url_retour_err' 	=> add_query_arg( array( 'mktzr_forms_cart' => 'payment_error' ), home_url() ),
		'lgue' 				=> 'FR', // Code langage de la société
		'societe' 			=> '', // Code société
		'display_callback'  => 'mktzr_forms_cart_cmcic_display_form'
	);
}

/**
 * Formulaire de paiement
 */
function mktzr_forms_cart_cmcic_display_form( ){
	global $mktzr_forms, $current_user;
	get_currentuserinfo();
	// Bypass
	if( ! $current_user )
		return;
	
	// Bypass
	if( ! $cart = mktzr_forms_cart_get_from_cookie() )
		return;		

	if( $current_user->ID != $cart->client_id )
		return;

	// Définition du formulaire courant
	$mktzr_forms->forms->set_current( $cart->form_id );
	
	$options = $mktzr_forms->addons->get_form_option( 'gateway', 'shopping-cart' );
	if( !isset( $options['cmcic'] ) )
		return;
	
	$options = $mktzr_forms->options->parse( $options['cmcic'], mktzr_forms_cart_cmcic_default_options() );
	
	define( "CMCIC_CLE", 			$options['cle'] );
	define( "CMCIC_TPE",			$options['TPE'] );
	define( "CMCIC_VERSION", 		'3.0' );
	define( "CMCIC_SERVEUR", 		$options['serveur'] );
	define( "CMCIC_CODESOCIETE", 	$options['societe'] );
	define( "CMCIC_URLOK", 			$options['url_retour_ok'] );
	define( "CMCIC_URLKO", 			$options['url_retour_err'] );

	$oTpe 	= new CMCIC_Tpe( $options['lgue'] );
	$oHmac 	= new CMCIC_Hmac( $oTpe ); 

	// Reference: unique, alphaNum (A-Z a-z 0-9), 12 caractères maxi
	//$sReference = substr( $cart->order_reference, strpos( $cart->order_reference, '-' ) + 1 );
	$sReference = "ref" . date("His");
	// Prix : format  "xxxxx.yy" (sans espaces)
	$sMontant = $cart->price;
	//----$sMontant = mktzr_forms_cart_calculate_price( $cart->form_id );	
	// Devise : ISO 4217 conforme
	$sDevise  = "EUR";	
	// Texte libre : peut être une plus grande référence ou un identifiant de session necessaire pour le retour sur le site marchand
	$sTexteLibre = $cart->order_reference;	
	// Date de transaction : format d/m/y:h:m:s
	$sDate = date( "d/m/Y:H:i:s" );	
	// Code langage de la société
	$sLangue = $options['lgue'];	
	// email du client
	$sEmail = $current_user->user_email;
	// Facultatif
		// Variable de paiement fractionné
		// Entre 2 et 4
		//$sNbrEch = "4";
		$sNbrEch = "";	
		// date echeance 1 - format dd/mm/yyyy
		//$sDateEcheance1 = date("d/m/Y");
		$sDateEcheance1 = "";	
		// montant échéance 1 - format  "xxxxx.yy" (sans espaces)
		//$sMontantEcheance1 = "0.26" . $sDevise;
		$sMontantEcheance1 = "";	
		// date echeance 2 - format dd/mm/yyyy
		$sDateEcheance2 = "";	
		// montant échéance 2 - format  "xxxxx.yy" (sans espaces)
		//$sMontantEcheance2 = "0.25" . $sDevise;
		$sMontantEcheance2 = "";	
		// date echeance 3 - format dd/mm/yyyy
		$sDateEcheance3 = "";	
		// montant échéance 3 - format  "xxxxx.yy" (sans espaces)
		//$sMontantEcheance3 = "0.25" . $sDevise;
		$sMontantEcheance3 = "";	
		// date echeance 4 - format dd/mm/yyyy
		$sDateEcheance4 = "";	
		// montant échéance 4 - format  "xxxxx.yy" (no spaces)
		//$sMontantEcheance4 = "0.25" . $sDevise;
		$sMontantEcheance4 = "";	
	// Options supplémentaire
	$sOptions = "";     	        
	
	// Control String for support
	$CtlHmac = sprintf( CMCIC_CTLHMAC, $oTpe->sVersion, $oTpe->sNumero, $oHmac->computeHmac( sprintf( CMCIC_CTLHMACSTR, $oTpe->sVersion, $oTpe->sNumero ) ) );
	
	// Data to certify
	$PHP1_FIELDS = sprintf( CMCIC_CGI1_FIELDS,    $oTpe->sNumero,
	                                              $sDate,
	                                              $sMontant,
	                                              $sDevise,
	                                              $sReference,
	                                              $sTexteLibre,
	                                              $oTpe->sVersion,
	                                              $oTpe->sLangue,
	                                              $oTpe->sCodeSociete, 
	                                              $sEmail,         
	                                              $sNbrEch,
	                                              $sDateEcheance1,
	                                              $sMontantEcheance1,
	                                              $sDateEcheance2,
	                                              $sMontantEcheance2,
	                                              $sDateEcheance3,
	                                              $sMontantEcheance3,
	                                              $sDateEcheance4,
	                                              $sMontantEcheance4,
	                                              $sOptions
					);
	
	// MAC computation
	$sMAC = $oHmac->computeHmac( $PHP1_FIELDS );
	
	// $token
	$token = mktzr_get_cart_token( $cart );
	
?>
<form action="<?php echo $oTpe->sUrlPaiement;?>" method="post" id="PaymentRequest">
	<input type="hidden" name="version"             id="version"        value="<?php echo $oTpe->sVersion;?>" />
	<input type="hidden" name="TPE"                 id="TPE"            value="<?php echo $oTpe->sNumero;?>" />
	<input type="hidden" name="date"                id="date"           value="<?php echo $sDate;?>" />
	<input type="hidden" name="montant"             id="montant"        value="<?php echo $sMontant . $sDevise;?>" />
	<input type="hidden" name="reference"           id="reference"      value="<?php echo $sReference;?>" />
	<input type="hidden" name="MAC"                 id="MAC"            value="<?php echo $sMAC;?>" />
	<input type="hidden" name="url_retour"          id="url_retour"     value="<?php echo site_url();?>" />
	<input type="hidden" name="url_retour_ok"       id="url_retour_ok"  value="<?php echo add_query_arg( array( 'token' => $token ), $oTpe->sUrlOK );?>" />
	<input type="hidden" name="url_retour_err"      id="url_retour_err" value="<?php echo $oTpe->sUrlKO;?>" />
	<input type="hidden" name="lgue"                id="lgue"           value="<?php echo $oTpe->sLangue;?>" />
	<input type="hidden" name="societe"             id="societe"        value="<?php echo $oTpe->sCodeSociete;?>" />
	<input type="hidden" name="texte-libre"         id="texte-libre"    value="<?php echo HtmlEncode($sTexteLibre);?>" />
	<input type="hidden" name="mail"                id="mail"           value="<?php echo $sEmail;?>" />
	<?php /*
	<input type="hidden" name="nbrech"              id="nbrech"         value="<?php echo $options['nbrech']; ?>" />
	<input type="hidden" name="dateech1"            id="dateech1"       value="<?php echo $options['dateech1']; ?>" />
	<input type="hidden" name="montantech1"         id="montantech1"    value="<?php echo $options['montantech1']; ?>" />
	<input type="hidden" name="dateech2"            id="dateech2"       value="<?php echo $options['dateech2']; ?>" />
	<input type="hidden" name="montantech2"         id="montantech2"    value="<?php echo $options['montantech2']; ?>" />
	<input type="hidden" name="dateech3"            id="dateech3"       value="<?php echo $options['dateech3']; ?>" />
	<input type="hidden" name="montantech3"         id="montantech3"    value="<?php echo $options['montantech3']; ?>" />
	<input type="hidden" name="dateech4"            id="dateech4"       value="<?php echo $options['dateech4']; ?>" />
	<input type="hidden" name="montantech4"         id="montantech4"    value="<?php echo $options['montantech4']; ?>" />
	 */ ?>
	<input type="submit" name="submit_paycard"      id="bouton"         value="Payer" />
</form>
<?php
}

/**
 * Affichage des erreurs de paiement
 */
function mktzr_forms_cart_cmcic_payment_error(){
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<title><?php _e( 'Paiement CMCIC en erreur', 'mktzr_forms' );?></title>
</head>

<body>

<h3><?php _e( 'Paiement CMCIC en erreur', 'mktzr_forms' );?></h3>
<?php
global $mktzr_forms;
if( $cart = mktzr_forms_cart_get_from_cookie() ) :
	// Définition du formulaire courant
	$mktzr_forms->forms->set_current( $cart->form_id );
	// Récupération des otions de la plateforme de paiement
	$options = $mktzr_forms->addons->get_form_option( 'gateway', 'shopping-cart' );
	if( !isset( $options['cmcic'] ) )
		$options['cmcic'] = array();
	
	$options = $mktzr_forms->options->parse( $options['cmcic'], mktzr_forms_cart_cmcic_default_options() );
	
	define( "CMCIC_CLE", 			$options['cle'] );
	define( "CMCIC_TPE",			$options['TPE'] );
	define( "CMCIC_VERSION", 		'3.0' );
	define( "CMCIC_SERVEUR", 		$options['serveur'] );
	define( "CMCIC_CODESOCIETE", 	$options['societe'] );
	define( "CMCIC_URLOK", 			$options['url_retour_ok'] );
	define( "CMCIC_URLKO", 			$options['url_retour_err'] );
endif;
 
// Récupération des variable renvoyé par CMCIC
$CMCIC_bruteVars = getMethode();

// Initialisation des variables du TPE
$oTpe = new CMCIC_Tpe();
$oHmac = new CMCIC_Hmac($oTpe);

// Message Authentication
$cgi2_fields = sprintf(
				CMCIC_CGI2_FIELDS, 
					$oTpe->sNumero,
					@$CMCIC_bruteVars['date'],
					@$CMCIC_bruteVars['montant'],
					@$CMCIC_bruteVars['reference'],
					@$CMCIC_bruteVars['texte-libre'],
					$oTpe->sVersion,
					@$CMCIC_bruteVars['code-retour'],
					@$CMCIC_bruteVars['cvx'],
					@$CMCIC_bruteVars['vld'],
					@$CMCIC_bruteVars['brand'],
					@$CMCIC_bruteVars['status3ds'],
					@$CMCIC_bruteVars['numauto'],
					@$CMCIC_bruteVars['motifrefus'],
					@$CMCIC_bruteVars['originecb'],
					@$CMCIC_bruteVars['bincb'],
					@$CMCIC_bruteVars['hpancb'],
					@$CMCIC_bruteVars['ipclient'],
					@$CMCIC_bruteVars['originetr'],
					@$CMCIC_bruteVars['veres'],
					@$CMCIC_bruteVars['pares']
				);

if( $oHmac->computeHmac( $cgi2_fields ) == strtolower( @$CMCIC_bruteVars['MAC'] ) ) :
	switch($CMCIC_bruteVars['code-retour']) {
		case "Annulation" :
			// Payment has been refused
			// put your code here (email sending / Database update)
			// Attention : an autorization may still be delivered for this payment
			break;

		case "payetest":
			// Payment has been accepeted on the test server
			// put your code here (email sending / Database update)
			break;

		case "paiement":
			// Payment has been accepted on the productive server
			// put your code here (email sending / Database update)
			break;


		/*** ONLY FOR MULTIPART PAYMENT ***/
		case "paiement_pf2":
		case "paiement_pf3":
		case "paiement_pf4":
			// Payment has been accepted on the productive server for the part #N
			// return code is like paiement_pf[#N]
			// put your code here (email sending / Database update)
			// You have the amount of the payment part in $CMCIC_bruteVars['montantech']
			break;

		case "Annulation_pf2":
		case "Annulation_pf3":
		case "Annulation_pf4":
			// Payment has been refused on the productive server for the part #N
			// return code is like Annulation_pf[#N]
			// put your code here (email sending / Database update)
			// You have the amount of the payment part in $CMCIC_bruteVars['montantech']
			break;
			
	}

	$receipt = CMCIC_CGI2_MACOK;
else :
	// your code if the HMAC doesn't match
	$receipt = CMCIC_CGI2_MACNOTOK.$cgi2_fields;
endif;
?>
<a href="<?php echo site_url();?>"><?php _e( 'Retour à la boutique', 'mktzr_forms' );?></a>
</body>
</html>
<?php
	exit;	
}
 
/**
 * Affichage du paiement réussi
 */ 
function mktzr_forms_cart_cmcic_payment_success(){
?>
	<h3><?php _e( 'Paiement effectué avec succès', 'mktzr_forms' );?></h3>
<?php
	exit;	
}