<?php
namespace tiFy\Components\ArchiveFilters;

use \tiFy\Environment\Component;

/** @Autoload */
final class ArchiveFilters extends Component
{
	/* = ARGUMENTS = */
	// Liste des Filtres à déclencher
	protected $CallFilters				= array(
		'query_vars',	
		'posts_clauses'
	);
	// Ordres de priorité d'exécution des filtres
	protected $CallFiltersPriorityMap	= array(
		'posts_clauses'	=> 99
	);
	// Nombre d'arguments autorisé lors de l'appel des filtres
	protected $CallFiltersArgsMap		= array(
		'posts_clauses' => 2
	);
	
	// Configuration
	/// Environnements permis 
	private $AllowedObj 				=  array(
		'post_type', 'taxonomy'	
	);
	
	/// Liste des Element de filtrage
	private static $Nodes				= array(
		'post_type'			=> array(),
		'taxonomy'			=> array()	
	);	
	/// Liste des filtres déclarés
	private static $Filters				= array();
	
	///
	private static $Walker				= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
		
		// Traitement de la configuration
		foreach( (array) self::getConfig() as $obj => $attrs ) :
			if( ! in_array( $obj, $this->AllowedObj ) )
				continue;
			foreach( (array) $attrs as $obj_type => $opts ) :
				if( isset( $opts['walker'] ) )
					self::$Walker[$obj][$obj_type] =  $opts['walker'];
				/// Déclaration des éléments de filtrage
				foreach( (array) $opts['nodes'] as $id => $args ) :
					self::RegisterNode( $id, $args, $obj_type, $obj );
				endforeach;				
			endforeach;
		endforeach;
	}
	
	/* = DECLENCHEURS = */
	/** == Définition des arguments de requête personnalisé == **/
	final public function query_vars( $aVars ) 
	{
		//$aVars[] = '_tyaf';

		return $aVars;
	}
	
	/** == Conditions de requête personnalisés == **/
	final public function posts_clauses( $pieces, $query )
	{	
		// Bypass
		if( ! $query->is_main_query() )
			return $pieces;		
		if( empty( $_REQUEST['_tyaf']['submit'] ) )
			return $pieces;
		
		// Récupération des variable d'environnement	
		list( $obj, $obj_type ) = preg_split( '/:/', $_REQUEST['_tyaf']['submit'], 2 );
		
		// Vérification de l'environnement
		if( ! in_array( $obj, $this->AllowedObj ) )
			return $pieces;
		switch( $obj ) :
			case 'taxonomy' :
				if( ! taxonomy_exists( $obj_type ) )
					return $pieces;
				if( ! $query->is_tax( $obj_type ) )
					return $pieces;
				break;
			case 'post_type' :
				if( ! post_type_exists( $obj_type ) )
					return $pieces;
				if( ! $query->is_post_type_archive( $obj_type ) )
					return $pieces;
				break;
		endswitch;
		
		if( empty( self::$Filters[$obj][$obj_type] ) )
			return $pieces;
		
		$filters = 	self::$Filters[$obj][$obj_type];
					
		global $wpdb;
		extract( $pieces );

		$distinct = "DISTINCT";
		
		// Filtrage par metadonnées
		/// Unique
		if( ! empty( $filters['meta']['single'] ) ) :
			foreach( (array) $filters['meta']['single'] as $filter ) :
				$meta_value = self::getSelected( $filter, '' );
				
				$join .= " INNER JOIN $wpdb->postmeta as {$filter}_postmeta ON ($wpdb->posts.ID = {$filter}_postmeta.post_id AND {$filter}_postmeta.meta_key = '{$filter}')";
				$where .= " AND {$filter}_postmeta.meta_value = '{$meta_value}'";
			endforeach;
		endif;
		/// Multiple
		if( ! empty( $filters['meta']['multi'] ) ) :
			foreach( (array) $filters['meta']['multi'] as $filter ) :
				if( ! $meta_values = self::getSelected( $filter, array() ) )
					continue;
				
				$join .= " INNER JOIN $wpdb->postmeta as {$filter}_postmeta ON ($wpdb->posts.ID = {$filter}_postmeta.post_id AND {$filter}_postmeta.meta_key = '{$filter}')";
				$_meta_values = join( ',', $meta_values );
				$where .= " AND {$filter}_postmeta.meta_value IN ( $_meta_values )";
			endforeach;
		endif;
		
		// Filtrage par taxonomie
		/// Unique
		if( ! empty( $filters['term']['single'] ) ) :
			foreach( (array) $filters['term']['single'] as $filter ) :
				$term_id = self::getSelected( $filter, -1 );
	
				if( $term_id <= 0 )
					continue;
				
				$join .= " INNER JOIN $wpdb->term_relationships as {$filter}_relationships ON ($wpdb->posts.ID = {$filter}_relationships.object_id)";
				$join .= " INNER JOIN $wpdb->term_taxonomy as {$filter}_taxonomy ON ({$filter}_taxonomy.term_taxonomy_id = {$filter}_relationships.term_taxonomy_id)";
				$where .= " AND {$filter}_taxonomy.term_id = $term_id";
			endforeach;
		endif;		
		/// Multiple
		if( ! empty( $filters['term']['multi'] ) ) :
			foreach( (array) $filters['term']['multi'] as $filter ) :
				if( ! $term_ids = self::getSelected( $filter, array() ) )
					continue;
				
				$join .= " INNER JOIN $wpdb->term_relationships as {$filter}_relationships ON ($wpdb->posts.ID = {$filter}_relationships.object_id)";
				$join .= " INNER JOIN $wpdb->term_taxonomy as {$filter}_taxonomy ON ({$filter}_taxonomy.term_taxonomy_id = {$filter}_relationships.term_taxonomy_id)";
				$_term_ids = join( ',', $term_ids );
				$where .= " AND {$filter}_taxonomy.term_id IN ( $_term_ids )";
			endforeach;
		endif;

		// Empêche l'execution multiple du filtre
		if( $query->is_main_query() )
			\remove_filter( current_filter(), __METHOD__ );
		
		return compact( array_keys( $pieces ) );
	}
	
	/* = CONFIGURATION = */
	/** == Déclaration d'un élément de filtrage == **/
	public static function RegisterNode( $node_id, $args = array(), $obj_type = 'post', $obj = 'post_type' )
	{
		if( ! isset( self::$Nodes[$obj][$obj_type] ) )
			self::$Nodes[$obj][$obj_type] = array();
		
		// Traitement des arguments	
		$defaults = array(
			'title'		=> $node_id,
			'type'		=> '',	
			'selector'	=> 'checkbox',
			'choices'	=> array(),
			'default'	=> '',
			'single'	=> null
		);		
		$args = wp_parse_args( $args, $defaults );
		
		if( is_null( $args['single'] ) ) :
			switch( $args['selector'] ) :
				default :
				case 'checkbox' :
					$args[ 'single'] = false;
					$single = 'multi';
					if( ! $args['default'] )
						$args['default'] = array();
					break;
				case 'radio' :
					$args[ 'single'] = true;
					$single = 'single';
					if( ! $args['default'] )
						$args['default'] = 0;
					break;
			endswitch;
		endif;
		
		// Déclaration des interface de filtrage
		self::$Nodes[$obj][$obj_type][$node_id] = $args;
		
		// Déclaration des éléments de filtrage	
		self::$Filters[$obj][$obj_type][$args['type']][$single][] = $node_id;		
	}
	
	/* = AFFICHAGE = */
	/** == == **/
	public static function Display( $obj_type = null, $echo = true )
	{	
		if( ! $obj_type ) :
			if( ! is_archive() )
				return;
			if( is_post_type_archive() ) :
				$obj 	= 'post_type';  	
				$obj_type 	= get_post_type();
			elseif( is_tax() ) :
				$obj	= 'taxonomy';
				$obj_type 	= get_queried_object()->taxonomy;
			endif;
		endif;	
		
		// Bypass 
		if( ! $obj || ! $obj_type )
			return;		
		if( ! $nodes = self::$Nodes[$obj][$obj_type] )
			return;	
		
		if( isset( self::$Walker[$obj][$obj_type] ) ) :
			$WalkerClass = self::$Walker[$obj][$obj_type];
		else :
			$WalkerClass = '\tiFy\Components\ArchiveFilters\Walker';
		endif;
		
		$walker = new $WalkerClass;

		$output  = $walker->Output( $obj, $obj_type, $nodes );
				
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/** == == **/
	private static function getSelected( $id, $defaults = null )
	{
		if( ! empty( $_REQUEST['_tyaf'][$id] ) )
			return $_REQUEST['_tyaf'][$id];
		
		return $defaults;
	}
}