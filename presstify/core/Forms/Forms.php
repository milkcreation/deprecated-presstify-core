<?php
namespace tiFy\Core\Forms;

use tiFy\tiFy;

class Forms extends \tiFy\Environment\Core
{
    /* = ARGUMENTS = */
    // Liste des actions à déclencher
    protected $tFyAppActions                = array(
        'after_setup_tify',
        'init',
        'admin_init',
        'wp'
    );        
    // Ordres de priorité d'exécution des actions
    protected $tFyAppActionsPriority    = array(
        'after_setup_tify'      => 11,
        'init'                  => 1,
        'wp'                    => 0
    );
    
    // Liste des Formulaires déclarés
    private static $Factories            = array();
    
    // Formulaire courant 
    private static $Current                = null;
    
    /* = CONSTRUCTEUR = */
    public function __construct()
    {        
        parent::__construct();
        
        // Initialisation des classes de contrôle
        new Addons;
        new Buttons;
        new FieldTypes;
    }

    /* = DECLENCHEURS = */
    /** == Initialisation l'interface d'administration == **/
    final public function after_setup_tify()
    {        
        // Boutons
        /// Instanciation    
        Buttons::init();
        /// Déclaration des boutons personnalisés
        do_action( 'tify_form_register_button' );
        
        // Types de champs
        /// Instanciation
        FieldTypes::init();
        /// Déclaration des types de champs personnalisés
        do_action( 'tify_form_register_type' );
        
        // Addons
        // Instanciation
        Addons::init();
        // Déclaration des addons personnalisés
        do_action( 'tify_form_register_addon' );
    }
    
    /** == Déclaration des formulaires == **/
    final public function init()
    {
        if( is_admin() )
            $this->registration();
    }
    
    /** == Déclaration des formulaires pour les requêtes ajax - !!! Modifier pour AddonAjaxSubmit puis rétablie pour AddonRecord 
    final public function admin_init(){ if( defined( 'DOING_AJAX' ) ) $this->registration();} == **/
    
    /** == Chargement de Wordpress complet == **/
    final public function wp()
    {
        if( ! is_admin() )
            $this->registration();
                
        do_action( 'tify_form_loaded' );
    }
        
    /* = PARAMETRAGE = */    
    /** == Déclaration des formulaires == **/
    private function registration()
    {
        // Déclaration des formulaires
        /// Depuis la configuration statique
        foreach( (array) self::tFyAppConfig() as $id => $attrs ) :
            $this->register($id, $attrs);
        endforeach;
            
        /// Depuis la déclaration dynamique    
        do_action( 'tify_form_register' );
    }    
    
    /** == Déclaration d'un formulaire == **/
    final public static function register($id, $attrs = array())
    {
        // retro-compatibilité
        if (! empty($attrs['ID'])) :
            $id = $attrs['ID'];
        else :
            $attrs['ID'] = $id;
        endif;

        $override_path = [];
        foreach ((array)self::getOverrideNamespaceList() as $namespace) :
            $override_path[] = $namespace . "\\Core\\Forms\\". self::sanitizeControllerName($id);
        endforeach;

        $FactoryClassName = self::getOverride( '\tiFy\Core\Forms\Factory', $override_path);
        $form = self::$Factories[$id] = new $FactoryClassName($id, $attrs);
        
        return $form->getForm()->getID();
    }
    
    /* = CONTROLEURS = */
    /** == Récupération d'un formulaire déclaré == **/
    public static function has( $id )
    {
        return isset( self::$Factories[ $id ] );
    }
    
    /** == Récupération d'un formulaire déclaré == **/
    public static function get( $id )
    {
        if( self::has( $id ) )
            return self::$Factories[ $id ];
    }
    
    /** == Récupération de la liste des formulaires == **/
    public static function getList()
    {
        return self::$Factories;    
    }    
    
    /** == Définition du formulaire courant == **/
    public static function setCurrent( $form = null )
    {
        if( ! is_object( $form ) )
            $form = self::get( $form );
                
        if( ! $form instanceof \tiFy\Core\Forms\Factory )
            return;
            
        self::$Current = $form;
        self::$Current->getForm()->onSetCurrent();
        
        return self::$Current;
    }
    
    /** == Définition du formulaire courant == **/
    public static function getCurrent()
    {
        return self::$Current;
    }
    
    /** == Définition du formulaire courant == **/
    public static function resetCurrent()
    {
        if( self::$Current )
            self::$Current->getForm()->onResetCurrent();
        
        self::$Current = null;
    }
    
    /** == Affichage du formulaire == **/
    public static function display( $form_id = null, $echo = false )
    {
        // Bypass
        if( ! $form = self::setCurrent( $form_id ) )
            return;

        // Traitement des options du formulaire    
        $output  = "";
        $output .= "\n<div id=\"tiFyForm-{$form_id}\" class=\"tiFyForm\">";
        $output .= $form->display( false );
        $output .= "\n</div>";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
}