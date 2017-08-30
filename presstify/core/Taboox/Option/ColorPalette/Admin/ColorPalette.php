<?php
namespace tiFy\Core\Taboox\Option\ColorPalette\Admin;

class ColorPalette extends \tiFy\Core\Taboox\Option\Admin
{
    /**
     * Instance
     */
    private static $Instance;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {    
        $this->args = wp_parse_args( 
            $this->args,
            array(
                'name'      => 'tify_taboox_color_palette',
                'colors'    => array()
            )
        );
        
        add_action( 'wp_ajax_tify_taboox_color_palette', array( $this, 'wp_ajax' ) );
        register_setting( $this->page, $this->args['name'] );
    }
        
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_style( 'tify_taboox-color_palette', self::tFyAppUrl( get_class() ) ."/ColorPalette.css", array( 'tify_control-colorpicker' ), '150325' );
        wp_enqueue_script( 'tify_taboox-color_palette', self::tFyAppUrl( get_class() ) ."/ColorPalette.js", array( 'jquery', 'jquery-ui-sortable', 'tify_control-colorpicker' ), '150325', true );
    }
    
    /**
     * Récupération Ajax
     */
    public function wp_ajax()
    {
        $this->_itemRender( $_POST['index'], $_POST['name'] );
        exit;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    public function form()
    {
        // Attribution des valeurs
        $colors = array(); $sort     = array(); 
        
        if( $values = get_option( $this->args['name'], array() ) ) :
            $colors     = $values['colors'];
            $sort       = $values['order'];
        else :
            $i = 0;
            foreach( (array) $this->args['colors'] as $title => $hex ) :
                $colors[]       = compact( 'title', 'hex' );            
                $sort[]         = $i++;
            endforeach;
        endif;
        
        @array_multisort( $colors, $sort, ASC );
    ?>
        <div id="tify_taboox-color_palette-<?php echo self::$Instance++;?>" class="tify_taboox-color_palette" data-name="<?php echo $this->args['name'];?>">
            <ul>
            <?php foreach( (array) $colors as $index => $color ) $this->_itemRender( $index, $this->args['name'], $color );?>
            </ul>
            <a class="tify_theme_color-add button-secondary" href="#">
                <span class="dashicons dashicons-art" style="vertical-align:middle;"></span>
                <?php _e( 'Ajouter une couleur', 'tify' );?>
            </a>
        </div>
    <?php    
    }
    
    /**
     * Affichage d'un élément
     */
    private function _itemRender( $index, $name, $value = null )
    {
        if( ! isset( $value['hex'] ) ) 
            $value['hex'] = "#FFFFFF";
        if( empty( $value['title'] ) ) 
            $value['title'] = sprintf( __( 'Nouvelle couleur #%d', 'tify' ), $index+1 );
        
        $output  = "";
        $output .= "<li>";
        // Champs de saisie
        $output .= tify_control_colorpicker( 
                array( 
                    'name'         => "{$name}[colors][{$index}][hex]", 
                    'value'     => $value['hex'],
                    'attrs'        => array( 'autocomplete' => 'off' ),
                    'options'    => array(
                        'showInitial'             => false,
                        'showInput'             => true,
                        'showSelectionPalette'     => true,
                        'showButtons'             => true,
                        'allowEmpty'             => false
                    ),
                    'echo'         => false
                ) 
            );
        $output .= "<div class=\"title\"><input type=\"text\" name=\"{$name}[colors][{$index}][title]\" value=\"{$value['title']}\" /></div>";
        $output .= "<input type=\"hidden\" name=\"{$name}[order][]\" value=\"{$index}\"/>";    
        // Contrôleurs
        $output .= "<a href=\"#\" class=\"dashicons dashicons-sort handle\"></a>";
        $output .= "<a href=\"#\" class=\"dashicons dashicons-no-alt delete\"></a>";
        $output .= "</li>";
        
        echo $output;
    }
}