<?php 
namespace tiFy\App;

use tiFy\Apps;

abstract class Config
{
    /**
     * CONSTRUCTEUR
     */
    public function __construct($classname)
    {

        if (!$attrs = Apps::getAttrs($classname)) :
            return;
        endif;

        $this->filter($attrs['Config'], $classname);
    }

    /**
     * Récupération de la surchage de configuration
     * 
     * @param array $attrs Attributs de configuration initiaux
     * 
     * @return array|mixed
     */   
    final private function filter($attrs = [], $classname)
    {
        // Traitement global des attributs de configuration
        $attrs = (array) call_user_func( array( $this, 'sets' ), $attrs );
        
        // Traitement par propriété des attributs de configuration
        if( $matches = preg_grep( '/^set_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $key = preg_replace( '/^set_/', '', $method );
                $default = isset( $attrs[$key] ) ? $attrs[$key] : '';
                $attrs[$key] = call_user_func( array( $this, $method ), $default );
            endforeach;
        endif;

        Apps::setAttrs(['Config' => $attrs],$classname);
    }
    
    /**
     * Définition globale des attributs de configuration
     * 
     * @param mixed $attrs Liste des attributs existants
     * 
     * @return array|mixed
     */
    public function sets($attrs = [])
    {
        return $attrs;
    }
}