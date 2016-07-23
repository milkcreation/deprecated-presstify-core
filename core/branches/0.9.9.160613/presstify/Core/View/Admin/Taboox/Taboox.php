<?php
namespace tiFy\Core\View\Admin\Taboox;

use tiFy\Environment\App;

class Taboox extends App
{			
	/* = ARGUMENTS = */
	// Classe de la vue
	protected 	$View;
	// 
	protected 	$Hookname;
		
	/* = CONSTRUCTEUR = */
	public function __construct( \tiFy\Core\View\Factory $viewObj )
	{
		parent::__construct();
		
		if( is_null( $this->View ) )
			$this->View = $viewObj;

		// Actions et Filtres PressTiFy		
		add_action( 'tify_taboox_register_box', array( $this, '_tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
	}
		
	/* = DECLENCHEURS = */	
	/** == Déclaration de la boîte à onglets == **/
	final public function _tify_taboox_register_box()
	{
		$menu_slug 		= $this->View->getAdminViewAttrs( 'menu_slug', 'Taboox' );
		$parent_slug 	= $this->View->getAdminViewAttrs( 'parent_slug', 'Taboox' );
		$this->Hookname = \get_plugin_page_hookname( $menu_slug, $parent_slug );
		
		tify_taboox_register_box( 
			$this->Hookname,
			'option',
			array(
				'title'		=> __( 'Réglages des options de forum', 'tify' ),
				'page'		=> $menu_slug
			)
		);
	}
			
	/* = VUES = */	
	/** == Rendu == **/
	public function Render()
	{
	?>	
		<div class="wrap">
			<h2><?php _e( 'Réglage des Options', 'tify');?></h2>
			
			<form method="post" action="options.php">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php \settings_fields( 'tify_forum_options' );?>	
						<?php \do_settings_sections( 'tify_forum_options' );?>
					</div>					
					<div style="margin-right:-300px; width: 280px; float:right;">
						<div id="submitdiv">
							<h3 class="hndle"><span><?php _e( 'Enregistrer', 'tify' );?></span></h3>
							<div style="padding:10px;">
								<div class="submit">
								<?php \submit_button(); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	<?php
	}
}