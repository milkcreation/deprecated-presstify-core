<?php
/* = LISTE DES JEUX CONCOURS = */
Class tiFy_Contest_AdminListTable extends tiFy_AdminView_List_Table{
	/* = PARAMETRES = */
	public	// Référence
			$main;
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Contest_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_contest_item',
	            'plural'    => 'tify_contest_items',
	            'ajax'      => true,
	            'screen'	=> $this->master->admin->hookname['parent']
        	) 
		);
	}
	
	/* = CONFIGURATION = */
	/** == Préparation de l'object à éditer == **/
	public function prepare_items(){
		$items = array();
		foreach( (array) $this->master->get_list() as $contest_id => $args ) :
			$items[] = (object) wp_parse_args( array( 'ID' => $contest_id ), $args );
		endforeach;
		$this->items = $items;
	}
		
	/* = AFFICHAGE = */
	/** == COLONNES == **/
	/*** === Liste des colonnes === ***/
	public function get_columns(){
		return array(
			'contest' 				=> __( 'Jeux Concours', 'tify' ),
			'participations_details' 	=> __( 'Détails des participations', 'tify' ),
		);
	}
	
	/*** === Colonne Titre du jeux concours === ***/
	public function column_contest( $item ){
		echo $item->title;
	}
	
	/*** === === ***/
	public function column_participations_details( $item ){
		printf( __( 'Dates : du %1$s au %2$s', 'tify' ), mysql2date( 'd/m/y', $item->participations['start'] ), mysql2date( 'd/m/y', $item->participations['end'] ) );
	}
}