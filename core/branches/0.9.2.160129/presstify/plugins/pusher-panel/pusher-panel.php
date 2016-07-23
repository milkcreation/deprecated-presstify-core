<?php
/*
Plugin Name: Pusher
Plugin URI: http://presstify.com/theme-manager/addons/pusher
Description: Panneau pousseur - Interface latérale responsive
Version: 1.150206
Author: Milkcreation
Author URI: http://milkcreation.fr
*/


/**
 * 
 * @see http://mango.github.io/slideout/ 
 * @see http://webdesignledger.com/web-design-2/best-practices-for-hamburger-menus
 *
 * USAGE : 
 * -------
 * # ETAPE 1 - MISE EN FILE DES SCRIPTS
 * ## SOLUTION 1 (recommandé) :
 * dependance css : 'tify-pusher_panel' +  dependance js : 'tify-pusher_panel'
 * ## SOLUTION 2 :
 * 	tify_enqueue_pusher_panel(); 
 * 
 * # ETAPE 2 - AFFICHAGE :
 * ## AUTOLOAD -> false 
 * <?php tify_pusher_panel_display();?>
 * 
 * 
 * 
 * RESSOURCES POUR EVOLUTION : 
 * http://tympanus.net/Blueprints/SlidePushMenus/
 * http://tympanus.net/Development/OffCanvasMenuEffects/
 * http://tympanus.net/Development/MultiLevelPushMenu/
 * 
 */
class tiFy_theme_manager_pusher_panel{
	var $tiFy,		
		$dir,
		$uri,
		$path,
		$options,
		$nodes;
		
	/**
	 * Initialisation
	 */
	function __construct(){
		global $tiFy, $tiFy_editbox;
		
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;

		// Action et Filtres Wordpress
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		add_filter( 'body_class', array( $this, 'body_class' ), 10, 2 );
	}
	
	/**
	 * 
	 */
	function default_options(){
		return array( 
				'pos' 		=> 'left',
				'width'		=> 300,
				'max-width' => 991,
				'autoload'	=> true
			);
	}
	
	/**
	 * Intialisation de Wordpress
	 */
	function init(){
		// Déclaration des scripts	
		wp_register_style( 'tify-pusher_panel', $this->uri.'/pusher-panel.css', array( ), '150206' );
		wp_register_script( 'tify-pusher_panel', $this->uri.'/pusher-panel.js', array( 'jquery' ), '150206', true );
		// Traitement des options
		$this->options = wp_parse_args( 
			apply_filters( 'tify_pusher_panel_opions', array() ), 
			$this->default_options() 
		);
	}
	
