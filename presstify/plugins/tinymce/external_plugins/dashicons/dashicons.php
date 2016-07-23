<?php
class tiFy_tinyMCE_PluginDashicons{	
	/* = ARGUMENTS = */	
	private // Configuration
			$uri,
			// Paramètres 
			$options, 
			$glyphs,
			// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_tinyMCE $master ){
		$this->master = $master;
		
		// Configuration
		$this->uri = tiFY_Plugin::get_url( $this );
		
		// Déclaration du plugin
		$this->master->register_external_plugin( 'dashicons', $this->uri .'/plugin.js' ); 
		
		// ACTIONS ET FILTRES WORDPRESS
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );  
		add_action( 'admin_head', array( $this, 'wp_admin_head' ) );
		add_action( 'admin_print_styles', array( $this, 'wp_admin_print_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );	
	}
	
	/* = ACTIONS ET FILTRES WORPDRESS = */
	/** == Initialisation globale de Wordpress == **/
	final public function wp_init(){
		// Déclaration des options
		$this->options = array( 
				// Nom d'accroche pour la mise en file de la police de caractères
				'hookname'		=> 'dashicons',
				// Url vers la police css
				'css' 			=> includes_url(). 'css/dashicons.css',
				// Numero de version pour la mise en file d'appel la police de caractères
				'version'		=> '4.1',
				// Dépendance pour la mise en file d'appel la police de caractères
				'dependencies'	=> array(),
				// Préfixe des classes de la police de caractères				
				'prefix'		=> 'dashicons',
				// Famille de la police
				'font-family' 	=> 'dashicons',
				// Suffixe de la classe du bouton de l'éditeur (doit être contenu dans la police)
				'button'		=> 'wordpress-alt',
				// Infobulle du bouton et titre de la boîte de dialogue
				'title'		=> __( 'Police de caractères Wordpress', 'tify' ),
				// Nombre d'éléments par ligne
				'cols'			=> 24				
		);
		// Déclaration des scripts
		wp_register_style( $this->options['hookname'], $this->options['css'], $this->options['dependencies'], $this->options['version'] );
		wp_register_style( 'tinymce-dashicons', $this->uri .'/plugin.css', array(), '20141219' );
		
		// Récupération des glyphs
		$css_path = tify_get_relative_url( $this->options['css'] );
		$css = file_get_contents( ABSPATH . $css_path );
		preg_match_all( '/.dashicons-(.*):before\s*\{\s*content\:\s*"(.*)"(;?|)\s*\}\s*/', $css, $matches );
		foreach( $matches[1] as $i => $class )
			$this->glyphs[$class] = $matches[2][$i];
	}
	
	/** == Initialisation de l'interface d'administration de Wordpress == **/
	final public function wp_admin_init(){
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) )
            add_filter( 'mce_css', array( $this, 'add_tinymce_editor_style' ) );
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_admin_enqueue_scripts() {
        wp_enqueue_style( $this->options['hookname'] );
		wp_enqueue_style( 'tinymce-dashicons' );
    }

	/** == Personnalisation des scripts de l'entête == **/
	final public function wp_admin_head(){
	?><script type="text/javascript">/* <![CDATA[ */var dashiconsChars = <?php echo $this->get_css_glyphs();?>, tinymceDashiconsl10n = { 'title' : '<?php echo $this->options['title'];?>' };/* ]]> */</script><?php
	}
	
	/** == Personnalisation des styles de l'entête == **/
	final public function wp_admin_print_styles(){
	?><style type="text/css">i.mce-i-dashicons:before{content:"<?php echo $this->glyphs[$this->options['button']];?>";} i.mce-i-dashicons:before,.mce-grid a.dashicons{font-family:<?php echo $this->options['font-family'];?>!important;}</style><?php
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts(){
		wp_enqueue_style( $this->options['hookname'] );
	}
	
	/* = CONTROLEUR = */
	/** == Ajout des styles dans l'éditeur == **/
 	public function add_tinymce_editor_style( $mce_css ) {
        return $mce_css .= ', '. $this->options['css'] .', '. $this->uri.'/editor.css';
    }
	
	/** == Récupération des glyphs depuis le fichier CSS == **/
	public function get_css_glyphs(){		
		$return = "[";
		$col = 0;
		foreach( (array) $this->glyphs as $class => $content ) :
			if( ! $col )
				$return .= "{";
			$return .= "'$class':'".preg_replace( '/'. preg_quote('\\').'/', '&#x', $content )."',";
			if( ++$col >=  $this->options['cols'] ) :
				$col = 0;
				$return .= "},";
			endif;
		endforeach;
		if( $col )
			$return .= "}";
		$return .= "]";
		
		return $return;
	}
}