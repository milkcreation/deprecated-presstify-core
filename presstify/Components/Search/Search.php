<?php 
namespace tiFy\Components\Search;

use tiFy\Environment\Component;
use tiFy\Core\CustomType\CustomType;

/** @Autoload */
class Search extends Component
{
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'pre_get_posts'		
	);
	// Liste des Filtres à déclencher
	protected $CallFilters				= array(
		'query_vars',
	);
	// Ordres de priorité d'exécution des filtres
	protected $CallFiltersPriorityMap	= array(
		'query_vars' 	=> 1
	);
	
	// Liste des arguments de section par défaut
	private static	$Defaults					= array(
		'posts_per_section'			=> 4,
		'showall_link'				=> true				
	);
	
	// Liste des sections déclarées
	private static	$Section					= array();
	// Liste des type de post de recherche par mot clef
	private static $PostTypeTags				= array();
	
	// Objects de type de post
	private	static	$PostTypeObject				= array();	
	// Affiche tous les resultats d'une section
	private static	$ShowAll					= false;
	
	// Elément de requête par section
	/// Type de post
	private $SectionPostTypeRequest				= array();
	/// Recherche
	private $SectionSearchRequest				= array();
	/// Status
	private $SectionStatusRequest				= array();
	/// 
	private $SectionLimitRequest				= array();
	
	// Résultats de recherche
	/// 
	private	static	$SectionHasResults			= array();
	/// Nombre de contenu trouvé par section
	private	static	$SectionPostCount			= array();
	/// Nombre de contenu total par section
	private	static	$SectionFoundPosts			= array();
	/// Nombre de contenu trouvé	
	private static	$PostCount					= 0;
	/// Nombre de contenu total
	private static	$FoundPosts					= 0;
			
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();
				
		// Traitement de la configuration
		/// Traitement des arguments par défaut
		self::$Defaults = wp_parse_args( self::getConfig( 'defaults' ), self::$Defaults );

		/// Traitement des sections
		if( ! empty( self::getConfig( 'sections' ) ) ) :
			foreach( (array) self::getConfig( 'sections' ) as $name => $args ) :	
				self::$Section[$name] = wp_parse_args( $args, self::$Defaults );
			endforeach;
		endif;
		
		/// Traitement des mots clefs de recherche
		if( ! empty( self::getConfig( 'tags' ) ) ) :
			foreach( (array) self::getConfig( 'tags' ) as $post_type ) :
				array_push( self::$PostTypeTags, $post_type );
				CustomType::RegisterTaxonomy( 'tify_search_tag', array( 'singular' => __( 'mot-clef de recherche', 'tify' ), 'plural' => __( 'mots-clefs de recherche', 'tify' ), 'object_type' => $post_type ) );
			endforeach;
		endif;
	}
	
	/* = ACTIONS = */
	/** == Personnalisation des variables de requête == **/
	final public function query_vars( $aVars ) 
	{
		$aVars[] = '_s';

		return $aVars;
	}	
	
	/** == == **/
	final public function pre_get_posts( &$query )
	{	
		// Requêtes l'interface d'administration
		if( is_admin() ) :				
		
		// Requêtes l'interface visiteur
		else :	
			/// Requête principale
			if( $query->is_main_query() ) :
				if( $query->is_search()  ) :
					add_filter( 'posts_search', array( $this, 'posts_search' ), 10, 2 );
					add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );					
				elseif( $query->get( '_s', '' ) ) :
					add_filter( 'posts_search', array( $this, 'posts_search' ), 11, 2 );
					add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 11, 2 );					
				endif;
			endif;
		endif;
	
		// Requêtes communes aux deux interfaces
	}
	
	/** == Désactivation des condition de requête native de Wordpress == **/
	final public function posts_search( $search, $query )
	{
		// Empêche l'execution multiple du filtre
		if( $query->is_main_query() )
			\remove_filter( current_filter(), __METHOD__ );
		
		return '';
	}
	
	/** == Condition de requête personnalisée pour la recherche == **/
	final public function posts_clauses( $pieces, $query )
	{	
		global $wpdb;

		extract( $pieces );
	
		$sresults = false;
		
		// Récupération de la requête de recheche		
		if( $q['s'] = $query->get( 's', '' ) ) :
			$sresults = true;
		elseif( $q['s'] = $query->get( '_s', '' ) ) :
			self::$ShowAll = true;
		else :
			$q['s'] = false;
		endif;
		
		if( self::$Section ) :
			$subquery_where = array(); $section_num = 1;	
			foreach( (array) self::$Section as $name => $args ) :				
				$args['s'] = $q['s'];
				
				// Traitement des arguments de requête
				/// Traitement des sections
				if( ! $this->SectionPostTypeRequest[$name] 	= $this->ParseSectionPostTypeRequest( $args ) )
					continue;
					
				/// Traitement des métadonnées	
				if( isset( $args['fields']['postmeta'] ) ) :
					$metakeys = array();
					foreach( $args['fields']['postmeta'] as $mk ) :
						$metakeys[] = " tyspm.meta_key = '{$mk}'";
					endforeach;
					
					$_metakeys 	= ( ! empty( $metakeys ) ) ? " AND (". join( ' OR ', $metakeys ) .")" : '';
					
					$join .= " LEFT OUTER JOIN {$wpdb->postmeta} AS tyspm ON ({$wpdb->posts}.ID = tyspm.post_id{$_metakeys})";
					$groupby = "{$wpdb->posts}.ID";
				endif;

				/// Traitement des mots clefs de recherche
				foreach( (array) $args['post_type'] as $pt ) :
					if( ! in_array( $pt, self::$PostTypeTags ) ) 
						continue;
					$join .= " LEFT OUTER JOIN {$wpdb->term_relationships} AS tystr{$name} ON ({$wpdb->posts}.ID = tystr{$name}.object_id)";
					$join .= " LEFT OUTER JOIN {$wpdb->term_taxonomy} AS tystt{$name} ON (tystr{$name}.term_taxonomy_id = tystt{$name}.term_taxonomy_id  AND tystt{$name}.taxonomy = 'tify_search_tag')";
					$join .= " LEFT OUTER JOIN {$wpdb->terms} AS tyst{$name} ON (tystt{$name}.term_id = tyst{$name}.term_id)";
					$groupby = "{$wpdb->posts}.ID";
					break;
				endforeach;					
													
				/// Traitement de la requête de recherche
				if( ! $this->SectionSearchRequest[$name] 	= $this->ParseSectionSearchRequest( $name, $args ) )
					continue;
				/// Traitement du status
				$this->SectionStatusRequest[$name] 			= $this->ParseSectionStatusRequest( $args );								
				/// Limite du nombre de résultat
				$this->SectionLimitRequest[$name] 			= $this->ParseSectionLimitRequest( $args, $query );
												
				$countquery = "SELECT COUNT(DISTINCT {$wpdb->posts}.ID) FROM {$wpdb->posts} {$join} WHERE 1 {$this->SectionPostTypeRequest[$name]} {$this->SectionSearchRequest[$name]} {$this->SectionStatusRequest[$name]}";

				// Création de la requête s'il existe des posts
				if( self::$SectionFoundPosts[$name] = (int) $wpdb->get_var( $countquery ) ) :
					// Compte des resultats
					self::$PostCount 	+= self::$SectionPostCount[$name] = ( self::$SectionFoundPosts[$name] < $this->SectionLimitRequest[$name] ) ? self::$SectionFoundPosts[$name] : $this->SectionLimitRequest[$name];
					self::$FoundPosts 	+= self::$SectionFoundPosts[$name];

					// Requête de récupération des posts
					$subquery			= "SELECT * FROM ( SELECT DISTINCT ID FROM {$wpdb->posts} {$join} WHERE 1 {$this->SectionPostTypeRequest[$name]} {$this->SectionSearchRequest[$name]} {$this->SectionStatusRequest[$name]} ". /*LIMIT 0,{$this->SectionLimitRequest[$name]}*/ ") as tyssub{$name}";
					$subquery_where[] 	= "( ( {$wpdb->posts}.ID IN ({$subquery})". ( $sresults ? " AND @section:=if( {$wpdb->posts}.ID, {$section_num}, 0 )" : "" ). " ) )";
					
					self::$SectionHasResults[$section_num] = $name;
		
					$section_num++;
				endif;				
			endforeach;			
			//var_dump(  compact( array_keys( $pieces ) ) );
			// Personnalisation des éléments de requête 
			/// Définition des variables de requête 
			if( $sresults ) :
				$wpdb->query( "SET @postnum := 0, @section:=0;" );
				
				/// Champs de récupération de données
				$fields .= ", @postnum:= @postnum+1 as tify_search_postnum, @section as tify_search_section_num";
				
				/// Gestion du tri
				$orderby = "tify_search_section_num ASC, tify_search_postnum DESC,". $orderby;
				
				/// Limite du nombre de résultat
				$limits = "LIMIT ". self::$PostCount;
			endif;	
			
			/// Conditions de recherche
			if( $subquery_where ) :
				$where .= " AND (";
				$where .= join( " OR ", $subquery_where );			
				$where .= " )";
			endif;
		endif;

		// Empêche l'execution multiple du filtre
		if( $query->is_main_query() )
			\remove_filter( current_filter(), __METHOD__ );
			
		return compact( array_keys( $pieces ) );
	}
	
	/* = TRAITEMENT DES ARGUMENTS DE REQUETE
	/** == Type de post de section == **/
	private function ParseSectionPostTypeRequest( &$args )
	{
		$post_types = array();		
			
		// Traitement du type de post			
		if( 'any' == $args['post_type'] ) :
			$post_types = get_post_types( array( 'exclude_from_search' => false ) );
		elseif( is_string( $args['post_type'] ) ) :
			$post_types = array_map( 'trim', explode( ',', $args['post_type'] ) );
		endif;

		/// Récupération des object de définition de type de post ou suppression des types de post invalides			
		foreach( (array) $post_types as $post_type ) :
			if( ! self::GetPostTypeObject($post_type) )
				unset( $post_types[$post_type] );
		endforeach;
		
		if( ! empty( $post_types ) ) :
			$args['post_type'] = $post_types;
			return "AND post_type IN ('". join("', '", $post_types ) ."')";	
		endif;
	}
		
	/** == Champs de recherche de section == **/
	private function ParseSectionSearchRequest( $name, &$q )
	{
		global $wpdb;

		$search = '';
		// added slashes screw with quote grouping when done early, so done later
		$q['s'] = stripslashes( $q['s'] );
		if ( empty( $_GET['s'] ) && $this->is_main_query() )
			$q['s'] = urldecode( $q['s'] );
		// there are no line breaks in <input /> fields
		$q['s'] = str_replace( array( "\r", "\n" ), '', $q['s'] );
		$q['search_terms_count'] = 1;
		if ( ! empty( $q['sentence'] ) ) {
			$q['search_terms'] = array( $q['s'] );
		} else {
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['s'], $matches ) ) {
				$q['search_terms_count'] = count( $matches[0] );
				$q['search_terms'] = $this->parse_search_terms( $matches[0] );
				// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
				if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 )
					$q['search_terms'] = array( $q['s'] );
			} else {
				$q['search_terms'] = array( $q['s'] );
			}
		}

		$n = ! empty( $q['exact'] ) ? '' : '%';
		$searchand = '';
		$q['search_orderby_title'] = array();
		$searchquery = array();
		foreach ( $q['search_terms'] as $term ) {
			// Terms prefixed with '-' should be excluded.
			$include = '-' !== substr( $term, 0, 1 );
			if ( $include ) {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			} else {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			}

			if ( $n && $include ) {
				$like = '%' . $wpdb->esc_like( $term ) . '%';
				$q['search_orderby_title'][] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $like );
			}

			$like = $n . $wpdb->esc_like( $term ) . $n;
			
			if( in_array( 'post_title', $q['fields'] ) ) :
				$searchquery[] = '( '. $wpdb->posts .'.post_title '. $like_op .' "%1$s" )';
			endif;
			if( in_array( 'post_excerpt', $q['fields'] ) ) :
				$searchquery[] = '( '. $wpdb->posts .'.post_excerpt '. $like_op .' "%1$s" )';
			endif;
			if( in_array( 'post_content', $q['fields'] ) ) :
				$searchquery[] = '( '. $wpdb->posts .'.post_content '. $like_op .' "%1$s" )';
			endif;

			if( isset( $q['fields']['postmeta'] ) ) :
				foreach( $q['fields']['postmeta'] as $meta_key ) :
					$searchquery[] = '( tyspm.meta_key = "'. $meta_key .'" AND tyspm.meta_value '. $like_op .' "%1$s" )';
				endforeach;			
			endif;
			
			foreach( (array) $q['post_type'] as $pt ) :
				if( ! in_array( $pt, self::$PostTypeTags ) ) 
					continue;
				$searchquery[] = '( ( tyst'. $name .'.name '. $like_op .' "%1$s" ) OR ( tyst'. $name .'.slug '. $like_op .' "%1$s" ) )';
			endforeach;
			
			if( !empty( $searchquery ) )	
				$search .= $wpdb->prepare( $searchand .'( '. join( ' '. $andor_op .' ', $searchquery ) .' )', $like );
			
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ( {$search} ) ";
			if ( ! is_user_logged_in() )
				$search .= " AND ($wpdb->posts.post_password = '') ";
		}
		
		return $search;
	}
	
	/** == Status de section == **/
	private function ParseSectionStatusRequest( $args )
	{
		global $wpdb;
		
		$status  = " AND ( ";				
		// Traitement du status de recherche
		$status .= " {$wpdb->posts}.post_status = 'publish'";
		
		/// Ajout des status publics
		$public_states = get_post_stati( array('public' => true) );
		foreach ( (array) $public_states as $state ) :
			if ( 'publish' == $state ) // Publish is hard-coded above.
				continue;
			$status .= " OR {$wpdb->posts}.post_status = '$state'";
		endforeach;	
		
		/// Ajout des status protégés qui devrait apparaitre dans la liste de l'interface d'administration
		if ( $this->is_admin ) :						
			$admin_all_states = get_post_stati( array('protected' => true, 'show_in_admin_all_list' => true) );
			foreach ( (array) $admin_all_states as $state )
				$status .= " OR {$wpdb->posts}.post_status = '$state'";
		endif;
		
		/// @TODO Add private states that are limited to viewing by the author of a post or someone who has caps to read private states.
		/*if ( is_user_logged_in() ) :
			// Définition des habilitations
			//$read_private_cap 	= $post_type_object->cap->read_private_posts;
			$private_states = get_post_stati( array('private' => true) );
			foreach ( (array) $private_states as $state )
				$status .= current_user_can( $read_private_cap ) ? " OR {$wpdb->posts}.post_status = '$state'" : " OR {$wpdb->posts}.post_author = $user_id AND {$wpdb->posts}.post_status = '$state'";
		endif;*/
			
		$status .= " ) ";
		
		return $status;
	}
	
	/** == == **/
	private function ParseSectionLimitRequest( $args, $query )
	{
		if( ! self::$ShowAll )		
			return $args['posts_per_section'];		
		else	
			return $query->get( 'posts_per_page', get_option( 'posts_per_page' ) );			
	}
	
	/* = CONTROLEURS = */
	/** == Récupération de l'objet d'un type de post == **/
	private static function GetPostTypeObject( $post_type )
	{
		if( ! empty( self::$PostTypeObject[$post_type] ) )
			return self::$PostTypeObject[$post_type];

		if( self::$PostTypeObject[$post_type] = get_post_type_object( $post_type ) )
			return self::$PostTypeObject[$post_type];
		
		return null;	
	}
	
	/** == Récupération du nombre de contenu trouvé pour une section == **/
	private static function GetSectionPostCount( $section )
	{
		if( isset( self::$SectionPostCount[$section] ) )
			return self::$SectionPostCount[$section];
		
		return 0;
	}
	
	/** == Récupération du nombre de contenu total par section == **/
	private static function GetSectionFoundPosts( $section )
	{
		if( isset( self::$SectionFoundPosts[$section] ) )
			return self::$SectionFoundPosts[$section];
		
		return 0;
	}
					
	/* = METHODES PUBLIQUES = */
	/** == Numéro du post (deprecated: utilisé pour le debug) == **/
	public static function PostNum( $post = null )
	{
		if( ! $post = get_post() )
			return 0;
		if( ! isset( $post->tify_search_postnum ) )
			return 0;
		
		return $post->tify_search_postnum;
	}
	
	/** == Section du post == **/
	public static function PostSection( $post = null )
	{
		if( ! $post = get_post() )
			return;
		if( ! isset( $post->tify_search_section_num ) )
			return;
			
		return self::$SectionHasResults[$post->tify_search_section_num];
	}
		
	/** == Intitulé de section == **/
	public static function SectionLabel( $section = null )
	{		
		if( ! $section && ! ( $section = self::PostSection() ) )
			return;
		
		if( $post_type_object = self::GetPostTypeObject( get_post_type() ) )
			return $post_type_object ->labels->name;
	}
	
	/** == == **/
	public static function SectionFoundPosts( $section = null )
	{		
		if( ! $section && ! ( $section = self::PostSection() ) )
			return;

		return self::GetSectionFoundPosts( $section );
	}
	
	/** == == **/
	public static function SectionPostCount( $section = null )
	{	
		if( ! $section && ! ( $section = self::PostSection() ) )
			return;

		return self::GetSectionPostCount( $section );
	}
	
	/** == == **/
	public static function SectionShowAllLink( $section = null, $args = array(), $echo = true )
	{
		if( ! $section && ! ( $section = self::PostSection() ) )
			return;
		
		if( ! ( self::SectionFoundPosts( $section ) > self::SectionPostCount( $section ) ) )
			return;
		
		if( empty( self::$Section[$section]['showall_link'] ) )
			return;
		
		$type = current( self::$Section[$section]['post_type'] );		

		$defaults = array(
			'url'	=> esc_attr( add_query_arg( '_s', get_search_query(), get_post_type_archive_link( $type ) ) ),
			'text'	=> sprintf( __( 'Voir tous les résultats %s', 'tify' ), get_post_type_object( $type )->labels->name ),
			'title'	=> sprintf( __( 'Afficher les résultats de recherche pour %s', 'tify' ), get_post_type_object( $type )->labels->name ),
			'class'	=> ''
		);
		if( is_array( self::$Section[$section]['showall_link'] ) )
			$defaults = wp_parse_args( self::$Section[$section]['showall_link'], $defaults );		
		$args = wp_parse_args( $args, $defaults );
		
		$output  = "";
		$output .= "<a href=\"{$args['url']}\"";
		$output .= " class=\"tify_search_all_link". ( $args['class'] ? ' '. $args['class'] : '' ) ."\"";
		$output .= " title=\"{$args['title']}\"";
		$output .= ">";
		$output .= $args['text'];
		$output .= "</a>";
		
		if( $echo )
			echo $output;
		else
			return $ouput;
	}
	
	/** == == **/
	public static function FoundPosts( $type = null )
	{
		return self::$FoundPosts;
	}
}