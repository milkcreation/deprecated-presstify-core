<?php
namespace tiFy\Plugins\Membership;

use tiFy\Environment\App;

class GeneralTemplate extends App
{
	
	/* = ARGUMENTS = */
	// Actions à déclencher
	protected $CallActions			= array(
		'the_content'
	);
	
	private static $Config;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		self::$Config = \tiFy\Plugins\Membership\Membership::getConfig();
	}
	
	/* = = */
	final public function the_content( $content )
	{
		// Bypass
		if( ! \in_the_loop() ||
			! \is_singular() ||
			( get_the_ID() !== (int) get_option( 'page_for_tify_membership' ) )
		)
			return $content;
		
		$view = isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'home'; 
		
		// Reset Content
		$content  = "";
		
		switch( $view ) :
			default :
			case 'home' :
				if( ! is_user_logged_in() ) :
					$content .= $this->login_form();
					$content .= $this->lostpassword_button() ."&nbsp;". $this->subscribe_button();
				else :
					$content .= $this->account_button() ."&nbsp;". $this->logout_button();
				endif;
					
				return apply_filters( 'tify_membership_view_default', $content );	
				break;
			case 'subscribe' :			
				if( is_user_logged_in() )
					$content .= __( 'Vous êtes déjà connecté', 'tify' );
				else
					$content .= $this->subscribe_form();
							
				return apply_filters( 'tify_membership_view_subscribe', $content );
				break;
			case 'account' :
				if( ! is_user_logged_in() )
					$content .= __( 'Cet espace est réservé aux utilisateurs connectés', 'tify' );
				elseif( ! $this->master->capabilities->has_account() )
					$content .= __( 'Cet espace est réservé aux utilisateurs possédant un compte accès pro.', 'tify' );
				else
					$content .= $this->subscribe_form();
								
				return apply_filters( 'tify_membership_view_subscribe', $content );
				break;
		endswitch;
		 
		return $content;		
	}

	/** == Formulaire d'authentification == **/
	function login_form( $args = array() )
	{		
		$defaults = array(
			'redirect' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		// Force le retour plutôt que l'affichage	
		$args['echo'] = false;
		
		$output  = "";
		$output .= wp_login_form( $args );
		
		return apply_filters( 'tify_membership_login_form', $output );
	}
	
	/** == Bouton de récupération de mot de passe oublié == **/
	function lostpassword_button( $args = array() )
	{
		$defaults = array(
			'redirect' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$output  = "";
		$output .= "<a href=\"". wp_lostpassword_url( $args['redirect'] ) ."\" title=\"". __( 'Récupération de mot de passe oublié', 'tify' ) ."\">". __( 'Mot de passe oublié', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_lostpassword_button', $output );
	}
	
	/** == Bouton de déconnection == **/
	function logout_button( $args = array() )
	{
		$defaults = array(
			'redirect' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$output  = "";
		$output .= "<a href=\"". wp_logout_url( $args['redirect'] ) ."\" title=\"". __( 'Déconnection du forum', 'tify' ) ."\">". __( 'Se déconnecter', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_logout_button', $output );
	}
	
	/** == Formulaire d'inscription == **/
	function subscribe_form(){
		return  \tify_form_display( self::$Config['form']['ID'], false );
	}
	
	/** == Bouton d'inscription == **/
	function subscribe_button( $args = array() )
	{
		$defaults = array(
			'url' => esc_url( get_permalink( get_option( 'page_for_tify_membership' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$subscribe_link = esc_url( add_query_arg( array( 'view' => 'subscribe' ), $args['url'] ) );
		
		$output  = "";
		$output .= "<a href=\"". $subscribe_link ."\" title=\"". __( 'Inscription à l\'accès pro.', 'tify' ) ."\">". __( 'S\'inscrire', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_subscribe_button', $output );
	}
	
	/** == Bouton d'inscription == **/
	function account_button( $args = array() )
	{
		$defaults = array(
			'url' => esc_url( get_permalink( get_option( 'page_for_tify_forum' ) ) )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$account_link = esc_url( add_query_arg( array( 'view' => 'account' ), $args['url'] ) );
		
		$output  = "";
		$output .= "<a href=\"". $account_link ."\" title=\"". __( 'Modification des paramètres du compte', 'tify' ) ."\">". __( 'Modifier mes paramètres', 'tify' ) ."</a>";
		
		return apply_filters( 'tify_membership_account_button', $output );
	}
}