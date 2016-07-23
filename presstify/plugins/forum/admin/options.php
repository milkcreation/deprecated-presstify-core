<?php
class tiFy_Forum_AdminOptions{
	/* = ARGUMENTS = */
	public	// Configuration
			$menu_slug,
			$hookname;
			
	private // Référence
			$master;
			
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;		
		
		// Actions et Filtres PressTiFy
		add_action( 'tify_taboox_register_box', array( $this, 'tify_taboox_register_box' ) );
		add_action( 'tify_taboox_register_node', array( $this, 'tify_taboox_register_node' ) );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
		
		// Actions et Filtres Wordpress
		add_action( 'admin_menu', array( $this, 'wp_admin_menu' ) ); 
	}
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Menu d'administration == **/
	public function wp_admin_menu(){
		// Configuration
		$this->menu_slug 	= $this->master->menu_slug['options'];
		$this->hookname 	= $this->master->hookname['options'];
	}
	
	/* = ACTIONS ET FILTRES PRESSTIFY = */
	/** == Déclaration de la boîte à onglets == **/
	public function tify_taboox_register_box(){
		tify_taboox_register_box( 
			$this->hookname,
			'option',
			array(
				'title'		=> __( 'Réglages des options de forum', 'tify' ),
				'page'		=> $this->menu_slug
			)
		);
	}
	
	/** == Déclaration des sections de boîte à onglets == **/
	public function tify_taboox_register_node(){
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-forum',
				'title' 		=> __( 'Forum', 'tify' ),
				'cb'			=> 'tiFy_Forum_Options_TabooxForum',
				'order'			=> 1
			)
		);
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-topics',
				'title' 		=> __( 'Sujets', 'tify' ),
				'cb'			=> 'tiFy_Forum_Options_TabooxTopic',
				'order'			=> 2
			)
		);
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-contributors',
				'title' 		=> __( 'Contributeurs', 'tify' ),
				'cb'			=> 'tiFy_Forum_Options_TabooxContributor',
				'order'			=> 3
			)
		);
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-contribs',
				'title' 		=> __( 'Contributions', 'tify' ),
				'order'			=> 4
			)
		);
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-contribs_global',
				'parent' 		=> 'tify_forum-options-contribs',
				'title' 		=> __( 'Généralités', 'tify' ),
				'cb'			=> 'tiFy_Forum_Options_TabooxContributionGlobal',
				'order'			=> 1
			)
		);
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-contribs_mailing',
				'parent' 		=> 'tify_forum-options-contribs',
				'title' 		=> __( 'Envoi de mail', 'tify' ),
				'cb'			=> 'tiFy_Forum_Options_TabooxContributionMailing',
				'order'			=> 2
			)
		);
		tify_taboox_register_node(
			$this->hookname,
			array(
				'id' 			=> 'tify_forum-options-contribs_moderation',
				'parent' 		=> 'tify_forum-options-contribs',
				'title' 		=> __( 'Modération', 'tify' ),
				'cb'			=> 'tiFy_Forum_Options_TabooxContributionModeration',
				'order'			=> 3
			)
		);
	}
	
	/** == Déclaration des interfaces de saisie == **/
	public function tify_taboox_register_form(){
		// Forum		
		tify_taboox_register_form( 'tiFy_Forum_Options_TabooxForum', $this->master );
		// Sujets
		tify_taboox_register_form( 'tiFy_Forum_Options_TabooxTopic', $this->master );
		// Contributions	
		tify_taboox_register_form( 'tiFy_Forum_Options_TabooxContributionGlobal', $this->master );
		tify_taboox_register_form( 'tiFy_Forum_Options_TabooxContributionMailing', $this->master );
		tify_taboox_register_form( 'tiFy_Forum_Options_TabooxContributionModeration', $this->master );
		// Contributeurs
		tify_taboox_register_form( 'tiFy_Forum_Options_TabooxContributor', $this->master );
	}
	
	/* = VUES = */
	/** == Page de gestion des options == **/
	public function admin_render(){
	?>
		<div class="wrap">
			<h2><?php _e( 'Réglages des options', 'tify' ); ?></h2>
			<?php settings_errors(); ?>
			
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

/* = TABOOXES = */
/** == FORUM == **/
/*** === Options des forum === ***/
class tiFy_Forum_Options_TabooxForum extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	$data_name 	= 'page_for_tify_forum',
			$data_key 	= 'page_for_tify_forum';
			
	private	// Référence
			$master;		
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		parent::__construct();		
	}

	/* = FORMULAIRE = */
	function form(){
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Page d\'affichage des forum', 'tify' );?>
				</th>
				<td>
				<?php wp_dropdown_pages( array(
					'selected' 			=> $this->data_value,
					'name' 				=> $this->data_name,
					'show_option_none' 	=> __( 'Aucune', 'tify' ), 
					'option_none_value' => 0 
					) 
				);?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}

