<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\CookieNotice;

class CookieNotice extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'cookie_notice';
    
    /**
     * Instance Courante
     */ 
    protected static $Instance = 0;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale de Wordpress
     */
    final public function init()
    {
        wp_register_script( 'tify_control-cookie_notice', self::tFyAppAssetsUrl('CookieNotice.js', get_class()), array( 'jquery' ), 170626, true );

        // Actions ajax
        add_action( 'wp_ajax_tiFyControlCookieNotice', array( $this, 'wp_ajax' ) );
        add_action( 'wp_ajax_nopriv_tiFyControlCookieNotice', array( $this, 'wp_ajax' ) );
    }

    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_script( 'tify_control-cookie_notice' );   
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du controleur
     * @param array $args
     * @return string
     */
    public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // Identifier
            'id'                    => 'tiFyControlCookieNotice-' . self::$Instance,
            // ID HTML du conteneur
            'container_id'          => 'tiFyControlCookieNotice--'. self::$Instance,
            // Classe HTML du conteneur
            'container_class'       => '',
            // 
            'cookie_name'           => '',
            // Expiration du cookie - exprimée en sec. 1 heure (3600sec) par défaut
            'cookie_expire'         => HOUR_IN_SECONDS,            
            // Contenu de la notification
            'html'                  => '',
        );        
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Traitement des arguments
        /// Action de récupération via ajax
        $ajax_action = 'tiFyControlCookieNotice';
        /// Agent de sécurisation de la requête ajax
        $ajax_nonce = wp_create_nonce( 'tiFyControlCookieNotice' );
        // Nom du cookie
        if( ! $cookie_name )
            $cookie_name = $id .'_';        
        
        // Liste des arguments pour le traitement de la requête Ajax
        $ajax_attrs = compact( 'ajax_action', 'ajax_nonce', 'cookie_name', 'cookie_expire' );        
        
        // Selecteur HTML
        $output  = "";
        $output .= "<div id=\"{$container_id}\" class=\"tiFyControlCookieNotice". ( $container_class ? ' '. $container_class : '' ) ."\" data-tify_control=\"cookie_notice\" data-attrs=\"". htmlentities( json_encode( $ajax_attrs ) ) ."\">\n";
        if( ! static::has( $cookie_name ) ) :
            $output .= $html ? $html : static::html( $args );
        endif;
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
    
    /**
     * Contenu de la notification
     * Pour fonctionner un lien contenant l'attribut data-toggle est requis
     */
    public static function html( $args = array() )
    {
        return "<a class=\"tiFyControlCookieNotice\" href=\"#{$args['container_id']}\" data-toggle=\"fade\" title=\"". __( 'Masquer l\'avertissement', 'tify' ) ."\">". __( 'Ignorer l\'avertissement', 'tify' ) ."</a>";
    }
    
    /**
     * Cookie de notification d'alerte site Pro
     */
    public function wp_ajax()
    {
        check_ajax_referer( 'tiFyControlCookieNotice' );
        
        $cookie_name = $_POST['cookie_name'];
        $cookie_expire = $_POST['cookie_expire'];
        $secure = ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
        
        setcookie( $cookie_name . COOKIEHASH, true, time() + $cookie_expire, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
        if ( COOKIEPATH != SITECOOKIEPATH ) :
            setcookie( $cookie_name . COOKIEHASH, true, time() + $cookie_expire, SITECOOKIEPATH, COOKIE_DOMAIN, $secure, true );
        endif;
        wp_die(1);
    }
    
    /**
     * Vérification d'existance du cookie
     */
    public static function has( $cookie_name )
    {
        return ! empty( $_COOKIE[ $cookie_name . COOKIEHASH ] );
    }
}