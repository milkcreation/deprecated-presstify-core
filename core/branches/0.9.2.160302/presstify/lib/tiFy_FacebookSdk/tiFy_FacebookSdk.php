<?php

/* = CLASSE = */
/** @see https://github.com/facebook/facebook-php-sdk-v4 **/
global $tify_facebook_sdk;
$tify_facebook_sdk = new tiFy_FacebookSDK;

class tiFy_FacebookSDK{
	/* = ARGUMENTS = */
	public	// Configuration
			$dir,
			$uri,
			
			$app_id,
			$app_secret,
			$default_graph_version = 'v2.4',
					
			// Paramètres
			$options = array(				
				'javascript_sdk' 		=> false,			
			),
			
			// Références
			$fb	= array(),
			$login;
	
	
	/* = CONSTRUCTEUR = */
	public function __construct( ){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		require_once( $this->dir ."/inc/helpers.php" );
		require_once( $this->dir ."/inc/login.php" );
		$this->login = new tiFy_FacebookSDKLogin( $this );
				
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp', array( $this, 'wp' ) );
		add_filter( 'language_attributes', array( $this, 'wp_language_attributes' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer_1' ), 1 );
		add_action( 'wp_footer', array( $this, 'wp_footer_99' ), 99 );
	}
	
	/* = PARAMETRAGE = */
	/** == initialisation des paramètres == **/
	public function init( $app_id = null, $args = array( ) ){
		$this->app_id = $app_id;
		
		if( ! empty( $args['app_secret'] ) )
			$this->app_secret = $args['app_secret'];
		if( ! empty( $args['default_graph_version'] ) )
			$this->default_graph_version = $args['default_graph_version'];
		
		unset( $args['app_secret'], $args['default_graph_version'] );
		
		foreach( $args as $option_name => $option_value )		
			$this->set_option( $option_name, $option_value );
			
		if( ! $this->app_secret )
			return;
		
		// Instanciation du SDK PHP
		session_start();

		$this->fb[$this->app_id] = new Facebook\Facebook(
			array(
		  		'app_id' 				=> $this->app_id,
		  		'app_secret' 			=> $this->app_secret,
		  		'default_graph_version' => $this->default_graph_version
		  	)
		);
	}

	/** == Définition d'option == **/
	public function set_option( $option, $value = '' ){
		if( isset( $this->options[ $option ] ) )
			$this->options[ $option ] = $value;
	}	
	
	/* = ACTION ET FILTRES WORDPRESS = */
	/** == Initialisation global == **/
	public function wp_init(){
		do_action( 'tify_facebook_sdk' );
	}
	
	/** == Initialisation global == **/
	public function wp(){
		// Authentification Facebook
		/** @see https://developers.facebook.com/docs/php/howto/example_facebook_login/5.0.0 **/
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
	}	
	
	/** == Langage Attribute de la balise HTML  == **/
	public function wp_language_attributes( $output ){
		if( is_admin() )
			return $output;
		if( $this->app_id )
			$output .= ' xmlns:fb="http://www.facebook.com/2008/fbml"';
		return $output;
	}
		
	/** == Modification de l'entête du site == **/
	public function wp_head(){
		if( $this->app_id )
			echo '<meta content="'. $this->app_id .'" property="fb:app_id">';
	}
	
	/** == Pied de page du site == **/
	public function wp_footer_1(){
		if( ! $this->app_id || ! $this->options['javascript_sdk'] )
			return;
		?><div id="fb-root"></div><?php
	}
	/** == Pied de page du site == **/
	public function wp_footer_99(){
		if( ! $this->app_id || ! $this->options['javascript_sdk'] )
			return;
		
		$src = ( @get_headers( 'http://connect.facebook.net/'. get_locale() .'/sdk.js' ) ) ? '//connect.facebook.net/'. get_locale() .'/sdk.js' : '//connect.facebook.net/en_US/sdk.js';
		?><script type="text/javascript">/* <![CDATA[ */			
			window.fbAsyncInit = function() {
				/** @see https://developers.facebook.com/docs/javascript/reference/FB.init/v2.4 **/
		        FB.init({
		          	appId      	: '<?php echo $this->app_id;?>',
	        		status		: true,
	        		cookie		: true,
					xfbml		: true,
					version		: 'v2.4'
		        });
			};
			(function(d, s, id){
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "<?php echo $src;?>";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		/* ]]> */</script><?php		
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