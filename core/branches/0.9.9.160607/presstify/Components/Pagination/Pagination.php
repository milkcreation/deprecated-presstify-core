<?php
namespace tiFy\Components\Pagination;

use tiFy\Environment\Component;

/** @Autoload */
class Pagination extends Component
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_enqueue_scripts'	
	);
	
	static $Instance = 1;
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function init()
	{
		if( $theme = self::getConfig( 'theme' ) )
			wp_register_style( 'tify_pagination', $this->Url ."/theme/{$theme}.css", array(), '160318' );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function wp_enqueue_scripts()
	{
		if( $theme = self::getConfig( 'theme' ) )
			wp_enqueue_style( 'tify_pagination' );
	}
	
	/* = AFFICHAGE = */
	/** == Interface de navigation == **/
	static function display( $args = false, $echo = true ) 
	{	
		$config = wp_parse_args( $args, self::getConfig() );
		extract( $config, EXTR_SKIP );
		
		if( ! $id )
			$id = 'tify_pagination-'. self::$Instance++;
		
		// Traitement des variables	
		/// Requête
		if( ! $query ) :
			global $wp_query;
			$query = $wp_query;			
		endif;
		$tify_query = ( $query instanceof tiFy_Query ) ? true : false;
		
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
		$output .= "<ul id=\"{$id}\" class=\"tify_pagination {$class}\">\n";
		// Page précédente	
		if( $paged > 1 && ! empty( $previous ) )
			$output .= "\t<li class=\"prev\">". sprintf( "<a href=\"%s\">%s</a>", $prevlink, stripslashes( $previous ) )."</li>\n";
		
		// Numérotation des pages
		if( $num ) :
			// Définition des variables d'environnement
			$min_links 	= ($range*2)+1;
			$block_min 	= min( $paged - $range, $total - $min_links );
			$block_high = max( $paged + $range, $min_links );
			$left_gap 	= ( ( $block_min - $anchor - $gap ) > 0 ) ? true : false;
			$right_gap 	= ( ( $block_high + $anchor + $gap ) < $total ) ? true : false;
			$ellipsis 	= "\t<li><span class=\"gap\">...</span></li>\n";
			
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
			$output .= "\t<li class=\"next\">". sprintf( "<a href=\"%s\">%s</a>", $nextlink, stripslashes( $next ) ) ."</li>\n";

		$output .= "</ul>\n";
	
		if( $echo ) 
			echo $output;
		else 
			return $output;
	}
	
	/* = CONTROLEUR */
	static private function loop( $start, $max, $paged = 0 )
	{
		$output = "";
		for ( $i = $start; $i <= $max; $i++ )
			$output .= ( $paged == intval( $i ) ) ? "\t<li class=\"active\"><span>{$i}</span></li>\n" : "\t<li class=\"navi\"><a href=\"". esc_url( get_pagenum_link( $i ) ) ."\">{$i}</a></li>\n";
		
		return $output;
	}	
}