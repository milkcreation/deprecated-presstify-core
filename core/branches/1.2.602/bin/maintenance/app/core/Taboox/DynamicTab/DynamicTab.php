<?php
namespace tiFy\Core\Taboox\Admin\Post\DynamicTab;

use tiFy\Core\Taboox\Admin;

class DynamicTab extends Admin
{
	/* = ARGUMENTS = */
	public 	$name = 'dynamic_tab';
	
	/* = CONSTRUCTEUR = */
	function __construct( ){	
		parent::__construct();
		
		// Actions et Filtres Wordpress
		add_action( 'wp_ajax_wp_editor_box_editor_html', 'wp_editor_box::editor_html' );
		add_action( 'wp_ajax_nopriv_wp_editor_box_editor_html', 'wp_editor_box::editor_html' );		
		add_filter( 'tiny_mce_before_init', 'wp_editor_box::tiny_mce_before_init', 10, 2 );
		add_filter( 'quicktags_settings', 'wp_editor_box::quicktags_settings', 10, 2 );
	}

	/** == Mise en file des scripts de l'interface d'administration == **/
	public function admin_enqueue_scripts(){
		wp_enqueue_script( 'tify_taboox_dynamic_tab', self::tFyAppUrl() . '/admin.js', array( 'jquery' ), '150325', true );
	}
	
	/* = Formulaire de saisie = */
	function form( $post ){
		$args = wp_parse_args( $this->args, array(
				'name' 			=> 'mkpbx_postbox',
				'tab_current' 	=> array( 'post-text_add' ),
				'container' 	=> 'dynamic_tabs',
				'group'			=> 'dynamic_tabs_group'
			 )
		);
		extract( $args );
		$metadatas = has_meta( $post->ID );
		foreach ( $metadatas as $key => $value )
			if ( $metadatas[ $key ][ 'meta_key' ] != '_dynamic_tabs' )
				unset( $metadatas[ $key ] );
			else
				$metadatas[ $key ]['meta_value'] = maybe_unserialize( $metadatas[ $key ]['meta_value'] );
	?>	
	<ul class="mkpack-postbox-topnav nav nav-tabs">
		<?php foreach( $metadatas as $meta ) :?>
		<li class="<?php if( in_array( 'dynamic_tab-content-'.$meta['meta_id'], $tab_current ) ) echo 'active';?>">
			<a data-toggle="tab" data-current="<?php echo $container;?>,dynamic_tab-content-<?php echo $meta['meta_id'];?>" data-group="<?php echo $group;?>" href="#dynamic_tab-content-<?php echo $meta['meta_id'];?>">
				<input type="text" name="mkpbx_postbox[multi][dynamic_tabs][<?php echo $meta['meta_id'];?>][tab-title]" value="<?php echo esc_attr( @$meta['meta_value']['tab-title'] );?>" />
			</a>
		</li>
		<?php endforeach; reset( $metadatas );?>
		<li class="<?php if( in_array('post-text_add', $tab_current ) ) echo 'active';?>">
			<a id="add-tab" data-toggle="tab" data-name="mkpbx_postbox[multi][dynamic_tabs]" data-sample="#tab-content-sample" data-container="<?php echo $container;?>" data-current="<?php echo $container;?>,post-text_add" data-group="<?php echo $group;?>" href="#">+</a>
		</li>			
	</ul>
	<div class="mkpack-postbox-inside tab-content">
	<?php foreach( $metadatas as $meta ) :?>
		<div id="dynamic_tab-content-<?php echo $meta['meta_id'];?>" class="tab-pane"></div>
	<?php endforeach;?>
	</div>
	
	<?php
	 	$value = $default;
		$output = "";
		if( is_array( $value ) ) :	
			$output .= preg_replace_callback( '/%%value%%\[([a-zA-Z0-9_\-]*)\]/', function( $matches ) use ( $value ) { return ( isset( $value[ $matches[1] ] ) ) ? $value[ $matches[1] ] : '';  }, $sample_html );
		else :
			$output .= $sample_html;
		endif;
	?>
	<div id="tab-content-sample" style="display:none;">		
		<?php echo $output;?>
	</div>					
	<?php
	}
}

/**
 * Chargement de l'éditeur de Wordpress via AJAX !!!
 */
class wp_editor_box {
	private static $mce_settings = null;
    private static $qt_settings = null;
	
	/** == == **/
	public static function editor_html() {
        $content = stripslashes( $_POST['content'] );
        wp_editor($content, $_POST['id'], array( 'textarea_name' => $_POST['textarea_name'], 'wpautop' => false, 'media_buttons' => true, 'textarea_rows' =>30, 'teeny' => false ) );
        $mce_init = self::get_mce_init($_POST['id']);
        $qt_init = self::get_qt_init($_POST['id']); ?>
        <script type="text/javascript">
            tinyMCEPreInit.mceInit = jQuery.extend( tinyMCEPreInit.mceInit, <?php echo $mce_init ?>);
            tinyMCEPreInit.qtInit = jQuery.extend( tinyMCEPreInit.qtInit, <?php echo $qt_init ?>);
        </script>
        <?php
        die();
    }

   /** == == **/
    public static function quicktags_settings( $qtInit, $editor_id ) {
        self::$qt_settings = $qtInit;
                    return $qtInit;
    }
	
	/** == == **/
    public static function tiny_mce_before_init( $mceInit, $editor_id ) {
        self::$mce_settings = $mceInit;
                    return $mceInit;
    }

    /** == == **/
    private static function get_qt_init($editor_id) {
        if ( !empty(self::$qt_settings) ) {
        	self::$qt_settings = apply_filters('quicktags_settings', self::$qt_settings, $editor_id);
            $options = self::_parse_init( self::$qt_settings );
            $qtInit = "'$editor_id':{$options},";
            $qtInit = '{' . trim($qtInit, ',') . '}';
        } else {
            $qtInit = '{}';
        }
        return $qtInit;
    }
	
	/** == == **/
    private static function get_mce_init($editor_id) {
        if ( !empty(self::$mce_settings) ) {
        	self::$mce_settings = apply_filters('tiny_mce_before_init', self::$mce_settings, $editor_id);
            $options = self::_parse_init( self::$mce_settings );
            $mceInit = "'$editor_id':{$options},";
            $mceInit = '{' . trim($mceInit, ',') . '}';
        } else {
            $mceInit = '{}';
        }
        return $mceInit;
    }
	
	/** == == **/
    private static function _parse_init($init) {
        $options = '';

        foreach ( $init as $k => $v ) {
            if ( is_bool($v) ) {
                $val = $v ? 'true' : 'false';
                $options .= $k . ':' . $val . ',';
                continue;
            } elseif ( !empty($v) && is_string($v) && ( ('{' == $v{0} && '}' == $v{strlen($v) - 1}) || ('[' == $v{0} && ']' == $v{strlen($v) - 1}) || preg_match('/^\(?function ?\(/', $v) ) ) {
                $options .= $k . ':' . $v . ',';
                continue;
            }
            $options .= $k . ':"' . $v . '",';
        }

        return '{' . trim( $options, ' ,' ) . '}';
    }
}