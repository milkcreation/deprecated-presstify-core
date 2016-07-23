<?php
namespace tiFy\Plugins\Wistify\Admin;

use tiFy\Entity\AdminView\ListTable;
use tiFy\Plugins\Wistify\Wistify;

class SubscriberListTable extends ListTable
{
	/* = ARGUMENTS = */
	public 	// Paramètres
			$status;
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( Wistify $master )
	{
		// Définition des classes de référence
		$this->master = $master;
		
		// Paramétrage
		/// Notifications
		$this->notifications = array(
			'deleted' 				=> array(
				'message'		=> __( 'L\'abonné a été supprimé définitivement', 'tify' ),
				'type'			=> 'success'
			),
			'trashed' 				=> array(
				'message'		=> __( 'L\'abonné a été placé dans la corbeille', 'tify' ),
				'type'			=> 'success'
			),
			'untrashed' 				=> array(
				'message'		=> __( 'L\'abonné a été restauré', 'tify' ),
				'type'			=> 'success'
			)
		);
		
		/// Vues Filtrées
		$this->status = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'any' ;
		$active = isset( $_REQUEST['active'] ) ? ( ! empty( $_REQUEST['active'] ) ? (int) $_REQUEST['active'] : 0 ) : null;
		$this->views = array(
			'any'		=> array(
				'label'				=> __( 'Tous (hors corbeille)', 'tify' ),
				'current'				=> ( $this->status === 'any' ) ? true : false,
				'remove_query_args'	=> array( 'status' ),
				'add_query_args'	=> array( 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ) ),
				'count_query_args'	=> array( 'status' => array( 'registred', 'waiting', 'unsubscribed' ), 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ) )
			),
			'registred'		=> array(
				'label'					=> __( 'Inscrits', 'tify' ),
				'current'				=> ( ( $this->status === 'registred' ) && ! is_null( $active ) && ( $active === 1 ) ) ? true : false,
				'add_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 1 ),
				'count_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 1 ),
				
			),			
			'unsubscribed'	=> array(
				'label'					=> __( 'Désinscrits', 'tify' ),
				'current'				=> ( ( $this->status === 'registred' ) && ! is_null( $active ) && ( $active === 0 ) ) ? true : false,
				'add_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 0 ),
				'count_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => 0 )
			),
			'waiting'		=> array(
				'label'					=> __( 'En attente', 'tify' ),
				'current'				=> ( ( $this->status === 'registred' ) && ! is_null( $active ) && ( $active === -1 ) ) ? true : false,
				'add_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => -1 ),
				'count_query_args'		=> array( 'status' => 'registred', 'list_id' => ( ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1 ), 'active' => -1 )
			),
			'trash' => array(
				'label'					=> __( 'Corbeille', 'tify' ),
				'current'				=> ( $this->status === 'trash' ) ? true : false,			
				'add_query_args'		=> array( 'status' => 'trash' ),
				'remove_query_args'		=> array( 'active' ),
				'count_query_args'		=> array( 'status' => 'trash' )
			)
		);
		
		/// Arguments de récupération des éléments
		$this->prepare_query_args = array(
			'status' 	=> ( $this->status !== 'any' ) 		? $this->status  		: array( 'registred', 'waiting', 'unsubscribed' ),
			'list_id'	=> isset( $_REQUEST['list_id'] ) 	? $_REQUEST['list_id'] 	: -1,
			'active'	=> isset( $_REQUEST['active'] ) 	? $_REQUEST['active'] 	: null
		);
	}
		
	/* = PARAMETRAGE = */
	/** == Définition des colonnes == **/
	public function get_columns() 
	{
		$c = array(
			'cb'       					=> '<input type="checkbox" />',
			'subscriber_email' 			=> __( 'Email', 'tify' ),
			'subscriber_lists' 			=> __( 'Listes de diffusion', 'tify' ),			
			'subscriber_date' 			=> __( 'Depuis le', 'tify' )
		);	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() 
	{
		return array(	
			'subscriber_email'  => 'email',
			'subscriber_date'	=> array( 'date', true )
		);
	}
	
	/** == Agrégations des actions aux éléments de la colonne primaire == **/
	public function handle_row_actions( $item, $column_name, $primary )
	{
		if ( $primary !== $column_name )
			return;
		if( $item->subscriber_status !== 'trash' )
			return $this->row_actions( $this->get_item_actions(  $item, array( 'edit', 'trash' ) ) );
		else
			return $this->row_actions( $this->get_item_actions(  $item, array( 'untrash', 'delete' ) ) );	

	}	
	
	/* = AFFICHAGE = */
	/** == Filtrage avancé  == **/
	public function extra_tablenav( $which ) 
	{
	?>
		<div class="alignleft actions">
		<?php if ( 'top' == $which ) : ?>
			<label class="screen-reader-text" for="list_id"><?php _e( 'Filtre par liste de diffusion', 'tify' ); ?></label>
			<?php 
				\tiFy\Plugins\Wistify\Core\wistify_mailing_lists_dropdown( 
					array(
						'show_option_all'	=> __( 'Toutes les listes de diffusion', 'tify' ),
						'show_count'		=> true,
						
						'selected' 			=> ! empty( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : -1,
						'orderby'			=> 'title',
						'order'				=> 'ASC'
					)
				); 
				submit_button( __( 'Filtrer', 'tify' ), 'button', 'filter_action', false, array( 'id' => 'mailing_list-query-submit' ) );?>
		<?php endif;?>
		</div>
	<?php
	}
	
	/** == Contenu personnalisé : Titre == **/
	public function column_subscriber_email( $item )
	{
		$title = ! $item->subscriber_email ? __( '(Pas d\'email)', 'tify' ) : $item->subscriber_email;			
		$label = ( $this->master->db->list_rel->is_orphan( $item->subscriber_id ) ) ? "<span> - ". __( 'Orphelin', 'tify' ) ."</span>" : false;
		
		return sprintf('<strong><a href="%2$s">%1$s</a> %3$s</strong>', $title, $this->get_edit_uri( $item->{$this->primary_key} ), $label );     	
	}

	/** == Contenu personnalisé : Listes de diffusion == **/
	public function column_subscriber_lists( $item )
	{
		$output  = "";
		$output .= "<ul style=\"margin:0\">\n";
		$output .= "\t<li style=\"margin-bottom:2px;\"><b>".__( 'Inscrit à : ')."</b>\n";
		if( $list_ids = $this->master->db->list_rel->select()->col( 'list_id', array( 'subscriber_id' => $item->{$this->primary_key}, 'active' => 1 ) ) ) : 
			$list = array();
			foreach( $list_ids as $list_id )
				$list[] = "<a href=\"". ( add_query_arg( 'list_id', $list_id, $this->base_url ) ) ."\">". ( $list_id ? $this->master->db->list->select()->cell_by_id( $list_id, 'title' ) : __( 'Inscription sans liste', 'tify' ) )  ."</a>";
			$output .= join( ', ', $list );
		else :
			$output .=  __( 'Aucune', 'tify' );
		endif;
		$output .= "\t<li style=\"margin-bottom:2px;\"><b>".__( 'Désinscrit de : ')."</b>\n";
		if( $list_ids = $this->master->db->list->get_subscriber_list_ids( $item->{$this->primary_key}, 0 ) ) : 
			$list = array();
			foreach( $list_ids as $list_id )
				$list[] = "<a href=\"". ( add_query_arg( 'list_id', $list_id, $this->base_url ) ) ."\">". $this->master->db->list->select()->cell_by_id( $list_id, 'title' ) ."</a>";
			$output .= join( ', ', $list );
		else :
			$output .=  __( 'Aucune', 'tify' );
		endif;
		$output .= "\t</li>\n";
		$output .= "\t<li style=\"margin-bottom:2px;\"><b>".__( 'En attente pour : ')."</b>\n";
		if( $list_ids = $this->master->db->list_rel->select()->col( 'list_id', array( 'subscriber_id' => $item->{$this->primary_key}, 'active' => -1 ) ) ) : 
			$list = array();
			foreach( (array) $list_ids as $list_id )
				$list[] = "<a href=\"". ( add_query_arg( 'list_id', $list_id, $this->base_url ) ) ."\">". ( $list_id ? $this->master->db->list->select()->cell_by_id( $list_id, 'title' ) : __( 'Inscription sans liste', 'tify' ) ) ."</a>";
			$output .= join( ', ', $list );
		else :
			$output .=  __( 'Aucune', 'tify' );
		endif;
		$output .= "\t</li>\n";	
		
		$output .= "<ul>\n";
		
		return $output;		
	}
	
	/** == Contenu personnalisé : Date d'inscription == **/
	public function column_subscriber_date( $item )
	{
		if( $item->subscriber_date !== '0000-00-00 00:00:00' )
			return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->subscriber_date );
		else
			return __( 'Indéterminé', 'tify' );
	}
}