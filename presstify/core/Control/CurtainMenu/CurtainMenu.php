<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Repeater;

class CurtainMenu extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'curtain_menu';
    
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
        $min = SCRIPT_DEBUG ? '' : '.min';
        
        wp_register_style( 'tify_control-curtain_menu', self::getAssetsUrl( get_class() ) .'/CurtainMenu'. $min .'.css', array( ), 170704 );
        wp_register_script( 'tify_control-curtain_menu', self::getAssetsUrl( get_class() ) .'/CurtainMenu'. $min .'.js', array( 'jquery' ), 170704, true );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-curtain_menu' );
        wp_enqueue_script( 'tify_control-curtain_menu' );
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du contrôleur
     * @param array $attrs
     * @return string
     */
    public static function display( $attrs = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // Marqueur d'identification unique
            'id'                    => 'tiFyControlRepeater--'. self::$Instance,
            // Id Html du conteneur
            'container_id'          => 'tiFyControlRepeater--'. self::$Instance,            
            // Classe Html du conteneur
            'container_class'       => '',
            // Entrées de menu
            'items'                 => array()
        );
        $attrs = wp_parse_args( $attrs, $defaults );
        extract( $attrs );
        
        // Traitement des attributs
        if( $order ) :
            $order = '__order_'. $name;
        endif;        
        $parsed_attrs = compact( array_keys( $defaults ) );
        
        $output  = "";        
        $output .= "<div id=\"{$id}\" class=\"tiFyControlRepeater". ( $class ? " {$class}" : "" )."\" data-tify_control=\"repeater\">\n";
        
        // Liste d'éléments
        $output .= "\t<ul class=\"tiFyControlRepeater-Items". ( $order ? ' tiFyControlRepeater-Items--sortable' : '' ) ."\">";
        if( ! empty( $value ) ) :
            foreach( (array) $value as $i => $v ) :                    
                $v = ( ! is_array( $v ) ) ? ( $v ? $v : $default ) : wp_parse_args( $v, (array) $default ); 
                ob_start();
                $parsed_attrs['item_cb'] ? call_user_func( $parsed_attrs['item_cb'], $i, $v, $parsed_attrs ) : self::$item( $i, $v, $parsed_attrs ); 
                $item = ob_get_clean();        
                                
                $output .= self::itemWrap( $item, $i, $v, $parsed_attrs );
            endforeach;            
        endif;
        $output .= "\t</ul>\n";
        
        // Interface de contrôle
        $output .= "\t<div class=\"tiFyControlRepeater-Handlers\">\n";        
        $output .= "\t\t<a href=\"#{$id}\" data-attrs=\"". htmlentities( json_encode( $parsed_attrs ) ) ."\" class=\"tiFyControlRepeater-Add". ( $add_button_class ? ' '. $add_button_class : '' ) ."\">\n";
        $output .= $add_button_txt;
        $output .= "\t\t</a>\n";
        $output .= "\t</div>\n";
            
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
        
        return $output;
    }
        
    /**
     * Champs d'édition d'un élément
     */
    public static function item( $index, $value, $attrs = array() )
    {
?>
<input type="text" name="<?php echo $attrs['name'];?>[<?php echo $index;?>]" value="<?php echo $value;?>" class="widefat"/>
<?php
    }
    
    /**
     * Encapsulation Html d'un élément
     */
    final protected static function itemWrap( $item, $index, $value, $attrs )
    {
        $output  = "";
        $output .= "\t\t<li class=\"tiFyControlRepeater-Item\" data-index=\"{$index}\">\n";
        $output .= $item;
        $output .= "\t\t\t<a href=\"#\" class=\"tiFyControlRepeater-ItemRemove tify_button_remove\"></a>";
        if( $attrs['order'] ) :
            $output .= "\t\t\t<input type=\"hidden\" name=\"{$attrs['order']}[]\" value=\"{$index}\"/>\n";
        endif;
        $output .= "\t\t</li>\n";
        
        return $output;
    }
    
    /**
     * Récupération de la reponse via Ajax
     */
    public function ajax()
    {
        check_ajax_referer( 'tiFyControlRepeater' );
        
        $index = $_POST['index'];
        $value = $_POST['value'];
        $attrs = $_POST['attrs'];
        
        ob_start();
        if( ! empty( $_POST['attrs']['item_cb'] ) ) :
            call_user_func( wp_unslash( $_POST['attrs']['item_cb'] ), $index, $value, $attrs );
        else :
            static::item( $index, $value, $attrs );
        endif;
        $item = ob_get_clean();
        
        echo self::itemWrap( $item, $index, $value, $attrs );
        
        wp_die();
    }
}