<?php
namespace tiFy\Core\Control\Findposts;

class Findposts extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'findposts';
    
    /**
     * Instance
     */
    protected static $Instance = 0;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        $min = SCRIPT_DEBUG ? '' : '.min';
        
        wp_register_style( 'tify_control-findposts', self::getAssetsUrl( get_class() ) .'/Findposts'. $min .'.css', array(), '170530' );
        wp_register_script( 'tify_control-findposts', self::getAssetsUrl( get_class() ) .'/Findposts'. $min .'.js', array( 'media' ), '170530' );
        
        add_action( 'wp_ajax_tify_control_findposts', array( $this, 'wp_ajax' ) );
        add_action( 'wp_ajax_nopriv_tify_control_findposts', array( $this, 'wp_ajax' ) );
    }

    /**
     * Mise en file des scripts
     */
    public function enqueue_scripts()
    {        
        wp_enqueue_style( 'tify_control-findposts' );
        wp_enqueue_script( 'tify_control-findposts' );
    }
    
    /**
     * Récupération de la reponse via Ajax
     */
    public function wp_ajax() 
    {
        check_ajax_referer( 'find-posts' );
    
        $post_types = get_post_types( array( 'public' => true ), 'objects' );        
        unset( $args['post_type']['attachment'] );
        
        $s = wp_unslash( $_POST['ps'] );
        $args = array(
            'post_type'         => array_keys( $post_types ),
            'post_status'       => 'any',
            'posts_per_page'    => 50,
        );
        $args = wp_parse_args( $_POST['query_args'], $args );

        if ( '' !== $s )
            $args['s'] = $s;
               
        $posts_query = new \WP_Query;
        $posts = $posts_query->query( $args );

        if ( ! $posts ) {
            wp_send_json_error( __( 'No items found.' ) );
        }
    
        $html = '<table class="widefat"><thead><tr><th class="found-radio"><br /></th><th>'.__('Title').'</th><th class="no-break">'.__('Type').'</th><th class="no-break">'.__('Date').'</th><th class="no-break">'.__('Status').'</th></tr></thead><tbody>';
        $alt = '';
        foreach ( $posts as $post ) {
            $title = trim( $post->post_title ) ? $post->post_title : __( '(no title)' );
            $alt = ( 'alternate' == $alt ) ? '' : 'alternate';
    
            switch ( $post->post_status ) {
                case 'publish' :
                case 'private' :
                    $stat = __('Published');
                    break;
                case 'future' :
                    $stat = __('Scheduled');
                    break;
                case 'pending' :
                    $stat = __('Pending Review');
                    break;
                case 'draft' :
                    $stat = __('Draft');
                    break;
            }
    
            if ( '0000-00-00 00:00:00' == $post->post_date ) {
                $time = '';
            } else {
                /* translators: date format in table columns, see https://secure.php.net/date */
                $time = mysql2date(__('Y/m/d'), $post->post_date);
            }
    
            $html .= '<tr class="' . trim( 'found-posts ' . $alt ) . '"><td class="found-radio"><input type="radio" id="found-'.$post->ID.'" name="found_post_id" value="' . esc_attr($post->ID) . '"></td>';
            $html .= '<td><label for="found-'.$post->ID.'">' . esc_html( $title ) . '</label></td><td class="no-break">' . esc_html( $post_types[$post->post_type]->labels->singular_name ) . '</td><td class="no-break">'.esc_html( $time ) . '</td><td class="no-break">' . esc_html( $stat ). ' </td></tr>' . "\n\n";
        }
    
        $html .= '</tbody></table>';
    
        wp_send_json_success( $html );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage du champ
     */
    public static function display( $args = array(), $echo = true )
    {        
        $defaults = array(
            'id'                    => 'tiFyControlFindposts-'. self::$Instance,
            'class'                 => '',
            'name'                  => '',
            'value'                 => '',
            'readonly'              => true,
            'placeholder'           => '',
            'attrs'                 => array(),
            'ajax_action'           => 'tify_control_findposts',
            'query_args'            => array(), 
        );    
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
            
        if( ! self::$Instance++ ) :
            $admin_footer = function() use ( $ajax_action, $query_args ) {
            ?><div id="ajax-response"></div><?php static::modal( $ajax_action, $query_args );
            };
            add_action( 'admin_footer' , $admin_footer );
        endif;
        
        $output = "";
        $output .= "<div class=\"tiFyControlFindposts {$class}\" data-tify_control=\"findposts\">\n";
        $output .= "<input type=\"text\" id=\"{$id}\"";
        if( $name )
            $output .= " name=\"{$name}\"";
        if( $readonly )
            $output .= " readonly=\"readonly\"";
        $output .= " value=\"{$value}\" placeholder=\"{$placeholder}\"";
        foreach( (array) $attrs as $k => $v )
            $output .= " {$k}=\"{$v}\"";
        $output .= " autocomplete=\"off\"/><button onclick=\"findPosts.open( 'target', '#{$id}' ); return false;\"></button>";
        $output .= "</div>";
                
        if( $echo )
            echo $output;

        return $output;
    }
    
    /**
     * Affichage de la fenêtre modale
     * @todo pagination + gestion instance multiple
     */
    public static function modal( $found_action = '', $query_args = array() ) 
    {
        // Définition des types de post         
        if( ! empty( $query_args['post_type'] ) ) :
            $post_types = (array) $query_args['post_type'];
            unset( $query_args['post_type'] );
        else :
            $post_types = get_post_types( array( 'public' => true ), 'objects' ); 
            unset( $post_types['attachment'] );
            $post_types = array_keys( $post_types );
        endif;
        
?>
<div id="find-posts" class="find-box" style="display: none;">
    <div id="find-posts-head" class="find-box-head">
        <?php _e( 'Attach to existing content' ); ?>
        <button type="button" id="find-posts-close">
            <span class="screen-reader-text"><?php _e( 'Close media attachment panel' ); ?></span>
        </button>
    </div>
    <div class="find-box-inside">
        <div class="find-box-search">
            <?php if ( $found_action ) : ?>
                <input type="hidden" name="found_action" value="<?php echo esc_attr( $found_action ); ?>" />
            <?php endif; ?>
            <?php if ( $query_args ) : ?>
                <input type="hidden" name="query_args" value="<?php echo urlencode( json_encode( $query_args ) );?>" />
            <?php endif; ?>
            <input type="hidden" name="affected" id="affected" value="" />
            <?php wp_nonce_field( 'find-posts', '_ajax_nonce', false ); ?>
            <label class="screen-reader-text" for="find-posts-input"><?php _e( 'Search' ); ?></label>
            <input type="text" id="find-posts-input" name="ps" value="" />
            
            &nbsp;&nbsp;<?php _e( 'Type :', 'tify' );?>
            <select id="find-posts-post_type" name="post_type">
	           <option value="any"><?php _e( 'Tous', 'tify' );?></option> 
               <?php foreach( $post_types as $post_type ) : ?>
               <option value="<?php echo $post_type;?>"><?php echo get_post_type_object( $post_type )->label;?></option> 
               <?php endforeach;?>  
            </select>
            
            <span class="spinner"></span>
            <input type="button" id="find-posts-search" value="<?php esc_attr_e( 'Search' ); ?>" class="button" />
            <div class="clear"></div>
        </div>
        <div id="find-posts-response"></div>
    </div>
    <div class="find-box-buttons">
        <?php submit_button( __( 'Select' ), 'primary alignright', 'find-posts-submit', false ); ?>
        <div class="clear"></div>
    </div>
</div>
<?php
    }
}