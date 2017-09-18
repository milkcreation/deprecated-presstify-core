<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\OwnGlyphs;

use tiFy\Lib\File;

class OwnGlyphs extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */	
	/** == ACTIONS == **/
	// Liste des actions à déclencher
	protected $tFyAppActions				= array( 
		'init',
		'admin_init',
		'admin_enqueue_scripts',
		'admin_head',
		'admin_print_styles',
		'wp_enqueue_scripts',
		'wp_head'
	);
	
	/** == CONFIGURATION == **/
	// 
	private $options;
	//
	private $glyphs;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();

		// Déclaration du plugin
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'ownglyphs', self::tFyAppUrl() . '/plugin.js' );

		add_action( 'wp_ajax_tinymce-ownglyphs-class', array( $this, 'wp_ajax' ) );
	}
	
	/* = ACTIONS ET FILTRES WORPDRESS = */
	/** == GLOBAL == **/
	/*** === Initialisation globale de Wordpress === ***/
	final public function init(){
		// Déclaration des options
		$defaults = array( 
			// Nom d'accroche pour la mise en file de la police de caractères
			'hookname'		=> 'font-awesome',
			// Url vers la police css
			'css' 			=> \tify_style_get_src( 'font-awesome', 'dev' ),
			// Numero de version pour la mise en file d'appel la police de caractères
			'version'		=> \tify_style_get_attr( 'font-awesome', 'version' ),
			// Dépendance pour la mise en file d'appel la police de caractères
			'dependencies'	=> array(),
			// Préfixe des classes de la police de caractères				
			'prefix'		=> 'fa',
			// Famille de la police
			'font-family' 	=> 'fontAwesome',
			// Suffixe de la classe du bouton de l'éditeur (doit être contenu dans la police)
			'button'		=> 'wordpress',
			// Infobulle du bouton et titre de la boîte de dialogue
			'title'		=> __( 'Police de caractères personnalisée', 'tify' ),
			// Nombre d'éléments par ligne
			'cols'			=> 32
		);
		$options = ( $_opts = \tiFy\Components\TinyMCE\TinyMCE::getExternalPluginConfig( 'ownglyphs' ) ) ? $_opts : array();
		$this->options = wp_parse_args( $options, $defaults );

		// Déclaration des scripts
		wp_register_style( $this->options['hookname'], $this->options['css'], $this->options['dependencies'], $this->options['version'] );
		wp_register_style( 'tinymce-ownglyphs', self::tFyAppUrl() . '/plugin.css', array(), '20141219' );
		
		// Récupération des glyphs
		$css = File::getContents( $this->options['css'] );
		preg_match_all( '/.'. $this->options['prefix'] .'-(.*):before\s*\{\s*content\:\s*"(.*)";\s*\}\s*/', $css, $matches );
		foreach( $matches[1] as $i => $class )
			$this->glyphs[$class] = $matches[2][$i];
	}
	
	/*** === Initialisation de l'interface d'administration de Wordpress === ***/
	final public function admin_init(){
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) )
            add_filter( 'mce_css', array( $this, 'add_tinymce_editor_style' ) );
	}
	
	/*** === Mise en file des scripts === ***/
	final public function admin_enqueue_scripts() 
	{
        wp_enqueue_style( $this->options['hookname'] );
		wp_enqueue_style( 'tinymce-ownglyphs' );
    }
	
	/*** === Personnalisation des scripts de l'entête === ***/
	final public function admin_head()
	{
	?><script type="text/javascript">/* <![CDATA[ */var glyphs = <?php echo $this->get_css_glyphs();?>, tinymceOwnGlyphsl10n = { 'title' : '<?php echo $this->options['title'];?>' };/* ]]> */</script><?php
	}
	
	/*** === Personnalisation des styles de l'entête === ***/
	final public function admin_print_styles()
	{
	?><style type="text/css">i.mce-i-ownglyphs:before{content:"<?php echo $this->glyphs[$this->options['button']];?>";} i.mce-i-ownglyphs:before,.mce-grid a.ownglyphs{font-family:<?php echo $this->options['font-family'];?>!important;}</style><?php
	}

	/*** === Mise en file des scripts === ***/	
	final public function wp_enqueue_scripts()
	{
		wp_enqueue_style( $this->options['hookname'] );
	}
	
	/*** === Personnalisation des scripts de l'entête === ***/
	final public function wp_head()
	{
	?><style type="text/css">.ownglyphs{font-family:'<?php echo $this->options['font-family'];?>';font-style:normal;}</style><?php
	}
	
	/** == Action ajax == **/
	final public function wp_ajax()
	{
		header("Content-type: text/css");
		echo '.ownglyphs{font-family:'. $this->options['font-family'] .';}'; exit;
	}
	
	/** == Ajout des styles dans l'éditeur == **/
 	final public function add_tinymce_editor_style( $mce_css )
 	{
        return $mce_css .= ', '. $this->options['css'] .', '. self::tFyAppUrl() . '/editor.css, '. admin_url( 'admin-ajax.php?action=tinymce-ownglyphs-class&bogus='.current_time( 'timestamp' ) );
    }
	
	/* = CONTROLEUR = */
	/** == Récupération des glyphs depuis le fichier CSS == **/
	public function get_css_glyphs(){		
		$return = "[";
		$col = 0;
		foreach( (array) $this->glyphs as $class => $content ) :
			if( ! $col )
				$return .= "{";
			$return .= "'$class':'". html_entity_decode( preg_replace( '/'. preg_quote('\\').'/', '&#x', $content ), ENT_NOQUOTES, 'UTF-8') ."',";
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