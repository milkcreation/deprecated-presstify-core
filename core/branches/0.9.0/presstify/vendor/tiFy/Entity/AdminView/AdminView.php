<?php
namespace tiFy\Entity\AdminView;

class AdminView{
	/* = ARGUMENTS = */
	protected	// OPTIONS
				$EntityID,
				$Entity,
				$Db;
			
	public	// CONFIGURATION DES ENVIRONEMENTS
			/// Accroche de menu parent
			$parent_slug		= array(),
			/// Accroche de menu
			$menu_slug			= array(),
			/// Classes de rappel
			$list_table,
			$edit_form,
			$import,

			// PARAMETRES D'ENVIRONEMENTS
			/// Url du menu
			$menu_page_url		= array(),
			/// Accroche de la page
			$hookname			= array(),
			// Object Ecran
			$screen				= array(),

			// PARAMETRES COURANT
			// Vue courante
			$current_view;	

	/* = CONSTRUCTEUR = */
	public function __construct( $entity_id, $args = array() )
	{
		// Définition des options
		global $tiFy;			
		$this->EntityID = $entity_id;		
		$this->Entity 	= $tiFy->Kernel->getEntity( $this->EntityID );
		
		// Définition de la configuration des environnements
		$allowed_env = array( 'list_table', 'edit_form', 'import' );
		foreach( $allowed_env as $env )
			if( isset( $args[$env] ) ) :
				if( isset( $args[$env]['parent_slug'] ) )
					$this->parent_slug[$env] = $args[$env]['parent_slug'];
				
				if( isset( $args[$env]['menu_slug'] ) )
					$this->menu_slug[$env] = $args[$env]['menu_slug'];
				
				$this->{$env} = ( isset( $args[$env]['callback'] ) ) ? $args[$env]['callback'] : true;
			endif;	

		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, '_admin_menu' ), 9999 );
		add_action( 'admin_init', array( $this, '_admin_init' ) );
		add_action( 'current_screen', array( $this, '_current_screen' ) );
	}
		
	/* = ACTIONS ET FILTRES WORDPRESS (privée) = */
	/** == Menu d'administration == **/
	final public function _admin_menu()
	{
		
	}
	
	/** == Initialisation de l'interface d'administration (privée) == **/
	final public function _admin_init()
	{
		// Définition de la vue courante
		$this->_currentView();
		// Instanciation des contrôleurs
		/// Liste des éléments
		if( $this->list_table ) :
			if( ! $this->list_table instanceof ListTable )
				$this->list_table = new ListTable;
			// Définition des paramètres d'environnement
			$this->_setViewEnv( 'list_table' );
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->list_table, '_admin_init' ) )
				call_user_func( array( $this->list_table, '_admin_init' ) );
		endif;
		
		/// Formulaire d'édition d'un élément
		if( $this->edit_form ) :
			if( ! $this->edit_form instanceof EditForm ) 
				$this->edit_form = new EditForm;
			// Définition des paramètres d'environnement
			$this->_setViewEnv( 'edit_form' );
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->edit_form, '_admin_init' ) )
				call_user_func( array( $this->edit_form, '_admin_init' ) );
		endif;
		
		/// Formulaire d'import
		if( $this->import ) :
			if( ! $this->import instanceof Import ) 
				$this->import = new Import;
			// Définition des paramètres d'environnement
			$this->_setViewEnv( 'import' );
			// Déclenchement de l'action dans les classes de rappel d'environnement
			if( method_exists( $this->import, '_admin_init' ) )
				call_user_func( array( $this->import, '_admin_init' ) );
		endif;				
	}
	
	/** == Chargement de l'écran courant (privée) == **/
	final public function _current_screen( $current_screen )
	{
		if( ! isset( $this->screen[$this->current_view] ) || ( $current_screen->id !== $this->screen[$this->current_view]->id ) )
			return;
		
		// Mise en file des scripts de l'ecran courant
		add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
			
		// Déclenchement de l'action dans la classe de rappel d'environnement			
		if( method_exists( $this->{$this->current_view}, '_current_screen' ) )
			call_user_func( array( $this->{$this->current_view}, '_current_screen' ), $current_screen );
	}
	
	/** == Mise en file des scripts de l'interface d'administration (privée) == **/
	final public function _admin_enqueue_scripts()
	{			
		// Déclenchement de l'action dans la classe de rappel d'environnement	
		if( method_exists( $this->{$this->current_view}, '_admin_enqueue_scripts' ) )
			call_user_func( array( $this->{$this->current_view}, '_admin_enqueue_scripts' ) );
	}
	
	/* = PARAMETRAGE = */
	/** == Définition du gestionnaire de la table de base de données == **/
	public function setDb( $Db ){
		if( ! $Db instanceof tiFy\Entity\Db\Db )
			$this->Db = new $this->Entity->DbClassProxy( $this->EntityID );
	}
	
	/** == Définition du gestionnaire de la table de base de données == **/
	public function getDb(){
		if( ! $this->Db instanceof tiFy\Entity\Db\Db )
			$this->Db = new $this->Entity->DbClassProxy( $this->EntityID );
		
		return $this->Db;
	}
		
	/** == Récupération de la vue courante == **/
	private function _currentView()
	{
		if( $this->current_view )
			return $this->current_view;
		
		return $this->current_view = isset( $_REQUEST['tyadmvw'] ) ? $_REQUEST['tyadmvw'] : 'list_table';
	}
	
	/** == Définition des paramètres d'environnement == **/
	private function _setViewEnv( $view )
	{
		// Bypass	
		if( ! $this->{$view} )
			return;
		
		// Définition des paramètres d'environement	
		$this->menu_page_url[$view] = menu_page_url( $this->menu_slug[$view], false );
		$this->hookname[$view] 		= get_plugin_page_hookname( $this->menu_slug[$view], $this->parent_slug[$view] );
		$this->screen[$view] 		= convert_to_screen( $this->hookname[$view] );
		$this->base_url[$view] 		= esc_attr( add_query_arg( array( 'tyadmvw' => $view ), $this->menu_page_url[$view] ) );

		// Passage des options à la classe de la vue
		$this->{$view}->table		= $this->getDb();
		$this->{$view}->table_id 	= $this->Db->ID;
		$this->{$view}->table_name 	= $this->Db->Name;
		$this->{$view}->primary_key = $this->Db->Primary;
		$this->{$view}->parent_slug	= $this->parent_slug[$view];
		$this->{$view}->menu_slug	= $this->menu_slug[$view];
		$this->{$view}->hookname	= $this->hookname[$view];	
		$this->{$view}->screen 		= $this->screen[$view];
		$this->{$view}->base_url 	= $this->base_url[$view];
	}	
	
	/* = AFFICHAGE = */
	/** == Point d'entrée == **/
	public function render()
	{
		switch( $this->current_view ) :
			default :
				$this->render_list_table();
				break;
			case 'edit_form' :
				$this->render_edit_form();
				break;
			case 'import' :
				$this->render_import();
				break;	
		endswitch;		
	}
	
	/** == Liste des éléments == **/
	public function render_list_table()
	{
		$env = 'list_table';
		$this->{$env}->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php echo $this->Entity->getLabel( 'all_items' );?>
				<?php if( isset( $this->base_url['edit_form'] ) ) : ?>
				<a class="add-new-h2" href="<?php echo $this->base_url['edit_form'];?>"><?php echo $this->Entity->getLabel( 'add_new' );?></a>
				<?php endif;?>
				<?php if( isset( $this->base_url['import'] ) ) : ?>
				<a class="add-new-h2" href="<?php echo $this->base_url['import'];?>"><?php _e( 'Import d\'élément', 'tify' );?></a>
				<?php endif;?>
			</h2>
			<?php $this->{$env}->notifications();?>
			<?php $this->{$env}->views(); ?>
			<form method="post" action="<?php echo $this->{$env}->base_url;?>">
				<?php $this->{$env}->search_box( $this->Entity->getLabel( 'search_items' ), 'tify_wacrm_partner' );?>
				<?php $this->{$env}->display();?>
	        </form>
		</div>
	<?php
	}
	
	/** == Fomulaire d'édition d'un élément == **/
	public function render_edit_form()
	{
		$env = 'edit_form';
		$this->{$env}->prepare_item();
	?>
		<div class="wrap">
			<h2>
				<?php echo $this->Entity->getLabel( 'edit_item' );?>
				<?php if( isset( $this->base_url['edit_form'] ) ) : ?>
				<a class="add-new-h2" href="<?php echo $this->base_url['edit_form'];?>"><?php echo $this->Entity->getLabel( 'new_item' );?></a>
				<?php endif;?>
			</h2>
			<?php $this->{$env}->notifications();?>
			
			<form method="post">
			<?php $this->{$env}->hidden_fields();?>
			<?php $this->{$env}->display();?>
			</form>
		</div>
	<?php
	}
	
	/** == Fomulaire d'import == **/
	public function render_import()
	{
		$env = 'import';
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Import d\'éléments', 'tify' );?>
				<?php if( ! empty( $this->{$env}->sample ) ) :?>
				<a id="tify_adminview_import-download_sample" class="add-new-h2" href="<?php echo esc_url( add_query_arg( array( 'action' => 'tify_adminview_import_download_sample_'. $this->EntityID ), admin_url( 'admin-ajax.php') ) );?>">
					<?php _e( 'Fichier d\'exemple', 'tify' );?>
				</a>
				<?php endif;?>
			</h2>
			<div style="margin-right:300px; margin-top:20px;">
				<div style="float:left; width: 100%;">					
					<div id="tify_adminview_import-table_preview">
					<?php $this->{$env}->display_table_preview();?>						
					</div>				
				</div>
				<div id="side-sortables" style="margin-right:-300px; width: 280px; float:right;">
					<div id="submitdiv" class="tify_submitdiv">
						<h3 class="hndle">
							<span><?php _e( 'Enregistrer', 'tify' );?></span>
						</h3>
						<div class="inside">
							<div class="minor_actions">
								<?php $this->{$env}->display_form_upload();?>
							</div>	
							<div class="major_actions">
								<div id="tify_adminview_import-options_form">
									<?php $this->{$env}->display_form_import_options();?>
								</div>
							</div>	
						</div>
					</div>					
				</div>
			</div>			
		</div>
	<?php
	}
}