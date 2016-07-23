<?php
class tiFy_WebAgencyCRM_AdminListTask extends tiFy_AdminView_ListTable{
	/* = PARAMETRES = */
	private	// OPTIONS
			// Classe de référence
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
	
	/* = CHARGEMENT = */
	public function current_screen(){
		// Chargement des scripts
		wp_enqueue_style( 'tify_wacrm-task_table', $this->master->admin->uri .'/css/task-table.css', array(), '151019' );
	}
	
	
	/* = PARAMETRAGE = */
	/** == Définition de la liste des colonnes == **/
	public function get_columns(){
		return array(
			'title'				=> __( 'Titre', 'tify' ),
			'project'			=> __( 'Projet', 'tify' ),
			'employee'			=> __( 'Attribuée à', 'tify' ),
			'dates'				=> __( 'Effectuée le', 'tify' ),
			'infos'				=> __( 'Information', 'tify' )
		);
	}

	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns(){
		$c = array(	
			'title' 				=> array( 'task_title', false ),
		);

		return $c;
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name )
			return;

		if( $item->task_status !== 'trash' ) 
			return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'trash' ) ) );
		else 
			return $this->row_actions( $this->get_item_actions( $item, array( 'untrash', 'delete' ) ) );
	}	
	
	/* = AFFICHAGE = */		
	/** == COLONNE - Titre == **/
	public function column_title( $item ){
		$title = ! $item->task_title ? __( '(Pas de titre)', 'tify' ) : $item->task_title;
		
		$status = false;
		if( $item->task_status === 'trash' )
			$status = __( '&Agrave; la corbeille', 'tify' );
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>', $title, $this->get_edit_uri( $item ), $status );		
	}
	
	/*** === COLONNE - Projet === ***/
	public function column_project( $item ){
		if( ! $rel = $this->master->db->project->get_item_by_id( $item->task_project_id ) )
			return;
		
		$output  = "";
		$output .= "<a href=\"". add_query_arg( 'task_project_id', $rel->project_id, $this->base_url ) ."\" title=\"". sprintf( __( 'Filtrée par %s', 'tify' ), $rel->project_title ). "\">{$rel->project_title}</a>";
		$output .= "<ul style=\"margin:0; padding:0;mist-style-type:none;\">";
		if( $customer = get_userdata( $rel->project_customer_id ) )
			$output .= 	"<li style=\"margin:0; padding:0;\">".
							"<label style=\"vertical-align:top;font-weight:bold;\">". __( 'Client', 'tify' ) ." : </label>".
							$customer->nickname;
						"</li>";	
		
		$output .= 	"<li style=\"margin:0; padding:0;\">".
						"<a href=\"#\" ".
							" title=\"". sprintf( __( 'Editer le projet %s', 'tify' ), $rel->project_title ). "\" ".
							" style=\"color:#666;text-transform:uppercase;font-size:10px;font-weight:bold;\" target=\"_blank\" >".
								__( 'Éditer le projet', 'tify' ) .
						"</a>".
					"</li>";
	
		$output .= "</ul>";		
		
		return $output;
	}
	
	/*** === COLONNE - Attribution === ***/
	public function column_employee( $item ){
		if( $userdata = get_userdata( $item->task_employee ) )
			return 	"<a href=\"". add_query_arg( 'task_employee', $userdata->ID, $this->item_action_base_link ) ."\"".
						" title=\"". sprintf( __( 'Filtrée les tâches attribuées à %s', 'tify' ), $userdata->display_name ). "\">".
							$userdata->display_name .
					"</a>";
	}
	
	/*** === COLONNE - Dates === ***/
	public function column_dates( $item ){
		$start 		= new DateTime( $item->task_start_datetime );
		$end 		= new DateTime( $item->task_end_datetime );
		$duration 	= $start->diff($end);
		$output  = "";
		$output .= "<ul style=\"margin:0; padding:0;mist-style-type:none;\">";
		$output .= 	"<li style=\"margin:0; padding:0;\">".
						"<label style=\"vertical-align:top;\"><strong>". __( 'Début', 'tify' ) ." : </strong></label>".
						$start->format( 'd/m/Y H:i' );
					"</li>";	
		$output .= 	"<li style=\"margin:0; padding:0;\">".
						"<label style=\"vertical-align:top;\"><strong>". __( 'Fin', 'tify' ) ." : </strong></label>".
						$end->format( 'd/m/Y H:i' );
					"</li>";
		$output .= 	"<li style=\"margin:0; padding:0;\">".
						"<label style=\"vertical-align:top;\"><strong>". __( 'Durée', 'tify' ) ." : </strong></label>".
						zeroise( $duration->h, 2 ) . ':'. zeroise( $duration->i, 2 );
					"</li>";	
		$output .= "</ul>";
		
		return $output;
	}
	
	/*** === COLONNE - Informations === ***/
	public function column_infos( $item ){
		$output  = "";
		$output .= 	"<ul>";
		// Date de création
		$output .= 		"<li>".
							"<label><strong>". __( 'Créé', 'tify' ) ." : </strong></label>".
							sprintf( __( 'le %s à %s', 'tify' ), mysql2date( 'd/m/Y', $item->task_date ), mysql2date( 'H\hi', $item->task_date ) );
		if( $userdata = get_userdata( $item->task_author ) )
			$output .=		"<br>". sprintf( __( 'par %s', 'tify' ), $userdata->display_name );							
		$output .= 		"</li>";
		// Date de Modification
		if( $item->task_modified !== "0000-00-00 00:00:00" ) :
			$output .= 		"<li>".
								"<label><strong>". __( 'Modifié', 'tify' ) ." : </strong></label>".
								sprintf( __( 'le %s à %s', 'tify' ), mysql2date( 'd/m/Y', $item->task_modified ), mysql2date( 'H\hi', $item->task_modified ) );
			if( ( $last_editor = $this->db->get_item_meta( $item->{$this->primary_key}, '_edit_last' ) ) && ( $userdata = get_userdata( $last_editor ) ) )
				$output .=		"<br>". sprintf( __( 'par %s', 'tify' ), $userdata->display_name );							
			$output .= 		"</li>";
		endif;
		$output .= "</ul>";
		
		return $output;
	}
}