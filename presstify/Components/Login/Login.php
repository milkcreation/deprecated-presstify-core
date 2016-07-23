<?php
namespace tiFy\Components\Login;

use tiFy\Environment\Component;

/** @Autoload */
final class Login extends Component
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init'
	);
	
	/** == CONFIGURATION == **/
	public static $Factories			= array();
		
	/* = DECLENCHEURS = */
	/** == Initialisation globale == **/
	final public function init()
	{
		if( empty( $_REQUEST['tify_login-form-id'] ) )
			return;
		if( ! in_array( $_REQUEST['tify_login-form-id'], array_keys( self::$Factories ) )  )
			return;
		
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
		
		switch( $action ) :	
			default :
			case 'login' :
				$this->signon( self::$Factories[ $_REQUEST['tify_login-form-id'] ] );
				break;
			case 'logout' :
				$this->logout();
				break;
		endswitch;
	}
	
	/* = CONTROLEURS = */
	/** == Authentification == **/
	private function signon( $class ){
		$secure_cookie = '';

		if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) :
			$user_name = sanitize_user( $_POST['log'] );
			if ( $user = get_user_by('login', $user_name) ) :
				if ( get_user_option('use_ssl', $user->ID) ) :
					$secure_cookie = true;
					force_ssl_admin(true);
				endif;
			endif;
		endif;
			
		if ( isset( $_REQUEST['redirect_to'] ) ) :
			$redirect_to = $_REQUEST['redirect_to'];
				// Redirect to https if user wants ssl
			if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) :
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			endif;
		else :
			$redirect_to = admin_url();
		endif;
		
		$reauth = empty( $_REQUEST['reauth'] ) ? false : true;	
		$user = wp_signon( '', $secure_cookie );

		if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) :
			if ( headers_sent() ) :
				$user = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
					__( 'https://codex.wordpress.org/Cookies' ), __( 'https://wordpress.org/support/' ) ) );
			elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) :
				// If cookies are disabled we can't log in even with a valid user+pass
				$user = new WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
					__( 'https://codex.wordpress.org/Cookies' ) ) );
			endif;
		endif;
				
		if ( ! is_wp_error( $user ) && ! $reauth ) :
			wp_safe_redirect( $redirect_to );
			exit();
		else :
			$class->errors = $user;
		endif;
	}
	
	/** == Déconnection == **/
	private function logout()
	{
		check_admin_referer('log-out');
		
		$user = wp_get_current_user();
		
		wp_logout();
		
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $requested_redirect_to = $_REQUEST['redirect_to'];
		} else {
			$redirect_to = 'wp-login.php?loggedout=true';
			$requested_redirect_to = '';
		}
		
		$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $user );

		wp_redirect( $redirect_to );
		exit();
	}
	
	/** == Affichage d'un élément de template == **/
	public static function display( $id, $tmpl )
	{
		if( ! in_array( $id, array_keys( self::$Factories ) ) )
			return;
		
		$Class = self::$Factories[$id];	
		
		$args = array_slice( func_get_args(), 2 );
		
		if( method_exists( $Class, $tmpl ) )
			return call_user_func_array( array( $Class, $tmpl ), $args );
	}
}