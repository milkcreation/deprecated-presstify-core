<?php
namespace tiFy\Plugins\SocialShare\Network\GooglePlus;

use \tiFy\Environment\App;

class GooglePlus extends App
{
	/* = ARGUMENTS = */
	// Liste des Actions à déclencher
	protected $CallActions				= array(
		'init',
		'wp_footer',
		'tify_options_register_node'
	);
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'wp_footer' => 99
	);
		
	/* = ACTIONS A DECLENCHER = */
	/** == Initialisation globale == **/
	final public function init()
	{
		require_once $this->Dirname ."/Helpers.php";
	}
	
	/** == Pied de page du site == **/
	final public function wp_footer()
	{
		?><script type="text/javascript">/* <![CDATA[ */
		      window.___gcfg = {
		        lang: '<?php echo get_locale();?>'
		      };		
		      (function() {
		        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		        po.src = 'https://apis.google.com/js/plusone.js';
		        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		      })();
	      /* ]]> */</script><?php		
	}

	/** == Déclaration d'un section de boîte à onglets == **/
	final public function tify_options_register_node(){
		tify_options_register_node(	
			array(
				'id' 		=> 'tify_social_share-gplus',
				'parent' 	=> 'tify_social_share',
				'title' 	=> "<i class=\"fa fa-google-plus\"></i> ". __( 'Google Plus', 'tify' ),
				'cb' 		=> "tiFy\\Plugins\\SocialShare\\Network\\GooglePlus\\Taboox\\Admin\\Option\\GooglePlus"	
			)
		);
	}
}