/** == SUJETS == **/
/*** === Options des sujets de forum === ***/
class tiFy_Forum_Options_TabooxTopic extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	$name = '';
			
	private	// Référence
			$master;		
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		parent::__construct();		
	}

	/* = FORMULAIRE = */
	function form(){
	?><?php
	}
}

/** == CONTRIBUTEURS == **/
/*** === Options des contributeurs de forum === ***/
class tiFy_Forum_Options_TabooxContributor extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$data_name 	= 'tify_forum_contributors_params',
			$data_key	= 'tify_forum_contributors_params';
			
	private	// Référence
			$master;		
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		parent::__construct();			
	}
	
	/* = Chargement de l'écran courant = */
	function current_screen( $current_screen ){		
		tify_control_enqueue( 'switch' );
		wp_enqueue_style( 'tify_forum-options' );
	}
	
	/* = Formulaire de saisie = */
	function form(){
		$params = $this->master->options->get( 'contributors_params' );
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Demander une confirmation d\'enregistrement par email aux nouveaux utilisateurs', 'tify' );?>
				</th>
				<td>
					<?php tify_control_switch( array( 'name' => 'tify_forum_contributors_params[double_optin]', 'checked' => $params['double_optin'] ) );?>	
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Le compte des nouveaux inscrits doit être activer par un modérateur', 'tify' );?>
				</th>
				<td>
					<?php tify_control_switch( array( 'name' => 'tify_forum_contributors_params[moderate_account_activation]', 'checked' => $params['moderate_account_activation'] ) );?>	
				</td>
			</tr>
		</tbody>
	</table>	
	<?php
	}
}

/** == CONTRIBUTIONS == **/
/*** === Options générales des contributions === ***/
class tiFy_Forum_Options_TabooxContributionGlobal extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$data_name 	= 'tify_forum_global_params',
			$data_key 	= 'tify_forum_global_params';
			
	private	// Référence
			$master;		
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		parent::__construct();		
	}
	
	/* = Chargement de l'écran courant = */
	function current_screen( $current_screen ){		
		tify_control_enqueue( 'switch' );
		wp_enqueue_style( 'tify_forum-options' );
	}
	
	/* = Formulaire de saisie = */
	function form(){
		$params = $this->master->options->get( 'global_params' );
		?>		
		<table class="form-table">
			<tbody>
				<?php //Renseignement sur le nom et l'email ?>
				<tr>
					<th scope="row">
						<?php _e( 'L\'auteur d’une réponse devra obligatoirement renseigner son nom et son adresse de messagerie', 'tify' );?>
					</th>
					<td><?php tify_control_switch( array( 'name' => 'tify_forum_global_params[require_name_email]', 'checked' => $params['require_name_email'] ) );?></td>
				</tr>
				<?php //Utilisateur en mode connecté ? ?>
				<tr>
					<th scope="row">
						<?php _e( 'Un utilisateur doit être enregistré et connecté pour publier des réponses', 'tify' );?>
					</th>
					<td><?php tify_control_switch( array( 'name' => 'tify_forum_global_params[contrib_registration]', 'checked' => $params['contrib_registration'] ) );?></td>
				</tr>
			</tbody>
		</table>
		<?php /*//Fil de Discussion ?>
			<?php tify_control_switch( array( 'name' => 'tify_forum_global_params[thread_contribs]', 'checked' => $params['thread_contribs'] ) );?>	
			<?php $maxdeep = (int) apply_filters( 'tify_forum_thread_contribs_depth_max', 5 );
				$thread_contribs_depth = '</label><select name="tify_forum_global_params[thread_contribs_depth]" id="thread_contribs_depth">';
			for ( $i = 2; $i <= $maxdeep; $i++ ) {
				$thread_contribs_depth .= "<option value='" . esc_attr($i) . "'";
				if ( $params['thread_contribs_depth'] == $i ) $thread_contribs_depth .= " selected='selected'";
				$thread_contribs_depth .= ">$i</option>";
			}
			$thread_contribs_depth .= '</select>';
			printf( __( 'Activer les commentaires imbriqués jusqu’à %s niveaux'), $thread_contribs_depth );
			?>
			<br />
			<br />
		<?php //Pagination ?>
			<?php tify_control_switch( array( 'name' => 'tify_forum_global_params[page_contribs]', 'checked' => $params['page_contribs'] ) );?>	
			<?php 
				$default_contribs_page = '</label><label for="default_contribs_page"><select name="tify_forum_global_params[default_contribs_page]" id="default_contribs_page"><option value="newest"';
				if ( 'newest' == $params['default_contribs_page'] ) $default_contribs_page .= ' selected="selected"';
				$default_contribs_page .= '>' . __('last') . '</option><option value="oldest"';
				if ( 'oldest' == $params['default_contribs_page'] ) $default_contribs_page .= ' selected="selected"';
				$default_contribs_page .= '>' . __('first') . '</option></select>';
				printf( __('Break comments into pages with %1$s top level comments per page and the %2$s page displayed by default'), '</label><label for="contribs_per_page"><input name="tify_forum_global_params[contribs_per_page]" type="text" id="contribs_per_page" value="' . esc_attr( $params['contribs_per_page'] ) . '" class="small-text" />', $default_contribs_page );
			?></label>
			<br />
			<br />
		<?php //Order?>	
			<?php
			$contribs_order = '<select name="tify_forum_global_params[contribs_order]" id="contribs_order"><option value="asc"';
			if ( 'asc' == $params['contribs_order'] ) $contribs_order.= ' selected="selected"';
			$contribs_order .= '>' . __('older') . '</option><option value="desc"';
			if ( 'desc' == $params['contribs_order'] ) $contribs_order .= ' selected="selected"';
			$contribs_order .= '>' . __('newer') . '</option></select>';
			printf( __( 'Comments should be displayed with the %s comments at the top of each page' ), $contribs_order );
		*/ ?>
		<?php
	}
}

