<?php
/*
Plugin Name: Pagination
Plugin URI: http://presstify.com/navigation/addons/pagination
Description: Interface de pagination
Version: 1.0.1
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * Pagination
 */
function mktzr_paginate( $args = false ) {
	$defaults = array(
		'title' => 'Pages:',		
		'previouspage' => '&laquo;',
		'nextpage' => '&raquo;',
		'css' => true,
		'empty' => true,
		'range' => 2,
		'anchor' => 3,
		'gap' => 1,
		'echo' => true,
		'wp_query' => '',
		'posts_per_page' => 0,
		'page' => 0,
		'pages' => false,
		'form_submit' => false
	);
	$args = wp_parse_args( $args, apply_filters( 'mktzr_paginate_args', array() ) );
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
		
	if( ! $wp_query )
		global $wp_query;

	if( ! $page )
		$page = $wp_query->query_vars['paged'];
	
	if( ! $posts_per_page )
		$posts_per_page = intval( $wp_query->query_vars['posts_per_page'] );
			
	if( ! $pages ) :
		$offset = isset( $wp_query->query_vars['offset'] ) ? $wp_query->query_vars['offset'] : 0;
		$pages = $offset ? intval( ceil( ( $wp_query->found_posts + ( ( $posts_per_page*( $page - 1 ) ) - $offset ) ) / $posts_per_page ) ) : intval( ceil( $wp_query->found_posts / $posts_per_page ) );
		// ???? $pages = $offset ? intval( ceil( ( $wp_query->found_posts + ( $posts_per_page + $offset ) / $posts_per_page  ) / $posts_per_page ) ) : intval( ceil( $wp_query->found_posts / $posts_per_page ) );
	endif;
	
	$page = ! empty($page) ? intval($page) : 1;
	
	$prevlink =  esc_url( get_pagenum_link( $page - 1 ) ); 
	$nextlink = esc_url( get_pagenum_link( $page + 1 ) ); 
	
	$output = "";
	if ($pages > 1) {	
		$output .= '<ul class="pagination">';
		$ellipsis = "<li><span class='gap'>...</span></li>";

		if ( $page > 1 && ! empty( $previouspage ) ) :
			$output .= '<li>';
			$output .= ! $form_submit ? sprintf( '<a href="%s" class="prev">%s</a>', $prevlink, stripslashes( $previouspage ) ) : sprintf( '<button name="paged" value="$1%s" class="prev">$1%s</a>', stripslashes( $previouspage ) ) ;
			$output .= '</li>';
		endif;

		$min_links = $range * 2 + 1;
		$block_min = min($page - $range, $pages - $min_links);
		$block_high = max($page + $range, $min_links);
		$left_gap = ( ( $block_min - $anchor - $gap ) > 0 ) ? true : false;
		$right_gap = ( ( $block_high + $anchor + $gap ) < $pages ) ? true : false;

		if ($left_gap && !$right_gap) {
			$output .= sprintf('%s%s%s',
				mktzr_paginate_loop( 1, $anchor, 0, $form_submit ),
				$ellipsis,
				mktzr_paginate_loop( $block_min, $pages, $page, $form_submit )
			);
		} else if ($left_gap && $right_gap) {
			$output .= sprintf('%s%s%s%s%s',
				mktzr_paginate_loop( 1, $anchor, 0, $form_submit ),
				$ellipsis,
				mktzr_paginate_loop( $block_min, $block_high, $page, $form_submit ),
				$ellipsis,
				mktzr_paginate_loop( ( $pages - $anchor + 1 ), $pages, $form_submit )
			);
		} else if ($right_gap && !$left_gap) {
			$output .= sprintf('%s%s%s',
				mktzr_paginate_loop( 1, $block_high, $page, $form_submit ),
				$ellipsis,
				mktzr_paginate_loop( ($pages - $anchor + 1), $pages, $form_submit )
			);
		} else {
			$output .= mktzr_paginate_loop( 1, $pages, $page, $form_submit );
		}

		if ($page < $pages && !empty($nextpage) ) :
			$output .= '<li>';
			$output .= ! $form_submit ? sprintf( '<a href="%s" class="next">%s</a>', $nextlink, stripslashes( $nextpage ) ) : sprintf( '<button name="paged" value="$1%s" class="next">$1%s</a>', stripslashes( $nextpage ) ) ;
			$output .= '</li>';
		endif;
		$output .= "</ul>";
	}

	if ($pages > 1 || $empty) {
		if( $echo ) 
			echo $output;
		else 
			return $output;
	}
}

/**
 * Boucle de pagination
 */
function mktzr_paginate_loop( $start, $max, $page = 0, $form_submit = false ) {
	$output = "";
	for ( $i = $start; $i <= $max; $i++ ) :
		$p = esc_url( get_pagenum_link( $i ) );
		if( ! $form_submit ) :
			$output .= ( $page == intval( $i ) )
				? "<li class=\"active\"><a href=\"#\">$i</a></li>"
				: "<li><a href='$p' title='$i' class='navi'>$i</a></li>";
		else :
			$output .= ( $page == intval( $i ) )
				? "<li class=\"active\"><button name=\"paged\" value=\"{$i}\">{$i}</button></li>"
				: "<li><button name=\"paged\" value=\"{$i}\" class=\"navi\">{$i}</button></li>";
		endif;
	endfor;
	
	return $output;
}