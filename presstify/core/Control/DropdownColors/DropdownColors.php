<?php
namespace tiFy\Core\Control\DropdownColors;

class DropdownColors extends \tiFy\Core\Control\Factory
{
    /* = ARGUMENTS = */    
    // Identifiant de la classe        
    protected $ID = 'dropdown_colors';
    
    // Instance Courante
	protected static $Instance = 0;
    
    /* = DECLENCHEURS = */
	/** == Initialisation de Wordpress == **/
    final public function init()
    {
        wp_register_style( 'tify_control-dropdown_colors', self::tFyAppUrl( get_class() ) .'/DropdownColors.css', array( ), '150512' );
        wp_register_script( 'tify_control-dropdown_colors', self::tFyAppUrl( get_class() ) .'/DropdownColors.js', array( 'jquery' ), '150512', true );
    }
    
    /** == Mise en file des scripts == **/
    public static function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-dropdown_colors' );
        wp_enqueue_script( 'tify_control-dropdown_colors' );
    }
        
    /* = CONTROLEURS = */
	/** == Affichage == **/
    public static function display( $args = array(), $echo = true )
    {
		self::$Instance++;
        
		// Traitement des arguments
        $defaults = array(
            // Conteneur
            'id'                    => 'tify_control_dropdown_colors-'. self::$Instance,
            'class'                 => 'tify_control_dropdown_colors',
            'name'                  => 'tify_control_dropdown_colors-'. self::$Instance,
            'attrs'                 => array(),
            
            // Valeur
            'selected'              => 0,
            'choices'               => array(),
            'show_option_none'      => false,
            'option_none_value'     => '',
            'labels'                => array(),  
            'disabled'              => false,
            
            // Liste de selection
            'picker'                => array(
                'class'                 => '',
                'append'                => 'body',
                'position'              => 'default', // default: vers le bas | top |  clever: positionnement intelligent
                'width'                 => 'auto'
            )            
        );
        
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Traitement des arguments de la liste de selection
		$picker = wp_parse_args(
			$picker,
			array(
				'id'		=> $id .'-picker',
				'append' 	=> 'body',
				'position'	=> 'default', // default: vers le bas | top | clever: positionnement intelligent
				'width'		=> 'auto'
			)
		);
        
		// Traitement de la liste des choix
		if( is_string( $choices ) ) :
			$choices = array_map( 'trim', explode( ',', $choices ) );
		endif;
		
		// Ajout du choix "aucun" en tête de la liste des choix
		if( $show_option_none ) :
			$choices = array_reverse( $choices, true );
			$choices[] = $option_none_value;
			$choices = array_reverse($choices, true);
		endif;
        
		// Traitement de la valeur sélectionnée
		if( $show_option_none && ! $selected  ) :
			$selected = $option_none_value;
		elseif( ! $selected ) :
		  $selected = current( $choices );
		endif;
        
		$selected_label = '';
		
		// Selecteur de traitement
		$output  = "";
		$output .= "\t<select id=\"{$id}-handler\" name=\"{$name}\" data-tify_control=\"dropdown_colors-handler\" data-selector=\"#{$id}\" data-picker=\"#{$picker['id']}\"". ( $disabled ? " disabled=\"disabled\"" : "" ) .">";
		foreach( (array) $choices as $value ) :
			$output .= "<option value=\"{$value}\" ". selected(  $value == $selected, true, false ) .">". wp_strip_all_tags( $value, true ) ."</option>";
		endforeach;
		$output .= "\t</select>\n";	
        
		// Selecteur HTML
        $output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_colors\" data-handler=\"#{$id}-handler\" data-picker=\"". htmlentities( json_encode( $picker ), ENT_QUOTES, 'UTF-8') ."\"";
        foreach( (array) $attrs as $k => $v ) :
			$output .= " {$k}=\"{$v}\"";
        endforeach;
        $output .= ">\n";
        $output .= "\t<span class=\"selected\">\n";
        $output .= self::displayValue( $selected, $selected_label );
        $output .= "\t</span>\n";
        $output .= "</div>\n";
        
        // Liste de selection HTML
		$output  .= "<div id=\"{$picker['id']}\" data-tify_control=\"dropdown_colors-picker\" class=\"tify_control_dropdown_colors-picker". ( $picker['class'] ? ' '. $picker['class'] : '' ) ."\" data-selector=\"#{$id}\" data-handler=\"#{$id}-handler\">\n";
        $output .= "\t<ul>\n";        
        foreach( $choices as $value ) :
            $output .= "\t\t<li". ( $selected == $value ? " class=\"checked\"" : "" ) .">\n";        
            $label = isset( $labels[$value] ) ? $labels[$value] : '';
            $output .= self::displayValue( $value, $label ); 
            $output .= "\t\t</li>\n";
        endforeach;
        $output .= "\t</ul>\n";
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;

        return $output;
    }

    /** == Affichage de la valeur == **/
    protected static function displayValue( $value = null, $label = '' )
    {
        $output = "<span class=\"color-square". ( $value ? "" : " none" ). "\" style=\"". ( $value ? "background-color:{$value}" : "" ). "\"></span>\n";
        if( $label ) :           
            $output .= "<label>{$label}</label>";
        endif;
        
        return $output;    
    }
}