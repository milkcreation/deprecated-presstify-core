<?php
/**
 * 
 * @see http://mango.github.io/slideout/ 
 * @see http://webdesignledger.com/web-design-2/best-practices-for-hamburger-menus
 *
 * USAGE : 
 * -------
 * # ETAPE 1 - MISE EN FILE DES SCRIPTS
 * dependance css : 'tiFySidebar' +  dependance js et css ('tiFySidebar')
 * 
 * # ETAPE 2 - AFFICHAGE :
 * ## AUTOLOAD -> false 
 * <?php tify_sidebar_display();?>
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
    /**
     * Liste des actions à déclencher
     */ 
    protected $CallActions                = array(
        'init',
        'wp_loaded',
        'wp_enqueue_scripts',    
        'wp_head',
        'body_class'
    );
    
    /**
     * Ordres de priorité d'exécution des actions
     */ 
    protected $CallActionsArgsMap    = array(
        'body_class' => 2        
    );
    
    /**
     * 
     */
    protected static $Factory;
    
    /**
     * Liste des greffons déclarés
     */
    private static $Nodes;
    
    /**
     * DECLENCHEURS
     */
    /**
     * Inititalisation globale
     */
    final public function init()
    {                
        // Déclaration des scripts    
        $min = SCRIPT_DEBUG ? '' : '.min';
        
        wp_register_style( 'tiFySidebar', self::getAssetsUrl( get_class() )  .'/Sidebar'. $min .'.css', array(), '150206' );
        wp_register_script( 'tiFySidebar', self::getAssetsUrl( get_class() )  .'/Sidebar'. $min .'.js', array( 'jquery' ), '150206', true );
        
        wp_register_style( 'tiFySidebar--theme-dark', self::getAssetsUrl( get_class() ) .'/Sidebar-ThemeDark'. $min .'.css', array( 'tiFySidebar' ), 170704 );
        wp_register_style( 'tiFySidebar--theme-light', self::getAssetsUrl( get_class() ) .'/Sidebar-ThemeLight'. $min .'.css', array( 'tiFySidebar' ), 170704 );        
    }
    
    /**
     * Au chargement complet de Wordpress
     */
    final public function wp_loaded()
    {
        do_action( 'tify_sidebar_register' );        
        
        // Traitement des éléments
        foreach( (array) self::getConfig( 'nodes' ) as $id => $args ) :
            self::register( $id, $args );
        endforeach;
        
        foreach( (array) self::$Nodes as $id => $args ) :
            $order[$id] = $args['position'];
        endforeach;
        
        @ array_multisort( $order, self::$Nodes );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function wp_enqueue_scripts()
    {
        // Bypass
        if( ! self::getConfig( 'enqueue_scripts' ) )
            return;
        
        wp_enqueue_style( 'tiFySidebar' );
        wp_enqueue_script( 'tiFySidebar' );
        
        if( $theme = self::getTheme( get_class() ) ) :
            wp_enqueue_style( 'tiFySidebar--theme-'. $theme );
        endif;
    }
    
    /**
     * Entête de l'interface utilisateur
     */
    final public function wp_head(){
        ?>
        <style type="text/css">
            .tiFySidebar{
                <?php if( $width = self::getConfig( 'width' ) ) :?>
                width:<?php echo $width ?>;
                <?php endif;?>
                <?php if( $zindex = self::getConfig( 'z-index' ) ) :?>
                z-index:<?php echo $zindex;?>;
                <?php endif;?>    
            }
                    
            /* = SIDEBAR A GAUCHE = */
            body.tiFySidebar-body--leftClosed .tiFySidebar--left{ 
                -webkit-transform:      translateX(-100%);
                -moz-transform:         translateX(-100%);
                -ms-transform:          translateX(-100%);
                -o-transform:           translateX(-100%);
                transform:              translateX(-100%);     
            }        
            body.tiFySidebar-body--leftOpened .tiFySidebar--left{
                -webkit-transform:      translateX(0);
                -moz-transform:         translateX(0);
                -ms-transform:          translateX(0);
                -o-transform:           translateX(0);
                transform:              translateX(0);
            }
            
            /* = SIDEBAR A DROITE = */
            body.tiFySidebar-body--rightClosed .tiFySidebar--right{ 
                -webkit-transform:      translateX(100%);
                -moz-transform:         translateX(100%);
                -ms-transform:          translateX(100%);
                -o-transform:           translateX(100%);
                transform:              translateX(100%);     
            }                
            body.tiFySidebar-body--rightOpened .tiFySidebar--right{
                -webkit-transform:      translateX(0);
                -moz-transform:         translateX(0);
                -ms-transform:          translateX(0);
                -o-transform:           translateX(0);
                transform:              translateX(0);
            }
            
            /* = RESPONSIVE = */
            @media (min-width: <?php echo ( self::getConfig( 'min-width' ) );?>) {
                body.tiFySidebar-body .tiFySidebar{
                    display:none;
                }
                /*body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tiFySidebar{
                    -webkit-transform:     translateX(-100%);
                    -moz-transform:     translateX(-100%);
                    -ms-transform:         translateX(-100%);
                    -o-transform:         translateX(-100%);
                    transform:             translateX(-100%);
                }
                body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-left_active .tiFySidebar-pushed{
                    -webkit-transition: none;
                    -moz-transition:     none;
                       -ms-transition:     none;
                    -o-transition:         none;
                    transition:         none;
                    -webkit-transform:     translateX(0);
                    -moz-transform:     translateX(0);
                    -ms-transform:         translateX(0);
                    -o-transform:         translateX(0);
                    transform:             translateX(0);
                }
                body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-right_active .tiFySidebar{
                    -webkit-transform:     translateX(100%);
                    -moz-transform:     translateX(100%);
                    -ms-transform:         translateX(100%);
                    -o-transform:         translateX(100%);
                    transform:             translateX(100%);
                }
                body.tify_sidebar-body.tify_sidebar-animated.tify_sidebar-right_active .tiFySidebar-pushed{
                    -webkit-transition: none;
                    -moz-transition:     none;
                       -ms-transition:     none;
                    -o-transition:         none;
                    transition:         none;
                    -webkit-transform:     translateX(0);
                    -moz-transform:     translateX(0);
                    -ms-transform:         translateX(0);
                    -o-transform:         translateX(0);
                    transform:             translateX(0);
                }*/
            }
               
            /* = ANIMATION = */    
            <?php if( self::getConfig( 'animated' ) ) :    ?>
            body.tiFySidebar-body--animated .tiFySidebar,
            body.tiFySidebar-body--animated .tiFySidebar-pushed{
                -webkit-transition:     -webkit-transform   300ms cubic-bezier(0.7,0,0.3,1);
                -moz-transition:        -moz-transform      300ms cubic-bezier(0.7,0,0.3,1);
                -ms-transition:         -ms-transform       300ms cubic-bezier(0.7,0,0.3,1);
                -o-transition:          -o-transform        300ms cubic-bezier(0.7,0,0.3,1);
                transition:             transform           300ms cubic-bezier(0.7,0,0.3,1);     
            }    
            <?php endif;?>               
        </style>
        <?php
    }
    
    /**
     * Classe du body de l'interface utilisateur
     */
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
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'un greffon
     */
    public static function register( $id = null, $args = array() )
    {
        if( ! $id )
            $id = uniqid();
        
        $defaults = array(
            'class'         => '',
            'position'      => 99,
            'content'       => '',
            /**
             * @deprecated : Utiliser l'argument html 
             */
            'cb'            => '__return_false',
        );
        $args = wp_parse_args( $args, $defaults );
            
        self::$Nodes[$id] = $args;    
    }
    
    /**
     * AFFICHAGE
     */
    /**
     * Affichage de la sidebar
     */
    public static function display()
    {
        $Nodes = self::loadOverride( '\tiFy\Components\Sidebar\Nodes' );
        $items = $Nodes->customs( self::$Nodes );
      
        $output  = "";
        $output .= "<div class=\"tiFySidebar tiFySidebar--". self::getConfig( 'pos' ) ."\" data-pos=\"". self::getConfig( 'pos' ) ."\">";
                
        // BOUTON DE BASCULE
        if( self::getConfig( 'toggle' ) ) :
            $buttonAttrs = array(
                'pos'    => self::getConfig( 'pos' ),
                'class'    => 'tiFySidebar-toggleButton tiFySidebar-toggleButton--'. self::getConfig( 'pos' )
            );
            if( is_string( self::getConfig( 'toggle' ) ) ) :
                $buttonAttrs['text'] = self::getConfig( 'toggle' );
            endif;
                        
            $output .= self::displayToggleButton( $buttonAttrs, false );
        endif;
        
        // PANNEAU DES GREFFONS
        $output .= "\t<div class=\"tiFySidebar-panel\">\n";
        $output .= "\t\t<div class=\"tiFySidebar-nodesWrapper\">\n";
        $output .= "\t\t\t<div class=\"tiFySidebar-nodesContainer\">\n";
		$Walker = self::loadOverride( '\tiFy\Components\Sidebar\Walker' );
        $output .= $Walker->walk( $items );
        $output .= "\t\t\t</div>"; 
        $output .= "\t\t</div>";        
        $output .= "\t</div>";
        $output .= "</div>";
        
        echo $output;
    }
    
    /**
     * Affichage du bouton de bascule
     * @return string
     */
    public static function displayToggleButton( $args = array(), $echo = true )
    {
        $defaults = array(
            'pos'       => self::getConfig( 'pos' ),
            'text'      => '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 75 75" xml:space="preserve" fill="#000" ><g><rect width="75" height="10" x="0" y="0" ry="0"/><rect width="75" height="10" x="0" y="22" ry="0"/><rect width="75" height="10" x="0" y="44" ry="0"/></g></svg>',
            'class'     => ''
        );        
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        $output  = "";
        $output .= "<a href=\"#tify_sidebar-panel_{$pos}\"". ( $class ? " class=\"". $class ."\"" : "" ) ." data-toggle=\"tiFySidebar\" data-target=\"{$pos}\">";
        $output .= $text;
        $output .= "</a>\n";
               
        if( $echo )
            echo $output;
        
        return $output;
    }
}