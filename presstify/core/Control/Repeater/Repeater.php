<?php
namespace tiFy\Core\Control\Repeater;

class Repeater extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'repeater';
    
    /**
     * Instance
     */
    protected static $Instance;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de Wordpress
     */
    final public function init()
    {
        wp_register_style( 'tify_control-repeater', self::getUrl( get_class() ) .'/Repeater.css', array( ), 170421 );
        wp_register_script( 'tify_control-repeater', self::getUrl( get_class() ) .'/Repeater.js', array( 'jquery', 'jquery-ui-sortable' ), 170421, true );
        wp_localize_script( 
            'tify_control-repeater', 
            'tiFyControlRepeater', 
            array( 
                'maxAttempt' => __( 'Nombre de valeur maximum atteinte', 'tify' ) 
            ) 
        );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-repeater' );
        wp_enqueue_script( 'tify_control-repeater' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du contrôleur
     * @param array $args
     * @return string
     */
    public static function display( $args = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            'id'                    => 'tiFyControlRepeater--'. self::$Instance,
            'class'                 => '',
            // Nom de la valeur a enregistrer
            'name'                  => 'tiFyControlRepeater-'. self::$Instance,
            // Valeur string | array indexé de liste des valeurs  
            'value'                 => '',
            // Valeur par défaut string | array à une dimension 
            'default'               => '',               
            // Interface d'affichage des valeurs enregistrées            
            'value_html'            => '',
            // Interface d'affichage de création de valeur
            'edit_html'             => '',
            // Bouton d'ajout d'une interface de création de nouvelle valeur
            'add_button_txt'        => __( 'Ajouter', 'tify' ),
            // Nombre maximum de valeur pouvant être ajoutées
            'max'                   => -1,
            
            /**
             * @todo trie des valeur selon order (metadonnées single auto)
             */
            // Valeur de l'ordonnacemment
            'order'                 => '',
            // Nom de la valeur d'ordonancement (par défaut '__order_'. $args['name'])
            'order_name'            => '', 
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        // Traitement des attributs
        if( ! $edit_html ) :
            ob_start();
            self::editHtml();
            $edit_html = ob_end_clean();
        endif;
        if( ! $value_html ) :
            $value_html = $edit_html;
        endif;
        if( ! $order_name ) :
            $order_name = '__order_'. $name;
        endif;
                    
        $output  = "";        
        $output .= "<div id=\"{$id}\" class=\"tiFyControlRepeater". ( $class ? " {$class}" : "" )."\" data-tify_control=\"repeater\">\n";
        $output .= "\t<ul class=\"tiFyControlRepeater-Items tiFyControlRepeater-Items--sortable\">";
        
        // Affichage des valeurs
        if( ! empty( $value ) ) :
            /**
             * @todo trie des valeur selon order (metadonnées simple auto)
             */
        
            foreach( (array) $value as $i => $v ) :                    
                $v = ( ! is_array( $v ) ) ? ( $v ? $v : $default ) : wp_parse_args( $v, (array) $default );
                
                // Remplacement des variables de valeur
                if( ! is_array( $v ) ) :
                    $html = preg_replace( 
                        '/%%value%%/', 
                        $v, 
                        $value_html
                    );
                else:
                    $html = preg_replace_callback( 
                        '/%%value%%\[([a-zA-Z0-9_\-]*)\]/', 
                        function( $matches ) use ( $v ) 
                        { 
                            return ( isset( $v[ $matches[1] ] ) ) ? $v[ $matches[1] ] : ''; 
                        }, 
                        $value_html 
                    );                    
                endif;
                
                // Remplacement des variables d'index et de nom
                $patterns = array(); 
                array_push( $patterns, '/%%name%%/', '/%%index%%/' );
                $replacements = array();
                array_push( $replacements, $name, $i );            
                $html = preg_replace( 
                    $patterns, 
                    $replacements, 
                    $html 
                );            
                
                $output .= "\t\t<li class=\"tiFyControlRepeater-Item\" data-index=\"{$i}\">\n";
                $output .= $html; 
                $output .= "\t\t\t<input type=\"hidden\" name=\"{$order_name}[]\" value=\"{$i}\"/>\n";
                $output .= "\t\t\t<a href=\"#{$id}\" class=\"tify_button_remove\"></a>\n";
                $output .= "\t\t</li>\n";
            endforeach;            
        endif;
        $output .= "\t</ul>\n";

        // Éditeur
        if( ! preg_match( '/%%value%%\[([a-zA-Z0-9_\-]*)\]/', $edit_html ) ) :    
            $html = preg_replace( 
                '/%%value%%/', 
                $default, 
                $edit_html
            );
        else :
            $html = preg_replace_callback( 
                '/%%value%%\[([a-zA-Z0-9_\-]*)\]/', 
                function( $matches ) use ( $default ) 
                { 
                    return ( isset( $default[ $matches[1] ] ) ) ? $default[ $matches[1] ] : '';  
                },
                $edit_html 
            );
        endif;
        
        $output .= "\t<div>\n";        
        $output .= "\t\t<div style=\"display:none;\">{$html}</div>\n";
        $output .= "\t\t<a href=\"#tify_control_dynamic_inputs-add_button\" data-name=\"{$name}\" data-order_name=\"{$order_name}\" data-max=\"{$max}\" data-default=\"". ( htmlentities( json_encode( $args['default'] ) ) ) ."\" class=\"tiFyControlRepeater-Add button-secondary\">\n";
        $output .= $add_button_txt;
        $output .= "\t\t</a>\n";
        $output .= "\t</div>\n";
            
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
    
    /**
     * 
     */
    protected function editHtml()
    {
?>
<input type="text" name="%%name%%[%%index%%]" value="%%value%%"/>
<?php        
    }
}