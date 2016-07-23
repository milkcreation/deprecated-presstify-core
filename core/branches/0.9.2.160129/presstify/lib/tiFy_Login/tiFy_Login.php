<?php
class tiFy_Login{
	/* = ARGUMENTS = */
	public	$redirect_to,
			$error_map,
			$errors;
	
	protected static $instance = 0;
	
	private $id;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( $id = null ){
		$this->id = ( ! empty( $id ) ) ? $id : ( is_subclass_of( $this, __CLASS__ ) ? get_class( $this ) :  get_class( $this ) .'-'. ++self::$instance );
		
		// Définition du lien de redirection
		$this->redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];
		
		// Définition des erreurs
		$this->_map_errors();
		
		global $tify_login;
		
		if( ! $tify_login instanceof tiFy_LoginHandle )
			$tify_login = new tiFy_LoginHandle;
		
		$tify_login->{$this->id} = $this; 
	}
	
	/* = PARAMETRAGE = */
	/** == Traitement des arguments du formulaire d'authentification == **/
	private function _login_parse_args( $args = array() ){
		$defaults = array(
			'redirect' 				=> $this->redirect_to,
			'form_id' 				=> 'tify_login-form',
			'label_username' 		=> __( 'Identifiant', 'tify' ),
			'label_password' 		=> __( 'Mot de passe', 'tify' ),
			'placeholder_username' 	=> __( 'Identifiant', 'tify' ),
			'placeholder_password' 	=> __( 'Mot de passe', 'tify' ),
			'label_remember' 		=> __( 'Se souvenir de moi', 'tify' ),
			'label_log_in' 			=> __( 'Connexion', 'tify' ),
			'id_username' 			=> 'tify_login-username',
			'id_password' 			=> 'tify_login-password',
			'id_remember'	 		=> 'tify_login-rememberme',
			'id_submit' 			=> 'tify_login-submit',
			'remember' 				=> true,
			'value_username' 		=> '',
			'value_remember' 		=> false
		);
		
		return wp_parse_args( $args, apply_filters( 'tify_login_form_defaults', $defaults ) );
	}

	/** == Définition de la liste des erreurs == **/
	private function _map_errors(){
		$defaults = array(
			'empty_password'		=> __( 'Veuillez renseigner le mot de passe', 'tify' ),
			'authentication_failed'	=> __( 'Les idenfiants de connexion fournis sont invalides.', 'tify' )
		);
		$this->error_map = wp_parse_args( $this->error_map, $defaults );
	}
	
	/** == Définition de la liste des erreurs == **/
	private function _error_message( $errors ){
		$code = $errors->get_error_code();
		
		if( isset( $this->error_map[$code] ) )
			return $this->error_map[$code];
		elseif( $message = $errors->get_error_message() )
			return $message.$code;
		else
			return __( 'Erreur lors de la soummission du formulaire', 'tify' );
	}
	
	
	/* = AFFICHAGE = */	
	/** == Formulaire == **/
	public function form( $args = array() ){
		$args = $this->_login_parse_args( $args );
		
		$login_form_top = apply_filters( 'tify_login_form_top', '', $args );
		$login_form_middle = apply_filters( 'tify_login_form_middle', '', $args );
		$login_form_bottom = apply_filters( 'tify_login_form_bottom', '', $args );

		$output =	"<form name=\"{$args['form_id']}\"".
					" id=\"{$args['form_id']}\"".
					" action=\"". esc_url( $this->redirect_to ) ."\"".
					" method=\"post\">".
						// Requis
						$this->hidden_fields( $args ).
						
						$login_form_top.
						"<p class=\"tify_login-username\">".
							"<label for=\"". esc_attr( $args['id_username'] ) ."\">". 
								esc_html( $args['label_username'] ).
							"</label>".
							"<input type=\"text\"".
							" name=\"log\"".
							" id=\"". esc_attr( $args['id_username'] ) . "\"".
							" class=\"input\"".
							" value=\"". esc_attr( $args['value_username'] ) ."\"".
							" placeholder=\"". esc_html( $args['placeholder_username'] ) ."\"".
							" size=\"20\" />".
						"</p>".
						"<p class=\"tify_login-password\">".
							"<label for=\"". esc_attr( $args['id_password'] ) ."\">".
								esc_html( $args['label_password'] ). 
							"</label>".
							"<input type=\"password\"".
							" name=\"pwd\"". 
							" id=\"". esc_attr( $args['id_password'] ) . "\"".
							" class=\"input\"".
							" value=\"\"".
							" placeholder=\"". esc_html( $args['placeholder_password'] ) ."\"".
							" size=\"20\" />".
						"</p>".
						$login_form_middle;
		if( $args['remember']  )				
			$output .=	"<p class=\"tify_login-remember\">".
							"<label>".
								"<input name=\"rememberme\"".
								" type=\"checkbox\"".
								" id=\"". esc_attr( $args['id_remember'] ) . "\"".
								" value=\"forever\"".
								( $args['value_remember'] ? " checked=\"checked\"" : "" ).
								" />". 
								esc_html( $args['label_remember'] ).
							"</label>".
						"</p>";
		$output .=		"<p class=\"tify_login-submit\">".
							"<input type=\"submit\"".
							" name=\"tify_login-submit\"".
							" id=\"". esc_attr( $args['id_submit'] ) ."\"".
							" class=\"button-primary\"".
							" value=\"". esc_attr( $args['label_log_in'] ) ."\"".
							" />".							
						"</p>".
						$login_form_bottom.
					"</form>";
	
		return apply_filters( 'tify_login_display', $output, $args, $this );
	}
	
	/** == Champs cachés == **/
	public function hidden_fields( $args = array() ){
		return 	"<input type=\"hidden\" name=\"tify_login-form-id\" value=\"{$this->id}\">".
				"<input type=\"hidden\" name=\"redirect_to\" value=\"". esc_url( $args['redirect'] ) ."\" />";
	}

	/** == Affichage des erreurs == **/
	public function errors(){
		if( ! is_wp_error( $this->errors ) )
			return;
		
		return	"<div class=\"callout callout-danger\">".
					"<h4>". $this->_error_message( $this->errors ) ."</h4>".
					//"<p>There is a problem that we need to fix. A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart.</p>".
				"</div>";
	}
	
	/** == Lien de récupération de mot de passe oublié == **/
	public function lostpassword_link( $args = array() ){
		$defaults = array(
			'redirect' 	=> $this->redirect_to,
			'text'		=> __( 'Mot de passe oublié', 'tify' )	
		);
		$args = wp_parse_args( $args, $defaults );

		$output = 	"<a href=\"". wp_lostpassword_url( $args['redirect'] ) ."\"".
				 	" title=\"". __( 'Récupération de mot de passe perdu', 'tify' ) ."\"".
				 	" class=\"tify_login-lostpassword_link\">".
				 		$args['text'].
				 	"</a>";
		
		return apply_filters( 'tify_login_lostpassword_link', $output, $args, $this );
	}
	
	/** == Lien de déconnection == **/
	public function logout_link( $args = array() ){
		$defaults = array(
			'redirect' 	=> $this->redirect_to,
			'text'		=> __( 'Se déconnecter', 'tify' ),
			'class'		=> ''
		);
		$args = wp_parse_args( $args, $defaults );
		
		$output  = 	"<a href=\"". wp_logout_url( $args['redirect'] ) ."\"".
					" title=\"". __( 'Déconnection', 'tify' ) ."\"".
					" class=\"tify_login-logout_link {$args['class']}\">".
						$args['text'].
					"</a>";
		
		return apply_filters( 'tify_login_logout_link', $output, $args, $this );
	}
}

