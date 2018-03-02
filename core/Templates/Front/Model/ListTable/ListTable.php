<?php
namespace tiFy\Core\Templates\Front\Model\ListTable;
    
class ListTable extends \tiFy\Core\Templates\Front\Model\Table
{
    /**
     * DECLENCHEURS
     */
    /**
     * Mise en file des scripts
     */
    public function wp_enqueue_scripts()
    {
        wp_enqueue_style( 'tiFy\Core\Templates\Front\Model\ListTable\ListTable', self::tFyAppUrl( get_class() ) .'/ListTable.css', array(), 161214 );
    }
}