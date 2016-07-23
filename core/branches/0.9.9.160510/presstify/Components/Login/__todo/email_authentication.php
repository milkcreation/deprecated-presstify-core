<?php
/*
Plugin Name: Email Athentication
Plugin URI: http://presstify.com/plugins/email_authentication
Description: Authentification à Wordpress grâce à l'email
Version: 1.150813
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * @see http://www.thebinary.in/blog/wordpress-login-using-email/
 */
new tiFy_EmailAuthentication;
class tiFy_EmailAuthentication{
 	/* = ARGUMENTS = */
 	
 	/* = CONSTRUCTEUR = */
 	public function __construct(){
 		// Actions et Filtres Wordpress
 		remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );
		add_filter( 'authenticate', array( $this, 'wp_authenticate' ), 20, 3 );
		add_action( 'login_form_login', array( $this, 'wp_login_form_login' ), 20 );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	public function wp_authenticate( $user, $email, $password ){ 
	    // Vérification de l'intégrité des champs
        if( empty( $email ) || empty ( $password ) ) :       
            $error = new WP_Error();
 			
            if( empty( $email ) )
                $error->add( 'empty_username', __( '<strong>ERREUR</strong>: Le champ "Email" doit être renseigné.', 'tify' ) );
 
            if( empty( $password ) )
                $error->add( 'empty_password', __('<strong>ERREUR</strong>: Le champ "Mot de passe" doit être renseigné.', 'tify' ) );

            return $error;
		elseif( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) :
            return new WP_Error( 'invalid_username', __( '<strong>ERREUR</strong>: Le format de l\'email fourni est invalide.', 'tify' ) );
		endif;
 
        // Récupération de l'utilisateur associé à l'email
        if(! $user = get_user_by( 'email', $email ) )
            return new WP_Error( 'authentication_failed', __('<strong>ERREUR</strong>: L\'email ou le mot de passe fourni est invalide.', 'tify' ) );
		
       	// Vérification du mot de passe
        if( ! wp_check_password( $password, $user->user_pass, $user->ID ) )
            return new WP_Error( 'authentication_failed', __('<strong>ERREUR</strong>: L\'email ou le mot de passe fourni est invalide.', 'tify' ) );

		return $user;
	}
	
	/** == Modification de l'intitulé == **/
	function wp_login_form_login(){
		add_filter( 
			'gettext', 
			function( $translations, $text, $domain ){
				return ( 'Username' === $text ) ? __( 'Email', 'tify' ) : $translations; 
			},
			20,
			3
		);
	}
}