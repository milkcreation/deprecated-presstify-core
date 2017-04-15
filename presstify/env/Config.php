<?php 
namespace tiFy\Environment;

abstract class Config
{
    final public function get()
    {
        $config = array();
        if( $matches = preg_grep( '/^set_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $key = preg_replace( '/^set_/', '', $method );
                $config[$key] = call_user_func( array( $this, $method ) );
            endforeach;
        endif;

        return $config;
    }
}