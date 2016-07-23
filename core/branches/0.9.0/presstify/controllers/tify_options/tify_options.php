<?php
/* = HELPER = */
/** == Definition d'un boîte de saisie == 
 * USAGE :
 **/ 
function tify_options_register_node( $node ){
	global $tify_options;

	array_push( $tify_options->nodes, $node );
}

global $tify_options;
$tify_options = new tiFy_Options;
class tiFy_Options{
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
			
	
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Configuration
		$this->page_title 		=  __( 'Réglages des options du thème', 'tify' );
		$this->menu_title 		= get_bloginfo( 'name' );
		$this->admin_bar_title 	= false;		
		$this->menu_slug		= 'tify_theme_options';		
				
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'wp_admin_bar_menu' ) ); 
		
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_box', array( $this, 'tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */	
	/** == Menu d'administration == **/
	public function wp_admin_menu(){
		$this->hookname = add_options_page( $this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array( $this, 'admin_render' ) );
		
		do_action( 'tify_options_register_node' );	
	}
	
	/** == Barre d'administration == **/
	public function wp_admin_bar_menu( $wp_admin_bar ){
		// Bypass
		if( is_admin() )
			return;	
		if( ! current_user_can( 'manage_options' ) )
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
	
	/* = ACTIONS ET FILTRES PressTiFy = */
	/** == Déclaration de la boîte à onglets == **/
	public function tify_taboox_register_box(){
		tify_taboox_register_box( 
			$this->hookname,
			'option',
			array(
				'title'		=> $this->page_title,
				'page'		=> $this->menu_slug
			)
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	public function tify_taboox_register_node(){
		foreach( (array) $this->nodes as $node )
			tify_taboox_register_node(
				$this->hookname,
				$node
			);
	}
	
	/* = AFFICHAGE = */
	/** == Rendu de l'interface d'administration == **/
	public function admin_render(){
	?>		
		<div class="wrap">
			<h2><?php echo $this->page_title;?></h2>
			
			<form method="post" action="options.php">
				<div style="margin-right:300px; margin-top:20px;">
					<div style="float:left; width: 100%;">
						<?php settings_fields( $this->menu_slug );?>	
						<?php do_settings_sections( $this->menu_slug );?>
					</div>					
					<div style="margin-right:-300px; width: 280px; float:right;">
						<div id="submitdiv">
							<h3 class="hndle"><span><?php _e( 'Enregistrer', 'tify' );?></span></h3>
							<div style="padding:10px;">
								<div class="submit">
								<?php submit_button(); ?>
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