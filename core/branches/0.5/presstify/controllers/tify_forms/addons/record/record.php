<?php
/*
Addon Name: Record
Addon ID: record
Callback: tiFy_Forms_Addon_Record
Version: 1.150815
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

Class tiFy_Forms_Addon_Record extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	public 	// Configuration
			$dir,
			$uri,
			$hookname,
			$menu_slug,
			$parent_slug,
			
			// Paramètres
			$table,			
			
			// Contrôleurs
			$export;	
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ) {
		// Définition des options de formulaire par défaut
		$this->default_form_options = array( 
			'export' => true 
		);
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array(
			'save' 		=> true, 
			'column' 	=> false 
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_before_redirect'	=> array( 'function' => array( $this, 'cb_handle_before_redirect' ), 'order' => 1 )
		);
		
        parent::__construct( $master );
		
		// Configuration
		/// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		/// Identifiants de page
		$this->parent_slug  = 'tify_forms'; 
		$this->menu_slug	= 'tify_forms_record';
				
		/// Déclaration des tables de base de données
		global $wpdb;		
		$wpdb->tables = array_merge( $wpdb->tables, array( 'mktzr_forms_records', 'mktzr_forms_recordmeta' ) );	
		$wpdb->set_prefix( $wpdb->base_prefix );		

		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'wp_admin_init' ), 1 );
		
		// Initialisation de l'interface d'export
		require_once( $this->dir .'/export.php' );
		$this->export = new tiFy_Forms_Addon_RecordExport( $this );		
	}

	/* = CALLBACKS = */
	/** == Enregistrement des données de formulaire en base == **/
	function cb_handle_before_redirect( $parsed_request, $original_request ){
		global $wpdb;

		if( $wpdb->query( $wpdb->prepare( "SELECT form_id FROM $wpdb->mktzr_forms_records WHERE record_session = %s", $parsed_request['session'] ) ) )
			return;
		
		$wpdb->insert( 
			$wpdb->mktzr_forms_records, 
			array(
				'form_id' 			=> $parsed_request['form_id'],
				'record_session' 	=> $parsed_request['session'],	 
				'record_date' 		=> current_time( 'mysql' ) 
			)
		);

		if( $record_id = $wpdb->insert_id )	:	
			foreach( $parsed_request['fields'] as $slug => $field ) :
				if( ! $field['add-ons']['record']['save'] ) :
					continue;
				else :
					add_metadata( 'mktzr_forms_record', $record_id, $slug, $field['value'], true );
				endif;
			endforeach;
		endif;		
	}

	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration == **/
	function wp_admin_menu(){
		add_submenu_page( $this->parent_slug, __( 'Tous les enregistrements', 'tify' ), __( 'Tous les enregistrements', 'tify' ), 'manage_options', $this->menu_slug, array( $this, 'admin_display' ) );	
	}
	
	/** == Initialisation de l'interface d'administration == */
	function wp_admin_init(){
		$this->hookname = get_plugin_page_hookname( $this->menu_slug, $this->parent_slug );
		
		// Installation des prérequis de l'addon	
		$this->db_install();
		
		// Initialisation de la table  des enregistrements		
		$this->table = new tiFy_Forms_Addon_RecordListTable( $this );
	}
	
	/* = VUES = */		
	/** == Affichage de la page d'administration == **/
	function admin_display(){
		$this->table->prepare_items();    
		?>
	    <div class="wrap">        
	        <h2><?php _e( 'Enregistrement de formulaire', 'tify' );?></h2>
	
	        <form method="get">
	            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $this->table->display() ?>
				<?php if ( $this->table->has_items() ) $this->table->inline_preview();?>
	        </form>
	        <?php if ( $this->table->has_items() ) $this->export->render();?>      
	    </div>
    	<?php
	}
	
	/* = CONTRÔLEURS = */	
	/** == Installeur == **/
	function db_install(){
		global $wpdb;
			
		require_once( ABSPATH .'wp-admin/install-helper.php' );
				
		$version = 1506101121;	
		
		if( version_compare( get_option('mktzr_forms_record_version', 0 ), $version, '>=') )
			return;
		
		$sql = array();
		$charset_collate = $wpdb->get_charset_collate();

		if( version_compare( get_option('mktzr_forms_record_version', 0 ), 1309181252, '<') ) :
			// Création des tables.
			maybe_create_table( 
				$wpdb->mktzr_forms_records,
				"CREATE TABLE $wpdb->mktzr_forms_records (
		  				 ID  bigint(25) NOT NULL AUTO_INCREMENT,
		  				 form_id  bigint(25) NOT NULL,
		  				 record_session varchar(13) NOT NULL,
		  				 record_status varchar(20) NOT NULL DEFAULT 'publish',
						 record_date  datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						PRIMARY KEY ( ID ),
						KEY form_id ( form_id )
					) $charset_collate;" 
			);
			maybe_create_table( 
				$wpdb->mktzr_forms_recordmeta,
				"CREATE TABLE $wpdb->mktzr_forms_recordmeta (
		  				 meta_id  bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						 mktzr_forms_record_id  bigint(20) unsigned NOT NULL DEFAULT '0',
						 meta_key  varchar(255) DEFAULT NULL,
						 meta_value  longtext,
						PRIMARY KEY ( meta_id ),
						KEY mktzr_forms_record_id ( mktzr_forms_record_id ),
						KEY meta_key ( meta_key )
					) $charset_collate;"
			);
		endif;
		
		// Mise à jour de la colonne de session
		if( version_compare( get_option('mktzr_forms_record_version', 0 ), 1506101121, '<' ) ) :
			if( ! check_column( $wpdb->mktzr_forms_records, 'record_session', 'varchar(32)' ) ) :
				$ddl = "ALTER TABLE $wpdb->mktzr_forms_records MODIFY COLUMN record_session varchar(32) NOT NULL DEFAULT '' ";
	      		$q = $wpdb->query( $ddl );
			endif;
		endif;
		
		update_option( 'mktzr_forms_record_version', $version );
	}
	
	/**
	 * Récupération d'enregistrements
	 */
	function db_get_items( $args = array() ){
		global $wpdb;
		
		$defaults = array(					
			'status'	=> 'publish',
			'parent'	=> 0,
			'per_page' 	=> -1,
			'paged' 	=> 1,
			'order' 	=> 'DESC',
			'orderby' 	=> 'ID'
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );
		
		$query  = "SELECT ID FROM $wpdb->mktzr_forms_records";
			
		// Conditions
		$query .= " WHERE 1";
		if( $parent )
			$query .= " AND form_id = '{$parent}'";
		if( $status != 'any' ) :
			if( is_array($status) ) :
				$status = join( ',', $status );
				$query .= " AND record_status IN '($_status)'";
			else :	
				$query .= " AND record_status = '{$status}'";
			endif;
		endif;	
		// Ordre	
		$query .= " ORDER BY $orderby $order";
		// Limit
		if( $per_page > 0 ) :
			$offset = ($paged-1)*$per_page;
			$query .= " LIMIT $offset, $per_page";
		endif;	
			
		if( ! $ids = $wpdb->get_col( $query ) )
			return;
		
		$r = array();
		foreach( $ids as $id )
			$r[] = $this->db_get_item( $id );
				
		return $r;
	}
	
	/**
	 * Récupération d'un enregistrement
	 */
	function db_get_item( $ID ){
		// Récupération du cache
		if( $meta_cache = wp_cache_get( $ID, 'tify_forms_record' ) )
			return $meta_cache;
	
		global $wpdb;
		$record =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->mktzr_forms_records WHERE ID = %d", $ID ) );
		
		// Mise en cache
		wp_cache_add( $ID, $record, 'tify_forms_record' );
		
		return wp_cache_get( $ID, 'tify_forms_record' );
	}
	
	/**
	 * Récupération d'une metadonnée
	 */
	function db_get_meta_item( $ID, $key, $single = true ){
		return get_metadata( 'mktzr_forms_record', $ID, $key, $single );
	}
	
	/**
	 * Compte le nombre d'enregistrements
	 */
	function db_count_items( $args = array() ){
		global $wpdb;
		
		$defaults = array(					
			'status'=> 'publish',
			'parent'=> 0,
			'limit' => -1
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args, EXTR_SKIP );	
	
		$query  = "SELECT COUNT( ID ) FROM $wpdb->mktzr_forms_records";	
		// Conditions
		$query .= " WHERE 1";
		if( $parent )
			$query .= " AND form_id = '{$parent}'";
		if( $limit > -1 )
			$query .= " LIMIT $limit";
				
		return $wpdb->get_var( $query );
	}
	
	/**
	 * Suppression d'un enregistrement
	 */
	function db_delete_item( $ID ) {
		global $wpdb;
		
		if( $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mktzr_forms_records WHERE ID = %d", $ID ) ) )
			$this->db_delete_metas_item( $ID );
	}
	
	/**
	 * Suppression des metas d'une signature
	 */
	function db_delete_metas_item( $ID ) {
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->mktzr_forms_recordmeta WHERE mktzr_forms_record_id = %d", $ID ) );
	}
}

