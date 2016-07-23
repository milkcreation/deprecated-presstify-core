<?php
namespace tiFy\Components\ArchiveFilters;

class Walker
{
	/* = CONTRÔLEURS = */
	/** == Récupération de la liste de choix == **/
	private function _getChoices( $args )
	{
		$choices = array(); 		
		// Définition de la liste des choix
		if( ! $args['choices'] ) :
			if( $args['type'] === 'term' ) : 
				if( $terms = get_terms( $args['get_terms']['args'] ) ) :
					foreach( $terms as $term ) :
						$choices[$term->term_id] = $term->name;
					endforeach;
				endif;
			endif;
		else :
			$choices = $args['choices'];
		endif;
		
		return $choices;
	}
	
	/** == == **/
	private function _getSelected( $id, $defaults = null )
	{
		if( ! empty( $_REQUEST['_tyaf'][$id] ) )
			return $_REQUEST['_tyaf'][$id];
		
		return $defaults;
	}
	
	/** == == **/
	private function _getName( $id, $single = false )
	{
		$name = "_tyaf[{$id}]";
		if( ! $single )
			$name .= "[]";
		
		return $name;
	}
	
	/** == == **/
	public function Output( $obj, $obj_type, $nodes = array() )
	{							
		$output  = "";
		$output .= "<div id=\"tiFy_ArchiveFilter_{$obj}_{$obj_type}\">";
		$output .= "\t<form method=\"post\" action=\"\">\n";
		$output .= "\t\t<ul>\n";
		foreach( (array) $nodes as $id => $args ) :
			$output .= "\t\t\t<li>";
			$output .= $this->Node( $id, $args );
			$output .= "\t\t\t</li>\n";
		endforeach;
		$output .= "\t\t</ul>\n";
		$output .= "\t\t<button type=\"submit\" name=\"_tyaf[submit]\" value=\"{$obj}:{$obj_type}\" >". __( 'Rechercher', 'tify' ) ."</button>";
		$output .= "\t</form>\n";
		$output .= "</div>";
		
		return $output;
	}
	
	/** == == **/
	protected function Node( $id, $args = array() )
	{				
		$choices = $this->_getChoices( $args );
		
		$output  = "";
		$output .= "<h3>{$args['title']}</h3>";		
		$output .= $this->start_lvl( $id, $args );
		
		foreach( $choices as $value => $label ) :
			$selected 	= $this->_getSelected( $id, $args['default'] );
			$name 		= $this->_getName( $id, $args['single'] );
			
			$output .= $this->start_el( $id, $args );
			
			if( method_exists( $this, 'choice_'. $id ) ) :
				$output .= call_user_func(
					array( $this, 'choice_'. $id ),
					$value, 
					$name, 
					$label, 
					$selected
				);
			else :
				$output .= $this->choice_default( $value, $name, $label, $selected, $args['selector'], $id, $args );
			endif;
			
			$output .= $this->end_el( $id, $args );			
		endforeach;	
		
		$output .= $this->end_lvl( $id, $args );
				
		return $output;
	}
	
	/** == == **/
	public function choice_default( $value, $name, $label = '', $selected = null, $selector, $id, $args = array() )
	{
		$output = "";
		switch( $selector ) :
			default :
			case 'checkbox' :
				$output .= "\t\t<label>\n";
				$output .= "\t\t\t<input type=\"checkbox\" name=\"{$name}\" value=\"{$value}\" ". checked( in_array( $value, $selected ), true, false ) ." autocomplete=\"off\"/>\n";
				$output .= $label;
				$output .= "\t\t</label>\n";
			break;
			case 'radio' :
				$output .= "\t\t<label>\n";
				$output .= "\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" ". checked( ( $value == $selected ), true, false ) ." autocomplete=\"off\"/>\n";
				$output .= $label;
				$output .= "\t\t</label>\n";
			break;
		endswitch;
		
		return $output;
	}
	
	/* = SURCHARGE (recommandée) = */
	/** == == **/
	public function start_lvl( $id, $args = array() )
	{
		return "<ul>";
	}
	
	/** == == **/
	public function end_lvl( $id, $args = array() )
	{
		return "</ul>";
	}
	
	/** == == **/
	public function start_el( $id, $args = array() )
	{
		return "<li>";
	}
	
	/** == == **/
	public function end_el( $id, $args = array() )
	{
		return "</li>";
	}
}