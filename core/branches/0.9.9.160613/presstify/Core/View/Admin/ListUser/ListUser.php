<?php
namespace tiFy\Core\View\Admin\ListUser;

use tiFy\Core\View\Admin\ListTable\ListTable as tiFy_ViewAdminListTable;

class ListUser extends tiFy_ViewAdminListTable 
{	
	/* = PARAMETRAGES = */
	/** == Récupération de la liste des rôles concernés par la vue == **/
	public function get_roles()
	{
		if( $editable_roles = array_reverse( get_editable_roles() ) )
			return array_keys( $editable_roles );
	}
	
	/* = CONFIGURATION = */
	/** == Lien vers l'édition d'un élément == **/
	public function get_edit_uri( $item_id )
	{
		return esc_attr( add_query_arg( array( $this->View->getDb()->Primary => $item_id ), $this->View->getAdminViewAttrs( 'menu_page_url', 'EditUser' ) ) );
	}
	
	/* = TRAITEMENT = */
	/** == Récupération des éléments == **/
	public function prepare_items()
	{
		global $wpdb;
		
		// Récupération des arguments
		$search 	= isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$per_page	= 20;
		$paged 		= $this->get_pagenum();
		$roles 		= ! empty( $_REQUEST['role'] ) ? array( $_REQUEST['role'] ) : $this->get_roles();

		// Arguments par défaut
		$args = array(
				'number' 		=> $per_page,
				'offset' 		=> ( $paged-1 ) * $per_page,
				'count_total'	=> true,
				'search' 		=> $search,
				'fields' 		=> 'all_with_meta',
				'orderby'		=> 'user_registered',
				'order'			=> 'DESC',
				'meta_query'	=> array(
					array(
		    			'key' => $wpdb->get_blog_prefix( get_current_blog_id() ) . 'capabilities',
		    			'value' => '"(' . implode('|', array_map( 'preg_quote', $roles ) ) . ')"',
		    			'compare' => 'REGEXP'
					)
				)
		);

		// Traitement des arguments
		/// Recherche
		if ( '' !== $args['search'] ) :
			$args['search'] = '*' . $args['search'] . '*';
		endif;
		/// Ordre d'affichage
		if ( isset( $_REQUEST['orderby'] ) ) :
			$args['orderby'] = $_REQUEST['orderby'];
		endif;
		/// Sens de trie
		if ( isset( $_REQUEST['order'] ) ) :
			$args['order'] = $_REQUEST['order'];
		endif;
		/// Statut
		if ( ! empty( $_REQUEST['status'] ) && $_REQUEST['status'] !== 'any' ) :
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = array(
				'key' 		=> $wpdb->get_blog_prefix() .'tify_membership_status',
				'value'		=> (int) $_REQUEST['status'],
				'type'		=> 'NUMERIC'
			);
		endif;

		// Récupération des items
		$wp_user_query = new \WP_User_Query( $args );
		$this->items = $wp_user_query->get_results();

		// Pagination
		$total_items = $wp_user_query->get_total();
		$this->set_pagination_args( 
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);

		return wp_parse_args( $this->extra_parse_query_items(), $args );
	}
	
	/** == Éxecution de l'action - suppression == **/
	protected function process_bulk_action_delete()
	{
		$item_id = $this->current_item();
		check_admin_referer( $this->View->getDb()->ID . $this->current_action() . $this->current_item() );
		
		wp_delete_user( $this->current_item() );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'deleted', $sendback );	
		
		wp_redirect( $sendback );
		exit;
	}
			
	/* = COLONNES = */
	/** == Définition des colonnes == **/
	public function get_columns()
	{
		$c = array(
			'user_login' 		=> __( 'Username' ),
			'display_name'		=> __( 'Nom', 'tify' ),
			'user_email'		=> __( 'E-mail', 'tify' ),
			'user_registered'	=> __( 'Enregistrement', 'tify' ),
			'role'				=> __( 'Rôle', 'tify' )
		);
	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns()
	{
		$c = array(
			'user_login' 		=> 'user_login',
			'display_name'     	=> 'display_name',
			'user_email'    	=> 'user_email',
			'user_registered'	=> 'user_registered'
		);

		return $c;
	}
	
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_cb( $item )
	{
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID );
    }	
	
	/** == Contenu personnalisé : Login == **/
	public function column_user_login( $item )
	{
		$edit_link = '#';
		$actions = array();
		$avatar = get_avatar( $item->ID, 32 );

		if ( current_user_can( 'edit_user',  $item->ID ) ) :
			$edit = "<strong><a href=\"{$edit_link}\">{$item->user_login}</a></strong><br />";
			$actions['edit'] = "<a href=\"{$edit_link}\">". __( 'Editer', 'tify' ) . "</a>";
		else :
			$edit = "<strong>{$item->user_login}</strong><br />";
		endif;
		
		return $avatar . $edit;
	}
	
	/** == Contenu personnalisé : Rôle == **/
	public function column_user_registered( $item )
	{
		return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->user_registered, true );
	}
	
	/** == Contenu personnalisé : Rôle == **/
	public function column_role( $item )
	{
		global $wp_roles;
						
		$editable_roles = array_keys( get_editable_roles() );
		if ( count( $item->roles ) <= 1 )
			$role = reset( $item->roles );
		elseif ( $roles = array_intersect( array_values( $item->roles ), $editable_roles ) ) 
			$role = reset( $roles );
		else
			$role = reset( $item->roles );
		
		$role_link = '#'; //add_query_arg( array( 'role' => $role ), $this->main->list_link );		
				
		return isset( $wp_roles->role_names[$role] ) ? "<a href=\"{$role_link}\">". translate_user_role( $wp_roles->role_names[$role] ) ."</a>" : __( 'Aucun', 'tify' );
	}
	
  /** == Rendu de la page  == **/
    public function Render()
    {
 		$this->prepare_items();
    ?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->View->getLabel( 'all_items' );?>
    			
    			<?php if( $this->View->getAdminViewAttrs( 'base_url', 'EditUser' ) ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->View->getAdminViewAttrs( 'base_url', 'EditUser' );?>"><?php echo $this->View->getLabel( 'add_new' );?></a>
    			<?php endif;?>
    			
    			<?php if( $this->View->getAdminViewAttrs( 'base_url', 'Import' ) ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->View->getAdminViewAttrs( 'base_url', 'Import' );?>"><?php echo $this->View->getLabel( 'import_items' );?></a>
    			<?php endif;?>
    		</h2>
    		<?php $this->notifications();?>
    		
    		<?php $this->views(); ?>
    		
    		<form method="post" action="<?php echo $this->View->getAdminViewAttrs( 'base_url' );?>">
    			<?php $this->search_box( $this->View->getLabel( 'search_items' ), $this->View->getID() );?>
    			<?php $this->display();?>
			</form>
    	</div>
    <?php
    }
}