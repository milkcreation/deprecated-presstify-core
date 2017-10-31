<?php
namespace tiFy\Core\Taboox;

use tiFy\App\Core;

class Taboox extends Core
{
    // Boîtes à onglets déclarées
    public static $Boxes                = array();
    
    // Sections de boîte à onglets déclarées
    public static $Nodes                = array();
    
    // Interface d'administration déclarées
    public static $AdminForm            = array();
    
    // Classes de rappel de l'interfaces d'administration
    protected $AdminFormClass           = array();
    
    // Classes de rappel de l'interface visiteur
    public static $HelpersClass         = array();
    
    // Liste des identifiants d'accroche
    public static $HooknameMap          = array();
    
    // Translation des pages d'accroche
    public static $ScreenHooknameMap    = array();
        
    // ID de l'écran courant d'affichage de l'interface d'administration
    protected $CurrentScreenID;
    
    // Classe de rappel l'écran courant
    public static $Screen               = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements de déclenchement
        $this->tFyAppActionAdd('after_setup_tify', null, 11);
        $this->tFyAppActionAdd('init', null, 25);
        $this->tFyAppActionAdd('admin_init', null, 25);
        $this->tFyAppActionAdd('current_screen');
        $this->tFyAppActionAdd('admin_enqueue_scripts');
        $this->tFyAppActionAdd('add_meta_boxes');
        $this->tFyAppActionAdd('wp_ajax_tify_taboox_current_tab');
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * A l'issue de la configuration de PresstiFy
     *
     * @return void
     */
    final public function after_setup_tify()
    {
        // Bypass
        if (!self::tFyAppConfig()) :
            return;
        endif;

        foreach (self::tFyAppConfig() as $object => $hooknames) :
            if (!in_array($object, ['post', 'taxonomy', 'user', 'option'])) :
                continue;
            endif;

            foreach ((array)$hooknames as $hookname => $args) :
                if ($object === 'taxonomy') :
                    $hookname = 'edit-' . $hookname;
                endif;

                if (!empty($args['box'])) :
                    self::registerBox($hookname, $object, $args['box']);
                endif;

                if (!empty($args['nodes'])):
                    foreach ((array)$args['nodes'] as $node_id => $attrs) :
                        $attrs['id'] = $node_id;
                        self::registerNode($hookname, $attrs, $object);
                    endforeach;
                endif;
            endforeach;
        endforeach;
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des boîtes à onglets
        do_action('tify_taboox_register_box');

        // Déclaration des sections de boîtes à onglets
        do_action('tify_taboox_register_node');

        // Déclaration des helpers
        do_action('tify_taboox_register_helpers');

        // Initialisation des sections de boîtes à onglets            
        foreach( (array) self::$HooknameMap as $hookname ) :
            if( isset( self::$Nodes[$hookname] ) ) :
                foreach( self::$Nodes[$hookname] as $node )    :    
                    $this->initAdminFormClass( $node, $hookname );
                endforeach;
            endif;
        endforeach;

        // Initialisation des classes d'aide
        foreach( (array) self::$HelpersClass as $HelperClassName ) :
            new $HelperClassName;
        endforeach;
    }
    
    /** == Initialisation de l'interface d'administration == **/
    final public function admin_init()
    {
        // Déclaration des translations des pages d'accroche
        foreach( (array) self::$HooknameMap as $hookname ) :
            if( !  preg_match( '/::/', $hookname ) )
                continue;
            @list( $menu_slug, $parent_slug ) = preg_split( '/::/', $hookname, 2 );
                
            $screen_id = get_plugin_page_hookname( $menu_slug, $parent_slug );
            self::$ScreenHooknameMap[$screen_id] = $hookname;
        endforeach;
        
        // Déclenchement de l'action "Initialisation de l'interface d'administration" dans l'ensemble classes de rappel de formulaire
        foreach( (array) $this->AdminFormClass as $Screen => $Classes ) :
            foreach( (array) $Classes as $ID => $Class ) :
                if( is_callable( array( $Class, 'admin_init' ) ) ) :
                    call_user_func( array( $Class, 'admin_init' ) );
                endif;
            endforeach;
        endforeach;    
    }
    