/*** === Options d'envoi de mail des contributions === ***/
class tiFy_Forum_Options_TabooxContributionMailing extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$data_name = 'tify_forum_email_params',
			$data_key = 'tify_forum_email_params';
			
	private	// Référence
			$master;		
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		parent::__construct();		
	}
	
	/* = Chargement de l'écran courant = */
	function enqueue_scripts(){
		tify_control_enqueue( 'switch' );
		wp_enqueue_style( 'tify_forum-options' );
	}
	
	/* = Formulaire de saisie = */
	function form(){
		$params = $this->master->options->get( 'email_params' );
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Lorsqu\'une nouvelle contribution est publiée', 'tify' );?>
				</th>
				<td>
					<?php tify_control_switch( array( 'name' => 'tify_forum_email_params[contribs_notify]', 'checked' => $params['contribs_notify'] ) );?>	
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Lorsqu\'une contribution est en attente de modération', 'tify' );?>
				</th>
				<td>
					<?php tify_control_switch( array( 'name' => 'tify_forum_email_params[moderation_notify]', 'checked' => $params['moderation_notify'] ) );?>	
				</td>
			</tr>
		</tbody>
	</table>
	<?php		
	}
}

/*** === Options de modération des contributions === ***/
class tiFy_Forum_Options_TabooxContributionModeration extends tiFy_Taboox{
	/* = ARGUMENTS = */
	public 	// Configuration
			$data_name 	= 'tify_forum_moderation_params',
			$data_key 	= 'tify_forum_moderation_params';
			
	private	// Référence
			$master;		
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		parent::__construct( array( 'environnements' => array( 'options' ) ) );		
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	function enqueue_scripts(){
		tify_control_enqueue( 'switch' );
		wp_enqueue_style( 'tify_forum-options' );	
	}

	/* = FORMULAIRE = */
	function form( $args = array() ){
		$params = $this->master->options->get( 'moderation_params' );
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<?php _e( 'Les contributions doivent toujours être approuvées par un administrateur', 'tify' );?>
				</th>
				<td>
					<?php tify_control_switch( array( 'name' => 'tify_forum_moderation_params[contribs_moderation]', 'checked' => $params['contribs_moderation'] ) );?>	
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Approuver automatiquement les contributions des auteurs ayant déjà une contribution approuvée', 'tify' );?>
				</th>
				<td>
					<?php tify_control_switch( array( 'name' => 'tify_forum_moderation_params[contribs_whitelist]', 'checked' => $params['contribs_whitelist'] ) );?>	
				</td>
			</tr>
		</tbody>
	</table>
	<?php			
	}
}