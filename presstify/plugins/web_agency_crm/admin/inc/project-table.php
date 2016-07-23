<?php
class tiFy_WebAgencyCRM_AdminListProject extends tiFy_AdminView_ListTable{
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
		wp_enqueue_style( 'tify_wacrm-project_table', $this->master->admin->uri .'/css/project-table.css', array(), '151019' );
	}
	
	/* = PARAMETRAGE = */
	/** == Définition de la liste des colonnes == **/
	public function get_columns(){
		return array(
			'cb'				=> "<input type=\"checkbox\" />",
			'title'				=> __( 'Titre', 'tify' ),
			'project_ref'		=> __( 'Référence', 'tify' ),
			'customer'			=> __( 'Client', 'tify' ),
			'cumul'				=> __( 'Cumul', 'tify' ),
			'infos'				=> __( 'Informations', 'tify' )
		);
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns(){
		$c = array(	
			'title' 				=> array( 'project_title', false ),
			'project_ref' 			=> array( 'project_ref', false )
		);

		return $c;
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name )
			return;

		if( $item->project_status !== 'trash' ) 
			return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'trash' ) ) );
		else 
			return $this->row_actions( $this->get_item_actions( $item, array( 'untrash', 'delete' ) ) );
	}
		
	/* = AFFICHAGE = */		
	/** == COLONNE - Titre == **/
	public function column_title( $item ){
		$title = ! $item->project_title ? __( '(Pas de titre)', 'tify' ) : $item->project_title;
		
		$status = false;
		if( $item->project_status === 'trash' )
			$status = __( '&Agrave; la corbeille', 'tify' );
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>', $title, $this->get_edit_uri( $item ), $status );		
	}
	
	/** == COLONNE - Client == **/
	public function column_customer( $item ){
		if( $userdata = get_userdata( $item->project_customer_id ) )
			return  "<a href=\"". add_query_arg( array( 'project_customer_id' => $item->project_customer_id, 'filter_action' => 'filter' ), $this->base_url ) ."\"".
						" title=\"". sprintf( __( 'Afficher uniquement les projets du client %s', 'tify' ), $userdata->nickname ). "\">".
						$userdata->nickname.
					"</a>";		
	}
	
	/** == COLONNE - Cumul == **/
	public function column_cumul( $item ){
		if( ! $cumul_by_employee = $this->master->db->task->cumul_by_employee( $item->project_id ) )
			return;
		
		$output  = "";
		$output .= "<ul>"; 
		foreach( (array) $cumul_by_employee as $cumul ) :
			if( ! $userdata = get_userdata( $cumul->task_employee ) )
				continue;
			$output .= "<li><label><strong>". $userdata->display_name ." : </strong></label>". $cumul->time ."</li>";
		endforeach;
		$output .= "<li style=\"border-top:solid 1px #DDD;padding-top:2px;margin-top:2px;\"><label style=\"text-transform:uppercase;\"><strong>". __( 'total' )." : </strong></label>". $this->master->db->task->cumul( $item->project_id ) ."</li>";
		$output .= "</ul>";
		
		return $output;
	}
	
	/** == COLONNE - Informations == **/
	public function column_infos( $item ){
		$output  = "";
		$output .= 	"<ul>";
		// Date de création
		$output .= 		"<li>".
							"<label><strong>". __( 'Créé', 'tify' ) ." : </strong></label>".
							sprintf( __( 'le %s à %s', 'tify' ), mysql2date( 'd/m/Y', $item->project_date ), mysql2date( 'H\hi', $item->project_date ) );
		if( $userdata = get_userdata( $item->project_author ) )
			$output .=		"<br>". sprintf( __( 'par %s', 'tify' ), $userdata->display_name );							
		$output .= 		"</li>";
		// Date de Modification
		if( $item->project_modified !== "0000-00-00 00:00:00" ) :
			$output .= 		"<li>".
								"<label><strong>". __( 'Modifié', 'tify' ) ." : </strong></label>".
								sprintf( __( 'le %s à %s', 'tify' ), mysql2date( 'd/m/Y', $item->project_modified ), mysql2date( 'H\hi', $item->project_modified ) );
			if( ( $last_editor = $this->db->get_item_meta( $item->{$this->primary_key}, '_edit_last' ) ) && ( $userdata = get_userdata( $last_editor ) ) )
				$output .=		"<br>". sprintf( __( 'par %s', 'tify' ), $userdata->display_name );							
			$output .= 		"</li>";
		endif;
		$output .= "</ul>";
		
		return $output;
	}
}