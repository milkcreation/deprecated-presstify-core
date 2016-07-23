<?php
/** DEBUG :
 	global $wp_rewrite;
	var_dump( $wp_rewrite );
	var_dump( get_option('permalink_structure' ) );
	var_dump( get_option( 'rewrite_rules') );
	exit;
	
 	add_rewrite_rule( 'reference/?$', 'index.php?post_type=reference', 'top' );
	add_rewrite_rule( 'reference/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?post_type=reference&feed=$matches[1]', 'top' );
	add_rewrite_rule( 'reference/(feed|rdf|rss|rss2|atom)/?$', 'index.php?post_type=reference&feed=$matches[1]', 'top' );
	add_rewrite_rule( 'reference/page/([0-9]{1,})/?$', 'index.php?post_type=reference&paged=$matches[1]', 'top' );
 */

/** == == **/
function toa3_rewrite_post_types(){
	return array( 'product-building', 'product-industry', 'reference' );
}

/** == == **/
add_action( 'save_post', 'toa3_rewrite_save_post', 10, 99 );
function toa3_rewrite_save_post( $post_id, $post ){		
	// Contrôle s'il s'agit d'une routine de sauvegarde automatique.	
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;
	// Contrôle s'il s'agit d'une routine de sauvegarde automatique.	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
      return;	
	
	//Bypass
	if( ! isset( $_POST['post_type'] ) )
		return;
		
	// Contrôle des permissions
  	if ( 'page' == $_POST['post_type'] )
    	if ( !current_user_can( 'edit_page', $post_id ) )
        	return;
  	else
    	if ( !current_user_can( 'edit_post', $post_id ) )
        	return;
			
	if( ( ! $post = get_post( $post_id ) ) || ( ! $post = get_post( $post ) ) )
		return;	
	
	if( ! ( $hook_parent_id = $post->post_parent ) )
		return;
	
	$hook = $post;	
	$post_types = toa3_rewrite_post_types();
	$post_type 	= false;
	
	foreach( $post_types as $_post_type ) :
		if( get_option( 'page_for_'. $_post_type ) != $hook_parent_id )
			continue;
		$post_type = $_post_type; break;
	endforeach;
	
	if( ! $post_type )
		return;	
	
	if( ! $hook_parent = get_post( $hook_parent_id ) )
		return;	

	$_permalink_structure =  $hook_parent->post_name .'/'. $hook->post_name;
	add_rewrite_rule( $_permalink_structure . '/page/([0-9]{1,})/?$', 'index.php?post_type='. $post_type .'&paged=$matches[1]&tify_hook_id='. $hook->ID, 'top' );
	add_rewrite_rule( $_permalink_structure . '/?$', 'index.php?post_type='. $post_type. '&paged=1&tify_hook_id='. $hook->ID, 'top' );	
	add_rewrite_rule( $_permalink_structure . '/([^/]+)(/[0-9]+)?/?$', 'index.php?'. $post_type .'=$matches[1]&page=$matches[2]&tify_hook_id='. $hook->ID, 'top' );
	
	flush_rewrite_rules( );
		
	return $post;
}

/** == == **/
add_action( 'init', 'toa3_rewrite_init', 99 );
function toa3_rewrite_init() {	
	$post_types = toa3_rewrite_post_types();
	foreach( $post_types as $post_type ) :
		if( ! ( $hook_parent_id = get_option( 'page_for_'. $post_type ) ) )
			continue;
		if( ! $hook_parent = get_post( $hook_parent_id ) )
			continue;
		if( ! $hooks = get_posts( array( 'post_parent' => $hook_parent->ID, 'post_type' => 'page', 'post_status' => 'publish', 'orderby' => 'menu_order', 'order' => 'ASC' ) ) )
			continue;

		foreach( $hooks as $hook ) :
			$_permalink_structure =  $hook_parent->post_name .'/'. $hook->post_name;
			add_rewrite_rule( $_permalink_structure . '/page/([0-9]{1,})/?$', 'index.php?post_type='. $post_type .'&paged=$matches[1]&tify_hook_id='. $hook->ID, 'top' );
			add_rewrite_rule( $_permalink_structure . '/?$', 'index.php?post_type='. $post_type. '&paged=1&tify_hook_id='. $hook->ID, 'top' );	
			add_rewrite_rule( $_permalink_structure . '/([^/]+)(/[0-9]+)?/?$', 'index.php?'. $post_type .'=$matches[1]&page=$matches[2]&tify_hook_id='. $hook->ID, 'top' );
		endforeach;		
	endforeach;	
}

