<?php
namespace tiFy\Components\Breadcrumb;

use tiFy\Environment\Component;

/** @Autoload */
class Breadcrumb extends Component
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts'	
	);
	
	// Configuration
	static $Instance = 1;
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function init()
	{
		if( $theme = self::getConfig( 'theme' ) )
			wp_register_style( 'tiFyBreadcrumb', $this->Url ."/theme/{$theme}.css", array(), '160318' );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function wp_enqueue_scripts()
	{
		if( $theme = self::getConfig( 'theme' ) )
			wp_enqueue_style( 'tiFyBreadcrumb' );
	}
		
	/* = AFFICHAGE = */
	public static function display( $args = array(), $echo = true )
	{
	    global $post;

		$config = wp_parse_args( $args, self::getConfig() );
		extract( $config, EXTR_SKIP );
		
		if( empty( $id )  )
			$id = 'tiFyBreadcrumb-'. self::$Instance++;		
		
		$output  = "";
		$output .= $before ."<ol id=\"{$id}\" class=\"tiFyBreadcrumb". ( ! empty( $class ) ? ' '. $class : '' ) ."\">";
		
		// Retour à la racine du site
		$output .= self::root();
		
		// Page 404 - Contenu introuvable
		if( is_404() ) : 
			$output .= self::is_404();
		// Page de résultats de recherche
		elseif( is_search() ) : 
			$output .= self::is_search();
		// Page de contenus associés à une taxonomie
		elseif( is_tax() ) :		
			$output .= self::is_tax();
		/** == Page d'accueil du site == **/
		elseif( is_front_page() ) : 
			$output .= self::is_front_page();
		/** == Page liste des articles du blog == **/
		elseif( is_home() ) :
			$output .= self::is_home();
		/** == Page de fichier média == **/
		elseif ( is_attachment() ) :
			$output .= self::is_attachment();
		/** == Page de contenu de type post == **/
		elseif ( is_single() ) :		
			$output .= self::is_single();		
		/** == Page de contenu de type page == **/
		elseif ( is_page() ) :  
			$output .= self::is_page();		
		/** == Page de contenus associés à une catégorie == **/
		elseif( is_category() ) :
			$output .= self::is_category();
		/** == Page de contenus associés à un mot-clef == **/				
		elseif ( is_tag() ):
			$output .= self::is_tag();
		/** == Page de contenus associés à un auteur == **/
		elseif ( is_author() ):
			$output .= self::is_author();
		/** == Page de contenus relatifs à une date == **/	
		elseif ( is_date() ) :
			$output .= self::is_date();
		/** == Pages de contenus == **/	
		elseif ( is_archive() )	:	
			$output .= self::is_archive();		
		/** 
		 * @todo
		elseif ( is_comments_popup() ) :
		elseif ( is_paged() ) :
		else : **/
		endif;		
				
		$output .= "</ol>". $after;
		
		if( $echo )
			echo $output;
		else
			return $output;
	}

	/* = RENDU = */
	/** == Titre d'une page de contenu == **/
	public static function title_render( $post_id )
	{
		return esc_html( wp_strip_all_tags( get_the_title( $post_id ) ) );
	}
	
	/* = CONTEXTE = */
	/** == Racine du site == **/
	public static function root()
	{		
		$front_page = get_option( 'page_on_front' );
		$url 		= ( $front_page ) ? get_permalink( $front_page ) : home_url();
		$name		= ( $front_page ) ? self::title_render( $front_page ) : __( 'Accueil', 'tify' );
		$title		= ( $front_page ) ? sprintf( __( 'Retour à %s', 'tify' ), self::title_render( $front_page ) ) : __( 'Retour à l\'accueil', 'tify' );
		
		return apply_filters( 'tify_breadcrumb_root', sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s" title="%3$s">%2$s</a></li>', $url, $name, $title ) );
	}

	/** == Page 404 == **/
	public static function is_404()
	{
		$name	= __( 'Erreur 404 - Impossible de trouver la page', 'tify' );
		
		return apply_filters( 'tify_breadcrumb_404', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
	
	/** == Page de résultat de recherche == **/
	public static function is_search()
	{
		$name	= sprintf( __( 'Résultat de recherche pour : "%s"' , 'tify' ), get_search_query() );
		
		return apply_filters( 'tify_breadcrumb_is_search', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
	
	/** == Page de contenus associés à une taxonomie == **/
	public static function is_tax()
	{
		$tax 	= get_queried_object();
		$name 	= sprintf( '%s : %s', get_taxonomy( $tax->taxonomy )->label, $tax->name );
				
		return apply_filters( 'tify_breadcrumb_is_tax', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
	
	/** == Page d'accueil du site == **/
	public static function is_front_page()
	{
		if( $page_on_front = get_option( 'page_on_front' ) ) :			
			$name = self::title_render( $page_on_front );
		else :
			if( is_paged() ) :
				global $wp_query;
				$name = sprintf( __( 'Actualités - page %d sur %d', 'tify'), ( ( $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ), $wp_query->max_num_pages );
			else :
				$name = __( 'Actualités', 'tify' );
			endif;
		endif;
		
		return apply_filters( 'tify_breadcrumb_is_front_page', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
	
	/** == Page liste des articles du blog == **/
	public static function is_home()
	{
		if( $page_for_posts = get_option( 'page_for_posts' ) ) :
			$name = self::title_render( $page_for_posts );
		else :			
			if( is_paged() ) :
				global $wp_query;
				$name = sprintf( __( 'Actualités - page %d sur %d', 'tify'), ( ( $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ) ), $wp_query->max_num_pages );
			else :
				$name = __( 'Actualités', 'tify' );
			endif;
		endif;
		
		return apply_filters( 'tify_breadcrumb_is_home', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
	
	/** == Page de fichier média == **/
	public static function is_attachment()
	{
		$ancestors = '';
		if( $parents = get_ancestors( get_the_ID(), get_post_type() ) ) :
			if( ( 'post' === get_post_type( current( $parents ) ) )  && ( $page_for_posts = get_option( 'page_for_posts' ) ) )
				$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', get_permalink( $page_for_posts ), self::title_render( $page_for_posts ) );
			reset( $parents );
			foreach( array_reverse( $parents ) as $parent )
				$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), self::title_render( $parent ) );
		endif;		
		
		return apply_filters( 'tify_breadcrumb_is_attachment', sprintf( '%s<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $ancestors, self::title_render( get_the_ID() ) ) );
	}
	
	/** == Page de contenu de type post == **/
	public static function is_single()
	{
		$ancestors = '';
		// Le type du contenu est un article de blog
		if( is_singular( 'post' ) ) :
			if( $page_for_posts = get_option( 'page_for_posts' ) )
				$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', get_permalink( $page_for_posts ), self::title_render( $page_for_posts ) );
			else
				$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', home_url(), __( 'Actualités', 'tify' ) );		
		// Le type de contenu autorise les pages d'archives
		elseif( ( $post_type_obj = get_post_type_object( get_post_type() ) ) &&  $post_type_obj->has_archive ) :
			$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', get_post_type_archive_link( get_post_type() ), $post_type_obj->labels->name );
		endif;	

		// Le contenu a des ancêtres
		if( $parents = get_ancestors( get_the_ID(), get_post_type() ) )
			foreach( array_reverse( $parents ) as $parent )
				$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), self::title_render( $parent ) );

		return apply_filters( 'tify_breadcrumb_is_single', sprintf( '%s<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $ancestors, self::title_render( get_the_ID() ) ) );
	}

	/** == Page de contenu de type page == **/
	public static function is_page()
	{
		$ancestors = '';
		if( $parents = get_ancestors( get_the_ID(), get_post_type() ) )
			foreach( array_reverse( $parents ) as $parent )
				$ancestors .= sprintf( '<li class="tiFyBreadcrumb-Item"><a href="%1$s">%2$s</a></li>', get_permalink( $parent ), self::title_render( $parent ) );
	
		return apply_filters( 'tify_breadcrumb_is_page', sprintf( '%s<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $ancestors, self::title_render( get_the_ID() ) ) );
	}
	
	/** == Page de contenus associés à une catégorie == **/
	public static function is_category()
	{
 		$category = get_category( get_query_var( 'cat' ), false );
		
		return apply_filters( 'tify_breadcrumb_is_category', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">Catégorie : %s</li>', $category->name ) );
	}

	/** == Page de contenus associés à un mot-clef == **/
	public static function is_tag()
	{
 		$tag = get_tag( get_query_var( 'tag' ), false );
		
		return apply_filters( 'tify_breadcrumb_is_tag', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">Mot-Clef : %s</li>', $tag->name ) );
	}
	
	/** == Page de contenus associés à un auteur == **/
	public static function is_author()
	{
 		$author_name = get_author_name( get_query_var( 'author' ) );
		
		return apply_filters( 'tify_breadcrumb_is_tag', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">Auteur : %s</li>', $author_name ) );
	}
	
	/** == Page de contenus relatifs à une date == **/
	public static function is_date()
	{
		if ( is_day() )
			$name = sprintf( __( 'Archives du jour : %s', 'tify' ), get_the_date() );
		elseif ( is_month() )  
			$name = sprintf( __( 'Archives du mois : %s', 'tify' ), get_the_date( 'F Y' ) );
		elseif ( is_year() )
			$name = sprintf( __( 'Archives de l\'année : %s', 'tify' ), get_the_date( 'Y' ) );
		
		return apply_filters( 'tify_breadcrumb_is_date', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
	
	/** == Page de contenus == **/
	public static function is_archive()
	{
		if( is_post_type_archive() )
			$name = post_type_archive_title( '', false );
		else
			$name = __( 'Actualités', 'tify' ); 
		
		return apply_filters( 'tify_breadcrumb_is_archive', sprintf( '<li class="tiFyBreadcrumb-Item tiFyBreadcrumb-Item--active">%s</li>', $name ) );
	}
}