/**
 * ADMINISTRATION
 */
if( ! is_admin () ) 
	return;

if( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Classe de la table des enregistrements
 */
Class tiFy_Forms_Addon_RecordListTable extends WP_List_Table {
	public 	// Configuration
			$current, 		// Formulaire courant
			
			// Paramètres
			
			// Contrôleurs
			$main;
			
	
	/* = CONSTRUCTEUR = */	
	function __construct( tiFy_Forms_Addon_Record $main ){
		$this->main = $main;
		
		// Définition de la classe parente
       	parent::__construct( 
       		array(
            	'singular'  => 'mktzr_forms_record',
            	'plural'    => 'mktzr_forms_record',
            	'ajax'      => true,
            	'screen'	=> $this->main->hookname
        	)
		);	
		
		// Action et Filtres Wordpress	
		add_action( 'load-'. $this->screen->id, array( $this, 'wp_load' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'wp_admin_print_footer_scripts' ), 10, 99 );
		add_action( 'wp_ajax_mktzr_forms_get_record', array( $this, 'wp_ajax' ) );		
	}

	/**
	 * Récupération des items
	 */
	function prepare_items() {
		$per_page = 20;	
			
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array( $columns, $hidden, $sortable );	
		
		$args = array();
		
		$current_page = $this->get_pagenum();

		$args['paged'] = $current_page;		 				
		$args['per_page'] = $per_page;
		
		if( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];
		
		if( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby']; 
		
		if( $this->current )
			$args['parent'] = $this->current['ID'];
				
		$args['status'] = ( ! isset( $_REQUEST['status'] ) )? 'publish' : $_REQUEST['status']; 
											
		$this->items = $this->main->db_get_items( $args );
		$total_items = $this->main->db_count_items( $args );
		
		$this->set_pagination_args( array(
            'total_items' => $total_items,                  
            'per_page'    => $per_page,                    
            'total_pages' => ceil( $total_items / $per_page )
        ) );		
	}
			
	/**
	 * Liste des colonnes de la table
	 */
	function get_columns() {
		$columns['cb']	= '<input type="checkbox" />';	
				
		$columns['form'] = __( 'Formulaire', 'tify' );
		
		$columns += array(
			'date' 			=> __( 'Date', 'tify' )		
		);

		if( $this->current )
			foreach( $this->current['fields'] as $field )
				if( $field['add-ons']['record']['column'] )
					$columns[$field['slug']] = $field['label'];
	
		return $columns;
	}

	/**
	 * Liste des colonnes de la table pour lequelles le trie est actif
	 */
 	function get_sortable_columns() {
        $sortable_columns = array(
            //'date' => array( 'date', false )
        );				
        return $sortable_columns;
    }
	
	/**
	 * Affichage du contenu par defaut des colonnes
	 */
	function column_default($item, $column_name){
		$value = $this->main->db_get_meta_item( $item->ID, $column_name );		
		$field = $this->main->master->fields->get_by_slug( $column_name );
	
        switch($column_name) :
            default:
				$output = "";	
				if( ! $value ) :			
					$output .= $field['value'];
				elseif( is_string( $value ) ) :
					$output .= $this->main->master->fields->translate_value( $value, $field['choices'],$field );
				elseif( is_array( $value ) ) :
					$n = 0;
					foreach( $value as $val ) :
						if( $n++ ) $output .= ", ";
						$output .= $this->main->master->fields->translate_value($val, $field['choices'], $field );
					endforeach;	
				endif;
				return $output;
			break;
		endswitch;
    }
	
	/**
	 * Contenu de la colonne "case à cocher"
	 */
	function column_cb( $item ){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->form_id );
    }
	
	/**
	 * Contenu de la colonne
	 */
	function column_form( $item ){
		$actions = array(
        	'inline hide-if-no-js' => '<a href="#" class="editinline" title="' . esc_attr( __( 'Aperçu de l\'item', 'tify' ) ) . '" data-record_id="'.$item->ID.'">' . __( 'Afficher' ) . '</a>',
        	'delete'    => "<a href=\"" . wp_nonce_url( add_query_arg( array( 'page' => $_REQUEST['page'], 'action' => 'delete', 'record_id' => $item->ID, '_wp_http_referer' => urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ),  admin_url('admin.php') ), 'mktzr_forms_record_delete_' . $item->ID ) . "\">" . __( 'Supprimer', 'tify' ) . "</a>",
		);	
     	return sprintf('<a href="#">%1$s</a>%2$s', $this->main->master->forms->get_title( $item->form_id ), $this->row_actions($actions) );
    }	

	/**
	 * Contenu de la colonne "date"
	 */
	function column_date( $item ){		
        return mysql2date( 'd/m/Y @ H:i', $item->record_date, true );
    }
	
	/**
	 * Action groupées
	 */
    function get_bulk_actions() {
        $actions = array(
            //'delete'    => 'Delete'
        );
        return $actions;
    }
    
	/**
	 * Execution des actions groupées
	 */
	function process_bulk_action() {
        switch( $this->current_action() ) :
			case 'delete' :
				$record_id = (int) $_GET['record_id'];			
				check_admin_referer( 'mktzr_forms_record_delete_' . $record_id );
				$this->main->db_delete_item( $record_id );
				
				$sendback = remove_query_arg( array('action', 'action2', 'tags_input', '_status', 'bulk_edit' ), wp_get_referer() );

				wp_redirect($sendback);
				exit();
				break;	
		endswitch;            
    }
	
	/**
	 * Filtres et liste de sélection
	 */
	function extra_tablenav( $which ) {
		$output = "";
		if( ! $forms = $this->main->master->addons->get_forms_active( 'record' ) )
			return $output;
		$output .= "<div class=\"alignleft actions\">";
		if ( 'top' == $which && !is_singular() ) :
			$selected = $this->current ? $this->current['ID']: 0;
			$output  .= "\n<select name=\"form_id\" autocomplete=\"off\">";
			$output  .= "\n\t<option value=\"0\" ".selected( 0, $selected, false ).">".__( 'Tous les formulaires', 'tify' )."</option>";
			foreach( (array) $forms as $fid )
				$output  .= "\n\t<option value=\"{$fid}\"".selected( $fid, $selected, false ).">". $this->main->master->forms->get_title( $fid )."</option>";
			$output  .= "</select>";

			$output  .= get_submit_button( __( 'Filtrer', 'tify' ), 'secondary', false, false );
		endif;
		$output .= "</div>";

		echo $output;
	}
		
	/**
	 * 
	 */
	function inline_preview(){
		list( $columns, $hidden ) = $this->get_column_info();
		$colspan = count($columns);
	?>
		<table style="display: none">
			<tbody id="inlinepreview">
				<tr style="display: none" class="inline-preview" id="inline-preview">
					<td class="colspanchange" colspan="<?php echo $colspan;?>">
						<h3><?php _e( 'Chargement en court ...', 'tify' );?></h3>
					</td>
				</tr>	
			</tbody>
		</table>
	<?php	
	}
	
	/**
	 * 
	 */
	function wp_load(){
		// Définition de l'élément courant
		$form_id = 0;		
		if( isset( $_REQUEST['form_id'] ) && $this->main->master->forms->get( $_REQUEST['form_id'] ) )
			$form_id =  (int) $_REQUEST['form_id'];
		elseif( ( $forms = $this->main->master->addons->get_forms_active( 'record' ) ) &&  ( count( $forms ) == 1 ) )
			$form_id =  $forms[0];	
		
		if( $form_id ) :
			$this->main->master->forms->set_current( $form_id );
			$this->current = $this->main->master->forms->get_current( );
		endif;
		
		$this->process_bulk_action();
	}
	
	/**
	 * 
	 */
	function wp_ajax(){
		$record = $this->main->db_get_item( $_POST['record_id'] );
		$this->main->master->forms->set_current( $record->form_id );
		
		$output  = "";	
		if( ! empty( $this->main->master->forms->current['fields'] ) ) : 
			$output .= "\n<table class=\"form-table\">";
			$output .= "\n\t<tbody>";					
									
			foreach( (array) $this->main->master->forms->current['fields'] as $field ) :
				if( $field['type'] == 'string' ) continue;
				if( ! $field['add-ons']['record']['save'] ) continue;
				$output .= "\n\t\t<tr valign=\"top\">";
				if( $field['label'] ) :
					$output .= "\n\t\t\t<th scope=\"row\">";
					$output .= "\n\t\t\t\t<label><strong>{$field['label']}</strong></label>";
					$output .= "\n\t\t\t</th>";			
					$output .= "\n\t\t\t<td>";
				else :
					$output .= "\n\t\t\t<td colspan=\"2\">";
				endif;
				$output .= "\n\t\t\t\t<div class=\"value\">";
				$value = $this->main->db_get_meta_item( $record->ID, $field['slug'] );
				if( ! $value ) :			
					$output .= $field['value'];
				elseif( is_string( $value ) ) :
					$output .= $this->main->master->fields->translate_value( $value, $field['choices'], $field );
				elseif( is_array( $value ) ) :
					$n = 0;
					foreach( $value as $val ) :
						if( $n++ ) $output .= ", ";
						$output .= "<img src=\"". $this->main->uri ."/plugins/forms/images/checked.png\" width=\"16\" height=\"16\" align=\"top\"/>&nbsp;";
						$output .= $this->main->master->fields->translate_value($val, $field['choices'], $field );
					endforeach;	
				endif;	
				$output .= "\n\t\t\t\t</div>";
				$output .= "\n\t\t\t</td>";
			endforeach;
			$output .= "\n\t</tbody>";
			$output .= "\n</table>";
			$output .= "\n<div class=\"clear\"></div>";
		endif;
		
		$this->main->master->forms->reset_current( );
				
		echo $output;
		exit;
	}

	/**
	 * 
	 */
	function wp_admin_print_footer_scripts(){
		if( get_current_screen()->id != $this->screen->id )
			return;
	?>
	<style type="text/css">
		.form-table{
			margin-bottom:-3px;
		}
		.form-table td{
			padding:10px;
		}
		.tablenav.top .actions.bulkactions{
			padding:0;
		}
	</style>
	<script type="text/javascript">/* <![CDATA[ */
	jQuery(document).ready(function($){
		$('#the-list').on('click', 'a.editinline', function(){
			var record_id = $(this).data('record_id');
			$parent = $(this).closest('tr');
			if( $parent.next().attr('id') != 'inline-preview-'+ record_id ){
				// Création de la zone de prévisualisation
				$previewRow = $('#inline-preview').clone(true);
				$previewRow.attr('id', 'inline-preview-'+record_id );
				$parent.after($previewRow);
				// Récupération des éléments de formulaire
				$.post( tify_ajaxurl, { action: 'mktzr_forms_get_record', record_id: record_id }, function( data ){
					$('> td', $previewRow ).html(data);			
				});					
			} else {
				$previewRow = $parent.next();
			}	
			$parent.closest('table');
			$previewRow.toggle();	
					
			return false;
		});
	});
	/* ]]> */</script>
	<?php
	}
}