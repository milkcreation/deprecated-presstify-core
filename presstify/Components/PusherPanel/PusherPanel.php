<?php
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

namespace tiFy\Components\PusherPanel;

use tiFy\Environment\Component;

/** @Autoload */
class PusherPanel extends Component
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_loaded',
		'wp_enqueue_scripts',	
		'wp_head',
		'body_class',
		'wp_footer'	
	);	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsArgsMap	= array(
		'body_class' => 2		
	);
	
	private static $nodes;
		
	/* = = */
	final public function init()
	{
		require_once $this->Dirname .'/Helpers.php';
		
		// Définition de la configuration
		if( empty( self::getConfig( 'toggle' ) ) )
			self::setConfig( 'toggle', "<img src=\"data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSI1LjAgLTEwLjAgMTAwIDEzNS4wIj4gIDxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAsNTIpIj4gICAgPHJlY3Qgd2lkdGg9Ijc1IiBoZWlnaHQ9IjEwIiB4PSIxMi41IiB5PSItMjciIHJ5PSIwIi8+ICAgIDxyZWN0IHJ5PSIwIiB5PSItNy4wMDAwMDE0IiB4PSIxMi41IiBoZWlnaHQ9IjEwIiB3aWR0aD0iNzUiLz4gICAgPHJlY3Qgd2lkdGg9Ijc1IiBoZWlnaHQ9IjEwIiB4PSIxMi41IiB5PSIxMyIgcnk9IjAiLz4gIDwvZz48L3N2Zz4=\" />" );

		// Déclaration des scripts	
		wp_register_style( 'tify-pusher_panel', $this->Url. '/PusherPanel.css', array(), '150206' );
		wp_register_script( 'tify-pusher_panel', $this->Url .'/PusherPanel.js', array( 'jquery' ), '150206', true );
	}
	
	/* = = */
	final public function wp_loaded()
	{
		do_action( 'tify_pusher_register_nodes' );
	}
	
	/* = Mise en file des scripts = */
	final public function wp_enqueue_scripts()
	{
		// Bypass
		if( ! self::getConfig( 'enqueue' ) )
			return;
		
		wp_enqueue_style( 'tify-pusher_panel' );
		wp_enqueue_script( 'tify-pusher_panel' );
	}
	
	/* =  = */
	final public function wp_head(){
		?>
		<style type="text/css">
			.tify-pusher_panel{
				width: <?php echo self::getConfig( 'width' );?>px;
			}
			#tify-pusher_panel-left{ 
			    -webkit-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
				-moz-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
				-ms-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
				-o-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
				transform: 			translateX(-<?php echo self::getConfig( 'width' );?>px);	 
			}
			#tify-pusher_panel-right{ 
			    -webkit-transform: 	translateX(<?php echo self::getConfig( 'width' );?>px);
				-moz-transform: 	translateX(<?php echo self::getConfig( 'width' );?>px);
				-ms-transform: 		translateX(<?php echo self::getConfig( 'width' );?>px);
				-o-transform: 		translateX(<?php echo self::getConfig( 'width' );?>px);
				transform: 			translateX(<?php echo self::getConfig( 'width' );?>px);	 
			}
			body.tify-pusher_left_active .tify-pusher_target{
			    -webkit-transform: 	translateX(<?php echo self::getConfig( 'width' );?>px);
				-moz-transform: 	translateX(<?php echo self::getConfig( 'width' );?>px);
				-ms-transform: 		translateX(<?php echo self::getConfig( 'width' );?>px);
				-o-transform: 		translateX(<?php echo self::getConfig( 'width' );?>px);
				transform: 			translateX(<?php echo self::getConfig( 'width' );?>px);	
			}
			body.tify-pusher_right_active .tify-pusher_target{
			    -webkit-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
				-moz-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
				-ms-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
				-o-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
				transform: 			translateX(-<?php echo self::getConfig( 'width' );?>px);	
			}
			@media (min-width: <?php echo ( self::getConfig( 'max-width' )+1 );?>px) {
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
			@media (max-width: <?php echo self::getConfig( 'max-width' );?>px) {
				.tify-pusher_panel {
					display: inherit;
				}
			}
		</style>
		<?php
	}
	
	/* =  = */
	final public function body_class( $classes, $class )
	{
		$classes[] = 'tify-pusher';
		return $classes;
	}
	
	/* = Affichage du panneau = */
	final public function wp_footer()
	{
		if( self::getConfig( 'display' ) )
			return $this->display();
	}
	
	
	/* = Ajout d'un greffons = */
	public static function add_node( $node = array() )
	{
		self::$nodes[] = self::parse_node( $node );	
	}

	/* = Traitement des arguments d'un greffon = */
	private static function parse_node( $node = array() )
	{
		$defaults = array(
			'id'	=> uniqid(),
			'class' => '',
			'order'	=> 99,
			'cb'	=> '__return_false'	
		);
		return wp_parse_args( $node, $defaults );
	}
		
	/* = = */
	public static function display()
	{
	?>
	<div id="tify-pusher_panel-<?php echo self::getConfig( 'pos' );?>" class="tify-pusher_panel">
		<div class="wrapper">
			<?php if( self::$nodes ) : ?>
			<ul class="nodes">
			<?php foreach( (array) self::$nodes as $node ) :?>
				<li id="tify-pusher_panel-node-<?php echo $node['id'];?>" class="<?php $node['class'];?> tify-pusher_panel-node">
					<?php call_user_func( $node['cb'] );?>
				</li>
			<?php endforeach;?>
			</ul>
			<?php endif;?>
		</div>
		<a href="#" class="toggle-button tify-pusher_toggle" data-dir="<?php echo self::getConfig( 'pos' );?>"><?php echo self::getConfig( 'toogle' );?></a>
	</div>
	<?php
	}
}