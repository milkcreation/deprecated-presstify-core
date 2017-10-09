<?php
namespace tiFy\Components\Pagination;

class Pagination extends \tiFy\App\Component
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'init',
		'wp_enqueue_scripts'	
	);
	
	static $Instance = 1;
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function init()
	{
		wp_register_style( 'tiFyPagination', self::tFyAppUrl() ."/theme/base.css", array(), '160318' );
		
		if( $theme = self::tFyAppConfig( 'theme' ) ) :
			wp_register_style( 'tiFyPagination-theme', self::tFyAppUrl() ."/theme/{$theme}.css", array( 'tiFyPagination' ), '160318' );
		endif;
		
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function wp_enqueue_scripts()
	{
		if( $theme = self::tFyAppConfig( 'theme' ) ) :
			wp_enqueue_style( 'tiFyPagination-theme' );
		endif;
	}
	
	/* = AFFICHAGE = */
	/** == Interface de navigation == **/
	static function display( $args = false, $echo = true ) 
	{	
		$config = wp_parse_args( $args, self::tFyAppConfig() );
		extract( $config, EXTR_SKIP );
		
		if( ! $id )
			$id = 'tiFyPagination-'. self::$Instance++;
		
		// Traitement des variables	
		/// Requête
		if( ! $query ) :
			global $wp_query;
			$query = $wp_query;			
		endif;
		$tify_query = ( $query instanceof \tiFy\Core\Db\Query ) ? true : false;

		/// Page courante
		if( ! $paged )
			$paged = isset( $query->query_vars['paged'] ) ? $query->query_vars['paged'] : 0;
		$paged = ! empty( $paged ) ? intval( $paged ) : 1;
		
		/// Nombre d'éléments par page
		if( ! $per_page )
			$per_page = $tify_query ? intval( $query->query_vars['per_page'] ) : intval( $query->query_vars['posts_per_page'] );
		
		/// Total		
		$offset = ( isset( $query->query_vars['offset'] ) && ! $tify_query ) ? $query->query_vars['offset'] : 0;
		if( $tify_query )
			$total = intval( ceil( $query->found_items / $per_page ) );
		else
			$total = $offset ? intval( ceil( ( $query->found_posts + ( ( $per_page*( $paged - 1 ) ) - $offset ) ) / $per_page ) ) : intval( ceil( $query->found_posts / $per_page ) );
		
		if( $total <= 1 )
			return;
		
		// Génération des liens de navigation	
		$prevlink = esc_url( get_pagenum_link( $paged - 1 ) ); 
		$nextlink = esc_url( get_pagenum_link( $paged + 1 ) ); 
		
		$output = "";
		$output .= "<ul id=\"{$id}\" class=\"tiFyPagination {$class}\">\n";
		// Page précédente	
		if( $paged > 1 && ! empty( $previous ) )
			$output .= "\t<li class=\"tiFyPagination-Item tiFyPagination-Item--prev\">". sprintf( "<a href=\"%s\" class=\"tiFyPagination-ItemPage tiFyPagination-ItemPage--link\">%s</a>", $prevlink, stripslashes( $previous ) )."</li>\n";
		
		// Numérotation des pages
		if( $num ) :
			// Définition des variables d'environnement
			$min_links 	= ($range*2)+1;
			$block_min 	= min( $paged - $range, $total - $min_links );
			$block_high = max( $paged + $range, $min_links );
			$left_gap 	= ( ( $block_min - $anchor - $gap ) > 0 ) ? true : false;
			$right_gap 	= ( ( $block_high + $anchor + $gap ) < $total ) ? true : false;
			$ellipsis 	= "\t<li class=\"tiFyPagination-Item tiFyPagination-Item--gap\"><span class=\"tiFyPagination-ItemPage\">...</span></li>\n";
			
			// Numéros de pages
			if( $left_gap && ! $right_gap )
				$output .= sprintf( '%s%s%s', self::loop( 1, $anchor, 0 ), $ellipsis, self::loop( $block_min, $total, $paged ) );
			elseif( $left_gap && $right_gap )
				$output .= sprintf( '%s%s%s%s%s', self::loop( 1, $anchor, 0 ), $ellipsis, self::loop( $block_min, $block_high, $paged ), $ellipsis, self::loop( ( $total - $anchor + 1 ), $total ) );
			elseif( $right_gap && ! $left_gap )
				$output .= sprintf('%s%s%s', self::loop( 1, $block_high, $paged ), $ellipsis, self::loop( ( $total - $anchor + 1 ), $total ) );
			else
				$output .= self::loop( 1, $total, $paged );
		endif;
		
		// Page suivante	
		if( ( $paged < $total ) && ! empty( $next ) )
			$output .= "\t<li class=\"tiFyPagination-Item tiFyPagination-Item--next\">". sprintf( "<a href=\"%s\" class=\"tiFyPagination-ItemPage tiFyPagination-ItemPage--link\">%s</a>", $nextlink, stripslashes( $next ) ) ."</li>\n";

		$output .= "</ul>\n";
	
		if( $echo ) 
			echo $output;
		else 
			return $output;
	}
	
	/** == == **/
	static private function loop( $start, $max, $paged = 0 )
	{
		$output = "";
		for ( $i = $start; $i <= $max; $i++ )
			$output .= ( $paged == intval( $i ) ) ? "\t<li class=\"tiFyPagination-Item tiFyPagination-Item--active\"><span class=\"tiFyPagination-ItemPage\">{$i}</span></li>\n" : "\t<li class=\"tiFyPagination-Item tiFyPagination-Item--nav\"><a href=\"". esc_url( get_pagenum_link( $i ) ) ."\" class=\"tiFyPagination-ItemPage tiFyPagination-ItemPage--link\">{$i}</a></li>\n";
		
		return $output;
	}	
}