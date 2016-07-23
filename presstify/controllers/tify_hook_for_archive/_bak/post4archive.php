<?php
/*
Plugin Name: Post4Archives
Plugin URI: http://presstify.com/post4archive
Description: Lier l'affichage des archives d'un type de post à un autre post
Version: 1.141127
Author: Milkcreation
Author URI: http://milkcreation.fr
Text Domain: tify_post4archive
*/

class tify_post4archive{
	/**
	 * Initialisation
	 */
	function __construct(){
		
	}
}
new tify_post4archive;

/**
 * GESTION DU THEME
 *
 * @package WordPress
 * @subpackage Milkcreation Thematizer
 */

/**
 * Déclaration des types de post
 * 
 * array( 'page' => 'post' ) => page_for_post
 */
function mktzr_p4a_register(){
	$mktzr_p4a = array( );
	/*if( get_option( 'page_for_posts' ) )
		$mktzr_p4a['post'] = 'page';*/
	
	return apply_filters( 'mktzr_post_for_archive_register', $mktzr_p4a );
}

/**
 * CONTRÔLEUR
 */
/**
 * Récupération du type de post d'affichage des archives d'un type de post déclaré
 */
function mktzr_p4a_get_post_type_for( $post_type_archive ){
	if( ! $mktzr_p4a_register = mktzr_p4a_register() )
		return;
	if( isset( $mktzr_p4a_register[$post_type_archive] ) )
		return $mktzr_p4a_register[$post_type_archive];
}

/**
 * Vérifie si la page affichée est une page d'affichage d'archive pour un type de post
 */
function is_post_type_for_archive( $post_type_archive = null  ){
	if( is_post_type_archive( $post_type_archive ) )
		return true;
	if( ! is_singular() )
		return;
	
	$mktzr_p4a_register = mktzr_p4a_register();
	if( ! $post_type_archive ) :
		foreach( $mktzr_p4a_register as $post_type_archive => $post_type_for ) :
			if( get_the_ID() &&  ( get_option( $post_type_for.'_for_'.$post_type_archive ) == get_the_ID() ) ) :
				return true;
			endif;
		endforeach;
	elseif( array_key_exists( $post_type_archive, $mktzr_p4a_register ) && ( $post_type_for = mktzr_p4a_get_post_type_for( $post_type_archive ) ) ) :
		return ( get_option( $post_type_for.'_for_'.$post_type_archive ) == get_the_ID() );
	endif;
}

/**
 * Vérifie si un post et 
 */
function is_post_of_post_for_archive( $post_id = null ){
	if( ! $post_id && is_singular( ) )
		$post_id = get_the_ID();
	if( ! $post_id )
		return false;
	if( $post = get_post( $post_id ) )	
		return mktzr_p4a_get_post_type_for( $post->post_type );	
}

/**
 * Récupération si un post est une page d'affichage d'archive pour un type de post
 */
function mktzr_p4a_get_post_type_archive_by_id( $post_id  ){
	foreach( mktzr_p4a_register() as $post_type_archive => $post_type_for ) :
		if( get_option( $post_type_for.'_for_'.$post_type_archive ) == $post_id ) :
			return array( $post_type_archive, $post_type_for );
		endif;
	endforeach;
}

/**
 * Récupération du type de post d'affichage des archives d'un type de post déclaré
 */
function mktzr_p4a_get_post_type_for_archive_post_id( $post_type_archive ){
	if( ! $mktzr_p4a_register = mktzr_p4a_register() )
		return;
	if( isset( $mktzr_p4a_register[$post_type_archive] ) )
		return get_option( $mktzr_p4a_register[$post_type_archive].'_for_'.$post_type_archive );
}

/**
 * 
 */
function mktzr_p4a_get_ancestor( $archive_post_id ){
	return mktzr_p4a_get_post_type_for_archive_post_id( get_post_type( $archive_post_id ) );
}

/**
 * OPTIONS
 */
/**
 * Déclaration des paramètres.
 */
