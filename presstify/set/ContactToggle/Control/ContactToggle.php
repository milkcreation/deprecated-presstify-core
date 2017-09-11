<?php
/** @Overridable */
namespace tiFy\Set\ContactToggle\Control;

use tiFy\Lib\Modal\Modal;

class ContactToggle extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'contact_toggle';
    
    /**
     * Instance Courante
     */
    static $Instance;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale de Wordpress
     */
    public function init()
    {
        wp_register_script( 'tify_control-contact_toggle', static::tFyAppUrl( get_class() ) .'/ContactToggle.js', array( 'jquery' ), 170301, true );
    
        add_action( 'wp_ajax_tiFySetCoreControl_ContactToggle', array( $this, 'ajax' ) );
        add_action( 'wp_ajax_nopriv_tiFySetCoreControl_ContactToggle', array( $this, 'ajax' ) );
    }
    
    /**
     * Mise en file des scripts
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script( 'tify_control-contact_toggle' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;

        $defaults = array(
            // Id Html du conteneur
            'id'                => 'tiFySetCoreControl_contactToggle--'. self::$Instance,
            // Class Html du conteneur
            'class'             => '',
            // Titre Html du conteneur
            'title'             => '',
            // Intitulé du conteneur
            'text'              => __( 'Contacter', 'Theme' ),
            
            'query_args'        => array(),
            'modal'             => array()
        );        
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Traitement des attributs
        // @see 
        $modal = wp_parse_args( 
            $modal,
            array(
                'id'                => 'tiFyModal-setContactToggle--'. self::$Instance,
                'class'             => 'tiFyModal-setContactToggle',
                'target'            => 'tiFySetCoreControl_contactToggleModal--'. self::$Instance,
                'options'            => array(
                    'backdrop'          => true, // false | 'static'
                    'keyboard'          => true,
                    'show'              => false
                ),
                'dialog'            => array(
                    'size'              => null,
                    'title'             => __( 'Prendre contact', 'tify' ),
                    'header_button'     => true
                )
            )
        );
        
        $ajax_action = 'tiFySetCoreControl_ContactToggle';
        $ajax_nonce = wp_create_nonce( 'tiFySetCoreControl_ContactToggle' );
        $ajax_attrs = compact( 'ajax_action', 'ajax_nonce', 'query_args');
        
        $output  = "";
        $output .= "<a href=\"#{$id}\" title=\"{$title}\" id=\"{$id}\" class=\"tiFyControlContactToggle". ( $class? " ". (string) $class : '' ) ."\" data-tify_control=\"contact_toggle\" data-attrs=\"". htmlentities( json_encode( $ajax_attrs ) ) ."\" data-target=\"". esc_attr( $modal['target'] ) ."\">". $text ."</a>";
        Modal::display( $modal ); 
        
        if( $echo )
            echo $output;
    
        return $output;
    }
    
    /**
     * Récupération ajax des données
     */
    public function ajax()
    {
        check_ajax_referer( 'tiFySetCoreControl_ContactToggle' );
       
        $data = $this->response( $_POST['query_args'] );    
        if( ! is_wp_error( $data ) ) :
            wp_send_json_success( $data );
        else :
            wp_send_json_error( $data->get_error_messages() );
        endif;
    }
    
    /**
     * Réponse
     */
    public function response( $query_args = array() )
    {
        return get_option( 'admin_email' );
    }
}