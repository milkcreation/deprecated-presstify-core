<?php
namespace tiFy\Core\Admin\Model\ListUser;

use tiFy\Core\Admin\Model\Table;

class ListUser extends Table 
{	
	/* = ARGUMENTS = */
	/// Roles des utilisateurs de la table
	protected $Roles 				= array();
	
	/// Cartographie des paramètres
	protected $ParamsMap			= array( 
		'BaseUri', 'EditBaseUri', 'Plural', 'Singular','Notices', 'FilteredViewLinks', 'Columns', 'SortableColumns', 'PerPage',  
		'QueryArgs', 'NoItems', 'BulkActions', 'RowActions', 'Roles'
	);
	
	/* = DECLARATION DES PARAMETRES = */
	/** == Définition des colonnes == **/
	public function set_columns()
	{
		return array(
			'cb'				=> $this->get_cb_column_header(),
			'user_login' 		=> __( 'Username' ),
			'display_name'		=> __( 'Nom', 'tify' ),
			'user_email'		=> __( 'E-mail', 'tify' ),
			'user_registered'	=> __( 'Enregistrement', 'tify' ),
			'role'				=> __( 'Rôle', 'tify' )
		);
	}
	
	/** == Définition des colonnes pouvant être ordonnées == **/
	public function set_sortable_columns()
	{
		return array(
			'user_login' 		=> 'user_login',
			'display_name'     	=> 'display_name',
			'user_email'    	=> 'user_email',
			'user_registered'	=> 'user_registered'
		);
	}
	
	/** == Définition des actions sur un élément == **/
	public function set_row_actions()
	{
		return array();
	}
		
	/** == Définition des rôles des utilisateurs de la table == **/
	public function set_roles()
	{
		return array();
	}
	
	/* = INITIALISATION DES PARAMETRES = */
	/** == Initialisation de l'url d'édition d'un élément == **/
	public function init_param_EditBaseUri()
	{
		$this->EditBaseUri = $this->View->getModelAttrs( 'base_url', 'EditUser' );
	}
	
	/** == Initialisation des rôles des utilisateurs de la table == **/
	public function init_param_Roles()
	{		
		if( $editable_roles = array_reverse( get_editable_roles() ) )
			$editable_roles = array_keys( $editable_roles );
		
		$roles = array();
		if( $this->set_roles() ) :			
			foreach( (array) $this->set_roles() as $role ) :
				if( ! in_array( $role, $editable_roles ) ) 
					continue;
				array_push(  $roles, $role );
			endforeach;
		else :
			$roles = $editable_roles;
		endif;
		
		$this->Roles = $roles;
	}
	
	/** == Initialisation des actions sur un élément de la liste == **/
	public function init_param_RowActions()
	{
		$row_actions = array();
		foreach( (array) $this->set_row_actions() as $action => $attr ) :
			if( is_int( $action ) ) :
				$row_actions[$attr] = array();
			else :
				$row_actions[$action] = $attr;
			endif;
		endforeach;	
		
		if( ! $this->EditBaseUri )
			unset( $row_actions['edit'] );

		$this->RowActions = $row_actions;
	}
	
