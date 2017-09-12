<?php
namespace tiFy\Core\Templates\Admin;

class Factory extends \tiFy\Core\Templates\Factory
{
    /**
     * Liste des actions à déclencher
     */
    protected $tFyAppActions                    = array(
        'init',
        'admin_init',
        'current_screen'    
    ); 
    
    /**
     * Contexte d'execution
     */ 
    protected static $Context                = 'admin';
    
    /**
     * Liste des modèles prédéfinis
     */ 
    protected static $Models                = array(
        //'AjaxExport', 
        'AjaxListTable',
        'CsvPreview',
        'EditForm', 
        'EditUser',
        //'Export',
        //'ExportUser',
        'FileImport',
        'Import', 
        'ListTable', 
        'ListUser',   
        'TabooxEditUser',
        'TabooxOption'
    );    
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        // Bypass
        if( ! $callback = $this->getAttr( 'cb' ) )
            return;

        $className = false;
        if( preg_match( '/\\\/', $callback ) ) :
            $className = self::getOverride( $callback );
        elseif( in_array( $callback, self::$Models ) ) :
            $className = "\\tiFy\\Core\\Templates\\". ucfirst( $this->getContext() ) ."\\Model\\{$callback}\\{$callback}";
        endif;

        if( ! $className || ! class_exists( $className ) )
            return;
        
        // Définition du modèle de base du template
        $this->setModel( $className );
        
        // Instanciation du template
        $this->Template = new $className( $this->getAttr( 'args', null ) );

        // Création des méthodes dynamiques
        $factory = $this;
        $this->Template->template     = function() use( $factory ){ return $factory; };
        /** @var \tiFy\Core\Db\Factory */
        $this->Template->db           = function() use( $factory ){ return $factory->getDb(); };
        $this->Template->label        = function( $label = '' ) use( $factory ){ if( func_num_args() ) return $factory->getLabel( func_get_arg(0) ); };        
        $this->Template->getConfig    = function( $attr, $default = '' ) use( $factory ){ if( func_num_args() ) return call_user_func_array( array( $factory, 'getAttr' ), func_get_args() ); };    
        
        // Identifiants de menu
        $menu_slug      = $this->getID(); 
        $parent_slug    = null;
        
        if( $admin_menu = $this->getAttr( 'admin_menu' ) ) :
            if( ! empty( $admin_menu['menu_slug'] ) ) :
                $menu_slug = $admin_menu['menu_slug'];
            endif;
            if( ! empty( $admin_menu['parent_slug'] ) ) :
                $parent_slug = $admin_menu['parent_slug'];   
            endif;
        endif;
        $this->setAttr( '_menu_slug', $menu_slug );
        $this->setAttr( '_parent_slug', $parent_slug );
        
        // Fonction de rappel d'affichage du template
        if( ! $this->getAttr( 'render_cb', '' ) )
           $this->setAttr( 'render_cb', 'render' ); 
        
        // Déclenchement des actions dans le template
        if( method_exists( $this->Template, '_init' ) ) :
            call_user_func( array( $this->Template, '_init' ) );
        endif;
        if( method_exists( $this->Template, 'init' ) ) :
            call_user_func( array( $this->Template, 'init' ) );
        endif;
    }
    
    /**
     * Initialisation de l'interface d'administration
     */
    final public function admin_init()
    {
        // Bypass
        if( ! $this->Template )
            return;
                    
        // Définition des attributs privés de la vue    
        $this->setAttr( '_hookname', \get_plugin_page_hookname( $this->getAttr( '_menu_slug' ), $this->getAttr( '_parent_slug' ) ) );
        $this->setAttr( '_menu_page_url', \menu_page_url( $this->getAttr( '_menu_slug' ), false ) );
        
        if( ! $this->getAttr( 'base_url' ) )
            $this->setAttr( 'base_url', \esc_attr( $this->getAttr( '_menu_page_url' ) ) );
            
        // Déclenchement des actions dans le template
        if( method_exists( $this->Template, '_admin_init' ) ) :
            call_user_func( array( $this->Template, '_admin_init' ) );
        endif;
        if( method_exists( $this->Template, 'admin_init' ) ) :
            call_user_func( array( $this->Template, 'admin_init' ) );
        endif;
    }
    
    /**
     * Chargement de l'écran courant
     */
    final public function current_screen( $current_screen )
    {
        // Bypass
        if( ! $this->Template )
            return;
        if( $current_screen->id !== $this->getAttr( '_hookname', '' ) )
            return;
        
        \tiFy\Core\Templates\Templates::$Current = $this;        
            
        // Mise en file des scripts de l'ecran courant
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) );
        
        // Déclenchement des actions dans le template           
        if( method_exists( $this->Template, '_current_screen' ) ) :
            call_user_func( array( $this->Template, '_current_screen' ), $current_screen );
        endif;
        if( method_exists( $this->Template, 'current_screen' ) ) :
            call_user_func( array( $this->Template, 'current_screen' ), $current_screen );
        endif;
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    final public function admin_enqueue_scripts()
    {             
        // Déclenchement des actions dans le template   
        if( method_exists( $this->Template, '_admin_enqueue_scripts' ) ) :
            call_user_func( array( $this->Template, '_admin_enqueue_scripts' ) );
        endif;
        if( method_exists( $this->Template, 'admin_enqueue_scripts' ) ) :
            call_user_func( array( $this->Template, 'admin_enqueue_scripts' ) );
        endif;
    }
    
    /**
     * Ecriture des scripts en pied de page de l'interface d'administration
     */
    final public function admin_print_footer_scripts()
    {
        // Déclenchement des actions dans le template    
        if( method_exists( $this->Template, '_admin_print_footer_scripts' ) ) :
            call_user_func( array( $this->Template, '_admin_print_footer_scripts' ) );
        endif;
        if( method_exists( $this->Template, 'admin_print_footer_scripts' ) ) :
            call_user_func( array( $this->Template, 'admin_print_footer_scripts' ) );
        endif;  
    }
}