<?php
namespace tiFy\Options;

use tiFy\Environment\App;

class Bootstrap extends App
{
	/* = ARGUMENTS = */
	public 	// Configuration
			$page_title,
			$menu_title,
			$admin_bar_title,
			$menu_slug,
			$hookname,
			
			// Paramètres
			$nodes = array(),
			$options;
	
	// 
	protected $Hookname;
		
	// Sections de boîte à onglets déclarées
	protected $Nodes					= array();
	
	// Habilitation d'accès à l'interface d'administration des options de PresstiFy
	protected $Cap						= 'manage_options';
	
	// Actions à déclencher
	protected $CallActions				= array(
		'after_setup_theme',
		'wp_loaded',
		'admin_init', 
		'admin_menu',
		'admin_bar_menu'
	);
	
	// Ordre de priorité d'exécution des actions valide
	protected $CallActionsPriorityMap	= array(
		'wp_loaded' => 9
	);		
	
	/* = DECLENCHEMENT DES ACTIONS = */
	/** == Après le chargement du thème == **/	
	protected function after_setup_theme()
	{
		// Traitement des paramètres		
		/// Déclaration des sections de boîtes à onglets
		if( isset( $this->Params['options']['nodes'] ) ) :
			foreach( $this->Params['options']['nodes'] as $node_id => $attrs ) :
				$args = array();
				$args['id'] = $node_id;
				$args +=  $attrs;
				$this->registerNode( $args );
			endforeach;
		endif;
		
		// Configuration
		$this->page_title 		= __( 'Réglages des options du thème', 'tify' );
		$this->menu_title 		= get_bloginfo( 'name' );
		$this->admin_bar_title 	= false;		
		$this->menu_slug		= 'tify_options';
		$this->Hookname			= 'settings_page_tify_options';			
	}
	
	/** == Après le chargement complet == **/
	protected function wp_loaded()
	{
		do_action( 'tify_options_register_node' );	
					
		add_action( 'tify_taboox_register_box', array( $this, 'registerBox' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'registerNodes' ) );
	}
		
	/** == Menu d'administration == **/
	protected function admin_menu()
	{
		\add_options_page( $this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array( $this, 'admin_render' ) );			
	}
	
	/** == Barre d'administration == **/
	protected function admin_bar_menu( $wp_admin_bar )
	{
		// Bypass
		if( is_admin() )
			return;	
		if( ! \current_user_can( $this->Cap ) )
			return;
		
		$wp_admin_bar->add_node(
			array(
				'id' 		=> $this->menu_slug,
	    		'title'	 	=> ( $this->admin_bar_title ) ? $this->admin_bar_title : $this->page_title,
	    		'href' 		=> admin_url( "/options-general.php?page={$this->menu_slug}" ),
	   			'parent' 	=> 'site-name'
			)
		);
	}
		
	/** == Déclaration de la boîte à onglets == **/
	public function registerBox()
	{
		\tify_taboox_register_box( 
			$this->Hookname,
			'option',
			array(
				'title'		=> $this->page_title,
				'page'		=> $this->menu_slug
			)
		);
	}
	
	/** == Déclaration d'une section de boîte à onglets == **/
	public function registerNode( $args )
	{
		$this->Nodes[] = $args;
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	public function registerNodes()
	{
		foreach( (array) $this->Nodes as $node )
			\tify_taboox_register_node(
				$this->Hookname,
				$node
			);
	}
	
	/* = AFFICHAGE = */
	/** == Rendu de l'interface d'administration == **/
	public function admin_render()
	{
	?>		
		<div class="wrap">
			<h2><?php echo $this->page_title;?></h2>
			
			<form method="post" action="options.php">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php \settings_fields( $this->menu_slug );?>	
						<?php \do_settings_sections( $this->menu_slug );?>
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