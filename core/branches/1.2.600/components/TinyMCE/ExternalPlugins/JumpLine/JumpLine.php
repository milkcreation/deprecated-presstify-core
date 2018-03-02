<?php
namespace tiFy\Components\TinyMCE\ExternalPlugins\JumpLine;

use tiFy\Components\TinyMCE\TinyMCE;

class JumpLine extends \tiFy\App\Factory
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
	public function __construct()
	{
		parent::__construct();

		// Déclaration du plugin
		TinyMCE::registerExternalPlugin( 'jumpline', self::tFyAppUrl() . '/plugin.js' );

        // Déclaration des événements
        $this->appAddAction('admin_init');
        $this->appAddAction('admin_head');
        $this->appAddAction('admin_print_styles');
        $this->appAddAction('wp_enqueue_scripts');
	}

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
	final public function admin_init()
	{
		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) )
            add_filter( 'mce_css', array( $this, 'mce_css' ) );
	}
	
	/** == Ajout des styles dans l'éditeur == **/
	final public function mce_css( $mce_css )
	{
	    return $mce_css .= ', '. self::tFyAppUrl( get_class() ).'/Jumpline.css';
	}
	
	/** == Personnalisation des scripts de l'entête de l'interface d'administration == **/
	final public function admin_head()
	{
	?><script type="text/javascript">/* <![CDATA[ */var tiFyTinyMCEJumpLinel10n = { 'title' : '<?php _e( 'Saut de ligne', 'tify' );?>' };/* ]]> */</script><?php	
	}
	
	/** == Personnalisation des styles de l'entête de l'interface d'administration == **/
	final public function admin_print_styles()
	{
	?><style type="text/css">i.mce-i-jumpline:before{content:"\f474";font-family:"dashicons";}</style><?php
	}
		
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts()
	{
		wp_enqueue_style( 'tiFyComponentsTinyMCEExternalPluginsJumpLine', self::tFyAppUrl() . '/theme.css', array(), 160625 );
	}
}