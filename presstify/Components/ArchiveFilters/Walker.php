<?php
namespace tiFy\Components\ArchiveFilters;

class Walker extends \Walker
{
	/** == == **/
	public function start_lvl( &$output, $depth = 0, $args = array() )
	{
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class='children'>\n";
	}
		
	/** == == **/
	public function end_lvl( &$output, $depth = 0, $args = array() )
	{
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}
	
	/** == == **/
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 )
	{
		$output .= "<li>";
		
		switch( $args['data_type'] ) :
			case 'term' :
					$output .= "\t\t\t<input type=\"". ( $args['single'] ? 'radio': 'checkbox' ) ."\" id=\"term-{$object->taxonomy}-{$object->term_id}\" name=\"_tyaf[{$object->taxonomy}][]\" value=\"{$object->term_id}\" ". checked( in_array( $object->term_id, $args['selected'] ), true, false ) ." autocomplete=\"off\"/>\n";
					$output .= "\t\t<label for=\"term-{$object->taxonomy}-{$object->term_id}\">\n";
					$output .= apply_filters( 'the_category', $object->name );
					$output .= "\t\t</label>\n";
				break;
			case 'post_meta' :
					$output .= "\t\t\t<input type=\"". ( $args['single'] ? 'radio': 'checkbox' ) ."\" id=\"post_meta-{$object->meta_key}-{$object->meta_value}\" name=\"_tyaf[{$object->meta_key}][]\" value=\"{$object->meta_value}\" ". checked( in_array( $object->meta_value, $args['selected'] ), true, false ) ." autocomplete=\"off\"/>\n";
					$output .= "\t\t<label for=\"post_meta-{$object->meta_key}-{$object->meta_value}\">\n";
					$output .= $object->label;
					$output .= "\t\t</label>\n";
				break;
		endswitch;
	}
	
	/** == == **/
	public function end_el( &$output, $object, $depth = 0, $args = array() )
	{
		$output .= "</li>\n";
	}
}