function mktzr_p4a_register_setting(){
	// Bypass
	$register = mktzr_p4a_register();
	if( empty( $register ) )
		return;
	
	add_settings_section( 
		'mktzr_p4a_reading_section', 
		__( 'Affichage des archives (page de flux)', 'bigben' ), 
		null,
		'reading' 
	);
	foreach( mktzr_p4a_register() as $post_type_archive => $post_type_for ) :
		register_setting( 'reading', $post_type_for.'_for_'.$post_type_archive );
		add_settings_field( 
			$post_type_for.'_for_'.$post_type_archive, 
			sprintf( __( 'Archives "%s"', 'bigben' ), 
			get_post_type_object( $post_type_archive )->label ), 
			'mktzr_p4a_options_field_render', 'reading', 
			'mktzr_p4a_reading_section', 
			array( 'post_type_archive' => $post_type_archive, 'post_type_for' => $post_type_for )  
		);
	endforeach;
}
add_action( 'admin_init', 'mktzr_p4a_register_setting' );

/**
 * Rendu de la page d'édition des options du thème
 */
function mktzr_p4a_options_field_render( $args = array( ) ){
	extract( $args );

	wp_dropdown_pages( 
			array( 
				'name' => $post_type_for.'_for_'.$post_type_archive, 
				'post_type' => $post_type_for, 
				'selected' => get_option( $post_type_for.'_for_'.$post_type_archive ), 
				'show_option_none' => __( 'Aucune page choisie', 'mktzr_p4a' ), 
				'sort_column'  => 'menu_order' ) 
			);
}

/**
 * RÉÉCRITURE D'URL
 */
/**
 * Récupération de la structure de permalien de la page d'affichage du type
 */
function mktzr_p4a_get_permalink_structure( $post_type_archive ){
	if( ! $post_type_for = mktzr_p4a_get_post_type_for( $post_type_archive ) )
		return; // array( 'permalink' => get_post_type_archive_link( $post_type_archive ), 'name' => $post_type_archive, 'title' => get_post_type_object( $post_type_archive )->label );
	if( ! $p4a = get_option( $post_type_for.'_for_'.$post_type_archive ) )
		return; // array( 'permalink' => get_post_type_archive_link( $post_type_archive ), 'name' => $post_type_archive, 'title' => get_post_type_object( $post_type_archive )->label );
	
	$permalink_structure = array();
	
	if( $ancestors =  get_ancestors( $p4a, $post_type_for ) ) :
		foreach( $ancestors as $post_id ) : $post = get_post( $post_id );
			$permalink_structure[] = array( 'permalink' => get_permalink( $post->ID ), 'name' => $post->post_name, 'title' => $post->post_title );
		endforeach;
	endif;
	
	if( ! $post = get_post( $p4a ) )
		return;
	if( $sub_post_type_for =  mktzr_p4a_get_post_type_for( $post->post_type ) ) :
		$permalink_structure = array_merge( mktzr_p4a_get_permalink_structure( $post->post_type ), $permalink_structure );
	endif;
	
	$permalink_structure[] = array( 'permalink' => get_permalink( $post->ID ), 'name' => $post->post_name, 'title' => $post->post_title );
	
	return $permalink_structure;
}

/**
 * Réécriture d'url des types de post personnalisés concernés
 */
