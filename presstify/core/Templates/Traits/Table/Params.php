<?php 
namespace tiFy\Core\Templates\Traits\Table;

trait Params
{
	/* = INITIALISATION DES PARAMETRES = */
	/** == Initialisation des paramètres de configuration de la table == **/
	protected function initParams()
	{
		foreach( (array) $this->ParamsMap as $param ) :
			if( ! method_exists( $this, 'initParam' . $param ) ) 
				continue;
			call_user_func( array( $this, 'initParam' . $param ) );
		endforeach;
	}
	
	/** == Initialisation de l'url de la page d'administration == **/
	public function initParamBaseUri()
	{
		$this->BaseUri = $this->getConfig( 'base_url' );
	}
	
	/** == Initialisation de l'url d'édition d'un élément == **/
	public function initParamEditBaseUri()
	{
		if( $this->EditBaseUri = $this->set_edit_base_url() ) :
		elseif( $edit_template = $this->getConfig( 'edit_template' ) ) :
			$Method = ( $this->template()->getContext() === 'admin' ) ? 'getAdmin' : 'getFront';

			$this->EditBaseUri = \tiFy\Core\Templates\Templates::$Method( $edit_template )->getAttr( 'base_url' );
		elseif( $this->EditBaseUri = $this->getConfig( 'edit_base_url' ) ) :
		endif;
	}
	
	/** == Initialisation de l'intitulé des objets traités == **/
	public function initParamPlural()
	{
		if( ! $plural = $this->set_plural() )
			$plural = $this->template()->getID();
		
		$this->Plural = sanitize_key( $plural );
	}
	
	/** == Initialisation de l'intitulé d'un objet traité == **/
	public function initParamSingular()
	{
		if( ! $singular = $this->set_singular() )
			$singular = $this->template()->getID();
		
		$this->Singular = sanitize_key( $singular );
	}
		
	/** == Initialisation des notifications == **/
	public function initParamNotices()
	{
		$this->Notices = $this->parseNotices( $this->set_notices() );
	}
	
	/** == Initialisation des statuts == **/
	public function initParamStatuses()
	{
		$this->Statuses = $this->set_statuses();
	}
	
	/** == Initialisation des vues filtrées == **/
	public function initParamFilteredViewLinks()
	{			
		$this->FilteredViewLinks = $this->parseViews( $this->set_views() );
	}
	
	/** == Initialisation des classes de la table == **/
	public function initParamTableClasses()
	{
		$this->TableClasses = $this->set_table_classes();	
	}
	
	/** == Initialisation des colonnes de la table == **/
	public function initParamColumns()
	{	
		if( $columns = $this->set_columns() ) :
		elseif( $columns = $this->getConfig( 'columns' ) ) :
		else :
			$columns['cb'] = "<input id=\"cb-select-all-1\" type=\"checkbox\" />";
			foreach( (array)  $this->db()->ColNames as $name ) :
				$columns[$name] = $name;
			endforeach;
		endif;
		
		$this->Columns = $columns;
	}
	
	/** == Initialisation des colonnes triables == **/
	public function initParamSortableColumns()
	{
		$this->SortableColumns = $this->set_sortable_columns();
	}
	
	/** == Initialisation des colonnes masquées == **/
	public function initParamHiddenColumns()
	{
		if( $hidden_cols = $this->set_hidden_columns() ) :
		elseif( $hidden_cols = $this->getConfig( 'hidden_columns' ) ) :
		else :
			$hidden_cols = array();
		endif;

		if( $hidden_cols ) :
			$this->HiddenColumns = $hidden_cols;
			add_filter( 'hidden_columns', function( $hidden, $screen, $use_defaults ) use ( $hidden_cols ){ return $hidden_cols; }, 10, 3 );
		endif;
	}
	
	/** == Initialisation de la colonne principale == **/
	public function initParamPrimaryColumn()
	{
		if( $primary = $this->set_primary_column() ) :
		elseif( $primary = $this->getConfig( 'primary_column' ) ) :
		else :
			$primary = null;
		endif;

		if( $primary ) :
			$this->PrimaryColumn = $primary;
			add_filter( 'list_table_primary_column', function( $default ) use ( $primary ){ return $primary; }, 10, 1 );
		endif;
	}
	
	/** == Initialisation des arguments de requête == **/
	public function initParamQueryArgs()
	{
		$this->QueryArgs = (array) $this->set_query_args();
	}
	
	/** == Initialisation du nombre d'éléments affichés par page == **/
	public function initParamPerPage()
	{
		$this->PerPage = ( $per_page = (int) $this->set_per_page() ) ? $per_page : 20;	
	}
	
	/** == == **/
	public function initParamPerPageOptionName()
	{
		if( ! $per_page_option = $this->set_per_page_option_name() )
			return;
			
		$per_page_option = is_bool( $per_page_option ) ? $this->template()->getID() .'_per_page' : (string) $per_page_option;
		add_filter( 'set-screen-option', function( $none, $option, $value ) use ( $per_page_option ){ return ( $per_page_option  ===  $option ) ? $value : $none; }, 10, 3 );
		$per_page = $this->PerPage;
		add_filter( $this->PerPageOptionName, function() use ( $per_page ){ return $per_page; }, 0 );
	}
	
	/** == Initialisation de l'intitulé lorsque la table est vide == **/
	public function initParamNoItems()
	{
		$this->NoItems = ( $no_items = $this->set_no_items() ) ? $no_items :  ( ( $no_items = $this->label( 'not_found' ) ) ? $no_items : __( 'No items found.' ) );	
	}
	
	/** == Initialisation des actions groupées == **/
	public function initParamBulkActions()
	{
		$this->BulkActions = $this->set_bulk_actions();	
	}
	
	/** == Initialisation des actions sur un élément de la liste == **/
	public function initParamRowActions()
	{
		foreach( (array) $this->set_row_actions() as $action => $attr ) :
			if( is_int( $action ) ) :
				$this->RowActions[$attr] = array();
			else :
				$this->RowActions[$action] = $attr;
			endif;
		endforeach;	
	}
}