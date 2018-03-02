<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\Template;

class Template extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */	

	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		parent::__construct();		

		$config['templates'] = add_query_arg(
				array(
						'action' 	=> 'tinymce_templates',
						'nonce'		=> \wp_create_nonce( 'tinymce_templates' )
				),
				admin_url( 'admin-ajax.php' )
				);
		// Déclaration du plugin
		\tiFy\Components\TinyMCE\TinyMCE::registerExternalPlugin( 'template', self::tFyAppUrl() . '/plugin.min.js', $config );
				
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'mce_css', array( $this, 'mce_css' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
	    add_action( 'wp_ajax_tinymce_templates', array( $this, 'wp_ajax' ) );
	}
	
	/** == Initialisation globale == **/	
	final public function init(){
		wp_register_style( 'tify-tinymce_template', self::tFyAppUrl() . '/theme.css', array(), '1.150317' );
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts(){
		wp_enqueue_style( 'tify-tinymce_template' );
	}
	
	/** == Action Ajax == **/
	final public function wp_ajax(){
	    nocache_headers();
		header( 'Content-Type: application/x-javascript; charset=UTF-8' );
	    if ( ! wp_verify_nonce( $_GET['nonce'], 'tinymce_templates' ) ) 
	        return;
	   
		$arr = array(
					array( 	"title" 		=> "2 Colonnes : 1/4, 3/4", 
							"description" 	=> "1 colonne d'1/4 et l'autre de 3/4",
							"url" 			=> self::tFyAppUrl() . "/templates/2cols_0.25-0.75.htm"
					),
					array( 	"title" 		=> "2 Colonnes : 1/3, 2/3", 
							"description" 	=> "1 colonne d'1/3 et l'autre de 2/3",
							"url" 			=> self::tFyAppUrl() . "/templates/2cols_0.33-0.66.htm"
					),
					array( 	"title" 		=> "2 Colonnes : 1/2, 1/2", 
							"description" 	=> "1 colonnes d'1/2 et l'autre d'1/2",
							"url" 			=> self::tFyAppUrl() . "/templates/2cols_0.5-0.5.htm"
					),
					array( 	"title" 		=> "2 Colonnes : 2/3, 1/3", 
							"description" 	=> "1 colonne de 2/3 et l'autre d'1/3",
							"url" 			=> self::tFyAppUrl() . "/templates/2cols_0.66-0.33.htm"
					),
					array( 	"title" 		=> "2 Colonnes : 3/4, 1/4", 
							"description" 	=> "1 colonne de 3/4 et l'autre d'1/4",
							"url" 			=> self::tFyAppUrl() . "/templates/2cols_0.75-0.25.htm"
					),
					array( 	"title" 		=> "3 Colonnes : 1/4, 1/4, 1/2", 
							"description" 	=> "1 colonne d'1/4, une d'1/4 et une d'1/2",
							"url" 			=> self::tFyAppUrl() . "/templates/3cols_0.25-0.25-0.5.htm"
					),
					array( 	"title" 		=> "3 Colonnes : 1/4, 1/2, 1/4", 
							"description" 	=> "1 colonne d'1/4, une d'1/2 et une d'1/4",
							"url" 			=> self::tFyAppUrl() . "/templates/3cols_0.25-0.5-0.25.htm"
					),
					array( 	"title" 		=> "3 Colonnes : 1/3, 1/3, 1/3", 
							"description" 	=> "1 colonne d'1/3, une d'1/3 et une d'1/3",
							"url" 			=> self::tFyAppUrl() . "/templates/3cols_0.33-0.33-0.33.htm"
					),
					array( 	"title" 		=> "3 Colonnes : 1/2, 1/4, 1/4", 
							"description" 	=> "1 colonne d'1/2, une d'1/4 et une d'1/4",
							"url" 			=> self::tFyAppUrl() . "/templates/3cols_0.5-0.25-0.25.htm"
					),
					array( 	"title" 		=> "4 Colonnes : 1/4, 1/4, 1/4, 1/4", 
							"description" 	=> "1 colonnes d'1/4, une d'1/4, une d'1/4 et une d'1/4",
							"url" 			=> self::tFyAppUrl() . "/templates/4cols_0.25-0.25-0.25-0.25.htm"
					)
			);
				
	    echo json_encode($arr);
	    exit;
	}
	
	/** == == **/
	final public function mce_css( $mce_css )
	{
        return $mce_css .= ', '. self::tFyAppUrl() . '/editor.css';
    }	
}