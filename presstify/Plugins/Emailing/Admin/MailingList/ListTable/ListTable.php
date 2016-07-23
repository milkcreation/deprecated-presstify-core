<?php
namespace tiFy\Plugins\Emailing\Admin\MailingList\ListTable;

use tiFy\Core\View\Admin\ListTable\ListTable as tiFy_ListTable;

class ListTable extends tiFy_ListTable
{
	/* = ARGUMENTS = */
	public 	// Paramètres
			$status;
		
	/* = CHARGEMENT = */
	public function current_screen( $current_screen )
	{
		// Paramétrage
		/// Notifications
		$this->notifications = array(
			'deleted' 				=> array(
				'message'		=> __( 'La liste de diffusion a été défintivement supprimée', 'tify' ),
				'type'			=> 'success'
			),
			'trashed' 				=> array(
				'message'		=> __( 'La liste de diffusion a été placée dans la corbeille', 'tify' ),
				'type'			=> 'success'
			),
			'untrashed' 				=> array(
				'message'		=> __( 'La liste de diffusion a été rétablie', 'tify' ),
				'type'			=> 'success'
			)
		);
		
		/// Vue Filtrée
		$this->status = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any';
		$public = isset( $_REQUEST['public'] ) ? ( ! empty( $_REQUEST['public'] ) ? true : false ) : null;
		$this->views = array(
			'any'	=> array(
				'label'					=> __( 'Toutes (hors corbeille)', 'tify' ),
				'current'				=> ( $this->status === 'any' && is_null( $public ) ) ? true : false,
				'remove_query_args'		=> array( 'status', 'public' ),
				'count_query_args'		=> array( 'status' => 'publish' )
			),				
			'public'		=> array(
				'label'				=> __( 'Publique', 'tify' ),
				'current'			=> ( ( $this->status === 'any' ) && ! is_null( $public ) && $public ) ? true : false,
				'add_query_args'	=> array( 'status' => 'publish', 'public' => 1 ),
				'count_query_args'	=> array( 'status' => 'publish', 'public' => 1 ),
				
			),
			'private'		=> array(
				'label'				=> __( 'Privée', 'tify' ),
				'current'			=> ( ( $this->status === 'any' ) && ! is_null( $public ) && ! $public ) ? true : false,
				'add_query_args'	=> array( 'status' => 'publish', 'public' => 0 ),
				'count_query_args'	=> array( 'status' => 'publish', 'public' => 0 ),
				
			),
			'trash' 	=>  array(
				'label'				=> __( 'Corbeille', 'tify' ),
				'current'			=> ( $this->status === 'trash' ) ? true : false,
				'add_query_args'	=> array( 'status' => 'trash' ),
				'count_query_args'	=> array( 'status' => 'trash' )
			)
		);
		
		/// Arguments de récupération des éléments
		$this->prepare_query_args = array(
			'status' 	=> ( $this->status !== 'any' ) ? $this->status : 'publish'
		);
	}
					
	/* = PARAMETRAGE = */
	/** == Liste des colonnes == **/
	public function get_columns()
	{
		$c = array(
			'cb'       				=> '<input type="checkbox" />',
			'list_title' 			=> __( 'Intitulé', 'tify' ),
			'list_content'  		=> __( 'Description', 'tify' ),
			'subscribers_number'    => __( 'Nombre d\'abonnés', 'tify' ),
			'list_date' 			=> __( 'Date de création', 'tify' ),
			'list_public' 			=> __( 'Droit d\'accès', 'tify' )
		);	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns()
	{
		return array(	
			'list_title'  => 'title'
		);
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary )
	{
		if ( $primary !== $column_name )
			return;
				
		if( $item->list_status !== 'trash' )
			return $this->row_actions( $this->get_item_actions( $item, array( 'edit', 'trash' ) ) );
		else
			return $this->row_actions( $this->get_item_actions( $item, array( 'untrash', 'delete' ) ) );
	}
	
	/* = TRAITEMENT DES DONNEES = */
	/** == Éxecution de l'action - Suppression == **/
	public function process_bulk_action_delete()
	{
		$item_id = (int) $_GET[$this->item_request];
		check_admin_referer( $this->action_prefix . $this->current_action() . $item_id );
	
		// Destruction des liaisons abonnés <> liste
		tify_db_get( 'wistify_list_relationships' )->delete_list_subscribers( $item_id );
		// Suppression de la liste de diffusion
		$this->View->getDb()->delete_item( $item_id );
		
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 1, $sendback );

		wp_redirect( $sendback );
	}	
	
	/* = AFFICHAGE = */
	/** == Contenu personnalisé : Titre == **/
	public function column_list_title( $item )
	{
		$title = ! $item->list_title ? __( '(Pas de titre)', 'tify' ) : $item->list_title;			
		$label = ( ! in_array( $item->list_status, array( 'publish', 'auto-draft' ) ) && ( $this->current_view() === 'any' ) ) ? "<span> - ". $this->views[$item->list_status]['label'] ."</span>" : false;
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>', $title, $this->get_edit_uri( $item->list_id ), $label ); 	
	}

	/** == Contenu personnalisé : Nombre d'abonnés == **/
	public function column_subscribers_number( $item )
	{
		$DbSubscriber	= tify_db_get( 'wistify_subscriber' );
		
		$registred 		= (int) $DbSubscriber->select()->count( array( 'list_id' => $item->list_id, 'status' => 'registred', 'active' => 1 ) );
		$unsubscribed 	= (int) $DbSubscriber->select()->count( array( 'list_id' => $item->list_id, 'status' => 'registred', 'active' => 0 ) );
		$waiting 		= (int) $DbSubscriber->select()->count( array( 'list_id' => $item->list_id, 'status' => 'registred', 'active' => -1 ) );
		$trashed 		= (int) $DbSubscriber->select()->count( array( 'list_id' => $item->list_id, 'status' => 'trash' ) );			
		$total 			= (int) $DbSubscriber->select()->count( array( 'list_id' => $item->list_id ) );
		
		$output = "<strong style=\"text-transform:uppercase\">". sprintf( _n( '%d abonné au total', '%d abonnés au total', ( $total <= 1 ), 'tify' ), $total ) ."</strong>";
		$output .= "<br><em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d inscrit', '%d inscrits', ( $registred <= 1 ), 'tify' ), $registred ) .", </em>";
		$output .= "<em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d désinscrit', '%d désinscrits', ( $unsubscribed <= 1 ), 'tify' ), $unsubscribed ) .", </em>";
		$output .= "<em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d en attente', '%d en attente', ( $waiting <= 1 ), 'tify' ), $waiting ) .", </em>";
		$output .= "<em style=\"color:#999; font-size:0.9em;\">". sprintf( _n( '%d à la corbeille', '%d à la corbeille', ( $trashed <= 1 ), 'tify' ), $trashed ) ."</em>";
		
		return $output;
	}
	
	/** == Contenu personnalisé : Date de création de la liste == **/
	public function column_list_date( $item )
	{
		if( $item->list_date !== '0000-00-00 00:00:00' )
			return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->list_date );
		else
			return __( 'Indéterminée', 'tify' );
	}
	
	/** == == **/
	public function column_list_public( $item )
	{
		return ( $item->list_public ) ? "<strong style=\"color:green;\">". __( 'Publique', 'tify' ) ."</strong>" : "<strong style=\"color:red;\">". __( 'Privée', 'tify' ) ."</strong>";
	}
}