<?php
/**
 * ADMINISTRATION
 */
/**
 * Entrée de menu des formulaires
 */
function mktzr_forms_cart_add_menu_items(){
    //add_menu_page( 'Panier', __( 'Panier', 'mktzr_forms'), 'manage_options', 'mktzr_forms_cart', 'mktzr_forms_cart_list_page' );
} 
add_action( 'admin_menu', 'mktzr_forms_cart_add_menu_items' );

/**
 * Icone de menu
 */
function mktzr_forms_cart_admin_print_styles(){
?><style>
	#adminmenu #toplevel_page_mktzr_forms_cart .menu-icon-generic div.wp-menu-image:before {
		content: "\f07a";
	}
	#toplevel_page_mktzr_forms_cart div.wp-menu-image:before {
		font: 400 20px/1 FontAwesome !important;
	}
</style><?php
}
add_action( 'admin_print_styles', 'mktzr_forms_cart_admin_print_styles' );

/**
 * 
 */
function mktzr_forms_cart_list_page(){
    $formsListTable = new Mktzr_Forms_Cart_List();
    $formsListTable->prepare_items();    
?>
    <div class="wrap">        
        <h2><?php _e('Panier', 'mktzr_forms');?></h2>

        <form method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			<?php $formsListTable->display() ?>
			<?php if ( $formsListTable->has_items() ) $formsListTable->inline_preview();?>
        </form>        
    </div>
<?php
}

/**
 * Récupération des enregistrements de panier
 */
function mktzr_forms_cart_records( $args = array() ){
	global $wpdb;
	
	$defaults = array(					
		'status'=> 'publish',
		'form_id'=> 0,
		'per_page' => -1,
		'paged' => 1,
		'order' => 'DESC',
		'orderby' => 'ID'
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );	
	
	$where = '';
	$wheres = array();

	if( $form_id )
		$wheres[] = sprintf("form_id = %d", $form_id );
	/*if( $status )
		$wheres[] = sprintf("status = '%s'", $status ); */
	if( $per_page > 0 )
		$limit = sprintf("LIMIT %s,%s", ( ( $paged-1)*$per_page ) , $per_page );	
	else
		$limit = "";
	
	if( ! empty( $wheres ) )
		$where = "WHERE ".join( " AND ", $wheres )."";
			
	if( ! $records = $wpdb->get_col( "SELECT ID FROM $wpdb->carts $where ORDER BY ID DESC  $limit") )
		return;
	
	$r = array();
	foreach( $records as $record_id )
		$r[] = mktzr_forms_cart_record( $record_id );
			
	return $r;
}

/**
 * Récupération d'un enregistrement de panier
 */
function mktzr_forms_cart_record( $ID ){
	// Récupération du cache
	if( $meta_cache = wp_cache_get( $ID, 'mktzr_forms_cart_records' ) )
		return $meta_cache;

	global $wpdb;
	$record =  $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->carts WHERE ID = %d", $ID ) );
	
	// Mise en cache
	wp_cache_add( $ID, $record, 'mktzr_forms_cart_records' );
	
	return wp_cache_get( $ID, 'mktzr_forms_cart_records');
}

/**
 * Compte le nombre de panier enregistrés
 */
function mktzr_forms_cart_count_records( $args = array() ){
	global $wpdb;
	
	$defaults = array(					
		'form_id'=> 0
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );	
	
	$where = '';
	$wheres = array();
	
	if( ! empty( $wheres ) )
		$where = "WHERE ".join( " AND ", $wheres )."";
			
	if( ! $records = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->carts $where") )
		return 0;
	else
		return $records;
}

/**
 * Récupération de la classe de gestion des tables
 */ 
if( ! class_exists('WP_List_Table') )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );	

/**
 * Classe de la table des signatures
 */
class Mktzr_Forms_Cart_List extends WP_List_Table {
	var $current = false, $formObj = array();
		
