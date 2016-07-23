<?php
namespace tiFy\Core\Admin\Model\TabooxOption;

use tiFy\Core\Admin\Model\Form;

class TabooxOption extends Form
{			
	/* = ARGUMENTS = */
	private $MenuSlug;
	private $Hookname;
	
	/// Cartographie des paramètres
	protected $ParamsMap			= array( 
		'BaseUri', 'Singular', 'Notices', 'Sections', 'QueryArgs'
	);
		
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Actions et Filtres PressTiFy		
		add_action( 'tify_taboox_register_box', array( $this, '_tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, '_tify_taboox_register_node' ) );
	}
		
	/* = PARAMETRAGE = */
	/** == Définition des sections de formulaire d'édition == **/
	public function set_sections()
	{
		return array();	
	}
	
	/* = DECLENCHEURS = */
	/** == Déclaration de la boîte à onglets == **/
	final public function _tify_taboox_register_box()
	{
		$this->MenuSlug = $this->View->getModelAttrs( 'menu_slug', 'TabooxOption' );
		$parent_slug 	= $this->View->getModelAttrs( 'parent_slug', 'TabooxOption' );
		$this->Hookname = $this->MenuSlug .'::'. $parent_slug;

		tify_taboox_register_box( 
			$this->Hookname,
			'option',
			array(
				'title'	=> __( 'Réglages des options', 'tify' ),
				'page'	=> $this->MenuSlug
			)
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	final public function _tify_taboox_register_node()
	{
		foreach( (array) $this->set_sections() as $id => $args ) :
			if( is_string( $args ) )
				$args = array( 'title' => $args );
			
			$defaults = array(
				'id' 			=> $id
			);
			$args = wp_parse_args( $args, $defaults );
			
			if( method_exists( $this, "section_{$id}" ) )
				$args['cb'] = array( $this, 'section_'. $id );

			tify_taboox_register_node( $this->Hookname, $args );
		endforeach;
	}
	
	/* = TRAITEMENT = */
	/** == Éxecution des actions == **/
	protected function process_bulk_actions(){}
	
	/* = VUES = */	
	/** == Rendu == **/
	public function Render()
	{
	?>	
		<div class="wrap">
			<h2><?php _e( 'Réglage', 'tify');?></h2>
			
			<form method="post" action="options.php">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php \settings_fields( $this->MenuSlug );?>	
						<?php \do_settings_sections( $this->MenuSlug );?>
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