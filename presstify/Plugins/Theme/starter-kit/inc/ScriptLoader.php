<?php
namespace Theme;

use \tiFy\Environment\App;

class ScriptLoader extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
			'init',
			'wp_enqueue_scripts',
			'admin_enqueue_scripts',
			'login_enqueue_scripts'
	);

	// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array(
			'init' 					=> 'register_scripts',
			'wp_enqueue_scripts' 	=> 'enqueue_scripts'
	);

	/* = FILTRES = */
	/** == Déclaration des scripts == **/
	public function register_scripts()
	{
		// STYLES
		/// GOOGLE FONTS
		tify_register_style(
			'GoogleFonts',
			array(
				'src'		=> '//fonts.googleapis.com/css?family=Open+Sans:400,300,700',
				'version'	=> '160406'
			)
		);
		/// THEME - Reset
		tify_register_style(
			'theme-reset',
			array(
				'src'		=> get_stylesheet_directory_uri(). '/css/reset.css',
				'version'	=> '160406'
			)
		);
		/// THEME - Helpers
		tify_register_style(
			'theme-helpers',
			array(
					'src'		=> get_stylesheet_directory_uri(). '/css/helpers.css',
					'version'	=> '160406'
			)
		);
		/// THEME - Structure
		tify_register_style(
			'theme-structure',
			array(
				'src'		=> get_stylesheet_directory_uri(). '/css/structure.css',
				'version'	=> '160406'
			)
		);

		/// THEME - Animation
		tify_register_style(
			'theme-animation',
			array(
				'src'		=> get_stylesheet_directory_uri(). '/css/animation.css',
				'version'	=> '160406'
			)
		);
		/// THEME - Article
		tify_register_style(
			'theme-article',
			array(
				'src'		=> get_stylesheet_directory_uri(). '/css/article.css',
				'version'	=> '160406'
			)
		);
		/// THEME - Responsive
		tify_register_style(
			'theme-responsive',
			array(
				'src'		=> get_stylesheet_directory_uri(). '/css/responsive.css',
				'version'	=> '160406'
			)
		);
		/// THEME - Tronc commun
		tify_register_style(
			'theme-root',
			array(
				'src'		=> get_stylesheet_directory_uri(). '/style.css',
				'deps'		=> array(
					'bootstrap',
					'GoogleFonts',
					'theme-reset',
					'theme-helpers',
					'theme-structure',
					'theme-animation',
					'theme-article',
					'theme-responsive'
				),
				'version'	=> '160406'
			)
		);

		// SCRIPTS
		/// THEME - Tronc commun
		tify_register_script(
			'theme-root',
			array(
				'src'		=> get_stylesheet_directory_uri() .'/js/scripts.js',
				'deps'		=> array(
						'jquery',
						'holder'
				),
				'version'	=> '160406',
				'in_footer'	=> true
			)
		);
	}

	/** == Mise en file des scripts de l'interface utilisateur == **/
	public function enqueue_scripts()
	{
		// jQuery
		wp_deregister_script( 'jquery' );
		wp_register_script( 'jquery', '//code.jquery.com/jquery-2.2.3.min.js', '2.2.3', true );

		wp_enqueue_style( 'theme-root' );
		wp_enqueue_script( 'theme-root' );
	}

	/** == Mise en file des scripts de l'interface administrateur == **/
	final public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'GoogleFont-Roboto' );
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'font-tb' );
	}

	/** == Styles de l'interface d'authentification == **/
	final public function login_enqueue_scripts()
	{
	?><link rel="stylesheet" id="custom_wp_admin_css"  href="<?php echo  get_template_directory_uri();?>/css/login.css" type="text/css" media="all" />
	<style type="text/css">body.login div#login h1 a{ background-image: url('data:image/svg+xml;base64,<?php echo base64_encode( file_get_contents( get_stylesheet_directory().'/images/logo.svg' ) );?>');}</style>
	<?php
	}
}