<?php
/**
 * CLASSE MAITRESSE
 */
class tiFy_Control_Main{
	/* = ARGUMENTS = */
	public	// Référence
			$master;
			
	
	/* = CONTRUCTEUR = */
	public function __construct( tiFy $master ){
		$this->master	= $master;
		$this->dir 		= dirname( __FILE__ );
		
		// Instanciation des contrôleurs
		require_once $this->dir ."/controllers/checkbox/checkbox.php";
		new tiFy_Control_Checkbox( $master );
		
		require_once $this->dir ."/controllers/colorpicker/colorpicker.php";
		new tiFy_Control_Colopicker( $master );

		require_once $this->dir ."/controllers/dropdown/dropdown.php";
		new tiFy_Control_Dropdown( $master );
		
		require_once $this->dir ."/controllers/dropdown_colors/dropdown_colors.php";
		new tiFy_Control_DropdownColors( $master );
		
		require_once $this->dir ."/controllers/dropdown_glyphs/dropdown_glyphs.php";
		new tiFy_Control_DropdownGlyphs( $master );
		
		require_once $this->dir ."/controllers/dropdown_images/dropdown_images.php";
		new tiFy_Control_DropdownImages( $master );
		
		require_once $this->dir ."/controllers/dropdown_menu/dropdown_menu.php";
		new tiFy_Control_DropdownMenu( $master );
		
		require_once $this->dir ."/controllers/dynamic_inputs/dynamic_inputs.php";
		new tiFy_Control_DynamicInputs( $master );
		
		require_once $this->dir ."/controllers/findposts/findposts.php";
		new tiFy_Control_FindPosts( $master );
		
		require_once $this->dir ."/controllers/media_image/media_image.php";
		new tiFy_Control_MediaImage( $master );
		
		require_once $this->dir ."/controllers/quicktags_editor/quicktags_editor.php";
		new tify_Control_QuicktagsEditor( $master );
		
		require_once $this->dir ."/controllers/switch/switch.php";
		new tiFy_Control_Switch( $master );
		
		require_once $this->dir ."/controllers/text_remaining/text_remaining.php";
		new tiFy_Control_TextRemaining( $master );
		
		require_once $this->dir ."/controllers/touch_time/touch_time.php";
		new tiFy_Control_TouchTime( $master );
	}
}

class tiFy_Control{
	/* = ARGUMENTS = */
	public 	$master,
			$id,
			$filename,
			$dir,
			$uri;
			
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy $master ){
		$this->master = $master;
		
		// Configuration
		if( ! $this->id )
			$this->id = get_class( $this );
		$this->set_dirname();
		$this->set_uri();
		
		// Actions et Filtres Wordpress
		add_action( 'muplugins_loaded', array( $this, 'wp_muplugins_loaded' ) );
		add_action( 'init', array( $this, 'wp_init' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == == **/
	final public function wp_muplugins_loaded(){
		// Helpers	
		$this->master->control->{$this->id} = $this;
		eval( 
			'function tify_control_'. $this->id .'( $args = array() ){
				global $tiFy;
				return $tiFy->control->'. $this->id .'->display( $args );
			}'
		);
	}
	
	/** == Initialisation globale == **/
	final public function wp_init(){
		$this->register_scripts();
		
	}
	
	/* = CONFIGURATION = */
	/** == == **/
	private function get_filename(){
		if( $this->filename )
			return $this->filename;
		
		$reflection = new ReflectionClass( $this );
		return $this->filename =  $reflection->getFileName();
	}
	
	/** == == **/
	private function set_dirname(){
		$this->dir 	= dirname( $this->get_filename() );
	}
	
	/** == == **/
	private function set_uri(){
		$this->uri 	= untrailingslashit( plugin_dir_url( $this->get_filename() ) );
	}
	
	/* = METHODES PUBLIQUES = */
	/** == Déclaration des scripts == **/
	public function register_scripts(){
		
	}
	
	/** == Mise en file des scripts == **/
	public function enqueue_scripts(){
		
	}	
	
	/** == Affichage du controleur == **/
	public function display( $args = array() ){
		
	}
}


/**
 * UTILITAIRES
 */
/**
 * Mise en file des scripts
 */
function tify_control_enqueue( $scripts ){
	global $tiFy;

	if( is_string( $scripts ) )
		$scripts = array( $scripts );
		
	foreach( $scripts as $script ) :
		$tiFy->control->$script->enqueue_scripts();
		if( wp_style_is( 'tify_controls-'.$script, 'registered' ) ) 
			wp_enqueue_style( 'tify_controls-'.$script );
		if( wp_script_is( 'tify_controls-'.$script, 'registered' ) ) 
			wp_enqueue_script( 'tify_controls-'.$script );
	endforeach;				
}