<?php
class tiFy_AdminView_Edit_Form{
	/* = ARGUMENTS = */
	public	// Paramètres
			/// Environnement
			$screen,								// Objet de l'écran d'affichage de l'interface
			$notifications 			= array(		// Liste des messages de notification prédéfinis
			/*	array(
					'message'			=> ''				// (string) Intitulé du message de notification
			 		'type'				=> 'error'			// (string) error (default) | warning | success - Type de notification 
					'dismissible'		=> false			// (bool) Permet de rendre une notification révocable
				) */
			),
			$hidden_fields			= array(		// Liste des champs cachés
			/*	array(
					'id'	=>	'',
					'class'	=>	'',
					'name'	=>	'',
					'value'	=>	'',
					'attrs'	=> array()
				) */
			),
			$action_prefix,							// Préfixe du processus d'execution des actions
			$actions				= array(		// Liste des actions prédéfinies
			/*	array(
					'label'				=> ''
			 		'title'				=> ''
					'single'			=> true
				) */
			),
			$lock_time,
					
			/// Elément
			$primary_key		= 'ID',				// Clef primaire d'un élément
			$item_request		= 'post_id',		// Argument de requête d'un élément
			$item_id			= 0,				// Identifiant de l'élément courant
			$item,									// Attributs de l'élément courant
			$item_default_args	= array(),			// Arguments par defaut de récupération d'élément
			$item_action_base_link,					// Base du lien d'une action sur un élément 
			
			// Formulaire
			$form_attrs			= array();			// Attribut de formulaire			
				
