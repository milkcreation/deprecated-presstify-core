<?php
require_once( dirname( __FILE__ ). '/inc/list_table.php' );
require_once( dirname( __FILE__ ). '/inc/edit_form.php' );

class tiFy_AdminView{
	/* = ARGUMENTS = */
	public	// OPTIONS
			$query,
			$args,
			
			// CONFIGURATION
			/// Vues actives
			$list				= true,
			$edit				= true,
			/// Objet vues
			$list_table,
			$edit_form,
			/// Éléments de menu
			$parent_slug,
			$menu_slug,
			
			// PARAMETRES
			/// Url du menu
			$menu_page_url,
			/// Accroche de la page
			$hookname,
			// Object Ecran
			$screen,
			// Vue courante
			$current_view;	
	
	/* = CONSTRUCTEUR = */
	public function __construct( $query = null, $args = array() ){
		// Conservation des options
		$this->query	= $query;
		$this->args 	= $args;
			
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, '_wp_admin_menu' ), 9999 );
		add_action( 'admin_init', array( $this, '_wp_admin_init' ) );
		add_action( 'current_screen', array( $this, '_wp_current_screen' ) );	
	}
		
	/* = ACTIONS ET FILTRES WORDPRESS (privée) = */
	/** == Menu d'administration == **/
	final public function _wp_admin_menu(){
		 // Configuration	
		extract( $this->args );
		$allowed_config = array( 'list', 'edit', 'list_table', 'edit_form', 'parent_slug', 'menu_slug' );
		foreach( $allowed_config as $config )
			if( isset( ${$config} ) )
				$this->{$config} = ${$config};	
			
		// Définition des paramètre de l'écran	
		$this->menu_page_url 	= menu_page_url( $this->menu_slug, false );
		$this->hookname 		= get_plugin_page_hookname( $this->menu_slug, $this->parent_slug );
		$this->screen 			= convert_to_screen( $this->hookname );
		
		// Définition de la vue courante
		$this->current_view();
	}
	
	/** == Initialisation de l'interface d'administration == **/
	final public function _wp_admin_init(){
		// Instanciation des contrôleurs
		/// Liste des éléments
		if( $this->list ) :
			if( ! $this->list_table instanceof tiFy_AdminView_ListTable ) 
				$this->list_table	= new tiFy_AdminView_ListTable( $this->query );
			// Définition de la paramètres de configuration par défaut
			$this->list_table->base_url = esc_attr( add_query_arg( array( 'tyadmvw' => 'list_table' ),$this->menu_page_url ) );
		endif;
		/// Formulaire d'édition d'un élément
		if( $this->edit ) :
			if( ! $this->edit_form instanceof tiFy_AdminView_EditForm ) 
				$this->edit_form 	= new tiFy_AdminView_EditForm( $this->query );
			// Définition de la paramètres de configuration par défaut
			$this->edit_form->base_url = esc_attr( add_query_arg( array( 'tyadmvw' => 'edit_form' ),$this->menu_page_url ) );
		endif;		
	}
	
	/** == Chargement de l'écran courant == **/
	final public function _wp_current_screen( $current_screen ){
		// Bypass
		if( ! $this->screen || ( $current_screen->id !== $this->screen->id ) )
			return;
		
		if( method_exists( $this->{$this->current_view}, '_wp_current_screen' ) )
			call_user_func( array( $this->{$this->current_view}, '_wp_current_screen' ), $current_screen );
	}
	
	/* = RECUPERATION DES ARGUMENTS DE REQUETE = */
	/** == Récupération de la vue courante == **/
	private function current_view(){
		if( $this->current_view )
			return $this->current_view;
		
		return $this->current_view = isset( $_REQUEST['tyadmvw'] ) ? $_REQUEST['tyadmvw'] : 'list_table';
	}

	/* = CONTRÔLEURS = */
	/** == Liens vers la liste des éléments == **/
	private function get_list_url( $echo = true ){
		return esc_attr( add_query_arg( 'tyadmvw', 'list_table', $this->menu_page_url ) );
	}
	
	/** == Liens vers le formulaire d'édition d'un élément == **/
	private function get_edit_url(){
		return esc_attr( add_query_arg( 'tyadmvw', 'edit_form', $this->menu_page_url ) );
	}		
	
	/* = AFFICHAGE = */
	/** == Point d'entrée == **/
	public function render(){		
		switch( $this->current_view ) :
			default :
				$this->render_list_table();
				break;
			case 'edit_form' :
				$this->render_edit_form();
				break;	
		endswitch;		
	}
	
	/** == Liste des éléments == **/
	public function render_list_table(){
		$this->list_table->prepare_items(); 
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Éléments', 'tify' );?>
				<?php if( $edit_url = $this->get_edit_url() ) : ?>
				<a class="add-new-h2" href="<?php echo $edit_url;?>"><?php _e( 'Ajouter un élément', 'tify' );?></a>
				<?php endif;?>
			</h2>
			<?php $this->list_table->notifications();?>
			<?php $this->list_table->views(); ?>
			<form method="post" action="<?php echo $this->list_table->base_url;?>">
				<?php $this->list_table->search_box( __( 'Rechercher un élément', 'tify' ), 'tify_wacrm_partner' );?>
				<?php $this->list_table->display();?>
	        </form>
		</div>
	<?php
	}
	
	/** == Fomulaire d'édition d'un élément == **/
	public function render_edit_form(){
		$this->edit_form->prepare_item();
	?>
		<div class="wrap">
			<h2>
				<?php _e( 'Éditer l\'élément', 'tify' );?>
				<?php if( $edit_url = $this->get_edit_url() ) : ?>
				<a class="add-new-h2" href="<?php echo $edit_url;?>"><?php _e( 'Ajouter un élément', 'tify' );?></a>
				<?php endif;?>
			</h2>
			<?php $this->edit_form->notifications();?>
			
			<form method="post">
			<?php $this->edit_form->hidden_fields();?>
			<?php $this->edit_form->display();?>
			</form>
		</div>
	<?php
	}	
}