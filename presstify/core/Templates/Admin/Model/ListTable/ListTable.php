<?php
namespace tiFy\Core\Templates\Admin\Model\ListTable;
	
class ListTable extends \tiFy\Core\Templates\Admin\Model\Table
{
    /**
     * DECLENCHEURS
     */
	/**
	 * Mise en file des scripts de l'interface d'administration
	 * {@inheritDoc}
	 * @see \tiFy\Core\Templates\Admin\Model\Table::_admin_enqueue_scripts()
	 */
	public function _admin_enqueue_scripts()
	{
        parent::_admin_enqueue_scripts();
        
        wp_enqueue_style( 'tiFyTemplatesAdminListTable', self::tFyAppUrl( get_class() ) .'/ListTable.css', array(), 160617 ); 
    }    
}