	protected	// Contrôleurs
				$db;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( ){
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Initialisation
		call_user_func_array( array( $this, '_init' ), func_get_args() );	
	}
	/* = DECLENCHEURS = */
	/** == Au chargement de la page courante == **/
	public function current_screen( $current_screen ){}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Chargement de la vue courante == **/
	final public function _wp_current_screen( $current_screen ){
		if( $current_screen->id !== $this->screen->id )
			return;

		// Définition de l'élément courant
		$this->item_id = isset( $_REQUEST[$this->item_request] ) ? (int) $_REQUEST[$this->item_request] : 0;
		
		// Déclenchement du processus des actions
		$this->process_bulk_action();
		
		$current_screen->base .= '-tify_admin_view-edit';
		if( $this->screen && ( $current_screen->id === $this->screen->id ) )
			call_user_func( array( $this, 'current_screen' ), $current_screen );
		
		// Actions et Filtres Wordress
		add_action( 'admin_enqueue_scripts', array( $this, '_wp_admin_enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, '_wp_admin_footer' ), 99 );
	}
	
	/** == Mise en file des scripts == **/
	final public function _wp_admin_enqueue_scripts(){
		tify_progress();
		wp_enqueue_style( 'tify_admin_view-edit', $this->uri .'/css/tify_admin_view-edit.css', array(), 151211 );
	}
	
	/** == == 
	 * @todo locker 
	 **/
	final public function _wp_admin_footer(){
		if( $this->db->has_lock_type( 'edit' ) ) :	
			?><script type="text/javascript">/* <![CDATA[ */
				( function( $ ) {
					$(document).on( 'heartbeat-send.refresh-lock', function( e, data ){	
					}).ready( function() {
						if ( typeof wp !== 'undefined' && wp.heartbeat ) {
							wp.heartbeat.interval( parseInt( <?php echo (int) $this->lock_time - 30;?> ) );
						}
					});
				})(jQuery);/* ]]> */</script><?php
		endif;
	}
	
	/* = PARAMETRAGE = */
	/** == Initialisation des paramètres == **/
	private function _init(){
		// Récupération des arguments
		$numargs 	= func_num_args();
		$args 		= ( $numargs >= 1 )? func_get_arg( 0 ) : array();		 
		$db 		= ( $numargs >= 2 )? func_get_arg( 1 ) : null;

		// Définition des arguments
		$args = wp_parse_args( 
			$args, 
			array(
				'ajax' 		=> false,
				'screen' 	=> 'tify_adminview_edit_screen',
			) 
		);
		
		// Définition de l'objet de l'écran courant
		$this->screen = convert_to_screen( $args['screen'] );
		// Définition des notifications prédéfines
		$this->notifications = array(
			'updated' 				=> array(
				'message'		=> __( 'L\'élément a été enregistré avec succès', 'tify' ),
				'type'			=> 'success',
				'dismissible'	=> true
			),
			'trashed' 				=> array(
				'message'		=> __( 'L\'élément a été placé dans la corbeille', 'tify' ),
				'type'			=> 'success'
			)
		);				
		// Définition du préfixe de processus d'execution des actions
		$this->action_prefix = get_class( $this );		
		// Instanciation des paramètres relatifs à la base de données
		$this->db = $db;
		// Clef primaire
		$this->primary_key = $this->db->primary_key;
		// Argument de requête d'un élément
		$this->item_request = $this->primary_key;		
		// Temporisation de verrouillage de saisie 
		$this->lock_time = $this->db->get_lock_time();
		// Définition du lien de base d'exécution des actions
		$this->item_action_base_link =  remove_query_arg( $this->item_request, $this->screen->parent_file );
		
		// Actions et filtres Wordpress
		add_action( 'current_screen', array( $this, '_wp_current_screen' ), 99 );
	}
	
	/** == Préparation de l'object à éditer == **/
	public function prepare_item(){
		return $this->item = $this->db->get_item_by_id( $this->item_id );
	}

	/* = CONTRÔLEURS = */
	/** == Récupération de l'action courante == **/
	public function current_action() {
		if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
			return $_REQUEST['action'];

		if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
			return $_REQUEST['action2'];

		return false;
	}
	
	/** == Récupération de donnée de la table primaire == **/
	public function get_field( $field ){
		if( ! $this->item )
			return;
		elseif( isset( $this->item->{$field} ) )
			return $this->item->{$field};
		elseif( isset( $this->db->col_prefix ) && isset( $this->item->{$this->db->col_prefix.$field} ) )
			return $this->item->{$this->db->col_prefix.$field};
	}
	
	/** == Récupération de donnée de la table primaire == **/
	public function get_meta( $meta_key ){
		if( ! $this->item )
			return;
		elseif( isset( $this->item->{$this->primary_key} ) )
			return $this->db->get_item_meta( $this->item->{$this->primary_key}, $meta_key );
	}
	
	/** == Récupération de la notification courante == **/
	public function current_notification(){
		if( ! empty( $_REQUEST['message'] ) && isset( $this->notifications[$_REQUEST['message']] ) )
			return array( wp_parse_args( $this->notifications[$_REQUEST['message']], array( 'message' => '', 'type' => 'error', 'dismissible' => false ) ) );		
	}
		
	/* = AFFICHAGE = */
	/** == Affichage des messages de notifications == **/
	public function notifications(){
		$output = "";
		if ( $notifications = $this->current_notification() )
			foreach( $notifications as $i => $n )
				$output .= "<div id=\"{$n['type']}-{$i}\" class=\"notice notice-{$n['type']}". ($n['dismissible'] ? ' is-dismissible' : '' ) ."\"><p>{$n['message']}</p></div>";
			
		echo $output;
	}
	
	/** == Champs cachés == **/
	protected function _hidden_fields(){
		wp_nonce_field( $this->action_prefix .'_'. $this->item_id ); ?>
		<input type="hidden" id="hiddenaction" name="action" value="update" />
		<input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id();?>" />
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url( wp_get_referer() ); ?>" />		
		<input type="hidden" id="<?php echo $this->item_request;?>" name="<?php echo $this->item_request;?>" value="<?php echo $this->item_id;?>" />
	<?php
		$this->hidden_fields();
	}
	
	public function hidden_fields(){}
	
	/** == Affichage de l'interface de saisie == **/
	public function display(){
	?>
		<form method="post" action="">
			<div style="margin-right:300px; margin-top:20px;">
				<div style="float:left; width: 100%;">					
					<?php $this->_hidden_fields();?>
					<?php $this->form();?>					
				</div>
				<div style="margin-right:-300px; width: 280px; float:right;">
					<?php $this->submitdiv();?>
				</div>
			</div>
		</form>
	<?php
	}
	
	/** == Affichage du formulaire d'édition == **/
	public function form(){}
	
	/** == Affichage de la boîte de soumission du formulaire == **/
	public function submitdiv(){
	?>
		<div id="submitdiv" class="tify_submitdiv">
			<h3 class="hndle">
				<span><?php _e( 'Enregistrer', 'tify' );?></span>
			</h3>
			<div class="inside">
				<div class="minor_actions">
					<?php $this->minor_actions();?>
				</div>	
				<div class="major_actions">
					<?php $this->major_actions();?>
				</div>	
			</div>
		</div>			
	<?php
	}
	
	/** == Affichage des actions secondaire de la boîte de soumission du formulaire == **/
	public function minor_actions(){}
	
	/** == Affichage des actions principale de la boîte de soumission du formulaire == **/
	public function major_actions(){
	?><div class="updating"><?php submit_button( __( 'Enregistrer les modifications', 'tify' ), 'primary', 'submit', false );?></div><?php
	}	
	
	/* = TRAITEMENT DES DONNEES  = */
	/** == Création d'un élément par défaut == **/
	protected function get_default_item_to_edit(){
		if( $this->item_id = $this->db->insert_item( wp_parse_args( $this->item_default_args, array( $this->primary_key => 0 ) ) ) )
			return $this->item = $this->db->get_item_by_id( $this->item_id );
	}	
	
	/** == Traitement des données de requete == **/
	protected function parse_postdata( $postdata ){
		return $postdata;
	}
	
	/** == Éxecution des actions == **/
	protected function process_bulk_action(){
		if( $this->current_action() && is_callable( array( $this, 'process_bulk_action_'. $this->current_action() ) ) )
			call_user_func( array( $this, 'process_bulk_action_'. $this->current_action() ) );
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) 
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), $_REQUEST['_wp_http_referer'] ) );		
	}
	
	/** == Éxecution de l'action - édition == **/
	protected function process_bulk_action_edit(){
		if( ! $this->item_id ) :				
			$this->get_default_item_to_edit();
			
			// Traitement de la redirection
			$sendback = add_query_arg( array( $this->item_request => $this->item_id ), $this->item_action_base_link );	
				
			wp_redirect( $sendback );	
			exit;
		else :
			if ( ! $this->prepare_item() )
				wp_die( __( 'Vous tentez de modifier un contenu qui n’existe pas. Peut-être a-t-il été supprimé ?!', 'tify' ) );		
			if ( ! current_user_can( 'edit_posts' ) )
				wp_die( __( 'Vous n’avez pas l’autorisation de modifier ce contenu.', 'tify' ) );
		endif;
	}
	
	/** == Éxecution de l'action - mise à jour == **/
	protected function process_bulk_action_update(){		
		check_admin_referer( $this->action_prefix .'_'. $this->item_id );
					
		$data = $this->parse_postdata( $_POST );

		$sendback = remove_query_arg( array( 'action', 'action2' ), $this->item_action_base_link );
		$sendback = add_query_arg( array( 'action' => 'edit', $this->item_request => $this->item_id ), $sendback );
		if( is_wp_error( $data ) ) :
			$sendback = add_query_arg( array( 'message' => $data->get_error_code() ), $sendback );	
		else :		 
			$this->db->insert_item( $data );			
			$sendback = add_query_arg( array( 'message' => 'updated' ), $sendback );			
		endif;
	
		wp_redirect( $sendback );
		exit;
	}
	
	/** == Éxecution de l'action - mise à la corbeille == **/
	protected function process_bulk_action_trash(){
		check_admin_referer( $this->action_prefix . $this->current_action() . $this->item_id );
			
		// Traitement de l'élément				
		/// Conservation du statut original
		if( $this->db->has_meta && ( $original_status = $this->db->get_item_var_by_id( $this->item_id, 'status' ) ) )
			$this->db->update_item_meta( $this->item_id, '_trash_meta_status', $original_status );					
		/// Modification du statut
		$this->db->update_item( $this->item_id, array( 'status' => 'trash' ) );
		
		// Traitement de la redirection
		$sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
		$sendback = add_query_arg( 'message', 'trashed', $sendback );
											
		wp_redirect( $sendback );
		exit;
	}	
}