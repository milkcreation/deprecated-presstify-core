<?php
/**
 * @Overrideable
 */
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
        wp_register_style( 'tify_control-repeater', self::tFyAppAssetsUrl('Repeater.css', get_class()), array( ), 170421 );
        wp_register_script( 'tify_control-repeater', self::tFyAppAssetsUrl('Repeater.js', get_class()), array( 'jquery', 'jquery-ui-sortable' ), 170421, true );
        wp_localize_script( 
            'tify_control-repeater', 
            'tiFyControlRepeater', 
            array( 
                'maxAttempt' => __( 'Nombre de valeur maximum atteinte', 'tify' ) 
            ) 
        );
        
        add_action( 'wp_ajax_tify_control_repeater_item', array( $this, 'ajax' ) );
        add_action( 'wp_ajax_nopriv_tify_control_repeater_item', array( $this, 'ajax' ) );
    }
    
    /**
     * Mise en file des scripts
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-repeater');
        wp_enqueue_script('tify_control-repeater');
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage du contrôleur
     *
     * @param array $attrs {
     * }
     * @return string
     */
    public static function display( $attrs = array(), $echo = true )
    {
        self::$Instance++;
        
        $defaults = array(
            // Id Html du conteneur
            'id'                    => 'tiFyControlRepeater--'. self::$Instance,
            // Classe Html du conteneur
            'class'                 => '',
            // Nom de la valeur a enregistrer
            'name'                  => 'tiFyControlRepeater-'. self::$Instance,
            // Valeur string | array indexé de liste des valeurs  
            'value'                 => '',
            // Valeur par défaut string | array à une dimension 
            'default'               => '',
            // Action de récupération via ajax
            'ajax_action'           => 'tify_control_repeater_item',
            // Agent de sécurisation de la requête ajax
            'ajax_nonce'            => wp_create_nonce( 'tiFyControlRepeater' ),
            // Fonction de rappel d'affichage d'un élément
            'item_cb'               => '',
            // Intitulé du bouton d'ajout d'une interface d'édition
            'add_button_txt'        => __( 'Ajouter', 'tify' ),
            // Classe du bouton d'ajout d'une interface d'édition
            'add_button_class'      => 'button-secondary',
            // Nombre maximum de valeur pouvant être ajoutées
            'max'                   => -1,
            // Ordonnacemment des éléments
            'order'                 => true
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
                $parsed_attrs['item_cb'] ? call_user_func( $parsed_attrs['item_cb'], $i, $v, $parsed_attrs ) : self::item( $i, $v, $parsed_attrs ); 
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
    final public static function itemWrap( $item, $index, $value, $attrs )
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
            call_user_func(wp_unslash( $_POST['attrs']['item_cb'] ), $index, $value, $attrs);
        else :
            static::item( $index, $value, $attrs );
        endif;
        $item = ob_get_clean();
        
        echo self::itemWrap( $item, $index, $value, $attrs );
        
        wp_die();
    }
}