	function __construct(){
		global $status, $page;
			
		//Set parent defaults
       	parent::__construct( array(
            'singular'  => 'form',
            'plural'    => 'forms',
            'ajax'      => true
        ) );
		
		// Définition de l'élément courant
		if( isset( $_REQUEST['form_id'] ) ) :
			$form_id =  (int) $_REQUEST['form_id'];
		elseif( ( $forms = mktzr_forms_get_forms() ) &&  ( count($forms) == 1 ) && ( $form = array_shift($forms) ) ) :
			$form_id =  $form['ID'];	
		else :
			$form_id = 0;
		endif;
		
		if( $form_id ) :
			$this->formObj = New MKCF( mktzr_forms_get_forms() );	
			$this->formObj->_set_current_form( $form_id );
			$this->current = $this->formObj->form;
		endif;
	}
			
	/**
	 * Liste des colonnes de la table
	 */
	function get_columns() {
		$columns['cb']	= '<input type="checkbox" />';	
				
		$columns['form'] = __( 'Formulaire', 'mktzr_form_record' );
		
		$columns += array(
			'date' 			=> __( 'Date', 'mktzr_form_record' )		
		);
		
		if( $this->current )
			foreach( $this->current['fields'] as $field )
				if( isset( $field['add-ons']['shopping-cart']['column'] ) )
					$columns[$field['slug']] = $field['label'];
	
		return $columns;
	}

	/**
	 * Liste des colonnes de la table pour lequelles le trie est actif
	 */
 	function get_sortable_columns() {
        $sortable_columns = array(
            //'date' => array( 'date', false)
        );
				
        return $sortable_columns;
    }
	
	/**
	 * Affichage du contenu par defaut des colonnes
	 */
	function column_default($item, $column_name){
		$value = get_metadata( 'cart', $item->id, $column_name, true );		
		$field = $this->formObj->get_field( $column_name );
		
        switch($column_name){
            default:
				$output = "";	
				if( ! $value ) :			
					$output .= $field['default'];
				elseif( is_string( $value ) ) :
					$output .= $this->formObj->translate_value( $value, $field['choices'],$field );
				elseif( is_array( $value ) ) :
					$n = 0;
					foreach( $value as $val ) :
						if( $n++ ) $output .= ", ";
						$output .= $this->formObj->translate_value($val, $field['choices'], $field );
					endforeach;	
				endif;
				return $output;
				break;
        }
    }
	
	/**
	 * Contenu de la colonne "case à cocher"
	 */
	function column_cb($item){
        return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->form_id );
    }
	
	/**
	 * Contenu de la colonne
	 */
	function column_form($item){
		$actions = array(
			//'delete'    => "<a href=\"" . wp_nonce_url( "edit.php?page=". $_REQUEST['page'] ."&action=delete&record_id=".$item->id, 'delete-record_' . $item->id ) . "\">" . __('Supprimer', 'milk-petitions' ) . "</a>",
        	'inline hide-if-no-js' => '<a href="#" class="editinline" title="' . esc_attr( __( 'Aperçu de l\'item' ) ) . '" data-record_id="'.$item->id.'">' . __( 'Afficher' ) . '</a>'
		);	
     	//return sprintf('<a href="#">%1$s</a>%2$s', mktzr_forms_get_form_title( $item->form_id ), $this->row_actions($actions) );
     	return sprintf('<a href="#">%1$s</a>%2$s', $item->order_reference, $this->row_actions($actions) );
    }	

	/**
	 * Contenu de la colonne "date"
	 */
	function column_date($item){		
        return mysql2date( 'd/m/Y @ H:i', $item->date_create, true );
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
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }       
    }
	
	/**
	 * 
	 */
	function extra_tablenav( $which ) {
		$output = "";
		if( ! $forms = mktzr_forms_get_forms() )
			return $output;
		$output .= "<div class=\"alignleft actions\">";
		if ( 'top' == $which && !is_singular() ) :
			$selected = $this->current ? $this->current['ID']: 0;
			$output  .= "\n<select name=\"form_id\">";
			$output  .= "\n\t<option value=\"0\" ".selected( 0, $selected, false ).">".__( 'Tous les formulaires', 'mktzr_forms' )."</option>";
			foreach( (array) $forms as $form ) :
				if( isset( $form['add-ons']['shopping-cart']['active']) && $form['add-ons']['shopping-cart']['active'] )
					$output  .= "\n\t<option value=\"{$form['ID']}\"".selected( $form['ID'], $selected, false ).">".$form['title']."</option>";
			endforeach;
			$output  .= "</select>";

			submit_button( __( 'Filter' ), 'secondary', false, false );
		endif;
		$output .= "</div>";

		echo $output;
	}
	
	/**
	 * Récupération des items
	 */
	function prepare_items() {
		$per_page = 20;	
			
		$columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);	
		
		$this->process_bulk_action();
		
		$args = array();
		
		$current_page = $this->get_pagenum();

		$args['paged'] = $current_page;		 				
		$args['per_page'] = $per_page;
		
		if( isset( $_REQUEST['order'] ) )
			$args['order'] = $_REQUEST['order'];
		
		if( isset( $_REQUEST['orderby'] ) )
			$args['orderby'] = $_REQUEST['orderby']; 
		
		if( $this->current )
			$args['form_id'] = $this->current['ID'];
				
		$args['status'] = ( ! isset( $_REQUEST['status'] ) )? 'publish' : $_REQUEST['status']; 
											
		$this->items = mktzr_forms_cart_records( $args );
		
		$total_items = mktzr_forms_cart_count_records( $args );
			
		$this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );		
	}
	
	function inline_preview(){
		list( $columns, $hidden ) = $this->get_column_info();
		$colspan = count($columns);
	?>
	<table style="display: none">
		<tbody id="inlinepreview">
			<tr style="display: none" class="inline-preview" id="inline-preview">
				<td class="colspanchange" colspan="<?php echo $colspan;?>">
					<h3><?php _e('Chargement en court ...');?></h3>
				</td>
			</tr>	
		</tbody>
	</table>
	<?php	
	}
}


