<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\Sidebar;

use \tiFy\Components\Sidebar\Sidebar;

class Factory extends \tiFy\Environment\App
{  
    /**
     * 
     */
    final public function register()
    {
        if( $matches = preg_grep( '/^node_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $id = preg_replace( '/^node_/', '', $method );
                ob_start(); 
                call_user_func( array( $this, $method ) );
                $html = ob_get_clean();  
                Sidebar::register( $id, array( 'html' => $html ) );
            endforeach;
        endif; 
    }
}