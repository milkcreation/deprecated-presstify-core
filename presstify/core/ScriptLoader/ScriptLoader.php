<?php
namespace tiFy\Core\ScriptLoader;

use tiFy\Environment\App;
use tiFy\tiFy;

class ScriptLoader extends App
{
	/* = ARGUMENTS = */
	/** == ACTIONS == **/
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_theme',
		'init',
		'admin_enqueue_scripts',
		'admin_head',
		'wp_enqueue_scripts',
		'wp_head'
	);
	
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array();
	
	/** == CONFIGURATION == **/
	// Liste des librairies CSS référencées
	public static $CssLib			= array();
	// Liste des librairies JS référencées
	public static $JsLib			= array();			
	// Contexte par défaut de la source
	public static $DefaultSrc		= 'cdn';
	
	/* = DECLENCHEURS = */
	/** == Au chargement du thème == **/
	public function after_setup_theme()
	{
		$this->_register_native();
	}
	
	/** == Au chargement du thème == **/
	public function init()
	{
		foreach( array_keys( self::$JsLib ) as $handle )
			self::_register_script( $handle );
		
		foreach( array_keys( self::$CssLib ) as $handle )
			self::_register_style( $handle ); 	  			
	}
		
	/** == Mise en file des scripts de l'interface administrateurs == **/
	public function admin_enqueue_scripts()
	{
		do_action( 'tify_register_scripts' );
		wp_enqueue_style( 'tify-admin_styles' );
	}
	
	/** == Entête de l'interface administrateur == **/
	public function admin_head()
	{
	?><script type="text/javascript">/* <![CDATA[ */var tify_ajaxurl='<?php echo admin_url( 'admin-ajax.php', 'relative' );?>';/* ]]> */</script><?php
	}
	
	/** == Mise en file des scripts de l'interface utilisateurs == **/
	public function wp_enqueue_scripts()
	{
		do_action( 'tify_register_scripts' );
		wp_enqueue_style( 'tify-front_styles' );
	}
	
	/** == Entête de l'interface utilisateur == **/
	public function wp_head()
	{
	?><script type="text/javascript">/* <![CDATA[ */var tify_ajaxurl='<?php echo admin_url( 'admin-ajax.php', 'relative' );?>';/* ]]> */</script><?php
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration des librairies natives == **/
	private function _register_native()
	{
		self::$JsLib	= array(
			// Bootstrap
			'bootstrap'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/bootstrap/js/bootstrap.min.js',
					'cdn'		=> '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'					
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '3.3.7',
				'in_footer'	=> true 
			),
				
			/// DataTables
			//// Core
			'datatables'				=> array(
				'src'		=> array(
					'cdn'		=> '//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.10.11',
				'in_footer'	=> true
			),
			//// Bootstrap
			'datatables-bootstrap'		=> array(
				'src'		=> array(
					'cdn'		=> '//cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js'
				),
				'deps'		=> array( 'datatables' ),
				'version'	=> '1.10.11',
				'in_footer'	=> true
			),
				
			// Dentist	
			'dentist'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/dentist.min.js',
					'cdn'		=> '//cdn.rawgit.com/kelvintaywl/dentist.js/master/build/js/dentist.min.js'	
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '2015.10.24',
				'in_footer'	=> true 
			),
				
			// Easing
			'easing'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/jquery.easing.min.js',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.3',
				'in_footer'	=> true 
			),
			
			// Image Lightbox
			'imageLightbox'				=> array(
				'src'           => array(
					'local'         => tiFy::$AbsUrl .'/vendor/tiFy/Assets/imageLightbox.min.js',
				),
				'deps'          => array( 'jquery' ),
				'version'       => '160902',
				'in_footer'     => true
			),
				
			// Easing
			'holder'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/holder.min.js',
					'cdn'		=> '//cdn.rawgit.com/imsky/holder/master/holder.min.js'
				),
				'deps'		=> array(),
				'version'	=> '2.9.1',
				'in_footer'	=> true
			),
			
			// Moment
			'moment'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/moment.min.js',
					'cdn'		=> '//cdn.rawgit.com/moment/moment/develop/min/moment.min.js'
				),
				'deps'		=> array(), 
				'version'	=> '2.10.2',
				'in_footer'	=> true 
			),
				
			// MouseWheel
			'mousewheel'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/jquery.mousewheel.min.js',
					'cdn'		=> '//cdn.rawgit.com/jquery/jquery-mousewheel/master/jquery.mousewheel.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '3.1.13',
				'in_footer'	=> true
			),
			
			// Nanoscroller
			'nanoscroller'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/nanoscroller/jquery.nanoscroller.min.js',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/javascripts/jquery.nanoscroller.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '0.8.7',
				'in_footer'	=> true 
			),
				
			// OwlCarousel				
			'owlcarousel'				=> array(
				'src'		=> array(
					'cdn'		=> '//cdn.rawgit.com/smashingboxes/OwlCarousel2/master/dist/owl.carousel.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '2.0.0-beta.3',
				'in_footer'	=> true
			),
				
			// jQuery Parallax
			'jquery-parallax'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/jquery-parallax-min.js',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery-parallax/1.1.3/jquery-parallax-min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.1.3',
				'in_footer'	=> true 
			),
			
			// Slick				
			'slick-carousel'				=> array(
				'src'		=> array(
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.6.0',
				'in_footer'	=> true
			),	
				
			// Spectrum
			/// if( file_exists( $this->dir .'/assets/js/bgrins-spectrum/i18n/jquery.spectrum-'. $_locale[0] .'.js' ) )
			/// wp_register_script( 'spectrum-i10n', '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/i18n/jquery.spectrum-'. $_locale[0] .'.js', array( ), '1.7.0', true );
			'spectrum'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spectrum/spectrum.min.js',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.7.0',
				'in_footer'	=> true 
			),
			
			// ThreeSixty Slider
			'threesixty'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/threesixty/threesixty.min.js',
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '2.0.5',
				'in_footer'	=> true 
			),
			
			// TiFy
			/// TiFy - Calendar
			'tify-calendar'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-calendar.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '150409',
				'in_footer'	=> true 
			),
			/// TiFy - Find Posts
			'tify-findposts'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-findposts.min.js'
				),
				'deps'		=> array( 'jquery', 'jquery-ui-draggable', 'wp-ajax-response' ),
				'version'	=> '2.2.2',
				'in_footer'	=> true 
			),
			/// TiFy - Infinite Scroll
			'tify-infinite-scroll'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-infinite-scroll.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '160126',
				'in_footer'	=> true
			),
			/// TiFy - Lightbox
			'tify-lightbox'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-lightbox.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '150325',
				'in_footer'	=> true 
			),
			/// TiFy - Lightbox
			'tify-onepage-scroll'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-onepage-scroll.min.js',
					'dev'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-onepage-scroll.js'
				),
				'deps'		=> array( 'jquery', 'easing', 'mousewheel' ),
				'version'	=> '150325',
				'in_footer'	=> true
			),
			/// TiFy - Smooth Anchor
			'tify-smooth-anchor'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-smooth-anchor.min.js'
				),
				'deps'		=> array( 'jquery', 'easing' ),
				'version'	=> '150329',
				'in_footer'	=> true 
			),
			/// TiFy - Slideshow
			'tify-slideshow'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-slideshow.min.js'
				),
				'deps'		=> array( 'jquery', 'easing' ),
				'version'	=> '160602',
				'in_footer'	=> true 
			),
			/// TiFy - Fixed SubmitDiv
			'tify-fixed_submitdiv'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-fixed_submitdiv.min.js'
				),
				'deps'		=> array( 'jquery' ),
				'version'	=> '151023',
				'in_footer'	=> true 
			),				
			/// TiFy - Threesixty View
			'tify-threesixty_view'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-threesixty_view.min.js'
				),
				'deps'		=> array( 'jquery', 'threesixty' ),
				'version'	=> '150904',
				'in_footer'	=> true 
			)				
		);
		self::$CssLib	= array(
			// Bootstrap
			'bootstrap'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/bootstrap/css/bootstrap.min.css',
					'cdn'		=> '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
				),
				'deps'		=> array(),
				'version'	=> '3.3.7',
				'media'		=> 'all' 
			),
			
			// DataTables
			/// Theme par défaut
			'datatables'				=> array(
				'src'		=> array(
					'cdn'		=> '//cdn.datatables.net/1.10.11/css/jquery.dataTables.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.10.11',
				'media'		=> 'all'
			),
			/// Theme Bootstrap
			'datatables-bootstrap'		=> array(
				'src'		=> array(
					'cdn'		=> '//cdn.datatables.net/1.10.11/css/dataTables.bootstrap.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.10.11',
				'media'		=> 'all'
			),					
				
			// FontAwesome
			'font-awesome'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/font-awesome/css/font-awesome.min.css',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css',
					'dev'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/font-awesome/css/font-awesome.css',	// Pour les références plugin
				),
				'deps'		=> array(),
				'version'	=> '4.4.0',
				'media'		=> 'all' 
			),
			
			// Genericons
			'genericons'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/genericons/genericons.css',
					'cdn'		=> '//cdn.rawgit.com/Automattic/Genericons/master/genericons/genericons.css',
					'dev'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/genericons/genericons.css',	// Pour les références plugin
				),
				'deps'		=> array(),
				'version'	=> '4.4.0',
				'media'		=> 'all' 
			),	
			
			// Image Lightbox
			'imagelightbox'                            => array(
				'src'           => array(
					'local'         => tiFy::$AbsUrl .'/vendor/tiFy/Assets/imagelightbox.min.css',
				),
				'deps'          => array(),
				'version'       => '160902',
				'media'         => 'all'
			),		
			
			// NanoScroller	
			'nanoscroller'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/nanoscroller/nanoscroller.min.css',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/css/nanoscroller.min.css'
				),
				'deps'		=> array(),
				'version'	=> '0.8.7',
				'media'		=> 'all' 
			),
				
			// Owl Carousel
			'owlcarousel'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/owlcarousel/assets/owl.carousel.min.css',
					'cdn'		=> '//cdn.rawgit.com/smashingboxes/OwlCarousel2/master/dist/assets/owl.carousel.min.css'
				),
				'deps'		=> array(),
				'version'	=> '2.0.0-beta.3',
				'media'		=> 'all'
			),
		
			// Spectrum
			'spectrum'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spectrum/spectrum.min.css',
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.7.0',
				'media'		=> 'all' 
			),
				
			// Slick Carousel
			'slick-carousel'			=> array(
				'src'		=> array(
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.6.0',
				'media'		=> 'all'
			),
			'slick-carousel-theme'		=> array(
				'src'		=> array(
					'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick-theme.min.css'
				),
				'deps'		=> array( 'slick-carousel' ),
				'version'	=> '1.6.0',
				'media'		=> 'all'
			),				
			
			// SpinKit
			'spinkit'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/spinkit.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Rotating Plane
			'spinkit-rotating-plane'	=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/1-rotating-plane.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Fading Circle
			'spinkit-fading-circle'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/10-fading-circle.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Folding Cube
			'spinkit-folding-cube'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/11-folding-cube.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Double Bounce
			'spinkit-double-bounce'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/2-double-bounce.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Wave
			'spinkit-wave'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/3-wave.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Wandering Cubes
			'spinkit-wandering-cubes'		=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/4-wandering-cubes.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Pulse
			'spinkit-pulse'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/5-pulse.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Chasing Dots
			'spinkit-chasing-dots'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/6-chasing-dots.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Three bounce
			'spinkit-three-bounce'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/7-three-bounce.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Circle
			'spinkit-circle'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/8-circle.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Cube Grid
			'spinkit-cube-grid'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/spinkit/9-cube-grid.min.css'
				),
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),	
			
			// ThreeSixty Slider
			'threesixty'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/threesixty/threesixty.min.css'
				),
				'deps'		=> array(),
				'version'	=> '2.0.5',
				'media'		=> 'all' 
			),				
			
			// TiFY
			/// TiFy - Front Styles
			'tify-front_styles'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-front_styles.min.css'
				),
				'deps'		=> array(),
				'version'	=> '150907',
				'media'		=> 'all' 
			),
			/// TiFy - Admin Styles
			'tify-admin_styles'				=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-admin_styles.css'
				),
				'deps'		=> array(),
				'version'	=> '150409',
				'media'		=> 'all' 
			),
			/// TiFy - Calendar	
			'tify-calendar'					=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-calendar.css'
				),
				'deps'		=> array(
					'spinkit-pulse'		
				),
				'version'	=> '150409',
				'media'		=> 'all' 
			),
			/// TiFy - Slideshow
			'tify-slideshow'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-slideshow.min.css'
				),
				'deps'		=> array(),
				'version'	=> '160602',
				'media'		=> 'all'
			),
			/// TiFy - Modal
			'tify-modal_video-theme'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-modal_video-theme.css',
				),
				'deps'		=> array(),
				'version'	=> '161008',
				'media'		=> 'all'
			),
			/// TiFy - Threesixty View
			'tify-threesixty_view'			=> array(
				'src'		=> array(
					'local'		=> tiFy::$AbsUrl .'/vendor/tiFy/Assets/tify/tify-threesixty_view.min.css',
				),
				'deps'		=> array( 'threesixty', 'dashicons' ),
				'version'	=> '150904',
				'media'		=> 'all' 
			)	  	
		);
	}	
				
	/* = METHODES PUBLIQUES = */
	/** == Déclaration / Modification d'un script == **/
	public static function register_script( $handle, $args = array() ){
		$args = self::_script_parse_args( $args );
		if( isset( self::$JsLib[$handle] ) )
			self::$JsLib[$handle] = \wp_parse_args( $args, self::$JsLib[$handle] );
		else
			self::$JsLib[$handle] = $args;
	
		return self::_register_script( $handle );
	}
	
	/** == Déclaration / Modification d'un script == **/
	public static function register_style( $handle, $args = array() ){
		$args = self::_style_parse_args( $args );
		if( isset( self::$CssLib[$handle] ) )
			self::$CssLib[$handle] = wp_parse_args( $args, self::$CssLib[$handle] );
		else
			self::$CssLib[$handle] = $args;
		
		return self::_register_style( $handle );
	}
	
	/** == Récupération de la source selon le contexte == **/
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
	
	/** == Récupération de la source selon le contexte == **/
	public static function get_attr( $handle, $type = 'css', $attr = null )
	{
		return ( $type === 'css' ) ? self::$CssLib[$handle][$attr] : self::$JsLib[$handle][$attr];
	}
	
	/** == Traitement des arguments de déclaration de script == **/
	private static function _script_parse_args( $args )
	{
		return $args = wp_parse_args(
			$args,
			array(
				'src'		=> '',
				'deps'		=> array(),
				'version'	=> '',
				'in_footer'	=> true
			)
		);
	}
	
	/** == Traitement des arguments de déclaration de style == **/
	private static function _style_parse_args( $args )
	{
		return $args = wp_parse_args(
			$args,
			array(
				'src'		=> '',
				'deps'		=> array(),
				'version'	=> '',
				'media'		=> 'all'
			)
		);
	}
	
	/** == Déclaration d'un fichier Javascript == **/
	private	static function _register_script( $handle )
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
	
	/** == Déclaration d'une feuille de Style CSS == **/
	private	static function _register_style( $handle )
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
}