    /** == Chargement de l'écran courant == **/
    final public function current_screen( $current_screen )
    {                            
        $Hookname = false;
        if( in_array( $current_screen->id, array_keys( self::$HooknameMap ) ) )
            $Hookname = $current_screen->id;
        if( isset( self::$ScreenHooknameMap[$current_screen->id] ) )
            $Hookname = self::$ScreenHooknameMap[$current_screen->id];

        // Bypass
        if( ! $Hookname )
            return;
        if( ! isset( self::$Boxes[$Hookname] ) || ! isset( self::$Nodes[$Hookname] ) )
            return;

        // Initialisation de la classe de l'écran courant             
        self::$Screen             = new Screen;
        self::$Screen->ID        = $current_screen->id;
        self::$Screen->Hookname    = $Hookname;
        self::$Screen->Box         = self::$Boxes[$Hookname];
        self::$Screen->Nodes     = self::$Nodes[$Hookname];

        foreach( (array) self::$Nodes[$Hookname] as $id => $attrs ) :
            if( ! empty( $this->AdminFormClass[$Hookname][$id] ) && is_callable( array( $this->AdminFormClass[$Hookname][$id], 'form' ) ) ) :
                self::$Screen->Forms[$id] = array( $this->AdminFormClass[$Hookname][$id], 'form' );
            elseif( ! empty( $attrs['cb'] ) && is_callable( $attrs['cb'] ) ) :
                self::$Screen->Forms[$id] = $attrs['cb'];
            endif;
        endforeach;
        
        // Création de la section de boites de saisie dans les environnements
        switch( self::$Boxes[$Hookname]['env'] ) :
            case 'post_type' :
            case 'post' :
                if( $Hookname === 'page' ) :
                    add_action( 'edit_page_form', array( self::$Screen, 'box_render' ) );
                else :
                    add_action( 'edit_form_advanced', array( self::$Screen, 'box_render' ) );
                endif;
                break;
            case 'option' :
                add_settings_section( self::$Screen->ID, null, array( self::$Screen, 'box_render' ), self::$Boxes[$Hookname]['page'] );
                break;
            case 'taxonomy' :
                add_action( $current_screen->taxonomy .'_edit_form', array( self::$Screen, 'box_render' ), 10, 2 );
                break;
        endswitch;
        
        // Déclenchement de l'action "Chargement de l'écran courant" dans les classes de rappel de formulaire
        if( ! empty( $this->AdminFormClass[$Hookname] ) ) :
            foreach( (array) $this->AdminFormClass[$Hookname] as $ID => $Class ) :
                if( is_callable( array( $Class, 'current_screen' ) ) ) :
                    call_user_func( array( $Class, 'current_screen' ), $current_screen );
                endif;
            endforeach;    
        endif;
    }
    
    /** == Mise en file des scripts de l'interface d'administration == **/
    final public function admin_enqueue_scripts()
    {            
        // Bypass
        if( empty( self::$Screen ) )
            return;

        // Chargement des scripts
        wp_enqueue_style( 'tify_taboox_admin', self::tFyAppUrl() . '/assets/Admin.css', array(), '150216' );
        wp_enqueue_script( 'tify_taboox_admin', self::tFyAppUrl() . '/assets/Admin.js', array(), '151019', true );

        // Déclenchement de l'action "Mise en file des scripts de l'interface d'administration" dans les classes de rappel de formulaire
        if( ! empty( $this->AdminFormClass[self::$Screen->Hookname] ) ) :
            foreach( (array) $this->AdminFormClass[self::$Screen->Hookname] as $ID => $Class ) :
                if( is_callable( array( $Class, 'admin_enqueue_scripts' ) ) ) :
                    call_user_func( array( $Class, 'admin_enqueue_scripts' ) );
                endif;
            endforeach;
        endif;
    }
    
