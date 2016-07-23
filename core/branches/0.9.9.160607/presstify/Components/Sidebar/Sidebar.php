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

namespace tiFy\Components\Sidebar;

use tiFy\Environment\Component;

/** @Autoload */
class Sidebar extends Component
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_loaded',
		'wp_enqueue_scripts',	
		'wp_head',
		'body_class'
	);	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsArgsMap	= array(
		'body_class' => 2		
	);
	
	private static $Nodes;
	private static $Dirname;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Traitement des éléments
		foreach( (array) self::getConfig( 'nodes' ) as $id => $args ) :
			self::Register( $id, $args );
		endforeach;
	}
	
	
	/* = ACTIONS = */
	/** ==  = */
	final public function init()
	{
		static::$Dirname = $this->Dirname;
				
		// Déclaration des scripts	
		wp_register_style( 'tify_sidebar', $this->Url. '/Sidebar.css', array(), '150206' );
		wp_register_script( 'tify_sidebar', $this->Url .'/Sidebar.js', array( 'jquery' ), '150206', true );
	}
	
	/** ==  == **/
	final public function wp_loaded()
	{
		do_action( 'tify_sidebar_register' );
		
		
		foreach( (array) self::$Nodes as $id => $args ) :
			$order[$id] = $args['position'];
		endforeach;
		
		@ array_multisort( self::$Nodes, $order );
	}
	
	/** ==  Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		// Bypass
		if( ! self::getConfig( 'enqueue' ) )
			return;
		
		wp_enqueue_style( 'tify_sidebar' );
		wp_enqueue_script( 'tify_sidebar' );
	}
	
	/** == == **/
	final public function wp_head(){
		?>
		<style type="text/css">
			.tify_sidebar-container,
			.tify_sidebar-toggle,
			.tify_sidebar-panel{
				z-index:99990;
			}			
			.tify_sidebar-panel,
			.tify_sidebar-container
			{
				width: <?php echo self::getConfig( 'width' );?>px;
			}
			body.tify_sidebar-body #tify_sidebar-toggle_left{
				left: <?php echo self::getConfig( 'width' );?>px;
			}
			/* = ANIMATION = */	
			body.tify_sidebar-body.tify_sidebar-animated .tify_sidebar-element,
			body.tify_sidebar-body.tify_sidebar-animated .tify_sidebar-pushed{
				-webkit-transition: 	-webkit-transform 	300ms cubic-bezier(0.7,0,0.3,1);
			    -moz-transition: 		-moz-transform 		300ms cubic-bezier(0.7,0,0.3,1);
			    -ms-transition: 		-ms-transform 		300ms cubic-bezier(0.7,0,0.3,1);
			    -o-transition: 			-o-transform 		300ms cubic-bezier(0.7,0,0.3,1);
			    transition: 			transform 			300ms cubic-bezier(0.7,0,0.3,1);	 
			}	
			/** == GAUCHE == **/
			/*** === === ***/
			body.tify_sidebar-body.tify_sidebar-animated .tify_sidebar-element_left{ 
			    -webkit-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
				-moz-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
				-ms-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
				-o-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
				transform: 			translateX(-<?php echo self::getConfig( 'width' );?>px);	 
			}	
			/*** === === ***/				
			body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tify_sidebar-element_left{
				-webkit-transform: 	translateX(0);
				-moz-transform: 	translateX(0);
				-ms-transform: 		translateX(0);
				-o-transform: 		translateX(0);
				transform: 			translateX(0);
			}
			body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tify_sidebar-pushed{
			    -webkit-transform: 	translateX(<?php echo self::getConfig( 'width' );?>px);
				-moz-transform: 	translateX(<?php echo self::getConfig( 'width' );?>px);
				-ms-transform: 		translateX(<?php echo self::getConfig( 'width' );?>px);
				-o-transform: 		translateX(<?php echo self::getConfig( 'width' );?>px);
				transform: 			translateX(<?php echo self::getConfig( 'width' );?>px);	
			}
			@media (min-width: <?php echo ( self::getConfig( 'min-width' )+1 );?>px) {
				body.tify_sidebar-body.tify_sidebar-animated #tify_sidebar_left{
					display:none;
				}
				body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tify_sidebar-element_left{
					-webkit-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
					-moz-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>px);
					-ms-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
					-o-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>px);
            		transform: 			translateX(-<?php echo self::getConfig( 'width' );?>px);
            	}
            	body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tify_sidebar-pushed{
            		-webkit-transition: none;
			    	-moz-transition: 	none;
			   		-ms-transition: 	none;
			    	-o-transition: 		none;
			    	transition: 		none;
            		-webkit-transform: 	translateX(0);
					-moz-transform: 	translateX(0);
					-ms-transform: 		translateX(0);
					-o-transform: 		translateX(0);
					transform: 			translateX(0);
            	}
           	}
		</style>
		<?php
	}
	
	/** ==  == **/
	final public function body_class( $classes, $class )
	{
		$classes[] = 'tify_sidebar-body';
		$classes[] = 'tify_sidebar-animated';
		
		return $classes;
	}
	
	/* = CONTRÔLEURS = */
	/** == Ajout d'un greffons == **/
	public static function Register( $id = null, $args = array() )
	{
		if( ! $id )
			$id = uniqid();
		
		$defaults = array(
			'class' 	=> '',
			'position'	=> 99,
			'cb'		=> '__return_false'	
		);
		$args = wp_parse_args( $args, $defaults );
			
		self::$Nodes[$id] = $args;	
	}
		
	/** == Affichage du panneau latéral == **/
	public static function Display()
	{
		$output  = "";
		
		// CONTENEUR PRINCiPAL
		$output .= "<div id=\"tify_sidebar_". self::getConfig( 'pos' ) ."\" class=\"tify_sidebar\">";
		
		// BACKGROUND
		$output .= "\t<div id=\"tify_sidebar-container_". self::getConfig( 'pos' ) ."\" class=\"tify_sidebar-element tify_sidebar-element_". self::getConfig( 'pos' ) ." tify_sidebar-container\"></div>\n";
		
		// TOGGLE
		if( self::getConfig( 'toggle' ) ) :
			$output .= "\t<a id=\"tify_sidebar-toggle_". self::getConfig( 'pos' ) ."\" class=\"tify_sidebar-element tify_sidebar-element_". self::getConfig( 'pos' ) ." tify_sidebar-toggle\" href=\"#tify_sidebar-panel_". self::getConfig( 'pos' ) ."\" data-toggle=\"tify_sidebar\" data-dir=\"". self::getConfig( 'pos' ) ."\">";
			if( is_bool( self::getConfig( 'toggle' ) ) ) :
				$output .= "\t\t<div>";
				ob_start();
				include static::$Dirname .'/Sidebar.svg';
				$output .= ob_get_contents();
				ob_end_clean();
				$output .= "\t\t</div>";
			elseif( is_string( self::getConfig( 'toggle' ) ) ) :
				$output .= self::getConfig( 'toggle' );
			endif;
			$output .= "\t</a>\n";
		endif;
		
		// PANNEAU DES GREFFONS
		$output .= "\t<div id=\"tify_sidebar-panel_". self::getConfig( 'pos' ) ."\" class=\"tify_sidebar-element tify_sidebar-element_". self::getConfig( 'pos' ) ." tify_sidebar-panel\">\n";
		$output .= "\t\t<div class=\"wrapper\">\n";
		if( self::$Nodes ) :		
			$output .= "\t\t\t<ul class=\"nodes\">";
			foreach( (array) self::$Nodes as $id => $attrs ) :
				$output .= "\t\t\t\t<li id=\"tify_sidebar-node-{$id}\" class=\"{$attrs['class']} node-{$id} tify_sidebar-node\">";
				ob_start();
				call_user_func( $attrs['cb'] );
				$output .= ob_get_contents();
				ob_end_clean();
				$output .= "\t\t\t\t</li>";
			endforeach;
			$output .= "\t\t\t</ul>";
		endif;
		$output .= "\t\t</div>";		
		$output .= "\t</div>";
		$output .= "</div>";
		
		echo $output;
	}
}