/* = TRAITEMENT = */
final class tiFy_LoginHandle{
	public function __construct(){
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp_init(){
		if( empty( $_POST['tify_login-form-id'] ) )
			return;
		if( ! isset( $this->{$_POST['tify_login-form-id']} ) )
			return;
		
		$this->signon( $this->{$_POST['tify_login-form-id']} );
	}
	
	/* = CONTROLEURS = */
	/** == Authentification == **/
	private function signon( $class ){
		$secure_cookie = '';

		// If the user wants ssl but the session is not ssl, force a secure cookie.
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
				$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
			endif;
		else :
			$redirect_to = admin_url();
		endif;
				
		$reauth = empty($_REQUEST['reauth']) ? false : true;	
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
	
	/** == Affichage d'un élément de template == **/
	public function display( $id, $tmpl, $args = array() ){
		if( ! isset( $this->{$id} ) )
			return;
		
		if( method_exists( $this->{$id}, $tmpl ) )
			return call_user_func( array( $this->{$id}, $tmpl ), $args );
	}
}

/* = HELPER = */
/** == Affichage d'un élément de template == **/
function tify_login_display( $id, $tmpl, $args = array() ){
	global $tify_login;
	
	return $tify_login->display( $id, $tmpl, $args );
}

/** == Affichage du formulaire d'authentification == **/
function tify_login_form_display( $id, $args = array() ){
	return tify_login_display( $id, 'form', $args );
}

/** == Affichage des erreurs de traitement de formulaire == **/
function tify_login_errors_display( $id, $args = array() ){
	return tify_login_display( $id, 'errors', $args );
}

/** == Affichage des erreurs de traitement de formulaire == **/
function tify_login_logout_link_display( $id, $args = array() ){
	return tify_login_display( $id, 'logout_link', $args );
}