<?php
namespace tiFy\Plugins\Emailing\Admin\Options;

class Options
{	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_box', array( $this, 'tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );	
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration de la boîte à onglets == **/
	public function tify_taboox_register_box(){
		tify_taboox_register_box( 
			$this->master->hookname['options'],
			'option',
			array(
				'title'		=> __( 'Options', 'tify' ),
				'page'		=> $this->master->menu_slug['options']
			)
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	public function tify_taboox_register_node(){
		tify_taboox_register_node(
			$this->master->hookname['options'],
			array(
				'id' 			=> 'wistify-subscribe-form',
				'title' 		=> __( 'Formulaire d\'inscription', 'tify' ),
				'cb'			=> 'tiFy_Wistify_Options_SubscribeForm_Taboox',
				'order'			=> 2
			)
		);
		tify_taboox_register_node(
			$this->master->hookname['options'],
			array(
				'id' 			=> 'wistify-contact_options',
				'title' 		=> __( 'Information de contact', 'tify' ),
				'cb'			=> 'tiFy_Wistify_Options_ContactInformations_Taboox',
				'order'			=> 3
			)
		);
	}
	
	/** == Déclaration des interfaces de saisie == **/
	public function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_Wistify_Options_SubscribeForm_Taboox', $this->master );	
		tify_taboox_register_form( 'tiFy_Wistify_Options_ContactInformations_Taboox', $this->master );		
	}	
	
	/* = = */
	public function Render()
	{
	?>		
	<div class="wrap">
		<h2><?php _e( 'Réglages des options', 'tify' ); ?></h2>
		<?php settings_errors(); ?>
		
		<form method="post" action="options.php">
			<div style="margin-right:300px; margin-top:20px;">
				<div style="float:left; width: 100%;">
					<?php settings_fields( $this->master->menu_slug['options'] );?>	
					<?php do_settings_sections( $this->master->menu_slug['options'] );?>
				</div>					
				<div style="margin-right:-300px; width: 280px; float:right;">
					<div id="submitdiv">
						<h3 class="hndle"><span><?php _e( 'Enregistrer', 'tify' );?></span></h3>
						<div style="padding:10px;">
							<div class="submit">
							<?php submit_button();?>
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