function mktzr_p4a_registered_post_type( $post_type, $args ){
	//Bypass
	if( ! mktzr_p4a_get_post_type_for( $post_type ) )
		return;
	if( ! $permalink_structure = mktzr_p4a_get_permalink_structure( $post_type ) )
		return;
	//var_dump( $post_type );
	global $wp_post_types, $wp_rewrite;
	
	$_permalink_structure = "";
	foreach( $permalink_structure as $permalink )
		$_permalink_structure .= $permalink['name']. '/';
	$_permalink_structure = untrailingslashit( $_permalink_structure );
	if( $post_type != 'post' )
		$args->has_archive = true;
	$args->rewrite['slug'] = $_permalink_structure;
	
	/**
	 * @see /wp-includes/post.php -> register_post_type
	 */ 
	if ( $args->hierarchical )
		add_rewrite_tag( "%$post_type%", '(.+?)', $args->query_var ? "{$args->query_var}=" : "post_type=$post_type&pagename=" );
	else
		add_rewrite_tag( "%$post_type%", '([^/]+)', $args->query_var ? "{$args->query_var}=" : "post_type=$post_type&name=" );
	
	if ( $args->has_archive ) {
		$archive_slug = $args->has_archive === true ? $args->rewrite['slug'] : $args->has_archive;
		if ( $args->rewrite['with_front'] )
			$archive_slug = substr( $wp_rewrite->front, 1 ) . $archive_slug;
		else
			$archive_slug = $wp_rewrite->root . $archive_slug;
	
		add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$post_type", 'top' );
		if ( $args->rewrite['feeds'] && $wp_rewrite->feeds ) {
			$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
			add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
			add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
		}
		if ( $args->rewrite['pages'] )
			add_rewrite_rule( "{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );	
	}

	$permastruct_args = $args->rewrite;
	if( isset( $permastruct_args['feeds'] ) )
		$permastruct_args['feed'] = $permastruct_args['feeds'];
	add_permastruct( $post_type, "{$args->rewrite['slug']}/%$post_type%", $permastruct_args );	
	/** End **/
	
	add_rewrite_rule( $_permalink_structure."/?$", "index.php?post_type=$post_type", 'top' );
	add_rewrite_rule( $_permalink_structure.'/([^/]+)(/[0-9]+)?/?$', 'index.php?'.$post_type.'=$matches[1]&page=$matches[2]', 'top' );
	
	$wp_post_types[ $post_type ] = $args;
}
add_action( 'registered_post_type', 'mktzr_p4a_registered_post_type', 10, 2 );

/**
 * NAV MENU
 */
/**
 * Activation du menu de navigation
 */
function mktzr_p4a_nav_menu_objects( $sorted_menu_items, $args ){
	if( ! is_archive() )
		return $sorted_menu_items;
	if( ! is_post_type_for_archive() )
		return $sorted_menu_items;
	
	$post_type = get_query_var( 'post_type' );
	
	foreach( $sorted_menu_items as &$item ) :
		if( $item->object_id == mktzr_p4a_get_post_type_for_archive_post_id( $post_type ) ) :
			$item->classes[] = 'current-menu-item';
			$item->classes[] = 'current_page_item';		
		elseif( $item->object_id == mktzr_p4a_get_ancestor( mktzr_p4a_get_post_type_for_archive_post_id( $post_type ) ) ) :
			$item->classes[] = 'current-menu-ancestor';
			$item->classes[] = 'current-menu-parent';
			$item->classes[] = 'current_page_parent';
			$item->classes[] = 'current_page_ancestor';
		endif;
	endforeach;	
	
	return $sorted_menu_items;
}
add_filter( 'wp_nav_menu_objects', 'mktzr_p4a_nav_menu_objects', 10, 2 );

/**
 * Activation du menu de navigation pour les contenus seuls
 * 
 * A MODIFIER CAR SOLUTION BOURRIN
 */
 function mktzr_p4a_singular_nav_menu_objects( $sorted_menu_items, $args ){
 	global $post;
	
 	if( is_singular() && mktzr_p4a_get_ancestor( mktzr_p4a_get_post_type_for_archive_post_id( get_post_type() ) ) ):
		foreach( $sorted_menu_items as &$item ) :
			if( $item->object_id == mktzr_p4a_get_post_type_for_archive_post_id( get_post_type() ) ) :
				$item->classes[] = 'current-menu-item';
				$item->classes[] = 'current_page_item';		
			elseif( $item->object_id == mktzr_p4a_get_ancestor( mktzr_p4a_get_post_type_for_archive_post_id( get_post_type() ) ) ) :
				$item->classes[] = 'current-menu-ancestor';
				$item->classes[] = 'current-menu-parent';
				$item->classes[] = 'current_page_parent';
				$item->classes[] = 'current_page_ancestor';
			endif;
		endforeach;
	else:
		return $sorted_menu_items;
	endif;
	
	return $sorted_menu_items;
}
add_filter( 'wp_nav_menu_objects', 'mktzr_p4a_singular_nav_menu_objects', 10, 2 );

/**
 * FIL D'ARIANE
 */
/**
 * Modification du Fil d'Ariane : is_singular 
 */
function mktzr_p4a_breadcrumb_is_singular( $output, $separator, $ancestors, $post_type_archive_link, $post ){
	if( ! $post_type_for = mktzr_p4a_get_post_type_for( $post->post_type ) ) 
		return $output;
		
	if(  ! $permalink_structure = mktzr_p4a_get_permalink_structure( $post->post_type ) )
		return $output;

	$_output = "";
	foreach( $permalink_structure as $p )
		$_output .= sprintf( '%3$s<a href="%1$s" title="'.__( 'Vers %2$s', 'mktzr_p4a').'">%2$s</a>', $p['permalink'], $p['title'], $separator );
	
	return $_output.$separator.'<span class="current">'.esc_html( wp_strip_all_tags( get_the_title() ) ).'</span>';
}
add_filter( 'mktzr_breadcrumb_is_singular', 'mktzr_p4a_breadcrumb_is_singular', 10, 5 );

/**
 * Modification du Fil d'Ariane : is_post_type_archive 
 */
function mktzr_p4a_breadcrumb_is_post_type_archive( $output, $separator ){
	if( ! $post_type_for = mktzr_p4a_get_post_type_for( get_post_type() ) ) 
		return $output;
		
	if(  ! $permalink_structure = mktzr_p4a_get_permalink_structure( get_post_type() ) )
		return $output;
	
	$_output = "";
	if( is_paged() ) :
		foreach( $permalink_structure as $p )
			$_output .= sprintf( '%3$s<a href="%1$s" title="'.__( 'Vers %2$s', 'mktzr_p4a').'">%2$s</a>', $p['permalink'], $p['title'], $separator );
		$_output .= $separator.'<span class="current">'.sprintf( __( 'Page %d', 'mktzr_p4a'), get_query_var('paged') ).'</span>';
	else :
		$current = array_pop( $permalink_structure );
		foreach( $permalink_structure as $p )
			$_output .= sprintf( '%3$s<a href="%1$s" title="'.__( 'Vers %2$s', 'mktzr_p4a').'">%2$s</a>', $p['permalink'], $p['title'], $separator );
		$_output .= $separator.'<span class="current">'.$current['title'].'</span>';
	endif;
	
	return $_output;
}
add_filter( 'mktzr_breadcrumb_is_post_type_archive', 'mktzr_p4a_breadcrumb_is_post_type_archive', 10, 2 );
add_filter( 'mktzr_breadcrumb_is_home', 'mktzr_p4a_breadcrumb_is_post_type_archive', 10, 2 );

/**
 * Modification du Fil d'Ariane : is_search 
 */
function mktzr_p4a_breadcrumb_is_search( $output, $separator, $post ){
	if( ! $post_type_for = mktzr_p4a_get_post_type_for( get_query_var( 'post_type' ) ) ) 
		return $output;
		
	if(  ! $permalink_structure = mktzr_p4a_get_permalink_structure( get_query_var( 'post_type' ) ) )
		return $output;
	$_output = "";
	
	if( is_paged() ) :
		foreach( $permalink_structure as $p )
			$_output .= sprintf( '%3$s<a href="%1$s" title="'.__( 'Vers %2$s', 'mktzr_p4a').'">%2$s</a>', $p['permalink'], $p['title'], $separator );
		if( get_search_query() ) :
			$_output .= $separator.'<span class="current">'.sprintf( __( 'page %d - ', 'mktzr_p4a'), get_query_var('paged') ).sprintf( __( 'Recherche de "%s"', 'mktzr_p4a'), get_search_query() ).'</span>';
		else :
			$_output .= $separator.'<span class="current">'.sprintf( __( 'Page %d', 'mktzr_p4a'), get_query_var('paged') ).'</span>';
		endif;
	else :		
		$current = array_pop( $permalink_structure );
		foreach( $permalink_structure as $p )
			$_output .= sprintf( '%3$s<a href="%1$s" title="'.__( 'Vers %2$s', 'mktzr_p4a').'">%2$s</a>', $p['permalink'], $p['title'], $separator );
		if( get_search_query() ) :
			$_output .= sprintf( '%3$s<a href="%1$s" title="'.__( 'Vers %2$s', 'mktzr_p4a').'">%2$s</a>', $current['permalink'], $current['title'], $separator );
			$_output .= $separator.'<span class="current">'.sprintf( __( 'Recherche de "%s"', 'mktzr_p4a'), get_search_query() ).'</span>';
		else :
			$_output .= $separator.'<span class="current">'.$current['title'].'</span>';
		endif;
	endif;
	
	return $_output;	
}
add_filter( 'mktzr_breadcrumb_is_search', 'mktzr_p4a_breadcrumb_is_search', 10, 3 );

/**
 * Débuggage de la réécriture d'url
 */
function mktzr_p4a_rewrite_debug(){
	// global $wp_rewrite;
	// var_dump( $wp_rewrite );
	var_dump( get_option( 'rewrite_rules') );
}
//add_action( 'admin_init', 'mktzr_p4a_rewrite_debug' );