	/**
	 * 
	 */
	function wp_head(){
		?>
		<style type="text/css">
			.tify-pusher_panel{
				width: <?php echo $this->options['width'];?>px;
			}
			#tify-pusher_panel-left{ 
			    -webkit-transform: 	translateX(-<?php echo $this->options['width'];?>px);
				-moz-transform: 	translateX(-<?php echo $this->options['width'];?>px);
				-ms-transform: 		translateX(-<?php echo $this->options['width'];?>px);
				-o-transform: 		translateX(-<?php echo $this->options['width'];?>px);
				transform: 			translateX(-<?php echo $this->options['width'];?>px);	 
			}
			#tify-pusher_panel-right{ 
			    -webkit-transform: 	translateX(<?php echo $this->options['width'];?>px);
				-moz-transform: 	translateX(<?php echo $this->options['width'];?>px);
				-ms-transform: 		translateX(<?php echo $this->options['width'];?>px);
				-o-transform: 		translateX(<?php echo $this->options['width'];?>px);
				transform: 			translateX(<?php echo $this->options['width'];?>px);	 
			}
			body.tify-pusher_left_active .tify-pusher_target{
			    -webkit-transform: 	translateX(<?php echo $this->options['width'];?>px);
				-moz-transform: 	translateX(<?php echo $this->options['width'];?>px);
				-ms-transform: 		translateX(<?php echo $this->options['width'];?>px);
				-o-transform: 		translateX(<?php echo $this->options['width'];?>px);
				transform: 			translateX(<?php echo $this->options['width'];?>px);	
			}
			body.tify-pusher_right_active .tify-pusher_target{
			    -webkit-transform: 	translateX(-<?php echo $this->options['width'];?>px);
				-moz-transform: 	translateX(-<?php echo $this->options['width'];?>px);
				-ms-transform: 		translateX(-<?php echo $this->options['width'];?>px);
				-o-transform: 		translateX(-<?php echo $this->options['width'];?>px);
				transform: 			translateX(-<?php echo $this->options['width'];?>px);	
			}
			@media (min-width: <?php echo ( $this->options['max-width']+1 );?>px) {
				.tify-pusher_target{
					-webkit-transition: none !important;
				    -moz-transition: 	none !important;
				    -ms-transition: 	none !important;
				    -o-transition: 		none !important;
				    transition: 		none !important;
					-webkit-transform: 	none !important;
       				-moz-transform: 	none !important;
        			-ms-transform: 		none !important;
         			-o-transform: 		none !important;
            		transform: 			none !important;
            	}
           	}
			@media (max-width: <?php echo $this->options['max-width'];?>px) {
				.tify-pusher_panel {
					display: inherit;
				}
			}
		</style>
		<?php
	}
	/**
	 * 
	 */
	function wp_loaded(){
		do_action( 'tify_pusher_register_nodes' );
	}
	
	
	
	/**
	 * Ajout d'un greffons
	 */
	function add_node( $node = array() ){
		$this->nodes[] = $this->parse_node( $node );	
	}

	/**
	 * Traitement des arguments de greffon
	 */
	function parse_node( $node = array() ){
		$defaults = array(
			'id'	=> uniqid(),
			'class' => '',
			'order'	=> 99,
			'cb'	=> '__return_false'	
		);
		return wp_parse_args( $node, $defaults );
	}
	
	/**
	 * Affichage du panneau
	 */
	function wp_footer( ){
		if( $this->options['autoload'] )
			return $this->display( );
	}
	
	/**
	 * Affichage du panneau passeur
	 */
	function display( ){
	?>
		<div id="tify-pusher_panel-<?php echo $this->options['pos'];?>" class="tify-pusher_panel">
			<div class="wrapper">
				<?php if( $this->nodes ) :?>
				<ul class="nodes">
				<?php foreach( (array) $this->nodes as $node ) :?>
					<li id="tify-pusher_panel-node-<?php echo $node['id'];?>" class="<?php $node['class'];?> tify-pusher_panel-node">
						<?php call_user_func( $node['cb'] );?>
					</li>
				<?php endforeach;?>
				</ul>
				<?php endif;?>
			</div>
			<a href="#" class="toggle-button tify-pusher_toggle" data-dir="<?php echo $this->options['pos'];?>"><?php include $this->dir .'/pusher-panel.svg';?></a>
		</div>
	<?php
	}
	
	/**
	 * 
	 */
	 function body_class( $classes, $class ){
	 	$classes[] = 'tify-pusher';
		return $classes;
	 }
}
global $tify_pusher_panel;
$tify_pusher_panel = new tiFy_theme_manager_pusher_panel;

/**
 * Mise en file des scripts
 */
function tify_enqueue_pusher_panel(){
	wp_enqueue_style( 'tify-pusher_panel' );
	wp_enqueue_script( 'tify-pusher_panel' );
}

/**
 * 
 */
function tify_pusher_panel_display(){
	global $tify_pusher_panel;
	
	$tify_pusher_panel->display();
} 

/**
 * Affichage du panneau pousseur
 */
function tify_pusher_panel_add_node( $node = array() ){
	global $tify_pusher_panel;

	return $tify_pusher_panel->add_node( $node );
}