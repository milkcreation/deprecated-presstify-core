<?php
/* = HELPER = */
/** == Déclaration / Modification d'un script JavaScript == **/
function tify_register_script( $handle, $args = array() ){
	global $tiFy;
	
	return $tiFy->script_loader->register_script( $handle, $args );
}

/** == Déclaration / Modification d'un script JavaScript == **/
function tify_register_style( $handle, $args = array() ){
	global $tiFy;
	
	return $tiFy->script_loader->register_style( $handle, $args );
}

/** == Mise en file de FindPosts == **/
function tify_enqueue_findposts(){
	static $instance;
	if( $instance++ )
		return;

	wp_enqueue_script( 'media' );
	wp_enqueue_script( 'tify-findposts' );
	add_action( 'wp_footer' , create_function( '', 'echo "<div id=\"ajax-response\"></div>";'. find_posts_div() ) );
}

/* = CLASSE PRINCIPALE = */
class tiFy_CoreScriptLoader{
	/* = ARGUMENTS = */
	public 	// Configuration
			$css, 			// Liste des librairies CSS référencées
			$js,			// Liste des librairies JS référencées
			
			// Paramètres
			$locale,
			$context = 'cdn';
			
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy $master ){
		// Déclaration de la classe de référence principale
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, 'wp_admin_head' ) );
	}
	
	/* = METHODES PUBLIQUES = */
	/** == Déclaration / Modification d'un script == **/
	public function register_script( $handle, $args = array() ){
		$args = $this->_script_parse_args( $args );
		if( isset( $this->js[$handle] ) )
			$this->js[$handle] = wp_parse_args( $args, $this->js[$handle] );
		else
			$this->js[$handle] = $args;
		
		return $this->_register_script( $handle );
	}
	
	/** == Déclaration / Modification d'un script == **/
	public function register_style( $handle, $args = array() ){
		$args = $this->_style_parse_args( $args );
		if( isset( $this->css[$handle] ) )
			$this->css[$handle] = wp_parse_args( $args, $this->css[$handle] );
		else
			$this->css[$handle] = $args;
		
		return $this->_register_style( $handle );
	}
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Au chargement du thème == **/
	public function wp_init(){
		global $locale, $wp_local_package;
		$_locale = preg_split( '/_/', $locale );
		
		$this->_register_native();
		
		foreach( array_keys( $this->js ) as $handle )
			$this->_register_script( $handle );
		
		foreach( array_keys( $this->css ) as $handle )
			$this->_register_style( $handle ); 	  			
	}

	/** == Mise en file des scripts de l'interface utilisateurs == **/
	public function wp_enqueue_scripts(){
		wp_enqueue_style( 'tify-front_styles' );
	}

	/** == Entête de l'interface utilisateur == **/
	public function wp_head(){
	?><script type="text/javascript">/* <![CDATA[ */var tify_ajaxurl='<?php echo admin_url( 'admin-ajax.php', 'relative' );?>';/* ]]> */</script><?php
	}
		
	/** == Mise en file des scripts de l'interface administrateurs == **/
	public function wp_admin_enqueue_scripts(){
		wp_enqueue_style( 'tify-admin_styles' );
	}
	
	/** == Entête de l'interface administrateur == **/
	public function wp_admin_head(){
	?><script type="text/javascript">/* <![CDATA[ */var tify_ajaxurl='<?php echo admin_url( 'admin-ajax.php', 'relative' );?>';/* ]]> */</script><?php
	}
	
	/* = CONTRÔLEURS = */
	/** == Déclaration des librairies natives == **/
	private function _register_native(){
		$this->js	= array(
			// Bootstrap
			'bootstrap'	=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/js/bootstrap.min.js',
				'cdn'		=> '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '3.3.5',
				'in_footer'	=> true 
			),
			/// Bootstrap - Transitions
			'bootstrap-transitions'		=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/js/bootstrap-transitions.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '3.3.5',
				'in_footer'	=> true 
			),
			/// Bootstrap - Modals
			'bootstrap-modals'		=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/js/bootstrap-modals.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'bootstrap-transitions' ),
				'version'	=> '3.3.5',
				'in_footer'	=> true 
			),
			/// Bootstrap - Tooltips
			'bootstrap-tooltips'		=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/js/bootstrap-tooltips.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'bootstrap-transitions' ),
				'version'	=> '3.3.5',
				'in_footer'	=> true 
			),
			/// Bootstrap - Popovers
			'bootstrap-popovers'		=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/js/bootstrap-popovers.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'bootstrap-tooltips' ),
				'version'	=> '3.3.5',
				'in_footer'	=> true 
			),
			/// Bootstrap - Togglable tabs 
			'bootstrap-togglable-tabs'	=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/js/bootstrap-togglable-tabs.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'bootstrap-transitions' ),
				'version'	=> '3.3.5',
				'in_footer'	=> true 
			),			
					
			// Easing
			'easing'			=> array(
				'local'		=> $this->master->uri. 'assets/jquery.easing.min.js',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.3',
				'in_footer'	=> true 
			),
			
			// Moment
			'moment'			=> array(
				'local'		=> $this->master->uri. 'assets/moment.min.js',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js',
				'deps'		=> array(), 
				'version'	=> '2.10.2',
				'in_footer'	=> true 
			),
			
			// Nanoscroller
			'nanoscroller'		=> array(
				'local'		=> $this->master->uri. 'assets/nanoscroller/jquery.nanoscroller.min.js',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/javascripts/jquery.nanoscroller.min.js', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '0.8.7',
				'in_footer'	=> true 
			),
			
			//// jQuery Parallax
			'jquery-parallax'		=> array(
				'local'		=> $this->master->uri. 'assets/jquery-parallax-min.js',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery-parallax/1.1.3/jquery-parallax-min.js', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.1.3',
				'in_footer'	=> true 
			),
				
			// Spectrum
			/// if( file_exists( $this->dir .'/assets/js/bgrins-spectrum/i18n/jquery.spectrum-'. $_locale[0] .'.js' ) )
			/// wp_register_script( 'spectrum-i10n', '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/i18n/jquery.spectrum-'. $_locale[0] .'.js', array( ), '1.7.0', true );
			'spectrum'			=> array(
				'local'		=> $this->master->uri. 'assets/spectrum/spectrum.min.js',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.js', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '1.7.0',
				'in_footer'	=> true 
			),
			
			// ThreeSixty Slider
			'threesixty'		=> array(
				'local'		=> $this->master->uri .'assets/threesixty/threesixty.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '2.0.5',
				'in_footer'	=> true 
			),
			
			// TiFy
			/// TiFy - Calendar
			'tify-calendar'			=> array(
				'local'		=> $this->master->uri. 'assets/tify/tify-calendar.js',
				'deps'		=> array( 'jquery' ),
				'version'	=> '150409',
				'in_footer'	=> true 
			),
			/// TiFy - Find Posts
			'tify-findposts'		=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-findposts.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery', 'jquery-ui-draggable', 'wp-ajax-response' ),
				'version'	=> '2.2.2',
				'in_footer'	=> true 
			),
			/// TiFy - Modal
			'tify-modals'			=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-modals.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'bootstrap-modals' ),
				'version'	=> '150825',
				'in_footer'	=> true 
			),
			/// TiFy - Lightbox
			'tify-lightbox'			=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-lightbox.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '150325',
				'in_footer'	=> true 
			),
			/// TiFy - Smooth Anchor
			'tify-smooth-anchor'	=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-smooth-anchor.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '150329',
				'in_footer'	=> true 
			),
			/// TiFy - Slideshow
			'milk-slideshow'	=> array(
				'local'		=> $this->master->uri .'assets/tify/milk-slideshow.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '141218',
				'in_footer'	=> true 
			),
			/// TiFy - Fixed SubmitDiv
			'tify-fixed_submitdiv'	=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-fixed_submitdiv.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery' ),
				'version'	=> '151023',
				'in_footer'	=> true 
			),	
			/// TiFy - Video
			'tify-video'	=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-video.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery', 'tify-modals', 'froogaloop', 'wp-mediaelement' ),
				'version'	=> '150828',
				'in_footer'	=> true 
			),			
			/// TiFy - Threesixty View
			'tify-threesixty_view'	=> array(
				'local'		=> $this->master->uri .'assets/tify/tify-threesixty_view.min.js',
				'cdn'		=> '', 
				'deps'		=> array( 'jquery', 'threesixty' ),
				'version'	=> '150904',
				'in_footer'	=> true 
			)				
		);
		$this->css	= array(
			// Bootstrap
			'bootstrap'	=> array(
				'local'		=> $this->master->uri. 'assets/bootstrap/css/bootstrap.min.css',
				'cdn'		=> '//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css', 
				'deps'		=> array(),
				'version'	=> '3.3.5',
				'media'		=> 'all' 
			),
			//// Bootstrap - Tooltips
			'bootstrap-tooltips'	=> array(
				'local'		=> $this->master->uri .'assets/bootstrap/css/bootstrap-tooltips.min.css',
				'cdn'		=> '', 
				'deps'		=> array(),
				'version'	=> '3.3.5',
				'media'		=> 'all' 
			),
			//// Bootstrap - Popovers
			'bootstrap-popovers'	=> array(
				'local'		=> $this->master->uri .'assets/bootstrap/css/bootstrap-popovers.min.css',
				'cdn'		=> '', 
				'deps'		=> array( 'bootstrap-tooltips' ),
				'version'	=> '3.3.5',
				'media'		=> 'all' 
			),
			
			// FontAwesome
			'font-awesome'		=> array(
				'local'		=> $this->master->uri. 'assets/font-awesome/css/font-awesome.min.css',
				'cdn'		=> '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css',
				'dev'		=> $this->master->uri. 'assets/font-awesome/css/font-awesome.css',	// Pour les références plugin
				'deps'		=> array(),
				'version'	=> '4.4.0',
				'media'		=> 'all' 
			),
			
			// Genericons
			'genericons'		=> array(
				'local'		=> $this->master->uri. 'assets/genericons/genericons.css',
				'cdn'		=> '//cdn.rawgit.com/Automattic/Genericons/master/genericons/genericons.css',
				'dev'		=> $this->master->uri. 'assets/genericons/genericons.css',	// Pour les références plugin
				'deps'		=> array(),
				'version'	=> '4.4.0',
				'media'		=> 'all' 
			),
			
			
			// NanoScroller	
			'nanoscroller'		=> array(
				'local'		=> $this->master->uri. 'assets/nanoscroller/nanoscroller.min.css',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/jquery.nanoscroller/0.8.7/css/nanoscroller.min.css', 
				'deps'		=> array(),
				'version'	=> '0.8.7',
				'media'		=> 'all' 
			),
			
			// Spectrum
			'spectrum'			=> array(
				'local'		=> $this->master->uri. 'assets/spectrum/spectrum.min.css',
				'cdn'		=> '//cdnjs.cloudflare.com/ajax/libs/spectrum/1.7.0/spectrum.min.css', 
				'deps'		=> array(),
				'version'	=> '1.7.0',
				'media'		=> 'all' 
			),
			
			// SpinKit
			'spinkit'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/spinkit.min.css',
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Rotating Plane
			'spinkit-rotating-plane'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/1-rotating-plane.min.css',
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Fading Circle
			'spinkit'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/10-fading-circle.min.css',
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Folding Cube
			'spinkit'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/11-folding-cube.min.css',
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Double Bounce
			'spinkit-double-bounce'		=> array(
				'local'		=> $this->master->uri .'assets/spinkit/2-double-bounce.min.css',
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Wave
			'spinkit-wave'				=> array(
				'local'		=> $this->master->uri .'assets/spinkit/3-wave.min.css', 
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Wandering Cubes
			'spinkit-wandering-cubes'	=> array(
				'local'		=> $this->master->uri .'assets/spinkit/4-wandering-cubes.min.css',
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Pulse
			'spinkit-pulse'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/5-pulse.min.css', 
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Chasing Dots
			'spinkit-chasing-dots'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/6-chasing-dots.min.css', 
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Three bounce
			'spinkit-three-bounce'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/7-three-bounce.min.css', 
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Circle
			'spinkit-circle'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/8-circle.min.css', 
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),
			/// Cube Grid
			'spinkit-cube-grid'			=> array(
				'local'		=> $this->master->uri .'assets/spinkit/9-cube-grid.min.css', 
				'deps'		=> array(),
				'version'	=> '1.2.2',
				'media'		=> 'all' 
			),	
			
			// ThreeSixty Slider
			'threesixty'			=> array(
				'local'	=> $this->master->uri. 'assets/threesixty/threesixty.min.css',
				'cdn'	=> '', 
				'deps'		=> array(),
				'version'	=> '2.0.5',
				'media'		=> 'all' 
			),				
			
			// TiFY
			/// TiFy - Front Styles
			'tify-front_styles'			=> array(
				'local'	=> $this->master->uri. 'assets/tify/tify-front_styles.min.css',
				'cdn'	=> '', 
				'deps'		=> array(),
				'version'	=> '150907',
				'media'		=> 'all' 
			),
			/// TiFy - Admin Styles
			'tify-admin_styles'			=> array(
				'local'	=> $this->master->uri. 'assets/tify/tify-admin_styles.min.css',
				'cdn'	=> '', 
				'deps'		=> array(),
				'version'	=> '150409',
				'media'		=> 'all' 
			),
			/// TiFy - Calendar
			'tify-calendar'			=> array(
				'local'	=> $this->master->uri. 'assets/tify/tify-calendar.css',
				'deps'		=> array(),
				'version'	=> '150409',
				'media'		=> 'all' 
			),
			/// TiFy - Video
			'tify-video'			=> array(
				'local'	=> $this->master->uri. 'assets/tify/tify-video.min.css',
				'cdn'	=> '', 
				'deps'		=> array( 'wp-mediaelement', 'dashicons', 'spinkit-three-bounce' ),
				'version'	=> '150828',
				'media'		=> 'all' 
			),
			/// TiFy - Threesixty View
			'tify-threesixty_view'			=> array(
				'local'	=> $this->master->uri. 'assets/tify/tify-threesixty_view.min.css',
				'cdn'	=> '', 
				'deps'		=> array( 'threesixty', 'dashicons' ),
				'version'	=> '150904',
				'media'		=> 'all' 
			)	  	
		);
	}	
	
	/** == Traitement des arguments de déclaration de script == **/
	private function _script_parse_args( $args ){
		return $args = wp_parse_args(
			$args,
			array(
				'local'		=> '',
				'cdn'		=> '',
				'dev'		=> '',
				'deps'		=> array(),
				'version'	=> '',
				'in_footer'	=> true
			)
		);
	}

	/** == Traitement des arguments de déclaration de style == **/
	private function _style_parse_args( $args ){
		return $args = wp_parse_args(
			$args,
			array(
				'local'		=> '',
				'cdn'		=> '',
				'dev'		=> '',
				'deps'		=> array(),
				'version'	=> '',
				'media'		=> 'all'
			)
		);
	}
	
	/** == Récupération de la source selon le contexte == **/
	private function _get_src( $args = array() ){
		if( ! empty( $args[$this->context] ) )
			return $args[$this->context];
		else
			return $args['local'];	
	}
	
	/** == Déclaration d'un fichier Javascript == **/
	private	function _register_script( $handle ){
		if( ! isset( $this->js[$handle] ) )
			return;
		return wp_register_script( 
			$handle, 
			$this->_get_src( $this->js[$handle] ),
			$this->js[$handle]['deps'], 
			$this->js[$handle]['version'], 
			$this->js[$handle]['in_footer'] 
		);
	}
	
	/** == Déclaration d'une feuille de Style CSS == **/
	private	function _register_style( $handle ){
		if( ! isset( $this->css[$handle] ) )
			return;
		return wp_register_style( 
			$handle, 
			$this->_get_src( $this->css[$handle] ),
			$this->css[$handle]['deps'], 
			$this->css[$handle]['version'], 
			$this->css[$handle]['media'] 
		);
	}
}