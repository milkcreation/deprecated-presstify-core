<?php
namespace BigBen;

use \tiFy\Environment\App;

/** @Autoload */
class Query extends App
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'pre_get_posts'		
	);
	
	// Liste des Filtres à déclencher
	protected $CallFilters				= array(
		'query_vars',
		'terms_clauses'
	);
	// Ordres de priorité d'exécution des filtres
	protected $CallFiltersPriorityMap	= array(
		'query_vars' 	=> 1,
		'terms_clauses'	=> 99
	);
	// Nombre d'arguments autorisé lors de l'appel des filtres
	protected $CallFiltersArgsMap		= array(
		'terms_clauses' => 3
	);
	
	// Liste des filtre de court-circuitage des variables publiques
	private $FiltersPublic				= array( 'orderby', 'posts_per_page' );
	
	// Liste des filtres uniques de taxonomy
	private $FiltersTaxSingle			= array( 'color', 'gender', 'license', 'plateform', 'player', 'public', 'use' );
	
	// Liste des filtres multiples de taxonomy
	private $FiltersTaxMulti			= array();
	
	//  La page affichée est un type produit
	public static $isProduct			= false;
	
	//  La page affichée est un type magasin
	public static $isStore				= false;
	
	//  La page affichée est un type mise en avant
	public static $isFocus				= false;
	
	/* = ACTIONS A DECLENCHER = */
	/** == == **/
	final public function query_vars( $aVars ) 
	{
		foreach( (array) $this->FiltersPublic as $filter ) :
			$aVars[] = '_'. $filter;
		endforeach;
		
		foreach( (array) $this->FiltersTaxSingle as $filter ) :
			$aVars[] = '_'. $filter;
		endforeach;

		foreach( (array) $this->FiltersTaxMulti as $filter ) :
			$aVars[] = '_'. $filter .'__in';
		endforeach;
		
		$aVars[] = '_search_by_type';
		$aVars[] = '_compliance';

		return $aVars;
	}
	
	/** == == **/
	final public function pre_get_posts( &$query )
	{	
		// Requêtes l'interface d'administration
		if( is_admin() ) :
			/// Modification des conditions de requête de l'interface d'administration
			//add_filter( 'posts_clauses', array( $this, 'admin_posts_clauses' ), 99, 2 );
		
		// Requêtes l'interface visiteur
		else :	
			/// Requête principale
			if( $query->is_main_query() ) :
				// Magasins
				/// Page de flux
				if( $query->is_post_type_archive( 'store' ) ) :
					self::$isStore = true;
		
					$query->set( 'posts_per_page', -1 );
					
				/// Contenu seul	
				elseif( $query->is_single() && ( $query->get( 'post_type' ) === 'store' ) ) :
					self::$isStore = true;
				
				// Produits
				/// Page de flux
				elseif( $query->is_post_type_archive( 'bb_game' ) || $query->is_tax( 'tifyshopcat-bb_product' ) ) :
					self::$isProduct = true;
					
					$query->set( 'posts_per_page', $query->get( '_posts_per_page', 12 ) );
				
					add_filter( 'posts_clauses', array( $this, 'ProductFilterTaxClauses' ), 100, 2 );
					add_filter( 'posts_clauses', array( $this, 'ProductOrderClauses' ), 101, 2 );
					
				/// Contenu seul
				elseif( $query->is_single() && ( $query->get( 'post_type' ) === 'bb_game' ) || ( $query->get( 'post_type' ) === 'bb_product' )  ) :
					self::$isProduct = true;
				
				// Mise en avant
				elseif( $query->is_tax( 'focus' ) ) :
					self::$isFocus = true;
					$query->set( 'posts_per_page', $query->get( '_per_page', 12 ) );
				endif;
			/// Requêtes secondaire
			else :
			
			endif;
		endif;
	
		// Actions communes	
	}
	
	/** == Condition de requête personnalisée pour la recherche == **/
	final public function ProductSearchClauses( $pieces, $query )
	{	
		global $wpdb;
		extract( $pieces );

		$distinct = "DISTINCT";
		
		// Recherche
		if( $search_query = $query->get( '_s', '' ) ) :
		else :
			$search_query = false;
		endif;
		
		if( $search_query ) :
			$join 	.= 	" INNER JOIN {$wpdb->postmeta} as search_postmeta ON ({$wpdb->posts}.ID = search_postmeta.post_id)";
			$where 	.= 	" AND (".
							" ( {$wpdb->posts}.post_title LIKE '%{$search_query}%' )".
							" OR ( {$wpdb->posts}.post_content LIKE '%{$search_query}%' )".
							" OR ( {$wpdb->posts}.post_excerpt LIKE '%{$search_query}%' )".
							" OR ( search_postmeta.meta_key = '_bar_code' AND  search_postmeta.meta_value LIKE '%{$search_query}%' )".
							" OR ( search_postmeta.meta_key = '_de_ref' AND  search_postmeta.meta_value LIKE '%{$search_query}%' )".
							" OR ( search_postmeta.meta_key = '_ref' AND  search_postmeta.meta_value LIKE '%{$search_query}%' )".
						" )";
		endif;

		// Empêche l'execution multiple du filtre
		if( $query->is_main_query() )
			\remove_filter( current_filter(), __METHOD__ );
		
		return compact( array_keys( $pieces ) );
	}
	
	/** == Condition de requête personnalisé de filtrage des produits == **/
	final public function ProductFilterTaxClauses( $pieces, $query )
	{	
		global $wpdb;
		extract( $pieces );
		
		$distinct = "DISTINCT";
				
		// Filtres uniques de taxonomie 
		foreach( (array) $this->FiltersTaxSingle as $filter ) :
			if( ! $term_id = $query->get( '_'. $filter, -1 ) )
				continue;
			if( $term_id <= 0 )
				continue;
			
			$join .= " INNER JOIN $wpdb->term_relationships as {$filter}_relationships ON ($wpdb->posts.ID = {$filter}_relationships.object_id)";
			$join .= " INNER JOIN $wpdb->term_taxonomy as {$filter}_taxonomy ON ({$filter}_taxonomy.term_taxonomy_id = {$filter}_relationships.term_taxonomy_id)";
			$where .= " AND {$filter}_taxonomy.term_id = $term_id";
		endforeach;
		
		// Filtres multiple de taxonomie
		foreach( (array) $this->FiltersTaxMulti as $filter ) :
			if( ! $term_ids = $query->get( '_'. $filter .'__in', array() ) )
				continue;
			
			$join .= " INNER JOIN $wpdb->term_relationships as {$filter}_relationships ON ($wpdb->posts.ID = {$filter}_relationships.object_id)";
			$join .= " INNER JOIN $wpdb->term_taxonomy as {$filter}_taxonomy ON ({$filter}_taxonomy.term_taxonomy_id = {$filter}_relationships.term_taxonomy_id)";
			$_term_ids = join( ',', $term_ids );
			$where .= " AND {$filter}_taxonomy.term_id IN ( $_term_ids )";
		endforeach;
		
		// Empêche l'execution multiple du filtre
		if( $query->is_main_query() )
			\remove_filter( current_filter(), __METHOD__ );
		
		return compact( array_keys( $pieces ) );
	}
	
	/** == Condition de requête personnalisée d'ordonnancemment des produits == **/
	final public function ProductOrderClauses( $pieces, $query )
	{
		global $wpdb;
		extract( $pieces );
		
		$distinct = "DISTINCT";
		
		// Gestion de l'ordre
		$_orderby = $query->get( '_orderby', 'date' );
		switch( $_orderby ) :
			case 'date' :
				$orderby = "$wpdb->posts.post_date DESC";
				break;
			case 'update' :
				$orderby = "$wpdb->posts.post_modified DESC";
				break;
			case 'alpha' :
				$orderby = "$wpdb->posts.post_title ASC";
				break;
			case 'novelty' :
				$order1 = false; $order2 = false; $_orderby = array();
				if( get_option( 'focus_novelty_order' ) ) :
					$order1 = join( ',', get_option( 'focus_novelty_order' ) );
					$_orderby[] = "FIELD( $wpdb->posts.ID,  $order1) DESC";
				endif;
				if( get_option( 'focus_soon_order' ) ) :
					$order2 = join( ',', get_option( 'focus_soon_order' ) );
					$_orderby[] = "FIELD( $wpdb->posts.ID,  $order2) DESC";
				endif;
				
				$_orderby[] = "$wpdb->posts.post_modified DESC";
				$orderby = join( ', ', $_orderby );
				break;
			case 'soon' :
				$order1 = false; $order2 = false; $_orderby = array();
				if( get_option( 'focus_soon_order' ) ) :
					$order1 = join( ',', get_option( 'focus_soon_order' ) );
					$_orderby[] = "FIELD( $wpdb->posts.ID,  $order1) DESC";
				endif;
				if( get_option( 'focus_novelty_order' ) ) :
					$order2 = join( ',', get_option( 'focus_novelty_order' ) );
					$_orderby[] = "FIELD( $wpdb->posts.ID,  $order2) DESC";
				endif;
				
				$_orderby[] = "$wpdb->posts.post_modified DESC";
				$orderby = join( ', ', $_orderby );
				break;
		endswitch;
		
		// Empêche l'execution multiple du filtre
		if( $query->is_main_query() )
			\remove_filter( current_filter(), __METHOD__ );
		
		return compact( array_keys( $pieces ) );
	}	
	
	/** == == **/
	final public function admin_posts_clauses( $pieces, $query ){
		global $wpdb;
	
		extract( $pieces );
	
		if( $search_query = $query->get( 's', false ) ) :
			$distinct = "DISTINCT";
			$join .= " INNER JOIN $wpdb->postmeta as search_postmeta ON ($wpdb->posts.ID = search_postmeta.post_id)";
	
			//// Initialisation des conditions
			$where = "";
			
			//// Status
			if( ( ! $post_status = $query->get( 'post_status' ) ) || ( $query->get( 'post_status' ) == 'any' ) ) :
				$where .= " AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'future' OR $wpdb->posts.post_status = 'draft' OR $wpdb->posts.post_status = 'pending' OR $wpdb->posts.post_status = 'private')";
			else :
				$where_post_status = array();
			
				if( is_string( $post_status ) ) :
					$post_status = array_map( 'trim', explode( ',', $post_status ) );
				endif;
				
				foreach( (array) $post_status as $status ) :
					$where_post_status[] .= "$wpdb->posts.post_status = '{$status}'";
				endforeach;
				
				if( $where_post_status ) :
					$where .= " AND (". implode( " OR ", $where_post_status ) .")";
				endif;
			endif;
			
			//// Définition du type de post
			$post_types = $query->get( 'post_type' );
			$where_post_type = array();
			
			foreach( (array) $post_types as $post_type ) :
				if( $post_type == 'any' ) :
					$where_post_type = array(); break;
				else :
					$where_post_type[] .= "$wpdb->posts.post_type = '{$post_type}'";
				endif;
			endforeach;
			
			if( $where_post_type ) :
				$where .= " AND (". implode( " OR ", $where_post_type ) .")";
			endif;
			
			//// Définition des posts exclus
			if( $not_in = $query->get( 'post__not_in' ) ) :
				$post__not_in = implode( ',',  array_map( 'absint', $not_in ) );
				$where .= " AND {$wpdb->posts}.ID NOT IN ($post__not_in)";
			endif;
	
			$where .= 	" AND (".
							" ( $wpdb->posts.post_title LIKE '%".$search_query."%' )".
							" OR ( $wpdb->posts.post_content LIKE '%".$search_query."%' )".
							" OR ( search_postmeta.meta_key = '_bar_code' AND  search_postmeta.meta_value LIKE '%".$search_query."%' )".
							" OR ( search_postmeta.meta_key = '_de_ref' AND  search_postmeta.meta_value LIKE '%".$search_query."%' )".
							" OR ( search_postmeta.meta_key = '_ref' AND  search_postmeta.meta_value LIKE '%".$search_query."%' )".
						" )";
	
			if( $query->get( 'compliance' ) ) :
				//$join .= " INNER JOIN $wpdb->postmeta as support_active ON ($wpdb->posts.ID = support_active.post_id)";
				$join .= " INNER JOIN $wpdb->postmeta as compliance ON ($wpdb->posts.ID = compliance.post_id)";
				//$where .= " AND ( support_active.meta_key = '_support_active' AND  support_active.meta_value = 1 )";
				$where .= " AND ( compliance.meta_key = '_certificate_of_compliance' )";
			endif;

		endif;

		// Retour
		$_pieces = array( 'where', 'groupby', 'join', 'orderby', 'distinct', 'fields', 'limits' );
		$pieces =  compact( $_pieces );

		return $pieces;
	}	
	
	/** == Limitation de la liste des taxonomies de produits à celle incluse == **/
	final public function terms_clauses( $clauses, $taxonomy, $args ) 
	{
	
		if ( ! empty( $args['post_type'] ) ) :
			global $wpdb;
			
			$post_types = array();
	
			if( is_array( $args['post_type'] ) ) :
				foreach( $args['post_type'] as $cpt ) :
					$post_types[] = "'".$cpt."'";
				endforeach;
			elseif( is_string( $args['post_type'] ) ) :
				$post_types[] = "'".$args['post_type']."'";
			endif;

		    if( ! empty( $post_types ) ) :
				$clauses['fields'] = 'DISTINCT '. str_replace('tt.*', 'tt.term_taxonomy_id, tt.term_id, tt.taxonomy, tt.description, tt.parent', $clauses['fields'] ).', COUNT(t.term_id) AS count';
				$clauses['join'] .= ' INNER JOIN '.$wpdb->term_relationships.' AS r ON r.term_taxonomy_id = tt.term_taxonomy_id INNER JOIN '.$wpdb->posts.' AS p ON ( p.ID = r.object_id AND p.post_status = "publish" )';
				$clauses['where'] .= ' AND p.post_type IN ('.implode(',', $post_types).')';
				$clauses['orderby'] = 'GROUP BY t.term_id '.$clauses['orderby'];
			endif;
		endif;
		
		// Emprêche la double execution du filtre
		remove_filter( 'terms_clauses', array( $this, 'terms_clauses' ) );
		
	    return $clauses;
	}
}