<?php
class tiFy_FacebookSDKLogin{
	/* = ARGUMENTS = */
	private	$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_FacebookSDK $master ){
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp', array( $this, 'wp' ) );
		add_action( 'wp_ajax_tify_facebook_sdk_login', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_facebook_sdk_login', array( $this, 'wp_ajax' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation global == **/
	public function wp_init(){
		// Déclaration des scripts
		wp_register_style( 'tify_facebook_sdk-login', $this->master->uri ."css/login.css", array(), '1551123' );
		wp_register_script( 'tify_facebook_sdk-login', $this->master->uri ."js/login.js", array( 'jquery' ), '1551123', true );
	}
	
	/** == Initialisation de l'object WP == **/
	public function wp(){
		/** Authentification Facebook via PHP
		 * @see https://developers.facebook.com/docs/php/howto/example_facebook_login/5.0.0
		 **/
		if( ! isset( $_REQUEST['tify_facebook_sdk_login'] ) )
			return;
		
		//$app_id = $_REQUEST['tify_facebook_sdk_login']; // Dans le cas d'app multiples
		$fb = $this->master->fb[$this->master->app_id];
		
		// Récupération du jeton d'accès		
		$helper = $fb->getRedirectLoginHelper();
		try {
			$accessToken = $helper->getAccessToken();
		} catch( Facebook\Exceptions\FacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch( Facebook\Exceptions\FacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		if ( ! isset( $accessToken) ) :
			if ( $helper->getError() ) :
		    	header('HTTP/1.0 401 Unauthorized');
		    	echo "Error: " . $helper->getError() . "\n";
		    	echo "Error Code: " . $helper->getErrorCode() . "\n";
		    	echo "Error Reason: " . $helper->getErrorReason() . "\n";
		    	echo "Error Description: " . $helper->getErrorDescription() . "\n";
		  	else :
		    	header('HTTP/1.0 400 Bad Request');
		    	echo 'Bad request';
			endif;
		  	exit;
		endif;		
		
		// Récupération des identifiants utilisateurs
		try {
  			$response = $fb->get('/me?fields=email',  $accessToken );
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		$graphUser = $response->getGraphUser();

		$user = $this->get_user( $graphUser['id'], $graphUser['email'] );
		if( is_wp_error( $user ) ) :
			wp_die( $user->get_error_message() );
		else :	
			wp_set_auth_cookie( $user->ID );
			$redirect = wp_get_referer();
			do_action( 'tify_facebook_sdk_login', $accessToken->getValue(), $this->master->app_id, $redirect );			
			wp_redirect( $redirect );
			exit;
		endif;				
	}
	
	/** == Initialisation global 
	public function wp(){
		// Authentification Facebook
		if( ! isset( $_REQUEST['tify_facebook_sdk_login'] ) )
			return;
		
		$app_id = $_REQUEST['tify_facebook_sdk_login'];
		
		$helper = tify_facebook_sdk( $app_id )->getRedirectLoginHelper();

		try {
			$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		
		if ( ! isset( $accessToken) ) :
			if ( $helper->getError() ) :
		    	header('HTTP/1.0 401 Unauthorized');
		    	echo "Error: " . $helper->getError() . "\n";
		    	echo "Error Code: " . $helper->getErrorCode() . "\n";
		    	echo "Error Reason: " . $helper->getErrorReason() . "\n";
		    	echo "Error Description: " . $helper->getErrorDescription() . "\n";
		  	else :
		    	header('HTTP/1.0 400 Bad Request');
		    	echo 'Bad request';
			endif;
		  	exit;
		endif;
		
		$redirect = wp_get_referer();
		do_action( 'tify_facebook_sdk_login', $accessToken->getValue(), $app_id, $redirect );
		
		wp_redirect( $redirect );
		exit;		
	}	== **/
	
	/** == Authentifiaction via Ajax == **/
	public function wp_ajax(){
		// Autentification Wordpress via Facebook
		$fb_id = ( isset( $_POST['response']['id'] ) ) ? $_POST['response']['id'] : null;
		$email = ( isset( $_POST['response']['email'] ) ) ? $_POST['response']['email'] : null;
		
		$user = $this->get_user( $fb_id, $email );
		
		if( is_wp_error( $user ) ) :
			wp_send_json_error( $user->get_error_message() );
		else :	
			wp_set_auth_cookie( $user->ID );
			wp_die( 1 );
		endif;
	}
	
	/* = AFFICHAGE = */
	/** == Affichage du bouton == **/
	public function button( $args = array() ){
		static $instance; $instance++;
				
		$defaults = array(
			'id'				=> 'tify_facebook_sdk_login_button-'. $instance,
			'class'				=> '',
			'text'				=> __( 'Se connecter à Facebook', 'tify' ),
			'attrs'				=> array(),		
			'_wp_http_referer' 	=> urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), // urlencode( wp_unslash( strtok( $_SERVER['REQUEST_URI'], "#" ) ) )
			/**
			 * (bool) true active l'authentification Ajax  | (string) fb.api path ex : '/me?fields=email' | @todo (array) ex : array( 'path' => '/me', 'method' => 'get', 'params' => array( 'field' => 'email' ), 'callback' => '{function_js}' )
			 * @see https://developers.facebook.com/docs/javascript/reference/FB.api 
			 */ 
			'ajax'				=> true,
			'error_container'	=> '#tify_facebook_sdk_login-error-'. $instance,
			'permissions'		=> array( 'public_profile', 'email' ),
			'echo'				=> true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );	
		
		$login_url = $ajax ? '#'. $id : $this->login_url( array( 'tify_facebook_sdk_login' => true, '_wp_http_referer' => $_wp_http_referer ), $permissions );
		if( $ajax )
			$this->master->set_option( 'javascript_sdk', true );
		
		$output  = 	"";
		$output .= 	"<a".
					" href=\"". htmlspecialchars( $login_url ) ."\"".
					" id=\"{$id}\"".
					" class=\"tify_facebook_sdk_login_button". ( $class ? ' '. $class : '' ) ."\"";
		foreach( (array) $attrs as $key => $value ) 
			$output .= 	" {$key}=\"{$value}\"";
		if( $ajax ) :
			$output .= " data-tify_facebook_sdk_login=\"true\"";
			$output .= " data-error=\"{$error_container}\"";
		endif;
		
		$output .= " scope=\"". implode( ',', $permissions ) ."\"";
		$output .= ">".
						$text .
					"</a><div id=\"tify_facebook_sdk_login-error-{$instance}\" class=\"tify_facebook_sdk_login-error\"></div>";	
				
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/* = CONTROLEUR = */
	/** == url api PHP @todo == **/
	private function login_url( $url_params = array(), $permissions = array() ){
		$helper = $this->master->fb[$this->master->app_id]->getRedirectLoginHelper();
		return $helper->getLoginUrl(				
			add_query_arg( 
				$url_params,
				site_url('/')
			), 
			$permissions
		);
	}
	
	/** == Vérification des identifiants utilisateur == **/
	private function get_user( $fb_id = null, $email = null ){
		if( empty( $email ) )
			return new WP_Error( 'fb_user_email_empty', __( 'Impossible de récupérer l\'email de votre compte Facebook', 'tify' ) );
		if( empty( $fb_id ) )
			return new WP_Error( 'fb_user_id_empty', __( 'Impossible de récupérer l\'identifiant utilisateur de votre compte Facebook', 'tify' ) );		
		if( ! $user = get_user_by( 'email', $email ) )
			return new WP_Error( 'fb_user_email_invalid', __( 'Impossible de trouver un utilisateur existant correspondant à votre compte Facebook', 'tify' ) );
		if( get_user_meta( $user->ID, 'tify_facebook_sdk_user_id', true ) != $fb_id )
			return new WP_Error( 'fb_user_id_invalid', __( 'Impossible de trouver un utilisateur associé à votre compte Facebook', 'tify' ) );
		
		return $user;
	}	
}


/* = SAMPLE = */
/** == LOGIN JS == **/
/*
// Javascript
<a href="#" onclick="fb_login();">Test login FB</a>
<script>
	function fb_login(){
	    FB.login( function(response) {											
			if (response.authResponse) {				
	            FB.api('/me?fields=email', function(response) {
	                user_email = response.email; //get user email
	                console.log( user_email );      
	            });											
	        } else {
	            console.log('User cancelled login or did not fully authorize.');											
	        }
	    }, {scope: 'public_profile,email'});
	}											
</script>
*/