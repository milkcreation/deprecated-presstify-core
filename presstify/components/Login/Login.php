<?php
namespace tiFy\Components\Login;

final class Login extends \tiFy\Environment\Component
{
    /* = ARGUMENTS = */
    // Liste des Actions à déclencher
    protected $tFyAppActions                = array(
        'init'
    );
    
    // Liste des Filtres à déclencher
    protected $CallFilters                = array(
        'authenticate'
    );
    
    // Ordre de priorité d'exécution des filtres
    protected $CallFiltersPriorityMap    = array(
        'authenticate' => 50
    );
    
    // Nombre d'arguments autorisés
    protected $CallFiltersArgsMap        = array(
        'authenticate' => 3    
    );
        
    /** == PARAMETRES == **/
    // 
    private static $Factories            = array();
    
    //
    private static $Current                = null;
    
        
    /* = DECLENCHEURS = */
    /** == Initialisation globale == **/
    final public function init()
    {
        // Bypass
        if( empty( $_REQUEST['tiFyLogin-formID'] ) )
            return;        
        if( ! $this->_setCurrent( $_REQUEST['tiFyLogin-formID'] ) )
            return;
                
        $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
        
        switch( $action ) :    
            default :
            case 'login' :
                $this->_signon();
                break;
            case 'logout' :
                $this->_logout();
                break;
        endswitch;
    }
    
    /** == Authentification de l'utilisateur == **/
    final public function authenticate( $user = null, $username, $password )
    {
        // Bypass
        if( ! $this->_getCurrent() )
            return $user;
        if( ! $user || is_wp_error( $user ) )
            return $user;

        if( ! array_intersect( $user->roles, (array) $this->_getCurrent()->getRoles() ) ) :
            $user = new \WP_Error( 'role_not_allowed' );        
        endif;
        
        if( $user ) :
            $user = call_user_func( array( $this->_getCurrent(), 'checkAuthenticate' ), $user, $username, $password );
        endif;
              
        return $user;            
    }
    
    /* = CONTROLEURS = */
    /** == Déclaration d'un formulaire d'authentification == **/
    public static function register( $id, $callback = null, $attrs = array() )
    {
        if( isset( self::$Factories[$id] ) ) 
            return;
        
        if( $callback )
            $path[] = $callback; 
        $path[] = "\\". self::getOverrideNamespace() . "\\Login\\". self::sanitizeControllerName( $id );    
            
        $callback = self::getOverride( '\\tiFy\\Components\\Login\\Factory', $path );   
        
        return self::$Factories[$id] = new $callback( $id, $attrs = array() );
    }
    
    /** == Définition du formulaire d'authentification courant == **/
    private function _setCurrent( $id )
    {
        // Bypass
        if( ! isset( self::$Factories[$id] ) )
            return;
        
        return ( static::$Current = self::$Factories[ $id ] );
    }
    
    /** == == **/
    private function _resetCurrent()
    {
        static::$Current = null;
    }
    
    /** == Définition du formulaire d'authentification courant == **/
    private function _getCurrent()
    {
        // Bypass
        if( ! static::$Current )
            return;
        
        return static::$Current;
    }
    
    /** == Authentification == **/
    private function _signon()
    {
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
                $user = new \WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
                    __( 'https://codex.wordpress.org/Cookies' ), __( 'https://wordpress.org/support/' ) ) );
            elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) :
                // If cookies are disabled we can't log in even with a valid user+pass
                $user = new \WP_Error( 'test_cookie', sprintf( __( '<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
                    __( 'https://codex.wordpress.org/Cookies' ) ) );
            endif;
        endif;
                
        if ( ! is_wp_error( $user ) && ! $reauth ) :
            wp_safe_redirect( $redirect_to );
            exit();
        else :
            static::$Current->setErrors( $user );
        endif;
    }
    
    /** == Déconnection == **/
    private function _logout()
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