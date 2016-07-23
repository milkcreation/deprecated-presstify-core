<?php
class tiFy_Membership_Members{
	/* = ARGUMENTS = */
	public 	// Configuration
			$page_slug,		 
			$action,
			$list_link,
			$edit_link;
			
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Membership $master ){
		$this->master 		= $master;	
		// Configuration			
		$this->page_slug 	= 'tify_membership';		 
		$this->action		= isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'list';
		$this->list_link	= add_query_arg( array(  'page' => $this->page_slug ), admin_url( 'admin.php' ) );
		$this->edit_link	= add_query_arg( array(  'page' => $this->page_slug .'_edit_member', 'action' => 'edit' ), admin_url( 'admin.php' ) );
				
		// Actions et filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** === Initialisation de l'interface d'administration === **/
	function wp_admin_init(){
		wp_register_style( 'tify_membership-members-edit', $this->master->uri .'admin/members-edit.css', array(), 150507 );
	}
	
	/** === === ***/
	function wp_admin_enqueue_scripts( $hookname ){
		if( $hookname !== $this->master->hooknames['member_edit'] )
			return;
		tify_control_enqueue( 'switch' );
		wp_enqueue_style( 'tify_membership-members-edit' );
	}

	/** === === ***/
	function wp_current_screen(){		
		switch( get_current_screen()->id ) :
			case $this->master->hooknames['member_list'] :
				if( ! class_exists( 'WP_List_Table' ) )
					require_once( ABSPATH .'wp-admin/includes/class-wp-list-table.php' );	
				$this->list_table = new tiFy_Membership_Members_List_Table( $this, $this->master );
				break;
			case $this->master->hooknames['member_edit'] :
				$this->edit_form = new tiFy_Membership_Members_Edit_Form( $this, $this->master );
				break;
		endswitch;
	}
	
	/* = VUES = */
	/** == ADMIN == **/
	/*** === === ***/
	function view_admin_list(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Listes des clients', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_link;?>"><?php _e( 'Ajouter un client', 'tify' );?></a>
			</h2>
			
			<?php $this->list_table->views(); ?>
			<form method="get">
				<input type="hidden" name="page" value="<?php echo $this->page_slug;?>">
				<input type="hidden" name="status" value="<?php echo ( ! empty( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '' );?>">
				
				<?php $this->list_table->search_box( __( 'Recherche de client' ), 'tify_membership_member' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}
	
	/*** === === ***/
	function view_admin_edit(){
		$this->edit_form->prepare_item();
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Editer un client', 'tify' );?>
				<a class="add-new-h2" href="<?php echo $this->edit_link;?>"><?php _e( 'Ajouter un client', 'tify' );?></a>
			</h2>
			<?php $this->edit_form->notifications();?>
			
			<form method="post">
				<?php $this->edit_form->hidden_fields();?>
				<?php $this->edit_form->views();?>
			</form>
		</div>
	<?php
	}
}

if( ! is_admin() )
	return;

tify_require_lib( 'admin_view' );

/* = FORMULAIRE D'EDITION = */
class tiFy_Membership_Members_Edit_Form{
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Membership_Members $main, tiFy_Membership $master ){
		$this->main 	= $main;
		$this->master 	= $master;
		$this->roles 	= $master->roles;
		
		// Actions et filtres Wordpress
		add_action( 'load-'. $this->master->hooknames['member_edit'], array( $this, 'wp_load' ) );
	}
	
	/* = ACTION ET FILTRE WORDPRESS = */
	function wp_load(){
		$this->master->tify_forms->mkcf->forms->set_current( $this->master->form_id );
	}
	
	/* = PREPARATION DE L'OBJECT A ÉDITER = */
	function prepare_item(){
		$user_id 	= ( isset( $_REQUEST['user_id'] ) ) ? (int) $_REQUEST['user_id'] : 0;
		$this->item = get_user_to_edit( $user_id );
	}
	
	/* = VUES = */
	/** == == **/
	function views(){
	?>
		<div style="margin-right:300px; margin-top:20px;">
			<div style="float:left; width: 100%;">
				<?php $this->view();?>
			</div>
			<div style="margin-right:-300px; width: 280px; float:right;">
				<?php $this->submitdiv();?>
			</div>
		</div>
	<?php
	}
	
	/** == == **/
	function view(){
		$this->master->taboox->box_render( $this->item );
	}
	
	/** == == **/
	function submitdiv(){
	?>
		<div id="submitdiv" class="tify_submitdiv">
			<h3 class="hndle">
				<span><?php _e( 'Enregistrer', 'tify' );?></span>
			</h3>
			<div class="inside">
				<div class="minor_actions">

				</div>	
				<div class="major_actions">
					<div class="publishing">
						<?php echo $this->master->tify_forms->mkcf->forms->submit_button( $this->master->form_id ); ?>
					</div>
				</div>	
			</div>
		</div>			
	<?php
	}
	
	/* = NOTIFICATIONS =*/
	/** == Affichage == **/
	function notifications(){
		if ( $notice = $this->get_notices() ) : ?>
			<div id="notice" class="notice notice-warning">
				<p><?php echo $notice ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $error = $this->get_errors() ) : ?>
			<div id="error" class="notice notice-error">
				<p><?php echo $error ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $message = $this->get_messages() ) : ?>
			<div id="message" class="updated notice notice-success is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php endif;
	}
	
	/** == Récupération des notices == **/
	function get_notices(){
		if( ! isset( $_GET['notice'] ) )
			return;
		switch( $_GET['notice'] ) :
			default :
				break;	
		endswitch;
	}
	
	/** == Récupération des erreurs == **/
	function get_errors(){
		if( $this->master->tify_forms->mkcf->errors->has() )
			return $this->master->tify_forms->mkcf->errors->display();
		elseif( ! isset( $_GET['error'] ) )
			return;
		switch( $_GET['error'] ) :
			default :
				break;		
		endswitch;
	}
	
	/** == Récupération des messages == **/
	function get_messages(){
		if( ! isset( $_GET['message'] ) )
			return;
		switch( $_GET['message'] ) :
			default :
				break;
			case 1 :
				return __( 'L\'utilisateur a été enregistrée avec succès', 'tify' );
				break;		
		endswitch;	
	}	
}

