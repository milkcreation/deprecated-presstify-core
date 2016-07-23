<?php
/**
 * @DO
 */
/** V2 **/ 
/** RETIRER DE tiFy_HookForArchive_Post :
/** == Vérifie si un type de post est lié à un post d'accroche == 
function is_archive_post_type( $archive_post_type = null ){
	return ( $this->get_archive_post_type_hook_id( $archive_post_type ) ) ? true : false;	
}**/
	
/** == Récupération de l'ID du post d'accroche pour un type post d'archive == 
function get_archive_post_type_hook_id( $archive_post_type = null ){
	if( ! $archive_post_type )
		$archive_post_type = get_post_type();
	
	if( is_array( $archive_post_type ) && ( count( $archive_post_type ) > 1 ) )
		return false;
	
	if( is_array( $archive_post_type ) )
		$archive_post_type = current( $archive_post_type );
	
	if( ! post_type_exists( $archive_post_type ) )
		return  false;	
	
	return $this->get_hook_id( $archive_post_type );
}**/


/** == Récupération du type de post de l'accroche d'un type archive == **/
function tify_h4a_get_hook_post_type( $archive_post_type ){
	global $tify_h4a;
	
	return $tify_h4a->get_hook_post_type( $archive_post_type );	
}

/** == Vérifie si un type de post est lié à un post d'accroche == 
 * @param (mixed) archive_post_type - string | null dans la boucle
 **/
function tify_h4a_is_archive_post_type( $archive_post_type = null ){
	global $tify_h4a;
	
	return $tify_h4a->is_archive_post_type( $archive_post_type );
}

/** == Récupération de l'ID du post d'accroche pour un type de post d'archive == 
 * @param (mixed) archive_post_type - string | null dans la boucle
 **/
function tify_h4a_get_archive_post_type_hook_id( $archive_post_type = null ){
	global $tify_h4a;
	
	return $tify_h4a->get_archive_post_type_hook_id( $archive_post_type );
}

/** == Vérifie si un post est une lié à un post d'accroche == 
 * @param (mixed) $post_id - int | post obj | null dans la boucle
 **/
function tify_h4a_is_archive_post( $post_id = null ){
	global $tify_h4a;
	
	return $tify_h4a->is_archive_post( $post_id );
}

/** == Récupération de l'ID du post d'accroche pour un post d'archive == 
 * @param (mixed) $post_id - int | post obj | null dans la boucle
 **/
function tify_h4a_get_archive_post_hook_id( $post_id = null ){
	global $tify_h4a;
	
	return $tify_h4a->get_archive_post_hook_id( $post_id );	
} 
 
/** V1 **/ 
/**
 * Déclaration des types de post
 * 
 * array( 'post' => 'page' ) => page_for_post
 */
function mktzr_p4a_register(){
	global $tify_h4a;
	
	return apply_filters( 'mktzr_post_for_archive_register', $tify_h4a->relations );
}

/**
 * CONTRÔLEUR
 */
/**
 * Récupération du type de post d'affichage des archives d'un type de post déclaré
 */
function mktzr_p4a_get_post_type_for( $archive_post_type ){
	return tify_h4a_get_hook_post_type( $archive_post_type );
}

/**
 * Récupération du post d'accroche d'un type de post d'archive
 */
function mktzr_p4a_get_post_type_for_archive_post_id( $post_type_archive ){
	global $tify_h4a;
	
	return $tify_h4a->get_hook_id( $post_type_archive );
}

/**
 * Vérifie si un post est une lié à un post d'accroche 
 */
function is_post_of_post_for_archive( $post_id = null ){
	global $tify_h4a;
	
	return $tify_h4a->is_archive_post( $post_id );
}

/**
 * Vérifie si un post est un post d'accroche
 */
function mktzr_p4a_get_post_type_archive_by_id( $post_id = null ){
	global $tify_h4a;
	
	return $tify_h4a->is_hook( $post_id );
}

/**
 * Vérifie si un type de post est lié à un post d'accroche
 */
function is_post_type_for_archive( $archive_post_type = null ){
	return tify_h4a_is_archive_post_type( $archive_post_type );
}

/**
 * Récupération de l'ID du post d'accroche pour un post d'archive
 */
function mktzr_p4a_get_ancestor( $archive_post_id = null ){
	return tify_h4a_get_archive_post_hook_id( $post_id = null );
}