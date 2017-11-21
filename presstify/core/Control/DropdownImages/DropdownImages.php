<?php
namespace tiFy\Core\Control\DropdownImages;

use Emojione\Emojione;
use tiFy\Statics\Media;

class DropdownImages extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */      
    protected $ID             = 'dropdown_images';
    
    /**
     * Instance
     */
    static $Instance = 0;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale de Wordpress
     */
    final public function init()
    {        
        wp_register_style( 'tify_control-dropdown_images', self::tFyAppUrl( get_class() ) .'/DropdownImages.css', array( ), '150122' );
        wp_register_script( 'tify_control-dropdown_images', self::tFyAppUrl( get_class() ) .'/DropdownImages.js', array( 'jquery' ), '150122', true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public static function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-dropdown_images' );
        wp_enqueue_script( 'tify_control-dropdown_images' );    
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            'id'                    => 'tify_control_dropdown_images-'. self::$Instance,
            'class'                 => 'tify_control_dropdown_images',
            'name'                  => 'tify_control_dropdown_images-'. self::$Instance,
            
            // Liste de selection
            'picker'                => array(
                'class'                 => '',
                'append'                => 'body',
                // default: vers le bas | top |  clever: positionnement intelligent
                'position'              => 'default', 
                'width'                 => 'auto'
            ),          
            'choices'               => array(),
            'selected'              => 0,
            'show_option_none'      => self::tFyAppDirname( get_class() ) .'/none.jpg',
            'option_none_value'     => -1,
            // Nombre de colonnes d'icônes à afficher par ligne  
            'cols'                  => 6
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Traitement des arguments de la liste de selection
        $picker = wp_parse_args(
            $picker,
            array(
                'id'            => $id .'-picker',
                'class'         => '',
                'append'        => 'body',
                'position'      => 'default',
                'width'         => 'auto'
            )
        );    

        if( ! $choices ) :
            $client = Emojione::getClient();
            $n = 0;
            foreach( (array) $client->getRuleset()->getShortcodeReplace() as $shotcode => $filename ) :                
                $src = 'https:'. $client->imagePathSVG . $filename .'.svg'. $client->cacheBustParam;
                $choices[esc_url($src)] = $src;
                if( ++$n > 10 ) break;
            endforeach;
        endif;
        
        // Ajout du choix aucun au début de la liste des choix
        if( $show_option_none ) :
            $choices = array_reverse($choices, true);
            $choices[$option_none_value] = $show_option_none;
            $choices = array_reverse( $choices, true);
        endif;

        if( $show_option_none && ! $selected  )
            $selected = $option_none_value;
        
        $seletedSrc = ( ! $selected ) ? current( $choices ) : ( isset( $choices[$selected] ) ? $choices[$selected] : $option_none_value );
        
        $output  = "";
        
        // Selecteur HTML
        $output .= "<div id=\"{$id}\" class=\"{$class}\" data-tify_control=\"dropdown_images\" data-picker=\"{$picker['id']}\">\n";
        $output .= "\t<span class=\"selected\">\n";        
        $output .= "\t\t<b class=\"selection\">";
        $output .= "\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$selected}\" autocomplete=\"off\" checked=\"checked\">\n";
        $output .= "\t\t\t<img class=\"selection\" src=\"". Media::base64Src( $seletedSrc ) ."\" style=\"width:100%;height:auto;\" />";
        $output .= "\t\t</b>\n";
        $output .= "\t\t<i class=\"caret\"></i>\n";
        $output .= "\t</span>\n";
        $output .= "</div>\n";
        
        // Picker HTML
        $output  .= "<div id=\"{$picker['id']}\" class=\"dropdown_images-picker". ( $picker['class'] ? ' '. $picker['class'] : '' ) ."\" data-selector=\"#{$id}\">\n";
        $output .= "\t<ul>\n";
        $col = 0;    
        foreach( $choices as $value => $path ) :
            /// Ouverture de ligne
            if( ! $col )
                $output .= "\t\t<li>\n\t\t\t<ul>\n";
            $output .= "\t\t\t\t<li";
            if( $selected == $value )
                $output .= " class=\"checked\"";
    
            $output .= ">\n";
            $output .= "\t\t\t\t\t<label>\n";
            $output .= "\t\t\t\t\t\t<b class=\"selection\">";
            $output .= "\t\t\t\t\t\t\t<input type=\"radio\" name=\"{$name}\" value=\"{$value}\" autocomplete=\"off\" ". checked( ( $selected == $value ), true, false ) .">\n";
            $output .= "\t\t\t\t\t\t\t<img src=\"". Media::base64Src( $path ) ."\" style=\"width:100%;height:auto;\" />";
            $output .= "\t\t\t\t\t\t</b>";
            $output .= "\t\t\t\t\t</label>\n";
            $output .= "\t\t\t\t</li>\n";
        
            /// Fermeture de ligne
            if( ++$col >= $cols ) :
                $output .= "\t\t\t</ul>\n\t\t</li>\n";
                $col = 0;
            endif;
        endforeach;
        /// Fermeture de ligne si requise
        if( $col )
            $output .= "\t\t\t</ul>\n\t\t</li>\n";
        $output .= "\t</ul>\n";
        $output .= "</div>\n";
            
        if( $echo )
            echo $output;

        return $output;
    }
}