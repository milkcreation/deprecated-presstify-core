<?php
namespace tiFy\Components\Login;

class Factory
{
	/* = ARGUMENTS = */
	public	$redirect_to,
	$error_map,
	$errors;

	private static $Instance;

	private $id;

	/* = CONSTRUCTEUR = */
	public function __construct( $id = null, $config = array() )
	{
		self::$Instance++;
		
		$this->id = ( ! empty( $id ) ) ? $id : ( is_subclass_of( $this, __CLASS__ ) ? get_class( $this ) :  get_class( $this ) .'-'. self::$Instance );

		// Définition du lien de redirection
		$this->redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];

		// Définition des erreurs
		$this->_map_errors();

		\tiFy\Components\Login\Login::$Factories[$this->id] = $this;
	}

	/* = CONTROLEUR */
	/** == Récupération de l'ID == **/
	function getID()
	{
		return $this->id;
	}

	/* = PARAMETRAGE = */
	/** == Traitement des arguments du formulaire d'authentification == **/
	private function _login_parse_args( $args = array() )
	{
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
			'id_submit' 			=> 'tify_login-submit_button',
			'remember' 				=> true,
			'value_username' 		=> '',
			'value_remember' 		=> false
		);

		return wp_parse_args( $args, apply_filters( 'tify_login_form_defaults', $defaults ) );
	}

	/** == Définition de la liste des erreurs == **/
	private function _map_errors()
	{
		$defaults = array(
			'empty_password'		=> __( 'Veuillez renseigner le mot de passe', 'tify' ),
			'authentication_failed'	=> __( 'Les idenfiants de connexion fournis sont invalides.', 'tify' )
		);
		$this->error_map = wp_parse_args( $this->error_map, $defaults );
	}

	/** == Définition de la liste des erreurs == **/
	private function _error_message( $errors )
	{
		$code = $errors->get_error_code();

		if( isset( $this->error_map[$code] ) )
			return $this->error_map[$code];
		elseif( $message = $errors->get_error_message() )
			return $message.$code;
		else
			return __( 'Erreur lors de la tentative d\'authentification', 'tify' );
	}


	/* = AFFICHAGE = */
	/** == == **/
	public function display( $args = array(), $echo = false )
	{
		$output  = "";
		$output .= $this->errors();
		$output .= $this->form( $args = array() );
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	
	/** == Formulaire == **/
	public function form( $args = array() )
	{
		$args = $this->_login_parse_args( $args );

		$login_form_top = apply_filters( 'tify_login_form_top', '', $args );
		$login_form_middle = apply_filters( 'tify_login_form_middle', '', $args );
		$login_form_bottom = apply_filters( 'tify_login_form_bottom', '', $args );

		$output  = "";
		$output .=	"<form name=\"{$args['form_id']}\"".
				" id=\"{$args['form_id']}\"".
				" action=\"\"".
				" method=\"post\">".
				// Requis
		$this->hidden_fields( $args ).

		$login_form_top;
		$output .=	apply_filters( 'tify_login_form_content',
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
				"</p>", $args, $this );
		$output .=		$login_form_middle;
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
		$output .=	apply_filters( 'tify_login_form_submit',
				"<p class=\"tify_login-submit\">".
				"<input type=\"submit\"".
				" name=\"tify_login-submit\"".
				" id=\"". esc_attr( $args['id_submit'] ) ."\"".
				" value=\"". esc_attr( $args['label_log_in'] ) ."\"".
				" />".
				"</p>", $args, $this );
		$output .=	$login_form_bottom.
					"</form>";

		return apply_filters( 'tify_login_display', $output, $args, $this );
	}

	/** == Champs cachés == **/
	public function hidden_fields( $args = array() )
	{
		return 	"<input type=\"hidden\" name=\"tify_login-form-id\" value=\"{$this->id}\">".
				"<input type=\"hidden\" name=\"redirect_to\" value=\"". esc_url( $args['redirect'] ) ."\" />";
	}

	/** == Affichage des erreurs == **/
	public function errors()
	{
		if( ! is_wp_error( $this->errors ) )
			return;

		return	"<div class=\"tify_login-error\">".
					$this->_error_message( $this->errors ).
				//"<p>There is a problem that we need to fix. A wonderful serenity has taken possession of my entire soul, like these sweet mornings of spring which I enjoy with my whole heart.</p>".
				"</div>";
	}

	/** == Lien de récupération de mot de passe oublié == **/
	public function lostpassword_link( $args = array() )
	{
		$defaults = array(
				'redirect' 	=>  $this->redirect_to,
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
	public function logout_link( $args = array() )
	{
		$defaults = array(
				'redirect' 	=> add_query_arg( 'loggedout', 'true', $this->redirect_to ),
				'text'		=> __( 'Se déconnecter', 'tify' ),
				'class'		=> ''
		);
		$args = wp_parse_args( $args, $defaults );

		$output  = 	"<a href=\"". add_query_arg( 'tify_login-form-id', $this->id, wp_logout_url( $args['redirect'] ) ) ."\"".
				" title=\"". __( 'Déconnection', 'tify' ) ."\"".
				" class=\"tify_login-logout_link {$args['class']}\">".
				$args['text'].
				"</a>";

		return apply_filters( 'tify_login_logout_link', $output, $args, $this );
	}
}