/** == == **/
add_filter( 'mktzr_breadcrumb_is_singular', 'toa3_rewrite_mktzr_breadcrumb_is_singular', 10, 5 );
function toa3_rewrite_mktzr_breadcrumb_is_singular( $output, $separator, $ancestors, $post_type_archive_link, $post ){
	if( ! in_array( get_post_type( $post ), toa3_rewrite_post_types() ) )
		return $output;
	
	if( ! $hook_ids = get_post_meta( $post->ID, '_page_for_'. get_post_type( $post ) ) )
		return $output;
	
	if( ! $hook_parent_id = get_option( 'page_for_'. get_post_type( $post ) ) )
		return $output;	
	$hook_parent_link = $separator. '<a href="'. get_the_permalink( $hook_parent_id ) .'" title="'. sprintf( __( 'Retour à %s', 'toa3' ), get_the_title( $hook_parent_id ) ) .'">'. get_the_title( $hook_parent_id ) .'</a>';
	$hook_id = current( $hook_ids );
	$hook_link = $separator. '<a href="'. get_the_permalink( $hook_id ) .'" title="'. sprintf( __( 'Retour à %s', 'toa3' ), get_the_title( $hook_id ) ) .'">'. get_the_title( $hook_id ) .'</a>'; 	
	
	return $hook_parent_link . $hook_link . $ancestors . $separator .'<span class="current">'. esc_html( wp_strip_all_tags( get_the_title() ) ) .'</span>';
}

/** == == **/
add_filter( 'mktzr_breadcrumb_is_post_type_archive', 'toa3_rewrite_mktzr_breadcrumb_is_post_type_archive', 10, 2 );
function toa3_rewrite_mktzr_breadcrumb_is_post_type_archive( $output, $separator ){
	if( ! in_array( get_post_type( ), toa3_rewrite_post_types() ) )
		return $output;
	
	if( ! $hook_parent_id = get_option( 'page_for_'. get_post_type( ) ) )
		return $output;
	
	$hook_parent_link = $separator. '<a href="'. get_the_permalink( $hook_parent_id ) .'" title="'. sprintf( __( 'Retour à %s', 'toa3' ), get_the_title( $hook_parent_id ) ) .'">'. get_the_title( $hook_parent_id ) .'</a>';
	
	return $hook_parent_link . $output;
}

/** == == **/
add_filter( 'post_type_link', 'toa3_rewrite_post_type_link', 10, 4 );
function toa3_rewrite_post_type_link( $post_link, $post, $leavename, $sample ){
	if( ! $post = get_post( $post ) )
		return $post_link;	

	$post_type = $post->post_type;
	$name = 'page_for_'. $post_type;
	
	if( ! $hook_parent_id = get_option( $name ) )
		return $post_link;
	
	if( ! $hook_parent = get_post( $hook_parent_id ) )
		return $post_link;	
	
	if( ! $hook_ids = get_post_meta( $post->ID, '_'. $name ) )
		return $post_link;
	$hook_id = (int) current( $hook_ids );
	if( ! $hook = get_post( $hook_id  ) )
		return $post_link;

	$post_link =  site_url( $hook_parent->post_name .'/'. $hook->post_name .'/'. $post->post_name );
	
	return $post_link;
}

/** == == **/
add_action( 'pre_get_posts', 'toa3_rewrite_pre_get_posts' );
function toa3_rewrite_pre_get_posts( &$query ){
	if( ! is_admin() ) :
		if( $query->is_main_query() ) :
			if( is_archive() ) :
				if( $hook_id = $query->get( 'tify_hook_id' ) ) :
					if( in_array( $query->get( 'post_type' ), toa3_rewrite_post_types() ) )	:
						$query->set( 
							'meta_query', 
							array(
								array(
									'key'	=> '_page_for_'. $query->get( 'post_type' ),
									'value'	=> $hook_id
								)
							) 
						);
						$query->set( 'posts_per_page', 12 );
						$query->set( 'orderby', 'menu_order' );
						$query->set( 'order', 'ASC' );
					else :	
					endif;
				endif;
			endif;				
		endif;
	endif;	
}

class toa3_rewrite_custom_columns{
	var $post_types;
	
	/**
	 * Initialisation
	 */
	function __construct(){
		// Action
		add_action( 'admin_init', array( $this, 'admin_init' ) );	
	}
	
	/**
	 * Instanciation de la colonne
	 */
	function admin_init(){
		// Type de post actif
		$this->post_types = toa3_rewrite_post_types();
		
		// Activation de la colonne pour les types de post concernés
		foreach( $this->post_types as $post_type ) :
			add_filter( "manage_edit-{$post_type}_columns", array( $this, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_column' ), 10, 2 );
		endforeach;	
	}
	
	/**
	 * Entête et position de la colonne
	 */
	function columns( $columns ){
		$newcolumns = array(); $n = 0;
		foreach( $columns as $key => $column ) :
			if( $n == 3 ) 
				$newcolumns['type'] = __( 'Type', 'toa3' );
			$newcolumns[$key] = $column;
			$n++;				
		endforeach;
		$columns = $newcolumns;

		return $columns;
	}
	
	/**
	 * Affichage des données de la colonne
	 */
	function custom_column( $column, $post_id ){
		if( $column != 'type' )
			return $column;
		
		if( $types = get_post_meta( $post_id, '_page_for_'.get_post_type( $post_id ) ) )
			echo implode( ', ', array_map( 'get_the_title', $types ) );	
	}
}
new toa3_rewrite_custom_columns;