/* = LISTE = */
class tiFy_Membership_Members_List_Table extends tiFy_AdminView_List_Table {
	/* = ARGUMENTS = */
	public 	// Configuration 
			$roles,
			// Contrôleur
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Membership_Members $main ){
		// Définition du controleur principal
		$this->main = $main;
		// Configuration
		$this->roles = array_keys( $this->main->master->roles );
		// Définition de la classe parente
       	parent::__construct( array(
	            'singular'  => 'tify_membership_member',
	            'plural'    => 'tify_membership_members',
	            'ajax'      => true,
	            'screen'	=> $this->main->master->hooknames['member_list']
        	) 
		);
	}
	
	/* = CONFIGURATION = */
	/** == Définition des status == **/
	function set_status(){
		return 	array( 
			'available' 		=> array( 1 => __( 'Actif', 'tify' ), 0 => __( 'Inactif', 'tify' ), -1 => __( 'En attente', 'tify' )  ),
			'location'			=> $this->main->list_link		
		);
	}
	
	/* = PREPARATION DES ITEMS = */
	public function prepare_items(){
		global $wpdb;
		// Récupération des arguments
		$search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';	
		$per_page = $this->get_items_per_page( $this->per_page_option, $this->per_page_default );
		$paged = $this->get_pagenum();
		$roles = ! empty( $_REQUEST['role'] ) ? array( $_REQUEST['role'] ) : $this->roles;		
		
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
		if ( '' !== $args['search'] )
			$args['search'] = '*' . $args['search'] . '*';			
		if ( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby'];
		if ( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];
		if ( ! empty( $_REQUEST['status'] ) && $_REQUEST['status'] !== 'any' ) :
			$args['meta_query']['relation'] = 'AND'; 
			$args['meta_query'][] = array(
				'key' 		=> $wpdb->get_blog_prefix( ) .'tify_membership_status',
				'value'		=> (int) $_REQUEST['status'],
				'type'		=> 'NUMERIC'
			);
		endif;
		
		// Récupération des items
		$wp_user_query = new WP_User_Query( $args );
        $this->items = $wp_user_query->get_results();
		
		// Pagination
		$total_items = $wp_user_query->get_total();
		$this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                    
            'total_pages' => ceil( $total_items / $per_page )
			) 
		);
		
		return wp_parse_args( $this->extra_parse_query_items(), $args );
	}

	/* = ORGANES DE NAVIGATION = */
	/** == Filtrage principal  == **/
	public function get_views(){
		// Bypass
		if( ! $_status = $this->_get_status() )
			return;
	
		global $wpdb;
		$count_query_args = array(	
			'orderby'		=> 'ID',
			'order'			=> 'DESC',
			'meta_query' 	=> 
				array(
					array(
				    	'key' => $wpdb->get_blog_prefix( ) . 'capabilities',
				    	'value' => '"(' . implode('|', array_map( 'preg_quote', $this->roles ) ) . ')"',
				    	'compare' => 'REGEXP'
					)
				)
		);		
		
		$views = array();
		
		if( $_status['any'] ) :
			$query_args = array();
			foreach( (array) $_status['query_args'] as $args => $value ) :
				$query_args[$args] = sprintf( $value, 'any' );
			endforeach;
			$location = add_query_arg( $query_args, $_status['location'] );
			
			$_count_query_args = $count_query_args;			
			$wp_user_query = new WP_User_Query( $_count_query_args );
       		$results = $wp_user_query->get_results();

			$views[] = 	"<a href=\"$location\" class=\"". ( ( is_null( $_status['current'] ) || $_status['current'] === 'any' ) ? 'current' : '' ) ."\">". ( is_string( $_status['any'] ) ? $_status['any'] : __( 'Tous', 'tify' ) ) ." <span class=\"count\">(". 
						$wp_user_query->get_total().
						")</span></a>";
		endif;	
				
		foreach( $_status['available'] as $status  => $label ) :
			$query_args = array();
			foreach( (array) $_status['query_args'] as $args => $value ) :
				$query_args[$args] = sprintf( $value, $status );
			endforeach;
			$location = add_query_arg( $query_args, $_status['location'] );	
			
			$_count_query_args = $count_query_args;	
			$_count_query_args['meta_query']['relation'] = 'AND'; 
			$_count_query_args['meta_query'][] = array(
				'key' 		=> $wpdb->get_blog_prefix( ) .'tify_membership_status',
				'value'		=> $status,
				'type'		=> 'NUMERIC'
			);
			$wp_user_query = new WP_User_Query( $_count_query_args );
       		$results = $wp_user_query->get_results();

			$views[] = 	"<a href=\"$location\" class=\"". ( ( (string) $status === $_status['current'] ) ? 'current' : '' ) ."\">$label <span class=\"count\">(". 
						$wp_user_query->get_total().
						")</span></a>";	
		endforeach;
		
		return $views;
	}

	/** == Filtrage avancé  == **/
	protected function extra_tablenav( $which ) {
	?>
		<div class="alignleft actions">
		<?php if ( 'top' == $which ) : ?>
			<label class="screen-reader-text" for="campaign_id"><?php _e( 'Filtre par campagne', 'tify' ); ?></label>
			<?php 
				tify_membership_role_dropdown( 
					array(
						'show_option_all'	=> __( 'Tous les rôles', 'tify' ),
						'selected' 			=> ! empty( $_REQUEST['role'] ) ? $_REQUEST['role'] : 0
					)
				); 
				submit_button( __( 'Filtrer', 'tify' ), 'button', 'filter_action', false, array( 'id' => 'role-query-submit' ) );?>
		<?php endif;?>
		</div>
	<?php
	}
			
	/* = COLONNES = */
	/** == Définition des colonnes == **/
	public function get_columns() {
		$c = array(
			'cb'       			=> '<input type="checkbox" />',
			'user_login' 		=> __( 'Username' ),
			'display_name'		=> __( 'Nom', 'tify' ),
			'user_email'		=> __( 'E-mail', 'tify' ),
			'user_registered'	=> __( 'Enregistrement', 'tify' ),
			'role'				=> __( 'Rôle', 'tify' )
		);
	
		return $c;
	}
	
	/** == Définition de l'ordonnancement par colonne == **/
	public function get_sortable_columns() {
		$c = array(
			'user_login' 		=> 'user_login',
			'display_name'     	=> 'display_name',
			'user_email'    	=> 'user_email',
			'user_registered'	=> 'user_registered'
		);

		return $c;
	}
	
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->ID );
    }
	
	/** == Contenu personnalisé : Case à cocher == **/
	public function column_active( $item ){
        return ;
    }
	
	/** == Contenu personnalisé : Login == **/
	function column_user_login( $item ){
		$edit_link = esc_url( add_query_arg( array( 'user_id' => $item->ID ), $this->main->edit_link ) );
		$actions = array();

		if ( current_user_can( 'edit_user',  $item->ID ) ) :
			$edit = "<strong><a href=\"{$edit_link}\">$item->user_login</a></strong><br />";
			$actions['edit'] = "<a href=\"{$edit_link}\">". __( 'Editer', 'tify' ) . "</a>";
		else :
			$edit = "<strong>$item->user_login</strong><br />";
		endif;

		/*if ( ! is_multisite() && get_current_user_id() != $item->ID && current_user_can( 'delete_user', $item->ID ) )
			$actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=delete&amp;user=$item->ID&amp;wp_http_referer". urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'bulk-users' ) . "'>" . __( 'Delete' ) . "</a>";
		if ( is_multisite() && get_current_user_id() != $item->ID && current_user_can( 'remove_user', $item->ID ) )
			$actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=remove&amp;user=$item->ID", 'bulk-users' ) . "'>" . __( 'Remove' ) . "</a>";*/
		
		$edit .= $this->row_actions( $actions );
		
		return $edit;
	}
	
	/** == Contenu personnalisé : Rôle == **/
	function column_user_registered( $item ){
		return mysql2date( __( 'd/m/Y à H:i', 'tify' ), $item->user_registered );
	}
	
	/** == Contenu personnalisé : Rôle == **/
	function column_role( $item ){
		global $wp_roles;
						
		$editable_roles = array_keys( get_editable_roles() );
		if ( count( $item->roles ) <= 1 )
			$role = reset( $item->roles );
		elseif ( $roles = array_intersect( array_values( $item->roles ), $editable_roles ) ) 
			$role = reset( $roles );
		else
			$role = reset( $item->roles );
		
		$role_link = add_query_arg( array( 'role' => $role ), $this->main->list_link );		
				
		return isset( $wp_roles->role_names[$role] ) ? "<a href=\"{$role_link}\">". translate_user_role( $wp_roles->role_names[$role] ) ."</a>" : __( 'Aucun', 'tify' );
	}
	
	/* = TRAITEMENT DES ACTIONS = */
	function process_bulk_action(){
		if( $this->current_action() ) :	
			switch( $this->current_action() ) :
				case 'delete' :									
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );	
					wp_redirect( $sendback );
					exit;
				break;
				case 'trash' :									
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );	
					wp_redirect( $sendback );
					exit;
				break;
				case 'untrash' :									
					$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );	
					wp_redirect( $sendback );
					exit;
				break;		
			endswitch;
		elseif( ! empty( $_REQUEST['_wp_http_referer'] ) ) :
	 		wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI']) ) );
	 		exit;
		endif;
	}
}