/**
 * 
 */
function mktzr_forms_cart_admin_enqueue_script(){
?>
<style>
	.form-table{
		margin-bottom:-3px;
	}
	.form-table td{
		padding:10px;
	}
</style>
<script>
jQuery(document).ready(function($){
	$('#the-list').on('click', 'a.editinline', function(){
		var record_id = $(this).data('record_id');
		$parent = $(this).closest('tr');
		if( $parent.next().attr('id') != 'inline-preview-'+record_id ){
			// Création de la zone de prévisualisation
			$previewRow = $('#inline-preview').clone(true);
			$previewRow.attr('id', 'inline-preview-'+record_id );
			$parent.after($previewRow);
			// Récupération des éléments de formulaire
			$.post( ajaxurl, {action: 'mktzr_forms_cart_get_record', record_id:record_id}, function( data ){
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
</script>
<?php
}
add_action('admin_footer-toplevel_page_mktzr_forms_cart', 'mktzr_forms_cart_admin_enqueue_script', 10, 99 );

/**
 * 
 */
function mktzr_forms_cart_ajax_get_record(){
	global $mktzr_forms;
		
	$record = mktzr_forms_cart_record( $_POST['record_id'] );
	$mktzr_forms->_set_current_form( $record->form_id );
	
	$output  = "";
	
	if( !empty( $mktzr_forms->form['fields'] ) ) : 
		$output .= "\n<table class=\"form-table\">";
		$output .= "\n\t<tbody>";					
								
		foreach( (array) $mktzr_forms->form['fields'] as $field ) :
			// Bypass
			if( $field['type'] == 'html' ) continue;
			
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
			$value = get_metadata( 'cart', $record->id, $field['slug'], true );
			if( ! $value ) :			
				$output .= $field['default'];
			elseif( is_string( $value ) ) :
				$output .= $mktzr_forms->translate_value($value, $field['choices'], $field );
			elseif( is_array( $value ) ) :
				$n = 0;
				foreach( $value as $val ) :
					if( $n++ ) $output .= ", ";
					$output .= "<img src=\"".MKTZR_URL."/plugins/forms/images/checked.png\" width=\"16\" height=\"16\" align=\"top\"/>&nbsp;";
					$output .= $mktzr_forms->translate_value($val, $field['choices'], $field );
				endforeach;	
			endif;	
			$output .= "\n\t\t\t\t</div>";
			$output .= "\n\t\t\t</td>";
		endforeach;
		$output .= "\n\t</tbody>";
		$output .= "\n</table>";
		$output .= "\n<div class=\"clear\"></div>";
	endif;
			
	echo $output;
	exit;
}
add_action( 'wp_ajax_mktzr_forms_cart_get_record', 'mktzr_forms_cart_ajax_get_record' );