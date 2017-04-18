<?php 
namespace tiFy\Environment;

abstract class Config
{
    /**
     * Récupération de la surchage de configuration
     * 
     * @param array $original_conf
     * @return array|mixed
     */   
    final public function get( $original_conf = array() )
    {
        // Traitement global des attributs de configuration
        $config = (array) call_user_func( array( $this, 'defaults' ), $original_conf );
        
        // Traitement par propriété des attributs de configuration
        if( $matches = preg_grep( '/^set_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $key = preg_replace( '/^set_/', '', $method );
                $config[$key] = call_user_func( array( $this, $method ) );
            endforeach;
        endif;

        return $config;
    }
    
    /**
     * Traitement global des attributs de configuration
     * 
     * @param array $attrs
     * @return array|mixed
     */
    public function defaults( $attrs = array() )
    {
        return $attrs;
    }
}