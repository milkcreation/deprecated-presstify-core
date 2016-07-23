<?php
/* = HELPER = */
/** == Initialisation du SDK Facebook == **/
function tify_facebook_sdk_init( $app_id = null, $args = array( ) ){
	global $tify_facebook_sdk;
	
	return $tify_facebook_sdk->init( $app_id, $args );
}

/** == Appel du SDK PHP Facebook == **/
function tify_facebook_sdk( $app_id = null ){
	global $tify_facebook_sdk;
	
	if( ! $app_id )
		$app_id = $tify_facebook_sdk->app_id;
	
	return $tify_facebook_sdk->fb[$app_id];
}

/** == Récupération de l'AppID Facebook == **/
function tify_facebook_sdk_app_id( ){
	global $tify_facebook_sdk;
	
	return $tify_facebook_sdk->app_id;
}

/** == Appel du SDK PHP Facebook == **/
function tify_facebook_sdk_set_option( $option, $value ){
	global $tify_facebook_sdk;

	return $tify_facebook_sdk->set_option( $option, $value );
}

/** == Création d'un bouton d'authentification == 
 * USAGE :
 * 1 - Les scripts doivent être initialisé sur la page :
 * wp_register_style( 'tify_facebook_sdk-login' );
 * wp_register_script( 'tify_facebook_sdk-login' );
 * 2 - Instancier le bouton :
 * @see /mu-plugins/presstify/lib/tify_facebook_sdk/inc/login.php pour connaitre les arguments
 * tify_facebook_sdk_login_button();
 * 
 * **/
function tify_facebook_sdk_login_button( $args = array() ){	
	global $tify_facebook_sdk;
	
	return $tify_facebook_sdk->login->button( $args );
}

/** == == **/
function tify_facebook_sdk_app_access_token( $client_id, $client_secret ){
	$service_url = "https://graph.facebook.com/v2.4/oauth/access_token?client_id={$client_id}&client_secret={$client_secret}&grant_type=client_credentials";

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $service_url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	$curl_response = curl_exec($ch);
	curl_close($ch);
	$results = json_decode($curl_response, true );
	if( isset( $results['access_token'] ) )
		return $results['access_token'];
		
	$test = new	Facebook\Authentication\AccessToken( $results['access_token'], ( time()+ (15*MINUTE_IN_SECONDS ) ) );
	$access_token = $test->getValue();
}