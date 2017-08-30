<?php
namespace tiFy\Core\Templates\Front;

class Factory extends \tiFy\Core\Templates\Factory
{
    /**
     * Liste des actions à déclencher
     */
    protected $tFyAppActions                  = array(
        'init',
        'template_redirect',
        'wp_enqueue_scripts'
    );
    
    /**
     * Contexte d'exécution
     */
    protected static $Context               = 'front';
    
    /**
     * Liste des modèles prédéfinis
     */
    protected static $Models                = array(
        'AjaxListTable',
        'EditForm',
        'ListTable'
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
        $this->Template->db           = function() use( $factory ){ return $factory->getDb(); };
        $this->Template->label        = function( $label = '' ) use( $factory ){ if( func_num_args() ) return $factory->getLabel( func_get_arg(0) ); };        
        $this->Template->getConfig    = function( $attr, $default = '' ) use( $factory ){ if( func_num_args() ) return call_user_func_array( array( $factory, 'getAttr' ), func_get_args() ); };     
            
        if( ! $this->getAttr( 'base_url' ) )
            $this->setAttr( 'base_url', \site_url( $this->getAttr( 'route' ) ) ); 
        
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
     * Court-circuitage de l'affichage
     */
    final public function template_redirect()
    {
        // Bypass
        if( ! $this->Template )
            return;
        $rewrite_base = parse_url( home_url() );
        
        if ( isset( $rewrite_base['path'] ) ) :
            $rewrite_base = trailingslashit( $rewrite_base['path'] );
        else :
            $rewrite_base = '/';
        endif;

        if( ! preg_match( '/^'. preg_quote( $rewrite_base . ltrim( $this->getAttr( 'route' ), '//' ), '/' ) .'\/?$/', Front::getRoute() ) )
            return;
        
        \tiFy\Core\Templates\Templates::$Current = $this; 
            
        // Déclenchement des actions dans le template           
        if( method_exists( $this->Template, '_current_screen' ) ) :
            call_user_func( array( $this->Template, '_current_screen' ) );
        endif;
        if( method_exists( $this->Template, 'current_screen' ) ) :
            call_user_func( array( $this->Template, 'current_screen' ) );
        endif;    
        
        if( $template = $this->getAttr( 'template_part' ) ) :
            get_template_part( $template );
            exit;
        else :
            $this->render();
            exit;
        endif;
    }
    
    /**
     * Mise en file des scripts
     */
    final public function wp_enqueue_scripts()
    {                        
        // Bypass
        if( ! $this->Template )
            return;
        
        // Déclenchement des actions dans le template     
        if( method_exists( $this->Template, '_wp_enqueue_scripts' ) ) :
            call_user_func( array( $this->Template, '_wp_enqueue_scripts' ) );
        endif;
        if( method_exists( $this->Template, 'wp_enqueue_scripts' ) ) :
            call_user_func( array( $this->Template, 'wp_enqueue_scripts' ) );
        endif;
    }
}