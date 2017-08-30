<?php
namespace tiFy\Core\ScriptLoader;

use tiFy\tiFy;

class ScriptLoader extends \tiFy\App\Factory
{
    /**
     * Liste des actions à déclencher
     */
    protected $tFyAppActions                = array(
        'after_setup_theme',
        'init',
        'admin_enqueue_scripts',
        'admin_head',
        'wp_enqueue_scripts',
        'wp_head'
    );
    
    /**
     * Ordres de priorité d'exécution des actions
     */
    protected $tFyAppActionsPriority    = array(
        'wp_enqueue_scripts' => 0
    );
    
    /**
     * Liste des librairies CSS référencées
     */
    public static $CssLib            = array();
    
    /**
     * Liste des librairies JS référencées
     */
    public static $JsLib            = array();            
    
    /**
     * Contexte par défaut de chargement de la source
     */
    public static $DefaultSrc        = 'cdn';
    
    /**
     * DECLENCHEURS
     */
    /**
     * Après le chargement du thème
     */
    public function after_setup_theme()
    {
        $this->_register_native();
    }
    
    /**
     * Initialisation globale
     */
    public function init()
    {
        foreach( array_keys( self::$JsLib ) as $handle )
            self::_register_script( $handle );
        
        foreach( array_keys( self::$CssLib ) as $handle )
            self::_register_style( $handle );                   
    }
    
    /**
     * Mise en file des scripts de l'interface administrateurs
     */
    public function admin_enqueue_scripts()
    {
        do_action( 'tify_register_scripts' );
        wp_enqueue_style( 'tify-admin_styles' );
        wp_enqueue_style( 'tiFyAdmin' );
    }
    
    /**
     * Entête de l'interface administrateur
     */
    public function admin_head()
    {
    ?><script type="text/javascript">/* <![CDATA[ */var tify_ajaxurl='<?php echo admin_url( 'admin-ajax.php', 'relative' );?>';/* ]]> */</script><?php
    }
    
    /**
     * Mise en file des scripts de l'interface utilisateurs
     */
    public function wp_enqueue_scripts()
    {
        do_action( 'tify_register_scripts' );
    }
    
