<?php

namespace tiFy\Core\ScriptLoader;

use tiFy\tiFy;

class ScriptLoader extends \tiFy\App
{
    /**
     * Liste des librairies CSS référencées
     */
    public static $CssLib = [];

    /**
     * Liste des librairies JS référencées
     */
    public static $JsLib = [];

    /**
     * Contexte par défaut de chargement de la source
     */
    public static $DefaultSrc = 'cdn';

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('after_setup_theme');
        $this->appAddAction('init');
        $this->appAddAction('admin_enqueue_scripts');
        $this->appAddAction('admin_head');
        $this->appAddAction('wp_enqueue_scripts', null, 0);
        $this->appAddAction('wp_head');
    }

    /**
     * EVENEMENTS
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
        foreach (array_keys(self::$JsLib) as $handle) {
            self::_register_script($handle);
        }

        foreach (array_keys(self::$CssLib) as $handle) {
            self::_register_style($handle);
        }
    }

    /**
     * Mise en file des scripts de l'interface administrateurs
     */
    public function admin_enqueue_scripts()
    {
        do_action('tify_register_scripts');
        wp_enqueue_style('tify-admin_styles');
        wp_enqueue_style('tiFyAdmin');
    }

    /**
     * Entête de l'interface administrateur
     */
    public function admin_head()
    {
        ?>
        <script type="text/javascript">/* <![CDATA[ */
            var tify_ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative');?>';
            /* ]]> */</script><?php
    }

    /**
     * Mise en file des scripts de l'interface utilisateurs
     */
    public function wp_enqueue_scripts()
    {
        do_action('tify_register_scripts');
    }

    /**
     * Entête de l'interface utilisateur
     */
    public function wp_head()
    {
        ?>
        <script type="text/javascript">/* <![CDATA[ */
            var tify_ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative');?>';
            /* ]]> */</script><?php
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
        self::$CssLib = [
            /// TiFy - Theme
            'tiFyTheme'               => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tiFyTheme' . $min . '.css',
                ],
                'deps'    => [],
                'version' => 170130,
                'media'   => 'all',
            ],

            /// TiFy - Admin Styles
            'tify-admin_styles'       => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-admin_styles' . $min . '.css',
                ],
                'deps'    => [],
                'version' => '150409',
                'media'   => 'all',
            ],

            /// TiFy - Admin Styles BEM
            'tiFyAdmin'               => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tiFyAdmin' . $min . '.css',
                ],
                'deps'    => [],
                'version' => '170421',
                'media'   => 'all',
            ],

            /// TiFy - Calendar    
            'tify-calendar'           => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-calendar' . $min . '.css',
                ],
                'deps'    => ['spinkit-pulse'],
                'version' => '150409',
                'media'   => 'all',
            ],

            /// tiFy - Image Lightbox
            'tify-imagelightbox'      => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-imagelightbox' . $min . '.css',
                ],
                'deps'    => [],
                'version' => '170724',
                'media'   => 'all',
            ],

            /// TiFy - Select
            'tifyselect'             => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tifyselect' . $min . '.css',
                ],
                'deps'    => [],
                'version' => 180103,
                'media'   => 'all',
            ],

            /// TiFy - Slideshow
            'tify-slideshow'          => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-slideshow' . $min . '.css',
                ],
                'deps'    => [],
                'version' => '160602',
                'media'   => 'all',
            ],

            /// TiFy - Modal
            'tify-modal_video-theme'  => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-modal_video-theme' . $min . '.css',
                ],
                'deps'    => [],
                'version' => '161008',
                'media'   => 'all',
            ],

            /// TiFy - Threesixty View
            'tify-threesixty_view'    => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-threesixty_view' . $min . '.css',
                ],
                'deps'    => ['threesixty', 'dashicons'],
                'version' => '150904',
                'media'   => 'all',
            ],

            /**
             * @todo A RANGER
             */
            // Genericons
            'genericons'              => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/vendor/genericons/genericons.css',
                    'cdn'   => '//cdn.rawgit.com/Automattic/Genericons/master/genericons/genericons.css',
                    'dev'   => tiFy::$AbsUrl . '/bin/assets/vendor/genericons/genericons.css',
                    // Pour les références plugin
                ],
                'deps'    => [],
                'version' => '4.4.0',
                'media'   => 'all',
            ],

            // Image Lightbox
            'imagelightbox'           => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/vendor/imagelightbox.min.css',
                ],
                'deps'    => [],
                'version' => '160902',
                'media'   => 'all',
            ],

            // NanoScroller    
            'nanoscroller'            => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/vendor/nanoscroller/nanoscroller.min.css',
                    'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/css/nanoscroller.min.css',
                ],
                'deps'    => [],
                'version' => '0.8.7',
                'media'   => 'all',
            ],

            // Spinkit - All
            'spinkit'                 => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinkit.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Rotating Plane
            'spinkit-rotating-plane'  => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/1-rotating-plane.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Double Bounce
            'spinkit-double-bounce'   => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/2-double-bounce.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Wave
            'spinkit-wave'            => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/3-wave.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Wandering Cubes
            'spinkit-wandering-cubes' => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/4-wandering-cubes.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Pulse
            'spinkit-pulse'           => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/5-pulse.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Pulse
            'spinkit-spinner-pulse'   => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/5-pulse.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Chasing Dots
            'spinkit-chasing-dots'    => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/6-chasing-dots.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Three bounce
            'spinkit-three-bounce'    => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/7-three-bounce.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Circle
            'spinkit-circle'          => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/8-circle.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Cube Grid
            'spinkit-cube-grid'       => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/9-cube-grid.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Fading Circle
            'spinkit-fading-circle'   => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/10-fading-circle.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],
            // Spinkit - Folding Cube
            'spinkit-folding-cube'    => [
                'src'     => [
                    'cdn' => '//cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/11-folding-cube.min.css',
                ],
                'deps'    => [],
                'version' => '1.2.5',
                'media'   => 'all',
            ],

            // ThreeSixty Slider
            'threesixty'              => [
                'src'     => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/vendor/threesixty/threesixty.min.css',
                ],
                'deps'    => [],
                'version' => '2.0.5',
                'media'   => 'all',
            ],
        ];

        self::$JsLib = [
            /// TiFy - Theme
            'tiFyTheme'            => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tiFyTheme' . $min . '.js',
                ],
                'deps'      => ['jquery'],
                'version'   => 170130,
                'in_footer' => true,
            ],

            /// TiFy - Calendar
            'tify-calendar'        => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-calendar' . $min . '.js',
                ],
                'deps'      => ['jquery'],
                'version'   => '150409',
                'in_footer' => true,
            ],

            /// TiFy - Find Posts
            'tify-findposts'       => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-findposts' . $min . '.js',
                ],
                'deps'      => ['jquery', 'jquery-ui-draggable', 'wp-ajax-response'],
                'version'   => '2.2.2',
                'in_footer' => true,
            ],

            /// tiFy - Image Lightbox
            'tify-imagelightbox'   => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-imagelightbox' . $min . '.js',
                ],
                'deps'      => ['imageLightbox'],
                'version'   => '170724',
                'in_footer' => true,
            ],

            /// TiFy - Parallax
            'tify-parallax'        => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-parallax' . $min . '.js',
                ],
                'deps'      => ['jquery'],
                'version'   => 170120,
                'in_footer' => true,
            ],

            /// TiFy - Lightbox
            'tify-onepage-scroll'  => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-onepage-scroll' . $min . '.js',
                ],
                'deps'      => ['jquery', 'easing', 'mousewheel'],
                'version'   => '150325',
                'in_footer' => true,
            ],

            /// TiFy - Select
            'tifyselect'          => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tifyselect' . $min . '.js',
                ],
                'deps'      => ['jquery-ui-widget', 'jquery-ui-sortable'],
                'version'   => 180103,
                'in_footer' => true,
            ],

            /// TiFy - Slideshow
            'tify-slideshow'       => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-slideshow' . $min . '.js',
                ],
                'deps'      => ['jquery', 'easing', 'jquery-ui-draggable', 'jquery-touch-punch'],
                'version'   => '160602',
                'in_footer' => true,
            ],

            /// TiFy - Smooth Anchor
            'tify-smooth-anchor'   => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-smooth-anchor' . $min . '.js',
                ],
                'deps'      => ['jquery', 'easing'],
                'version'   => '150329',
                'in_footer' => true,
            ],

            /// TiFy - Fixed SubmitDiv
            'tify-fixed_submitdiv' => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-fixed_submitdiv' . $min . '.js',
                ],
                'deps'      => ['jquery'],
                'version'   => '151023',
                'in_footer' => true,
            ],

            /// TiFy - Threesixty View
            'tify-threesixty_view' => [
                'src'       => [
                    'local' => tiFy::$AbsUrl . '/bin/assets/lib/tify-threesixty_view' . $min . '.js',
                ],
                'deps'      => ['jquery', 'threesixty'],
                'version'   => '150904',
                'in_footer' => true,
            ],
        ];

        // Librairies tierces
        /// Bootstrap
        self::$CssLib['bootstrap'] = [
            'src'     => [
                'local' => tiFy::$AbsUrl . '/vendor/twbs/bootstrap/dist/css/bootstrap' . $min . '.css',
                'cdn'   => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
            ],
            'deps'    => [],
            'version' => '3.3.7',
            'media'   => 'all',
        ];
        self::$JsLib['bootstrap'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/vendor/twbs/bootstrap/dist/js/bootstrap' . $min . '.js',
                'cdn'   => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '3.3.7',
            'in_footer' => true,
        ];

        /// DataTables
        self::$CssLib['datatables'] = [
            'src'     => [
                'cdn' => '//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css',
            ],
            'deps'    => [],
            'version' => '1.10.11',
            'media'   => 'all',
        ];
        self::$CssLib['datatables-bootstrap'] = [
            'src'     => [
                'cdn' => '//cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css',
            ],
            'deps'    => [],
            'version' => '1.10.11',
            'media'   => 'all',
        ];
        self::$JsLib['datatables'] = [
            'src'       => [
                'cdn' => '//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '1.10.11',
            'in_footer' => true,
        ];
        self::$JsLib['datatables-bootstrap'] = [
            'src'       => [
                'cdn' => '//cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js',
            ],
            'deps'      => ['datatables'],
            'version'   => '1.10.11',
            'in_footer' => true,
        ];

        /// Dentist
        self::$JsLib['dentist'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/dentist.min.js',
                'cdn'   => '//cdn.rawgit.com/kelvintaywl/dentist.js/master/build/js/dentist.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '2015.10.24',
            'in_footer' => true,
        ];

        /// Easing
        self::$JsLib['easing'] = [
            'src'       => [
                'cdn' => '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '1.4.1',
            'in_footer' => true,
        ];

        // FontAwesome
        self::$CssLib['font-awesome'] = [
            'src'     => [
                'local' => tiFy::$AbsUrl . '/vendor/fortawesome/font-awesome/css/font-awesome' . $min . '.css',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css',
                'dev'   => tiFy::$AbsUrl . '/vendor/fortawesome/font-awesome/css/font-awesome.css',
            ],
            'deps'    => [],
            'version' => '4.4.0',
            'media'   => 'all',
        ];

        // Image Lightbox
        self::$JsLib['imageLightbox'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/imageLightbox.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '160902',
            'in_footer' => true,
        ];

        // Holder
        self::$JsLib['holder'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/holder.min.js',
                'cdn'   => '//cdn.rawgit.com/imsky/holder/master/holder.min.js',
            ],
            'deps'      => [],
            'version'   => '2.9.1',
            'in_footer' => true,
        ];

        /**
         * IsMobile
         * Plugin jQuery de détection de terminal mobile
         * @source https://github.com/kaimallea/isMobile
         **/
        self::$JsLib['isMobile'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/isMobile.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '0.4.1',
            'in_footer' => true,
        ];

        // Moment
        self::$JsLib['moment'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/moment.min.js',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js',
            ],
            'deps'      => [],
            'version'   => '2.18.1',
            'in_footer' => true,
        ];

        // MouseWheel
        self::$JsLib['mousewheel'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/jquery.mousewheel.min.js',
                'cdn'   => '//cdn.rawgit.com/jquery/jquery-mousewheel/master/jquery.mousewheel.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '3.1.13',
            'in_footer' => true,
        ];

        // Nanoscroller        
        self::$JsLib['nanoscroller'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/nanoscroller/jquery.nanoscroller.min.js',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/javascripts/jquery.nanoscroller.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '0.8.7',
            'in_footer' => true,
        ];

        // jQuery Parallax
        self::$JsLib['jquery-parallax'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/jquery-parallax-min.js',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/jquery-parallax/1.1.3/jquery-parallax-min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '1.1.3',
            'in_footer' => true,
        ];

        // Slick
        self::$CssLib['slick'] = [
            'src'     => [
                'local' => tiFy::$AbsUrl . '/vendor/kenwheeler/slick/slick/slick.css',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css',
            ],
            'deps'    => [],
            'version' => '1.6.0',
            'media'   => 'all',
        ];
        self::$CssLib['slick-theme'] = [
            'src'     => [
                'local' => tiFy::$AbsUrl . '/vendor/kenwheeler/slick/slick/slick-theme.css',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css',
            ],
            'deps'    => ['slick'],
            'version' => '1.6.0',
            'media'   => 'all',
        ];
        self::$JsLib['slick'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/vendor/kenwheeler/slick/slick/slick' . $min . '.js',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '1.6.0',
            'in_footer' => true,
        ];

        /**
         * Spectrum
         * @todo
        if( file_exists( $this->dir . '/bin/assets/js/bgrins-spectrum/i18n/jquery.spectrum-'. $_locale[0] .'.js' ) )
         * wp_register_script( 'spectrum-i10n', '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/i18n/jquery.spectrum-'. $_locale[0] .'.js', array( ), '1.7.0', true );
         */
        self::$CssLib['spectrum'] = [
            'src'     => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/spectrum/spectrum.min.css',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.css',
            ],
            'deps'    => [],
            'version' => '1.7.0',
            'media'   => 'all',
        ];
        self::$JsLib['spectrum'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/spectrum/spectrum.min.js',
                'cdn'   => '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '1.7.0',
            'in_footer' => true,
        ];

        // ThreeSixty Slider
        self::$JsLib['threesixty'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/threesixty/threesixty.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '2.0.5',
            'in_footer' => true,
        ];

        // Url Parser
        self::$JsLib['url'] = [
            'src'       => [
                'local' => tiFy::$AbsUrl . '/bin/assets/vendor/url.min.js',
            ],
            'deps'      => ['jquery'],
            'version'   => '2.5.2',
            'in_footer' => true,
        ];
    }

    /**
     * Traitement des arguments de déclaration de script
     */
    private static function _script_parse_args($args)
    {
        return $args = wp_parse_args($args, [
            'src'       => '',
            'deps'      => [],
            'version'   => '',
            'in_footer' => true,
        ]);
    }

    /**
     * Traitement des arguments de déclaration de style
     */
    private static function _style_parse_args($args)
    {
        return $args = wp_parse_args($args, [
            'src'     => '',
            'deps'    => [],
            'version' => '',
            'media'   => 'all',
        ]);
    }

    /**
     * Déclaration d'un fichier Javascript
     */
    private static function _register_script($handle)
    {
        if (!isset(self::$JsLib[$handle])) {
            return;
        }

        return \wp_register_script($handle, self::get_src($handle, 'js'), self::$JsLib[$handle]['deps'],
            self::$JsLib[$handle]['version'], self::$JsLib[$handle]['in_footer']);
    }

    /**
     * Déclaration d'une feuille de Style CSS
     */
    private static function _register_style($handle)
    {
        if (!isset(self::$CssLib[$handle])) {
            return;
        }

        return \wp_register_style($handle, self::get_src($handle, 'css'), self::$CssLib[$handle]['deps'],
            self::$CssLib[$handle]['version'], self::$CssLib[$handle]['media']);
    }

    /**
     * Déclaration / Modification d'un script
     */
    public static function register_script($handle, $args = [])
    {
        $args = self::_script_parse_args($args);
        if (isset(self::$JsLib[$handle])) {
            self::$JsLib[$handle] = \wp_parse_args($args, self::$JsLib[$handle]);
        } else {
            self::$JsLib[$handle] = $args;
        }

        return self::_register_script($handle);
    }

    /**
     * Déclaration / Modification d'un style
     */
    public static function register_style($handle, $args = [])
    {
        $args = self::_style_parse_args($args);
        if (isset(self::$CssLib[$handle])) {
            self::$CssLib[$handle] = wp_parse_args($args, self::$CssLib[$handle]);
        } else {
            self::$CssLib[$handle] = $args;
        }

        return self::_register_style($handle);
    }

    /**
     * Récupération de la source selon le contexte
     */
    public static function get_src($handle, $type = 'css', $context = null)
    {
        $src = ($type === 'css') ? self::$CssLib[$handle]['src'] : self::$JsLib[$handle]['src'];

        if (!$context) {
            $context = self::$DefaultSrc;
        }

        if (!empty($src[$context])) :
            return $src[$context];
        elseif (is_array($src)) :
            return current($src);
        elseif (is_string($src)) :
            return $src;
        endif;
    }

    /**
     * Récupération d'un attribut selon le contexte
     */
    public static function get_attr($handle, $type = 'css', $attr = null)
    {
        return ($type === 'css') ? self::$CssLib[$handle][$attr] : self::$JsLib[$handle][$attr];
    }
}