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

class Sidebar extends \tiFy\Environment\Component
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
		// Déclaration des scripts	
		wp_register_style( 'tiFySidebar', self::getUrl() .'/Sidebar.css', array(), '150206' );
		wp_register_script( 'tiFySidebar', self::getUrl() .'/Sidebar.js', array( 'jquery' ), '150206', true );
	}
	
	/** ==  == **/
	final public function wp_loaded()
	{
		do_action( 'tify_sidebar_register' );
		
		
		foreach( (array) self::$Nodes as $id => $args ) :
			$order[$id] = $args['position'];
		endforeach;
		
		@ array_multisort( $order, self::$Nodes );
	}
	
	/** ==  Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		// Bypass
		if( ! self::getConfig( 'enqueue_scripts' ) )
			return;
		
		wp_enqueue_style( 'tiFySidebar' );
		wp_enqueue_script( 'tiFySidebar' );
	}
	
	/** == == **/
	final public function wp_head(){
		?>
		<style type="text/css">
			.tiFySidebar{
				z-index: <?php echo self::getConfig( 'z-index' );?>;
				width: <?php echo self::getConfig( 'width' );?>;
			}
					
			/* = PANNEAU GAUCHE = */
			body.tiFySidebar-body--leftClosed .tiFySidebar--left{ 
			    -webkit-transform: 	translateX(-100%);
				-moz-transform: 	translateX(-100%);
				-ms-transform: 		translateX(-100%);
				-o-transform: 		translateX(-100%);
				transform: 			translateX(-100%);	 
			}		
			body.tiFySidebar-body--leftOpened .tiFySidebar--left{
				-webkit-transform: 	translateX(0);
				-moz-transform: 	translateX(0);
				-ms-transform: 		translateX(0);
				-o-transform: 		translateX(0);
				transform: 			translateX(0);
			}
			body.tiFySidebar-body--leftClosed .tiFySidebar-pushed{
			    -webkit-transform: 	translateX(0);
				-moz-transform: 	translateX(0);
				-ms-transform: 		translateX(0);
				-o-transform: 		translateX(0);
				transform: 			translateX(0);	
			}
        	/* = DROITE = */
			body.tiFySidebar-body--rightClosed .tiFySidebar--right{ 
			    -webkit-transform: 	translateX(100%);
				-moz-transform: 	translateX(100%);
				-ms-transform: 		translateX(100%);
				-o-transform: 		translateX(100%);
				transform: 			translateX(100%);	 
			}				
			body.tiFySidebar-body--rightOpened .tiFySidebar--right{
				-webkit-transform: 	translateX(0);
				-moz-transform: 	translateX(0);
				-ms-transform: 		translateX(0);
				-o-transform: 		translateX(0);
				transform: 			translateX(0);
			}
			body.tiFySidebar-body--rightOpened .tiFySidebar-pushed{
			    -webkit-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>);
				-moz-transform: 	translateX(-<?php echo self::getConfig( 'width' );?>);
				-ms-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>);
				-o-transform: 		translateX(-<?php echo self::getConfig( 'width' );?>);
				transform: 			translateX(-<?php echo self::getConfig( 'width' );?>);	
			}
			
			/* = RESPONSIVE = */
			@media (min-width: <?php echo ( self::getConfig( 'min-width' )+1 );?>px) {
				body.tiFySidebar-body .tiFySidebar{
					display:none;
				}
				/*body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tiFySidebar{
					-webkit-transform: 	translateX(-100%);
					-moz-transform: 	translateX(-100%);
					-ms-transform: 		translateX(-100%);
					-o-transform: 		translateX(-100%);
            		transform: 			translateX(-100%);
            	}
            	body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tiFySidebar-pushed{
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
				body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-right_active .tiFySidebar{
					-webkit-transform: 	translateX(100%);
					-moz-transform: 	translateX(100%);
					-ms-transform: 		translateX(100%);
					-o-transform: 		translateX(100%);
            		transform: 			translateX(100%);
            	}
            	body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-right_active .tiFySidebar-pushed{
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
            	}*/
           	}
           	
           	/* = ANIMATION = */	
           	<?php if( self::getConfig( 'animated' ) ) :	?>
           	body.tiFySidebar-body--animated .tiFySidebar,
			body.tiFySidebar-body--animated .tiFySidebar-pushed{
				-webkit-transition: 	-webkit-transform 	300ms cubic-bezier(0.7,0,0.3,1);
			    -moz-transition: 		-moz-transform 		300ms cubic-bezier(0.7,0,0.3,1);
			    -ms-transition: 		-ms-transform 		300ms cubic-bezier(0.7,0,0.3,1);
			    -o-transition: 			-o-transform 		300ms cubic-bezier(0.7,0,0.3,1);
			    transition: 			transform 			300ms cubic-bezier(0.7,0,0.3,1);	 
			}	
           	<?php endif;?>           	
		</style>
		<?php
	}
	
	/** ==  == **/
	final public function body_class( $classes, $class )
	{
		$classes[] = 'tiFySidebar-body';
		
		switch( self::getConfig( 'initial' ) ) :
			default:
			case 'closed' :
				$classes[] = 'tiFySidebar-body--'. self::getConfig( 'pos' ).'Closed' ;
				break;
			case 'opened' :
 				$classes[] = 'tiFySidebar-body--'. self::getConfig( 'pos' ).'Opened' ;
 				break;
		endswitch;
		
		if( self::getConfig( 'animated' ) ) :			
			$classes[] = 'tiFySidebar-body--animated';
		endif;
		
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
		$output .= "<div class=\"tiFySidebar tiFySidebar--". self::getConfig( 'pos' ) ."\" data-pos=\"". self::getConfig( 'pos' ) ."\">";
				
		// BOUTON DE BASCULE
		if( self::getConfig( 'toggle' ) ) :
			$output .= "\t<a class=\"tiFySidebar-toggleButton tiFySidebar-toggleButton--". self::getConfig( 'pos' ) ."\" tify_sidebar-toggle\" href=\"#tify_sidebar-panel_". self::getConfig( 'pos' ) ."\" data-toggle=\"tiFySidebar\" data-target=\"". self::getConfig( 'pos' ) ."\">";
			if( is_bool( self::getConfig( 'toggle' ) ) ) :
				$output .= "\t\t<div>";
				ob_start(); include self::getDirname() .'/Sidebar.svg';
				$output .= ob_get_clean();
				$output .= "\t\t</div>";
			elseif( is_string( self::getConfig( 'toggle' ) ) ) :
				$output .= self::getConfig( 'toggle' );
			endif;
			$output .= "\t</a>\n";
		endif;
		
		// PANNEAU DES GREFFONS
		$output .= "\t<div class=\"tiFySidebar-panel\">\n";
		$output .= "\t\t<div class=\"tiFySidebar-nodesWrapper\">\n";
		if( self::$Nodes ) :	
			$output .= "\t\t\t<ul class=\"tiFySidebar-nodes\">";
			foreach( (array) self::$Nodes as $id => $attrs ) :
				$output .= "\t\t\t\t<li id=\"tify_sidebar-node-{$id}\" class=\"{$attrs['class']} tiFySidebar-node tiFySidebar-node--{$id}\">";
				ob_start(); call_user_func( $attrs['cb'] );
				$output .= ob_get_clean();				
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