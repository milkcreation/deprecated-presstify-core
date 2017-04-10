<?php
namespace tiFy\Core\Templates\Admin\Model\TabooxOption;

class TabooxOption extends \tiFy\Core\Templates\Admin\Model\Form
{			
	/* = ARGUMENTS = */
	protected $MenuSlug;
	protected $Hookname;
	
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
		
	/* = DECLARATION DES PARAMETRES = */
	/** == Définition des sections de formulaire d'édition == **/
	public function set_sections()
	{
		return array();	
	}
	
	/* = DECLENCHEURS = */
	/** == Déclaration de la boîte à onglets == **/
	final public function _tify_taboox_register_box()
	{
		$this->MenuSlug = $this->getConfig( '_menu_slug' );
		$parent_slug 	= $this->getConfig( '_parent_slug' );
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
	
	/* = CONTROLEURS = */
	/** == Récupération du Hookname == **/
	final public function getHookname()
	{
	   return $this->Hookname;   
	}
	
	/** == Récupération du MenuSlug == **/
	final public function getMenuSlug()
	{
	   return $this->MenuSlug;   
	}
	
	/* = TRAITEMENT = */
	/** == Éxecution des actions == **/
	protected function process_bulk_actions(){}
	
    /** == Préparation de l'édition == **/
	public function prepare_item()
	{
		/// Vérification des habilitations
		if( ! current_user_can( $this->Cap ) )
			wp_die( __( 'Vous n\'êtes pas autorisé à modifier ce contenu.', 'tify' ) );
	}
	
	/* = VUES = */	
	/** == Rendu == **/
	public function render()
	{
	?>	
		<div class="wrap">
			<h2><?php _e( 'Réglages', 'tify');?></h2>
			
			<form method="post" action="options.php">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php \settings_fields( $this->getMenuSlug() );?>	
						<?php \do_settings_sections( $this->getMenuSlug() );?>
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