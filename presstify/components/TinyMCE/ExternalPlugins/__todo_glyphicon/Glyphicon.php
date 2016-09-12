<?php
class tinymceGlyphicon{	
	private $dir, $path, $uri, $options, $glyphs;
	
	public function __construct(){
		global $tiFy;
		
		$this->tiFy 	= $tiFy;
		// Définition des chemins
		$this->dir 		= dirname( __FILE__ );
		$this->path  	= $this->tiFy->get_relative_path( $this->dir );
		$this->uri		= $this->tiFy->uri . $this->path;
		
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );  
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_print_styles' ) );
		add_action( 'wp_ajax_tinymce-glyphicon-class', array( $this, 'ajax_action' ) );		
	}
	
	public function init(){
		// Déclaration des options
		$this->options = array( 
				// Nom d'accroche pour la mise en file de la police de caractères
				'hookname'		=> 'glyphicon',
				// Url vers la police css
				'css' 			=> 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.css',
				// Numero de version pour la mise en file d'appel la police de caractères
				'version'		=> '3.3.4',
				// Dépendance pour la mise en file d'appel la police de caractères
				'dependencies'	=> array(),
				// Préfixe des classes de la police de caractères				
				'prefix'		=> 'glyphicon',
				// Famille de la police ()
				'font-family' 	=> 'glyphicon',
				// Suffixe de la classe du bouton de l'éditeur (doit être contenu dans la police)
				'button'		=> 'bold',
				// Infobulle du bouton et titre de la boîte de dialogue
				'title'		=> __( 'Police de caractères Bootstrap', 'tify' ),
				// Nombre d'éléments par ligne
				'cols'			=> 24				
		);
		// Déclaration des scripts
		wp_register_style( $this->options['hookname'], $this->options['css'], $this->options['dependencies'], $this->options['version'] );
		wp_register_style( 'tinymce-glyphicon', $this->uri .'/plugin.css', array(), '20150210' );
		
		// Récupération des glyphs
		$css = tify_file_get_contents_curl( $this->options['css'] );
		preg_match_all( '/.glyphicon-(.*):before\s*\{\s*content\:\s*"(.*)"(;?|)\s*\}\s*/', $css, $matches );
		foreach( $matches[1] as $i => $class )
			$this->glyphs[$class] = $matches[2][$i];
	}

	public function admin_init(){
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) )
            add_filter( 'mce_css', array( $this, 'add_tinymce_editor_style' ) );
	}
	
	public function admin_enqueue_scripts() {
        wp_enqueue_style( 'glyphicon' );
		wp_enqueue_style( 'tinymce-glyphicon' );
    }

	public function admin_head(){
		?><script type="text/javascript">/* <![CDATA[ */var glyphiconChars = <?php echo $this->get_css_glyphs();?>, tinymceGlyphiconl10n = { 'title' : '<?php echo $this->options['title'];?>' };/* ]]> */</script><?php		
		new tinymcePlugins(
	        'glyphicon',
	        $this->uri .'/plugin.js',
	        null,
	        array()
	    );
	}

	public function admin_print_styles(){
		?>
		<style type="text/css">
			i.mce-i-glyphicon:before{
	   			content: "<?php echo $this->glyphs[$this->options['button']];?>";
	   		}
			i.mce-i-glyphicon:before,
			.mce-grid a.glyphicon{
				font-family: <?php echo $this->options['font-family'];?>!important;
			}
		</style>
		<?php
	}
	
	public function ajax_action(){
		header("Content-type: text/css");
		echo '.glyphicon{ font-family: '. $this->options['font-family'] .'; }'; exit;
	}

 	public function add_tinymce_editor_style( $mce_css ) {
        $mce_css .= ', ' . $this->options['css'] .', '. $this->uri.'/editor.css, '. admin_url( 'admin-ajax.php?action=tinymce-glyphicon-class&bogus='.current_time( 'timestamp' ) );

        return $mce_css;
    }
	
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
new tinymceGlyphicon();