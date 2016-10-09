<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\Genericons;

use tiFy\Environment\App;
use tiFy\Lib\File;

class Genericons extends App
{
	/* = ARGUMENTS = */	
	/** == ACTIONS == **/
	// Liste des actions à déclencher
	protected $CallActions				= array( 
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
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'genericons', $this->Url .'/plugin.js' );
	}
	
	/* = ACTIONS ET FILTRES WORPDRESS = */
	/** == Initialisation globale de Wordpress == **/
	final public function init(){
		// Déclaration des options
		$this->options = array( 
				// Nom d'accroche pour la mise en file de la police de caractères
				'hookname'		=> 'genericons',
				// Url vers la police css
				'css' 			=> \tify_style_get_src( 'genericons', 'dev' ),
				// Numero de version pour la mise en file d'appel la police de caractères
				'version'		=> '4.5.0',
				// Dépendance pour la mise en file d'appel la police de caractères
				'dependencies'	=> array(),
				// Préfixe des classes de la police de caractères				
				'prefix'		=> 'genericon',
				// Famille de la police
				'font-family' 	=> 'Genericons',
				// Suffixe de la classe du bouton de l'éditeur (doit être contenu dans la police)
				'button'		=> 'aside',
				// Infobulle du bouton et titre de la boîte de dialogue
				'title'			=> __( 'Police de caractères Genericons', 'tify' ),
				// Nombre d'éléments par ligne
				'cols'			=> 16				
		);
		// Déclaration des scripts
		wp_register_style( $this->options['hookname'], $this->options['css'], $this->options['dependencies'], $this->options['version'] );
		wp_register_style( 'tinymce-genericons', $this->Url .'/plugin.css', array(), '20141219' );
		
		// Récupération des glyphs
		$css = File::getContents( $this->options['css'] );
		preg_match_all( '/.genericon-(.*):before\s*\{\s*content\:\s*"(.*)"(;?|)\s*\}\s*/', $css, $matches );
		foreach( $matches[1] as $i => $class )
			$this->glyphs[$class] = $matches[2][$i];
	}
	
	/** == Initialisation de l'interface d'administration de Wordpress == **/
	final public function admin_init()
	{
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) )
            add_filter( 'mce_css', array( $this, 'mce_css' ) );
	}
	
	/** == Mise en file des scripts == **/
	final public function admin_enqueue_scripts() 
	{
        wp_enqueue_style( $this->options['hookname'] );
		wp_enqueue_style( 'tinymce-genericons' );
    }

	/** == Personnalisation des scripts de l'entête == **/
	final public function admin_head()
	{
	?><script type="text/javascript">/* <![CDATA[ */var GenericonsChars=<?php echo $this->get_css_glyphs();?>,tinymceGenericonsl10n={'title':'<?php echo $this->options['title'];?>'};/* ]]> */</script><?php
	}
	
	/** == Personnalisation des styles de l'entête == **/
	final public function admin_print_styles()
	{
	?><style type="text/css">i.mce-i-genericons:before{content:"<?php echo $this->glyphs[$this->options['button']];?>";} i.mce-i-genericons:before,.mce-grid a.genericons{font-family:<?php echo $this->options['font-family'];?>!important;}</style><?php
	}
	
	/** == Mise en file des scripts === ***/
	final public function wp_enqueue_scripts()
	{
		wp_enqueue_style( 'genericons' );
	}
	
	/** == Personnalisation des scripts de l'entête == **/
	final public function wp_head()
	{
	?><style type="text/css">.genericons{font-family:'<?php echo $this->options['font-family'];?>';font-style:normal;}</style><?php
	}
		
	/** == Ajout des styles dans l'éditeur == **/
 	final public function mce_css( $mce_css ) 
 	{
 		return $mce_css .= ', '. $this->options['css'] .', '. $this->Url.'/editor.css';
    }
	
	/* = CONTROLEUR = */
	/** == Récupération des glyphs depuis le fichier CSS == **/
	public function get_css_glyphs()
	{		
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