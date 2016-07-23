<?php
class tiFy_Forum_AdminListTopic extends tiFy_AdminView_List_Table{
	/* = PARAMETRES = */
	private	// Référence
			$master,
			$main;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Définition des classes de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->topic;
		
		// Instanciation de la classe parente
       	parent::__construct( 
       		array(
	            'singular'  => 'tify_contest_topic',
	            'plural'    => 'tify_contest_topics',
	            'ajax'      => true,
	            'screen'	=> $this->master->hookname['topic']
        	),
        	$this->master->db->topic 
		);		
		
		// Définition des vues filtrées
		$this->views = array(
			'any'		=> array(
				'label'				=> __( 'Tous', 'tify' ),
				'value'				=> 'any',
				'remove_query_args'	=> 'status'
			),
			'trash'		=> array(
				'label'				=> __( 'Corbeille', 'tify' ),
				'value'				=> 'trash',
				'add_query_args'	=> array( 'status' => 'trash' ),
				'count_query_args'	=> array( 'status' => 'trash' )
			)	
		);
	}
	
	/* = AFFICHAGE = */	
	/** == COLONNES == **/
	/*** === Liste des colonnes === ***/
	public function get_columns(){
		return array(
			'topic_title'		=> __( 'Titre', 'tify' ),
			'topic_date'		=> __( 'Date de création', 'tify' ),
			'topic_modified'	=> __( 'Date de modification', 'tify' ),
		);
	}
	
	/*** === COLONNE - Titre === ***/
	public function column_topic_title( $item ){
		$title = ! $item->topic_title ? __( '(Pas de titre)', 'tify' ) : $item->topic_title;
		
		$status = false;
		if( $item->topic_status === 'trash' ) :
			$actions = $this->default_single_actions( $item, array( 'delete' ) );
			$status = __( '&Agrave; la corbeille', 'tify' );
		else :
			$actions = $this->default_single_actions( $item, array( 'edit', 'trash' ) );
		endif;
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>%4$s', $title, $this->get_item_edit_link( $item ), $status, $this->row_actions( $actions ) );		
	}
}