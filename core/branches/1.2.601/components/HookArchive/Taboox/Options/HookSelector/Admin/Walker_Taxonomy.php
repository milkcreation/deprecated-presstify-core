<?php
namespace tiFy\Components\HookArchive\Taboox\Options\HookSelector\Admin;

class Walker_Taxonomy extends \Walker 
{
	public $tree_type = 'category';
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this
	// Passe
	static $n = 0;
	
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}

	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}
	
	public function start_el( &$output, $term, $depth = 0, $args = array(), $id = 0 ) 
	{
		if( false === ( ( isset( $args['exists'][$term->term_id] ) ) ? (bool) $args['exists'][$term->term_id]['edit'] : (bool) $args['options']['edit'] ) )
			return;
		$output .= "<li>";
		$output .= "<input type=\"hidden\" name=\"tify_hook_". $args['obj'] ."_". $args['archive'] ."[". self::$n ."][term]\" value=\"{$term->term_id}\" />";
		$output .= 	"<table class=\"form-table\">";
		$output .=		"<tbody>";
		$output .= 			"<tr>";
		$output .=				"<th role=\"scope\">". sprintf( __( '%s', 'tify' ), apply_filters( 'the_category', $term->name ) );
		if( $term->parent && ( $parent = get_term( $term->parent ) ) && ! is_wp_error( $parent ) )
			$output .=	"<em style=\"display:block;font-weifht:normal;font-size:11px; color:#999\">". sprintf( __( '(parent : %s)', 'tify' ), apply_filters( 'the_category', $parent->name ) ) ."</em>";	
		$output .=				"</th>";
		$output .=				"<td>";
		$output .= 	wp_dropdown_pages(
			array(
				'name' 				=> "tify_hook_". $args['obj'] ."_". $args['archive'] ."[". self::$n ."][id]",
				'post_type' 		=> 'page',
				'selected' 			=> isset( $args['exists'][$term->term_id] ) ? $args['exists'][$term->term_id]['id'] : 0,
				'show_option_none' 	=> __( 'Aucune page choisie', 'tify' ),
				'sort_column'  		=> 'menu_order',
				'echo'				=> false
			)
		);
		$output .= 				"</td>";
		$output .=			"</tr>";

		if( false === (bool) $args['options']['rewrite'] ) :
			$output .= "<input type=\"hidden\" name=\"tify_hook_". $args['obj'] ."_". $args['archive'] ."[". self::$n ."][permalink]\" value=\"0\">";
		elseif( ! $post_types = get_taxonomy( $args['archive'] )->object_type ) :					
		else :						
			$output .= 			"<tr>";
			$output .=				"<th role=\"scope\">". __( 'Réécriture des permaliens', 'tify' )."</th>";
			$output .=				"<td>";
			$has_post_type = false;
			foreach( (array) $post_types as $post_type ) :
				if( ! $post_type_object = get_post_type_object( $post_type ) )
					continue;
				if( ! $has_post_type ) :
					$output .= "<ul>";
				endif;
				$checked = isset( $args['exists'][$term->term_id] ) ? $args['exists'][$term->term_id]['permalink'] : $args['options']['permalink'];
				if( is_array( $checked ) ) :
					$checked = in_array( $post_type, $checked );
				endif;									
				
				$output .= "<li><label><input type=\"checkbox\" name=\"tify_hook_". $args['obj'] ."_". $args['archive'] ."[". self::$n ."][permalink][]\" value=\"{$post_type}\" ". checked( (bool) $checked, true, false )." autocomplete=\"off\">". $post_type_object->label ."</label></li>";
			endforeach;
			if( $has_post_type ) :
				$output .= "</ul>";
			endif;
			$output .= 				"</td>";
			$output .=			"</tr>";
		endif;
		$output .=		"</tbody>";
		$output .=	"</table>";
		
		self::$n++;
	}
}