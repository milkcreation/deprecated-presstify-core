<?php
/**
 * Redirection d'accès à l'interface d'administration
 * @see https://fr.wordpress.org/plugins/sf-move-login/
 */
namespace tiFy\Core\Security;

use tiFy\Core\Security\Security;

class LoginRedirect
{
    /**
     * Points d'accès
     * @var array
     */
    private static $Endpoints = array(
        'login'         => 'tify_login',
        'logout'        => 'tify_logout',
        'resetpass'     => 'tify_resetpass',
        'lostpassword'  => 'tify_lostpassword',
        'register'      => 'tify_register',
        'postpass'      => 'tify_postpass'
    );

    /**
     * Cartographie des redirections
     */
    private static $RedirectMap  = array(
        'login'         => 'wp-login.php',
        'logout'        => 'wp-login.php?action=logout',
        'resetpass'     => 'wp-login.php?action=resetpass',
        'lostpassword'  => 'wp-login.php?action=lostpassword',
        'register'      => 'wp-login.php?action=register',
        'postpass'      => 'wp-login.php?action=postpass'        
    );

    /**
     * Paramètres
     */
    private static $Options = array();

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {        
        self::$Options = Security::tFyAppConfig( 'login_redirect' );
        
        if( ! self::$Options['enabled'] )
            return;
        
        self::$Endpoints = self::$Options['endpoints'];   
        
        // Déclencheurs
        add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ), 5 );
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'login_init', array( $this, 'login_init' ), 0 );
        
        add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
        add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
        add_filter( 'logout_url', array( $this, 'logout_url' ), 1 );
        add_filter( 'lostpassword_url', array( $this, 'lostpassword_url' ), 1 );
        add_filter( 'wp_redirect', array( $this, 'wp_redirect' ) );
        add_filter( 'update_welcome_email', array( $this, 'update_welcome_email' ), 10, 2 );
        add_filter( 'register_url', array( $this, 'register_url' ) );
       
        remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation du thème
     */
    final public function after_setup_theme() 
    {
        global $pagenow;
        
        if ( ! ( is_admin() && ! ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( 'admin-post.php' === $pagenow && ! empty( $_REQUEST['action'] ) ) ) ) ) :
            return;
        endif;
    
        if ( is_user_admin() ) :
            $scheme = 'logged_in';
        else :
            $scheme = apply_filters( 'auth_redirect_scheme', '' );
        endif;
    
        if ( wp_validate_auth_cookie( '', $scheme ) ) :
            return;
        endif;
        
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }

    /**
     * Initialisation de Wordpress
     */
    final public function init()
    {                
        // Définition des règles de réécriture    
        foreach( self::$Endpoints as $context => $endpoints ) :
            $endpoints = self::parseEndpointValue( $endpoints );
            
            $redirect = self::$RedirectMap[$context];

            foreach( $endpoints as $endpoint ) :
                self::addEnpointRewriteRule( $endpoint, $redirect );
            endforeach;
        endforeach;
    }

    /**
     * Initialisation de l'interface d'administration
     */
    final public function admin_init()
    {
        if( get_option( 'tysecu_login_redirect_endpoints', '' ) !== base64_encode( serialize( self::$Endpoints ) ) ) :
            self::flushRewriteRules();        
        endif;
    }

    /**
     * Initialisation de l'interface de connection
     */
    final public function login_init()
    {
        // Bypass
        if( is_user_logged_in() )
            return true;            
        
        if( get_option( 'tysecu_login_redirect_endpoints', '' ) !== base64_encode( serialize( self::$Endpoints ) ) ) :
            self::flushRewriteRules();        
        endif;    
            
        $endpoint = self::getUrlEndpoint();
        
        if( self::checkEndpoint( $endpoint ) )
            return true;
        
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }

    /**
     * Court-circuitage de l'url du site
     */
    final public function site_url( $url, $path, $scheme, $blog_id = null ) 
    {
        if ( ! empty( $path ) && is_string( $path ) && ( false === strpos( $path, '..' ) ) && ( 0 === strpos( ltrim( $path, '/' ), 'wp-login.php' ) ) ) :
            $blog_id = (int) $blog_id;

            // Base url.
            if ( empty( $blog_id ) || ( get_current_blog_id() === $blog_id ) || ! is_multisite() ) :
                $url = get_option( 'siteurl' );
            else :
                $url = get_blog_option( $blog_id, 'siteurl' );
            endif;

            $url = set_url_scheme( $url, $scheme );
            $url = rtrim( $url, '/' );
            $path = self::setUrlPath( $path );

            return $url . $path;
        endif;
        
        
        
        return $url;
    }

    /**
     * Court-circuitage de l'url d'un multisite
     */
    final public function network_site_url( $url, $path, $scheme ) 
    {
          return site_url( $path, $scheme );
    }

    /**
     * Court-circuitage de l'url de deconnection
     */
    final public function logout_url( $url )
    {
        return self::setUrlEndpoint( $url, 'logout' );
    }
       
    /**
     * Court-circuitage de l'url de mot de passe oublié
     */
    final public function lostpassword_url( $url )
    {
        return self::setUrlEndpoint( $url, 'lostpassword' );
    }    

    /**
     * Court-circuitage de la redirection Wordpress
     */
    final public function wp_redirect( $location ) 
    {
        $base_uri = explode( '?', $location );
        $base_uri = reset( $base_uri );
        
        if ( site_url( $base_uri ) === site_url( 'wp-login.php' ) ) :
            return $this->site_url( $location, $location, 'login', get_current_blog_id() );
        endif;
        
        return $location;
    }

    /**
     * Court-circuitage du lien dans le message d'accueil Wordpress
     */
    final public function update_welcome_email( $welcome_email, $blog_id ) 
    {
        if ( false === strpos( $welcome_email, 'wp-login.php' ) ) :
            return $welcome_email;
        endif;
        
        $url = get_blogaddress_by_id( $blog_id );
        
        switch_to_blog( $blog_id );
        $login_url = wp_login_url();
        restore_current_blog();
        
        return str_replace( $url . 'wp-login.php', $login_url, $welcome_email );
    }

    /**
     * Court-circuitage de l'url d'enregistrement
     */
    final public function register_url( $url ) 
    {
        if( empty( $_SERVER['REQUEST_URI'] ) ) :
            return $url;
        endif;
        
        if( false === strpos( $_SERVER['REQUEST_URI'], '/wp-signup.php' ) && false === strpos( $_SERVER['REQUEST_URI'], '/wp-register.php' ) ) :
            return $url;
        endif;
        
        if( is_multisite() || is_user_logged_in() ) :
            return $url;
        endif;
    
        wp_die( __( 'Cheatin&#8217; uh?' ) );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération d'un point d'accès
     */
    private static function getEndpoint( $context )
    {
        // Bypass
        if( ! isset( self::$Endpoints[ $context ] ) )
            return;
        
        $endpoint = self::parseEndpointValue( self::$Endpoints[ $context ] );
        
        return current( $endpoint );            
    }

    /**
     * Vérification d'un point d'accès
     */
    private static function checkEndpoint( $endpoint )
    {
        foreach( self::$Endpoints as $context => $endpoints ) :
            $endpoints = self::parseEndpointValue( $endpoints );
            
            $endpoints = array_map( 'self::addEndpointRewriteBase', $endpoints );
            
            if( in_array( $endpoint, $endpoints ) ) :
                return true;
            endif;
        endforeach;
        
        return false;
    }

    /**
     * Traitement de la valeur d'un point d'accès
     */
    private static function parseEndpointValue( $endpoint )
    {
        if( is_string( $endpoint ) ) :
            $endpoint = array_map( 'trim', explode( ',', $endpoint ) );
        endif;
        
        return (array) $endpoint;
    }

    /**
     * Ajout du sous-repertoire d'accès au site
     */
    private static function addEndpointRewriteBase( $endpoint ) 
    {
        $rewrite_base = parse_url( home_url() );
        if ( isset( $rewrite_base['path'] ) ) :
            $rewrite_base = trailingslashit( $rewrite_base['path'] );
        else :
            $rewrite_base = '/';
        endif;
        
        return $rewrite_base . $endpoint;
    }

    /**
     * Ajout de la régle de réécriture d'un point d'accès
     */
    private static function addEnpointRewriteRule( $endpoint, $redirect )
    {       
        // Déclaration de la régle de réécriture
        add_rewrite_rule( $endpoint .'/?', $redirect, 'top' );
    }

    /**
     * Récupération du point d'accès depuis l'url courante
     */
    private static function getUrlEndpoint() 
    {
        $request_uri  = ! empty( $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] ) ? $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] : ( ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );
        @ list( $endpoint, $query_vars )  = explode( '?', $request_uri, 2 );
        
        //$endpoint =  trim( $endpoint, '/' );

        return $endpoint;
    }

    /**
     * Définition du chemin de l'url
     */
    private static function setUrlPath( $path ) 
    {       
        $action = 'login';
        
        if( $query_args = parse_url( $path, PHP_URL_QUERY ) ) :
            if( isset( $query_args['tify_redirect_nonce'] ) && isset( $query_args['endpoint'] ) ) :
                return $path;
            endif;
        
            $action = ! empty( $query_args['action'] ) ? $query_args['action'] : 'login';
            
            if ( isset( $query_args['key'] ) ) :
                $action = 'resetpass';
            endif;
        endif;
                
        if( ( $endpoint = self::getEndpoint( $action ) ) ) :
            $path = str_replace( 'wp-login.php', $endpoint, $path );
            $path = remove_query_arg( 'action', $path );
            
            return '/' . ltrim( $path, '/' );
        endif;
                
        return $path;
    }

    /**
     * Définition d'url relative à un point d'accès
     */
    private static function setUrlEndpoint( $url, $action ) 
    {
        // Bypass
        if( ! $endpoint = self::getEndpoint( $action ) )
            return $url;
        
        if (  $url && ( false === strpos(  $url, '/' . $endpoint ) ) ) :
            $url = str_replace( array( '/' . self::getEndpoint( 'login' ), '&amp;', '?amp;', '&' ), array( '/' . $endpoint, '&', '?', '&amp;' ), remove_query_arg( 'action', $url ) );
        endif;
        
        return $url;
    }

    /**
     * Enregistrement des règles de réécriture
     */
    private static function flushRewriteRules()
    {
        global $wp_rewrite; 
        
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        
        /** @todo test d'écriture du fichier .htaccess **/
        update_option( 'rewrite_rules', '' );
        $wp_rewrite->wp_rewrite_rules();
        
        if ( function_exists( 'save_mod_rewrite_rules' ) )
            save_mod_rewrite_rules();
        if ( function_exists( 'iis7_save_url_rewrite_rules' ) )
            iis7_save_url_rewrite_rules();
        
        update_option( 'tysecu_login_redirect_endpoints', base64_encode( serialize( self::$Endpoints ) ) );
        wp_redirect( ( stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    }
}