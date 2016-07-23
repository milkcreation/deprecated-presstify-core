<?php
namespace tiFy\Core\Admin\Model\Import;

use tiFy\Core\Admin\Model\Table as tiFyCoreAdminModelTable;

class ListTable extends tiFyCoreAdminModelTable
{
	public $main;
	
	public function __construct( $Main )
	{
		$this->main = $Main;
		$this->View = $this->main->View;	
		$this->_current_screen( get_current_screen() );
	}
	
	/** == Définition des colonnes == **/
	public function get_columns()
	{
		$c['row'] = '#';	
		foreach( $this->main->column_map as $col => $args ) :
			$c[$col] = "<b>{$args['title']}</b><em style=\"display:block;font-size:0.8em;line-height:0.9;color:#999;\">". ( ! $args['meta'] ? __( 'Données de la table principale', 'tify' ) : __( 'Metadonnée', 'tify' ) ) ."</em>";
		endforeach;
		$c[ $this->main->table_id .'_tify_adminview_import_result' ] = "<b>". __( 'Action d\'import', 'tify' ) ."</b>";

		return $c;
	}
}