    /**
     * Entête de l'interface utilisateur
     */
    public function wp_head()
    {
    ?><script type="text/javascript">/* <![CDATA[ */var tify_ajaxurl='<?php echo admin_url( 'admin-ajax.php', 'relative' );?>';/* ]]> */</script><?php
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Déclaration des librairies natives
     */
    private function _register_native()
    {
        $min = SCRIPT_DEBUG ? '' : '.min';
        
        // PresstiFy
        self::$CssLib = array(
            /// TiFy - Theme
            'tiFyTheme'              => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tiFyTheme'. $min .'.css'
                ),
                'deps'          => array(),
                'version'       => 170130,
                'media'         => 'all' 
            ),
            
            /// TiFy - Admin Styles
            'tify-admin_styles'         => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-admin_styles'. $min .'.css'
                ),
                'deps'          => array(),
                'version'       => '150409',
                'media'         => 'all' 
            ),
            
            /// TiFy - Admin Styles BEM
            'tiFyAdmin'                 => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tiFyAdmin'. $min .'.css'
                ),
                'deps'          => array(),
                'version'       => '170421',
                'media'         => 'all' 
            ),
            
            /// TiFy - Calendar    
            'tify-calendar'             => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-calendar'. $min .'.css'
                ),
                'deps'          => array( 'spinkit-pulse' ),
                'version'       => '150409',
                'media'         => 'all' 
            ),
            
            /// tiFy - Image Lightbox
            'tify-imagelightbox'                              => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-imagelightbox'. $min .'.css'
                ),
                'deps'          => array(),
                'version'       => '170724',
                'media'         => 'all' 
            ),
            
            /// TiFy - Slideshow
            'tify-slideshow'            => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-slideshow'. $min .'.css'
                ),
                'deps'          => array(),
                'version'       => '160602',
                'media'         => 'all'
            ),
            
            /// TiFy - Modal
            'tify-modal_video-theme'    => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-modal_video-theme'. $min .'.css',
                ),
                'deps'          => array(),
                'version'       => '161008',
                'media'         => 'all'
            ),
            
            /// TiFy - Threesixty View
            'tify-threesixty_view'      => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-threesixty_view'. $min .'.css',
                ),
                'deps'          => array( 'threesixty', 'dashicons' ),
                'version'       => '150904',
                'media'         => 'all' 
            ),
            
            /**
             * @todo A RANGER
             */            
            // Genericons
            'genericons'                => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/genericons/genericons.css',
                    'cdn'        => '//cdn.rawgit.com/Automattic/Genericons/master/genericons/genericons.css',
                    'dev'        => tiFy::$AbsUrl . '/bin/assets/vendor/genericons/genericons.css',    // Pour les références plugin
                ),
                'deps'        => array(),
                'version'    => '4.4.0',
                'media'        => 'all' 
            ),    
            
            // Image Lightbox
            'imagelightbox'                            => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/imagelightbox.min.css',
                ),
                'deps'          => array(),
                'version'       => '160902',
                'media'         => 'all'
            ),        
            
            // NanoScroller    
            'nanoscroller'                => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/nanoscroller/nanoscroller.min.css',
                    'cdn'        => '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/css/nanoscroller.min.css'
                ),
                'deps'        => array(),
                'version'    => '0.8.7',
                'media'        => 'all' 
            ),       
            
            // SpinKit
            'spinkit'                    => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/spinkit.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Rotating Plane
            'spinkit-rotating-plane'    => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/1-rotating-plane.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Fading Circle
            'spinkit-fading-circle'        => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/10-fading-circle.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Folding Cube
            'spinkit-folding-cube'        => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/11-folding-cube.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Double Bounce
            'spinkit-double-bounce'        => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/2-double-bounce.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Wave
            'spinkit-wave'                    => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/3-wave.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Wandering Cubes
            'spinkit-wandering-cubes'        => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/4-wandering-cubes.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Pulse
            'spinkit-pulse'                    => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/5-pulse.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Chasing Dots
            'spinkit-chasing-dots'            => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/6-chasing-dots.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Three bounce
            'spinkit-three-bounce'            => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/7-three-bounce.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Circle
            'spinkit-circle'                => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/8-circle.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),
            /// Cube Grid
            'spinkit-cube-grid'                => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/spinkit/9-cube-grid.min.css'
                ),
                'deps'        => array(),
                'version'    => '1.2.2',
                'media'        => 'all' 
            ),    
            
            // ThreeSixty Slider
            'threesixty'                    => array(
                'src'        => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/threesixty/threesixty.min.css'
                ),
                'deps'        => array(),
                'version'    => '2.0.5',
                'media'        => 'all' 
            )
        );
        
        self::$JsLib = array(           
            /// TiFy - Theme
            'tiFyTheme'                 => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tiFyTheme'. $min .'.js'
                ),
                'deps'          => array( 'jquery' ),
                'version'       => 170130,
                'in_footer'     => true  
            ),
                
            /// TiFy - Calendar
            'tify-calendar'                 => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-calendar'. $min .'.js'
                ),
                'deps'          => array( 'jquery' ),
                'version'       => '150409',
                'in_footer'     => true 
            ),
            
            /// TiFy - Find Posts
            'tify-findposts'                => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-findposts'. $min .'.js'
                ),
                'deps'          => array( 'jquery', 'jquery-ui-draggable', 'wp-ajax-response' ),
                'version'       => '2.2.2',
                'in_footer'     => true 
            ),
            
            /// tiFy - Image Lightbox
            'tify-imagelightbox'                              => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-imagelightbox'. $min .'.js'
                ),
                'deps'          => array( 'imageLightbox' ),
                'version'       => '170724',
                'in_footer'     => true 
            ),
                
            /// TiFy - Parallax
            'tify-parallax'                 => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-parallax'. $min .'.js'
                ),
                'deps'          => array( 'jquery' ),
                'version'       => 170120,
                'in_footer'     => true
            ),
                
            /// TiFy - Lightbox
            'tify-onepage-scroll'           => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-onepage-scroll'. $min .'.js',
                ),
                'deps'          => array( 'jquery', 'easing', 'mousewheel' ),
                'version'       => '150325',
                'in_footer'     => true
            ),
            
            /// TiFy - Smooth Anchor
            'tify-smooth-anchor'            => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-smooth-anchor'. $min .'.js'
                ),
                'deps'          => array( 'jquery', 'easing' ),
                'version'       => '150329',
                'in_footer'     => true 
            ),
            
            /// TiFy - Slideshow
            'tify-slideshow'                => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-slideshow'. $min .'.js'
                ),
                'deps'          => array( 'jquery', 'easing', 'jquery-ui-draggable', 'jquery-touch-punch' ),
                'version'       => '160602',
                'in_footer'     => true 
            ),
            
            /// TiFy - Fixed SubmitDiv
            'tify-fixed_submitdiv'          => array(
                'src'           => array(
                    'local'        => tiFy::$AbsUrl . '/bin/assets/lib/tify-fixed_submitdiv'. $min .'.js'
                ),
                'deps'          => array( 'jquery' ),
                'version'       => '151023',
                'in_footer'     => true 
            ),    
            
            /// TiFy - Threesixty View
            'tify-threesixty_view'          => array(
                'src'           => array(
                    'local'         => tiFy::$AbsUrl . '/bin/assets/lib/tify-threesixty_view'. $min .'.js'
                ),
                'deps'          => array( 'jquery', 'threesixty' ),
                'version'       => '150904',
                'in_footer'     => true 
            )            
        );     
                
        // Librairies tierces
        /// Bootstrap
        self::$CssLib['bootstrap']                  = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl .'/vendor/twbs/bootstrap/dist/css/bootstrap'. $min .'.css',
                'cdn'           => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
            ),
            'deps'          => array(),
            'version'       => '3.3.7',
            'media'         => 'all' 
        );
        self::$JsLib['bootstrap']      = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl .'/vendor/twbs/bootstrap/dist/js/bootstrap'. $min .'.js',
                'cdn'           => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '3.3.7',
            'in_footer'     => true 
        );
        
        /// DataTables
        self::$CssLib['datatables']                 = array(
            'src'        => array(
                'cdn'        => '//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css'
            ),
            'deps'        => array(),
            'version'    => '1.10.11',
            'media'        => 'all'
        );
        self::$CssLib['datatables-bootstrap']       = array(
            'src'        => array(
                'cdn'        => '//cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css'
            ),
            'deps'        => array(),
            'version'    => '1.10.11',
            'media'        => 'all'
        );        
        self::$JsLib['datatables']                  = array(
            'src'           => array(
                'cdn'           => '//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '1.10.11',
            'in_footer'     => true
        );
        self::$JsLib['datatables-bootstrap']        = array(
            'src'           => array(
                'cdn'           => '//cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js'
            ),
            'deps'          => array( 'datatables' ),
            'version'       => '1.10.11',
            'in_footer'     => true
        );
        
        /// Dentist
        self::$JsLib['dentist']                     = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/dentist.min.js',
                'cdn'           => '//cdn.rawgit.com/kelvintaywl/dentist.js/master/build/js/dentist.min.js'    
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '2015.10.24',
            'in_footer'     => true 
        );        
 
        /// Easing
        self::$JsLib['easing']                      = array(
            'src'           => array(
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '1.4.1',
            'in_footer'     => true 
        );
        
        // FontAwesome
        self::$CssLib['font-awesome']               = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl .'/vendor/fortawesome/font-awesome/css/font-awesome'. $min .'.css',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css',
                'dev'           => tiFy::$AbsUrl .'/vendor/fortawesome/font-awesome/css/font-awesome.css',
            ),
            'deps'          => array(),
            'version'       => '4.4.0',
            'media'         => 'all' 
        );
            
        // Image Lightbox
        self::$JsLib['imageLightbox']               = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/imageLightbox.min.js',
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '160902',
            'in_footer'     => true
        );
        
        // Holder
        self::$JsLib['holder']                      = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/holder.min.js',
                'cdn'           => '//cdn.rawgit.com/imsky/holder/master/holder.min.js'
            ),
            'deps'          => array(),
            'version'       => '2.9.1',
            'in_footer'     => true
        );
                
        /** 
         * IsMobile
         * Plugin jQuery de détection de terminal mobile
         * @source https://github.com/kaimallea/isMobile 
         **/
        self::$JsLib['isMobile']                    = array(
            'src'           => array(
                'local'        => tiFy::$AbsUrl . '/bin/assets/vendor/isMobile.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '0.4.1',
            'in_footer'     => true
        );    
            
        // Moment
        self::$JsLib['moment']                      = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/moment.min.js',
                'cdn'           => '//cdn.rawgit.com/moment/moment/develop/min/moment.min.js'
            ),
            'deps'          => array(), 
            'version'       => '2.10.2',
            'in_footer'     => true 
        );
                
        // MouseWheel
        self::$JsLib['mousewheel']                  = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/jquery.mousewheel.min.js',
                'cdn'           => '//cdn.rawgit.com/jquery/jquery-mousewheel/master/jquery.mousewheel.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '3.1.13',
            'in_footer'     => true
        );
            
        // Nanoscroller        
        self::$JsLib['nanoscroller']                = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/nanoscroller/jquery.nanoscroller.min.js',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/javascripts/jquery.nanoscroller.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '0.8.7',
            'in_footer'     => true 
        );
                                
        // jQuery Parallax
        self::$JsLib['jquery-parallax']             = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/jquery-parallax-min.js',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/jquery-parallax/1.1.3/jquery-parallax-min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '1.1.3',
            'in_footer'     => true 
        );
            
        // Slick
        self::$CssLib['slick']                      = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl .'/vendor/kenwheeler/slick/slick/slick.css',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css'
            ),
            'deps'          => array(),
            'version'       => '1.6.0',
            'media'         => 'all'
        );
        self::$CssLib['slick-theme']                = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl .'/vendor/kenwheeler/slick/slick/slick-theme.css',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css'
            ),
            'deps'          => array( 'slick' ),
            'version'       => '1.6.0',
            'media'         => 'all'
        );        
        self::$JsLib['slick']              = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl .'/vendor/kenwheeler/slick/slick/slick'. $min .'.js',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '1.6.0',
            'in_footer'     => true
        );    
                
        /**
         * Spectrum
         * @todo   
            if( file_exists( $this->dir . '/bin/assets/js/bgrins-spectrum/i18n/jquery.spectrum-'. $_locale[0] .'.js' ) )
                wp_register_script( 'spectrum-i10n', '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/i18n/jquery.spectrum-'. $_locale[0] .'.js', array( ), '1.7.0', true );
        */
        self::$CssLib['spectrum']                   = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/spectrum/spectrum.min.css',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.css'
            ),
            'deps'          => array(),
            'version'       => '1.7.0',
            'media'         => 'all' 
        );            
        self::$JsLib['spectrum']                    = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/spectrum/spectrum.min.js',
                'cdn'           => '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.js'
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '1.7.0',
            'in_footer'     => true 
        );
            
        // ThreeSixty Slider
        self::$JsLib['threesixty']                  = array(
            'src'           => array(
                'local'         => tiFy::$AbsUrl . '/bin/assets/vendor/threesixty/threesixty.min.js',
            ),
            'deps'          => array( 'jquery' ),
            'version'       => '2.0.5',
            'in_footer'     => true 
        );
    }
    
    /**
     * Traitement des arguments de déclaration de script
     */
    private static function _script_parse_args( $args )
    {
        return $args = wp_parse_args(
            $args,
            array(
                'src'        => '',
                'deps'        => array(),
                'version'    => '',
                'in_footer'    => true
            )
        );
    }
    
    /**
     * Traitement des arguments de déclaration de style
     */
    private static function _style_parse_args( $args )
    {
        return $args = wp_parse_args(
            $args,
            array(
                'src'        => '',
                'deps'        => array(),
                'version'    => '',
                'media'        => 'all'
            )
        );
    }
    
    /**
     * Déclaration d'un fichier Javascript
     */
    private static function _register_script( $handle )
    {
        if( ! isset( self::$JsLib[$handle] ) )
            return;
        return \wp_register_script(
            $handle,
            self::get_src( $handle, 'js' ),
            self::$JsLib[$handle]['deps'],
            self::$JsLib[$handle]['version'],
            self::$JsLib[$handle]['in_footer']
        );
    }
    
    /**
     * Déclaration d'une feuille de Style CSS
     */
    private  static function _register_style( $handle )
    {
        if( ! isset( self::$CssLib[$handle] ) )
            return;
    
        return \wp_register_style(
            $handle,
            self::get_src( $handle, 'css' ),
            self::$CssLib[$handle]['deps'],
            self::$CssLib[$handle]['version'],
            self::$CssLib[$handle]['media']
        );
    }
                
    /**
     * Déclaration / Modification d'un script
     */
    public static function register_script( $handle, $args = array() )
    {
        $args = self::_script_parse_args( $args );
        if( isset( self::$JsLib[$handle] ) )
            self::$JsLib[$handle] = \wp_parse_args( $args, self::$JsLib[$handle] );
        else
            self::$JsLib[$handle] = $args;
    
        return self::_register_script( $handle );
    }
    
    /**
     * Déclaration / Modification d'un style
     */
    public static function register_style( $handle, $args = array() )
    {
        $args = self::_style_parse_args( $args );
        if( isset( self::$CssLib[$handle] ) )
            self::$CssLib[$handle] = wp_parse_args( $args, self::$CssLib[$handle] );
        else
            self::$CssLib[$handle] = $args;
        
        return self::_register_style( $handle );
    }
    
    /**
     * Récupération de la source selon le contexte
     */
    public static function get_src( $handle, $type = 'css', $context = null )
    {
        $src = ( $type === 'css' ) ? self::$CssLib[$handle]['src'] : self::$JsLib[$handle]['src'];
        
        if( ! $context )
            $context = self::$DefaultSrc;            

        if( ! empty( $src[$context] ) ) :
            return $src[$context];
        elseif( is_array( $src ) ) :
            return current( $src );
        elseif( is_string( $src ) ) :
            return  $src;
        endif;
    }
    
    /**
     * Récupération d'un attribut selon le contexte
     */
    public static function get_attr( $handle, $type = 'css', $attr = null )
    {
        return ( $type === 'css' ) ? self::$CssLib[$handle][$attr] : self::$JsLib[$handle][$attr];
    }
}