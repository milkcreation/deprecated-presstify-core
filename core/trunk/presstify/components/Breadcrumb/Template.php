<?php
/**
 * @Overridable 
 */
namespace tiFy\Components\Breadcrumb;

class Template
{
	// Eléments
	private static $Parts	= array();
	
	/* = CONTROLEURS = */
	/** == Récupération des éléments == **/
	final public static function getParts()
	{
		return self::$Parts;
	}
	
	/** == Ajout d'un éléments== **/
	final public static function addPart( $part )
	{
		array_push( self::$Parts, $part );
	}
	
	/** == Réinitialisation des éléments == **/
	final public static function resetParts()
	{
		self::$Parts = array();		
	}
	
	/* = SURCHAGE = */
	/** == Titre d'une page de contenu == **/
	public static function titleRender( $post_id )
	{
		return esc_html( wp_strip_all_tags( get_the_title( $post_id ) ) );
	}

	/** == == **/
	public static function partRender( $attr = array(), $current = false )
	{
		$attr = wp_parse_args( 
			$attr,
			array(
				'name'	=> '',
				'url'	=> '#',
				'title'	=> ''
			)
		);
		
		if( ! $current ) :
			return sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s" title="%3$s" class="tiFyBreadcrumb-ItemLink" >%2$s</a></li>', $attr['url'], $attr['name'], $attr['title'] );
		else :
			return sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $attr['name'] );
		endif;
	}
		
	/** == == **/
	public static function getRoot()
	{
		$root = array( 
			'url'	=> home_url(),
			'name'	=> __( 'Accueil', 'tify' ),
			'title'	=> __( 'Retour à l\'accueil', 'tify' )
		);
		
		if( $front_page = get_option( 'page_on_front' ) ) :
			$root = array( 
				'url'	=> get_permalink( $front_page ),
				'name'	=> static::titleRender( $front_page ),
				'title'	=> sprintf( __( 'Retour à %s', 'tify' ), static::titleRender( $front_page ) )
			);
		endif;
		
		return $root;
	}
	
	/** == == **/
	public static function currentRender( $part )
	{
	    return static::partRender( $part, true );
	}
	
	/* = CONTEXTES = */
	/** == Racine du site == **/
	public static function root()
	{					
		return apply_filters( 'tify_breadcrumb_root', static::partRender( static::getRoot() ) );
	}

	/** == Page 404 == **/
	public static function is_404()
	{
		$part['name'] = __( 'Erreur 404 - Impossible de trouver la page', 'tify' );		
		self::addPart( $part );
		
		return apply_filters( 'tify_breadcrumb_404', static::currentRender( $part ) );
	}
	
	/** == Page de résultat de recherche == **/
	public static function is_search()
	{
		$part['name'] = sprintf( __( 'Résultat de recherche pour : "%s"' , 'tify' ), get_search_query() );		
		self::addPart( $part );
		
		return apply_filters( 'tify_breadcrumb_is_search', static::currentRender( $part ) );
	}
	
	/** == Page de contenus associés à une taxonomie == **/
	public static function is_tax()
	{
		$tax = get_queried_object();
		$part['name'] = sprintf( '%s : %s', get_taxonomy( $tax->taxonomy )->label, $tax->name );	
		self::addPart( $part );
		
		return apply_filters( 'tify_breadcrumb_is_tax', static::currentRender( $part ) );
	}
	
	/** == Page d'accueil du site == **/
	public static function is_front_page()
	{
		if( $page_on_front = get_option( 'page_on_front' ) ) :			
			$part['name'] = static::titleRender( $page_on_front );
		else :
			if( is_paged() ) :
				global $wp_query;
				$part['name'] = sprintf( __( 'Actualités - page %d sur %d', 'tify'), ( ( $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ), $wp_query->max_num_pages );
			else :
				$part['name'] = __( 'Actualités', 'tify' );
			endif;
		endif;
		self::addPart( $part );
		
		return apply_filters( 'tify_breadcrumb_is_front_page', static::currentRender( $part ) );
	}
	
	/** == Page liste des articles du blog == **/
	public static function is_home()
	{
		if( $page_for_posts = get_option( 'page_for_posts' ) ) :
			$part['name'] = static::titleRender( $page_for_posts );
		else :			
			if( is_paged() ) :
				global $wp_query;
				$part['name'] = sprintf( __( 'Actualités - page %d sur %d', 'tify'), ( ( $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ), $wp_query->max_num_pages );
			else :
				$part['name'] = __( 'Actualités', 'tify' );
			endif;
		endif;
		self::addPart( $part );

		return apply_filters( 'tify_breadcrumb_is_home', static::currentRender( $part ) );
	}
	
	/** == Page de fichier média == **/
	public static function is_attachment()
	{
		$ancestors = array();
		if( $parents = get_ancestors( get_the_ID(), get_post_type() ) ) :
			if( ( 'post' === get_post_type( current( $parents ) ) )  && ( $page_for_posts = get_option( 'page_for_posts' ) ) ) :
				$ancestors[] = array( 'url' => get_permalink( $page_for_posts ), 'name' => static::titleRender( $page_for_posts ), 'title' => static::titleRender( $page_for_posts ) );
			endif;
			reset( $parents );
			foreach( array_reverse( $parents ) as $parent ) :
				$ancestors[] = array( 'url' =>  get_permalink( $parent ), 'name' =>  static::titleRender( $parent ), 'title' =>  static::titleRender( $parent ) );
			endforeach;
		endif;
		
		$_ancestors = "";
		foreach( $ancestors as $a ) :
			$_ancestors .= static::partRender( $a );
			self::addPart( $a );
		endforeach;
		
		$part = array( 'url' => get_permalink(), 'name' => static::titleRender( get_the_ID() ), 'title' => static::titleRender( get_the_ID() ) );
		self::addPart( $part );
		
		return apply_filters( 'tify_breadcrumb_is_attachment', $_ancestors . static::currentRender( $part ) );
	}
	
	/** == Page de contenu de type post == **/
	public static function is_single()
	{
		$ancestors = array();
		// Le type du contenu est un article de blog
		if( is_singular( 'post' ) ) :
			if( $page_for_posts = get_option( 'page_for_posts' ) ) :
				$ancestors[] = array( 'url' => get_permalink( $page_for_posts ), 'name' => static::titleRender( $page_for_posts ), 'title' => static::titleRender( $page_for_posts ) );
			else :
				$ancestors[] = array( 'url' => home_url(), 'name' => __( 'Actualités', 'tify' ), 'title' => __( 'Actualités', 'tify' ) );
			endif;
		// Le type de contenu autorise les pages d'archives
		elseif( ( $post_type_obj = get_post_type_object( get_post_type() ) ) &&  $post_type_obj->has_archive ) :
			$ancestors[] = array( 'url' => get_post_type_archive_link( get_post_type() ), 'name' => $post_type_obj->labels->name, 'title' => $post_type_obj->labels->name );
		endif;	

		// Le contenu a des ancêtres
		if( $parents = get_ancestors( get_the_ID(), get_post_type() ) ) :
			foreach( array_reverse( $parents ) as $parent ) :
				$ancestors[] = array( 'url' => get_permalink( $parent ), 'name' => static::titleRender( $parent ), 'title' => static::titleRender( $parent ) );
			endforeach;
		endif;
		
		$_ancestors = "";
		foreach( $ancestors as $a ) :
			$_ancestors .= static::partRender( $a );
			self::addPart( $a );
		endforeach;
		
		$part = array( 'url' => get_permalink(), 'name' => static::titleRender( get_the_ID() ), 'title' => static::titleRender( get_the_ID() ) );
		self::addPart( $part );
		
		return apply_filters( 'tify_breadcrumb_is_single', $_ancestors . static::currentRender( $part ) );
	}

	/** == Page de contenu de type page == **/
	public static function is_page()
	{
		$ancestors = '';
		if( $parents = get_ancestors( get_the_ID(), get_post_type() ) ) :
			foreach( array_reverse( $parents ) as $parent ) :
				$ancestors .= static::partRender( array( 'url' => get_permalink( $parent ), 'name' => static::titleRender( $parent ) ) );
			endforeach;
		endif;
		
		$part = array( 'name' => static::titleRender( get_the_ID() ) );
				
		return apply_filters( 'tify_breadcrumb_is_page', $ancestors . static::currentRender( $part ) );
	}
	
	/** == Page de contenus associés à une catégorie == **/
	public static function is_category()
	{
 		$category = get_category( get_query_var( 'cat' ), false );
		
 		$part = array( 'name' => sprintf( 'Catégorie : %s', $category->name ) );
 		
		return apply_filters( 'tify_breadcrumb_is_category', static::currentRender( $part ) );
	}

	/** == Page de contenus associés à un mot-clef == **/
	public static function is_tag()
	{
 		$tag = get_tag( get_query_var( 'tag' ), false );
		
 		$part = array( 'name' => sprintf( 'Mot-Clef : %s', $tag->name ) );
 		
		return apply_filters( 'tify_breadcrumb_is_tag', static::currentRender( $part ) );
	}
	
	/** == Page de contenus associés à un auteur == **/
	public static function is_author()
	{
 		$author_name = get_author_name( get_query_var( 'author' ) );
 		
 		$part = array( 'name' => sprintf( 'Auteur : %s', $author_name ) );
		
		return apply_filters( 'tify_breadcrumb_is_tag', static::currentRender( $part ) );
	}
	
	/** == Page de contenus relatifs à une date == **/
	public static function is_date()
	{
		if ( is_day() )
			$part = array( 'name' => sprintf( __( 'Archives du jour : %s', 'tify' ), get_the_date() ) );
		elseif ( is_month() )  
			$part = array( 'name' => sprintf( __( 'Archives du mois : %s', 'tify' ), get_the_date( 'F Y' ) ) );
		elseif ( is_year() )
			$part = array( 'name' => sprintf( __( 'Archives de l\'année : %s', 'tify' ), get_the_date( 'Y' ) ) );
		
		return apply_filters( 'tify_breadcrumb_is_date', static::currentRender( $part ) );
	}
	
	/** == Page de contenus == **/
	public static function is_archive()
	{
		if( is_post_type_archive() ) :
			$part = array( 'name' => post_type_archive_title( '', false ) );
		else :
			$part = array( 'name' => __( 'Actualités', 'tify' ) ); 
		endif;
			
		return apply_filters( 'tify_breadcrumb_is_archive', static::currentRender( $part ) );
	}
}