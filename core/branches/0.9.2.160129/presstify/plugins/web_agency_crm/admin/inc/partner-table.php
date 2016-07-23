<?php
class tiFy_WebAgencyCRM_AdminListPartner extends tiFy_AdminView_ListTable{
	/* = PARAMETRES = */
	private	// Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_WebAgencyCRM_Master $master, tiFy_Query $query ){
		// Définition des classes de référence
		$this->master 	= $master;
		
		// Instanciation de la classe parente
       	parent::__construct( $query );	
		
		// Paramétrage
		/// Définition des vues filtrées
		$status = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any';
		$this->views = array(
			'any'		=> array(
				'label'				=> __( 'Tous', 'tify' ),
				'current'			=> $status === 'any' ? true : false,
				'remove_query_args'	=> 'status'
			),
			'trash'		=> array(
				'label'				=> __( 'Corbeille', 'tify' ),
				'current'			=> $status === 'trash' ? true : false,
				'add_query_args'	=> array( 'status' => 'trash' ),
				'count_query_args'	=> array( 'status' => 'trash' )
			)	
		);			
	}	
	
	/* = PARAMETRAGE = */
	/** == Définition de la liste des colonnes == **/
	public function get_columns(){
		return array(
			'title'				=> __( 'Titre', 'tify' )
		);
	}

	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		$c = array(	
			'title' 		=> array( 'partner_title', false )
		);

		return $c;
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name )
			return;

		if( $item->partner_status !== 'trash' ) 
			return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'trash' ) ) );
		else 
			return $this->row_actions( $this->get_item_actions( $item, array( 'untrash', 'delete' ) ) );
	}
	
	/* = AFFICHAGE = */
	/** == COLONNE - Titre == **/
	public function column_title( $item ){
		$title = ! $item->partner_title ? __( '(Pas de titre)', 'tify' ) : $item->partner_title;
		
		$status = false;
		if( $item->partner_status === 'trash' )
			$status = __( '&Agrave; la corbeille', 'tify' );
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>', $title, $this->get_edit_uri( $item->{$this->primary_key} ), $status );		
	}
}