	/* = TRAITEMENT = */
	/** == Récupération des éléments == **/
	public function prepare_items()
	{
		// Récupération des items
		$query = new \WP_User_Query( $this->parse_query_args() );
		$this->items = $query->get_results();

		// Pagination
		$total_items 	= $query->get_total();
		$per_page 		= $this->get_items_per_page( $this->View->getDb()->Name, $this->PerPage );
		
		$this->set_pagination_args( 
			array(
				'total_items' => $total_items,
				'per_page'    => $this->get_items_per_page( $this->View->getDb()->Name, $this->PerPage ),
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
	
	/** == Traitement des arguments de requête == **/
	public function parse_query_args()
	{
		// Récupération des arguments
		$per_page 	= $this->get_items_per_page( $this->View->getDb()->Name, $this->PerPage );
		$paged 		= $this->get_pagenum();
				
		// Arguments par défaut
		$query_args = array(
			'number' 		=> $per_page,
			'paged' 		=> $paged,
			'count_total'	=> true,
			'fields' 		=> 'all_with_meta',
			'orderby'		=> 'user_registered',
			'order'			=> 'DESC',
			'role__in' 		=> $this->Roles
		);
			
		// Traitement des arguments
		foreach( (array) $_REQUEST as $key => $value ) :
			if( method_exists( $this, 'parse_query_arg_' . $key ) ) :
				 call_user_func_array( array( $this, 'parse_query_arg_' . $key ), array( &$query_args, $value ) );
			elseif( $this->View->getDb()->isCol( $key ) ) :
				$query_args[$key] = $value;
			endif;
		endforeach;

		return wp_parse_args( $this->QueryArgs, $query_args );
	}
	
	/** == Traitement de l'argument de requête de recherche == **/
	public function parse_query_arg_s( &$query_args, $value )
	{
		if( ! empty( $value ) )
			$query_args['search'] = '*'. wp_unslash( trim( $value ) ) .'*';
	}
	
	/** == Traitement de l'argument de requête de recherche == **/
	public function parse_query_arg_role( &$query_args, $value )
	{
		if( ! empty( $value ) ) :
			if( is_string( $value ) ) :
				$value = array_map( 'trim', explode( ',', $value ) );
			endif;
			$roles = array();
			foreach( $value as $v ) :
				if( ! in_array( $v, $this->Roles ) )
					continue;
				array_push( $roles, $v );
			endforeach;
			if( $roles )
				$query_args['role__in'] = $roles;		
		endif;
	}
			
	/** == Compte le nombre d'éléments == **/
	public function count_items( $args = array() )
	{
		if( $query = new \WP_User_Query( $args ) )
			return $query->get_total();
		else
			return 0;
	}
	
	/** == Éxecution de l'action - suppression == **/
	protected function process_bulk_action_delete()
	{
		$item_ids = $this->current_item();

		// Vérification des permissions d'accès
		if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
			check_admin_referer( $this->get_item_nonce_action( 'delete', reset( $item_ids ) ) );
		endif;
		
		// Traitement de l'élément
		foreach( (array) $item_ids as $item_id ) :		
			wp_delete_user( $item_id );
		endforeach;
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'deleted', $sendback );	
		
		wp_redirect( $sendback );
		exit;
	}
			
	/* = HELPERS = */
	/** == Lien d'édition d'un élément == **/
	public function get_item_edit_args( $item, $args = array(), $label, $class = '' ) 
	{
		if( $base_uri = $this->EditBaseUri )
			return array(
				'label'			=> $label,
				'class'			=> $class,
				'base_uri'		=> $base_uri,
				'query_args'	=> array_merge( $args, array( $this->View->getDb()->Primary => $item->{$this->View->getDb()->Primary} ) ),
				'nonce'			=> false,
				'referer'		=> false
			);
	}
	
	/* = AFFICHAGE = */	
	/** == Contenu personnalisé : Login == **/
	public function column_user_login( $item )
	{
		$avatar = get_avatar( $item->ID, 32 );

		if ( current_user_can( 'edit_user',  $item->ID ) && $this->EditBaseUri ) :
			return sprintf( '%1$s<strong>%2$s</strong>', $avatar, $this->get_item_edit_link( $item, array(), $item->user_login ) );
		else :
			return sprintf( '%1$s<strong>%2$s</strong>', $avatar, $item->user_login );
		endif;
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
		
		$user_role = reset( $item->roles );	
		$role_link = esc_url( add_query_arg( 'role', $user_role, $this->BaseUri ) );	
				 
		return isset( $wp_roles->role_names[$user_role] ) ? "<a href=\"{$role_link}\">". translate_user_role( $wp_roles->role_names[$user_role] ) ."</a>" : __( 'Aucun', 'tify' );
	}
	
  	/** == Rendu de la page  == **/
    public function Render()
    {
    ?>
		<div class="wrap">
    		<h2>
    			<?php echo $this->View->getLabel( 'all_items' );?>
    			
    			<?php if( $this->EditBaseUri ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->EditBaseUri;?>"><?php echo $this->View->getLabel( 'add_new' );?></a>
    			<?php endif;?>
    			
    			<?php if( $this->View->getModelAttrs( 'base_url', 'Import' ) ) : ?>
    				<a class="add-new-h2" href="<?php echo $this->View->getModelAttrs( 'base_url', 'Import' );?>"><?php echo $this->View->getLabel( 'import_items' );?></a>
    			<?php endif;?>
    		</h2>
    		
    		<?php $this->views(); ?>
    		
    		<form method="post" action="<?php echo $this->View->getModelAttrs( 'base_url' );?>">
    			<?php $this->search_box( $this->View->getLabel( 'search_items' ), $this->View->getID() );?>
    			<?php $this->display();?>
			</form>
    	</div>
    <?php
    }
}