    /** == Action Ajax de sauvegarde de l'onglet courant == **/
    final public function wp_ajax_tify_taboox_current_tab()
    {
        // Bypass    
        if( empty( $_POST['current'] ) )
            wp_die(0);
        
        list( $screen_id, $node_id ) = explode( ':', $_POST['current'] );
        
        update_user_meta( get_current_user_id(), 'tify_taboox_'. $screen_id, ! empty( $node_id ) ? $node_id : 0 );
        
        wp_send_json_success( $node_id );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration de boîte à onglets
     *
     * @param $hookname Identifiant d'accroche de la page d'affichage
     *
     * @return
     */
    public static function registerBox($hookname = null, $env = 'post', $args = array())
    {
        // Bypass    
        if( ! $hookname )
            return;

        if( is_string( $hookname ) )
            $hookname = array( $hookname );

        foreach( (array) $hookname as $_hookname ) :
            if( ! in_array( $_hookname, self::$HooknameMap ) )
                array_push( self::$HooknameMap, $_hookname );
            
            self::$Boxes[$_hookname] =     wp_parse_args( 
                                            $args, 
                                            array( 
                                                'title'     => '', 
                                                'page'         => '' 
                                            ) 
                                        );
                                        
            self::$Boxes[$_hookname]['env']    = $env;            
        endforeach;
    }
    
    /**
     * Déclaration de section de boîte à onglets
     * 
     * @param string $hookname Identifiant d'accroche de la boîte à onglet
     * @param array $args {
     *      Attributs de configuration de la section de boîte à onglets
     *      
     *      @var string $id Requis. Identifiant de la section.
     *      @var string $title Requis. Titre de la section.
     *      @var string $cb Classe de rappel d'affichage de la section.
     *      @var string $parent Identifiant de la section parente
     *      @var mixed $args Argument passé à la classe de rappel
     *      @var string $cap Habilitation d'accès à la section
     *      @var bool $show Affichage de la section
     *      @var int $order Ordre d'affichage
     *      @var string|string[] $helpers Chaine de caractères séparés par de virgules|Tableau indexé des classes de rappel des aides à la saisie
     * }
     * 
     * @return
     */
    public static function registerNode($hookname, $args = array())
    {
        $defaults = array(
            'id'            => null,
            'title'         => '',
            'cb'            => \__return_null(),
            'parent'        => 0,
            'args'          => array(),
            'cap'           => 'manage_options',
            'show'          => true,
            'order'         => 99,
            'helpers'       => \__return_null()
        );
        $args = wp_parse_args($args, $defaults);
        
        // Bypass
        if (! $args['id'])
            return;
        
        // Traitement de l'attribut titre
        if(! $args['title'])
            $args['title'] = $args['id'];
        
        if (is_string($hookname))
            $hookname = array($hookname);
        
        foreach ((array) $hookname as $_hookname) :
            if (! isset(self::$Boxes[$_hookname])) :
                self::registerBox($_hookname);
            endif;
            
            self::$Nodes[$_hookname][$args['id']] = $args;
        endforeach;
        
        if ($args['helpers']) :
            self::registerHelpers($args['helpers']);
        endif;
        
        return $args['id'];
    }
    
    /*** === Classes d'aide (affichage, récupération ...) === ***/
    public static function registerHelpers( $helpers )
    {
        if( is_string( $helpers ) )
            $helpers = array_map( 'trim', explode( ',', $helpers ) );

        foreach( $helpers as $helper ) :
            if( ! in_array( $helpers, self::$HelpersClass ) ) :
                array_push( self::$HelpersClass, $helper );
            endif;
        endforeach;
    }
        
    /** == INITIALISATION == **/
    /*** === Classe de formulaire d'administration === ***/
    private function initAdminFormClass( $node, $hookname )
    {
        // Bypass
        if( ! $node['cb'] || ! is_string( $node['cb'] ) || ! class_exists( $node['cb'] ) ) 
            return;
        
        $AdminFormClassArgs             = isset( self::$AdminForm[$node['cb']] ) ? self::$AdminForm[$node['cb']] : null;        
        $AdminFormClass                 = new $node['cb']( $AdminFormClassArgs );
        $AdminFormClass->ScreenID        = $hookname;
        $AdminFormClass->page             = self::$Boxes[$hookname]['page'];
        $AdminFormClass->env            = self::$Boxes[$hookname]['env'];
        $AdminFormClass->args             = $node['args'];
        
        if( is_callable( array( $AdminFormClass, 'init' ) ) ) :
            call_user_func( array( $AdminFormClass, 'init' ) );
        endif;
        
        $this->AdminFormClass[$hookname][$node['id']] = $AdminFormClass;
                
        return $this->AdminFormClass[$hookname][$node['id']] = $AdminFormClass;
    }    
}