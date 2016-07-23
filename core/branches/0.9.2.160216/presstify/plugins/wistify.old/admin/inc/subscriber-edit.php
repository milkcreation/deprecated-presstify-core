<?php
class tiFy_Wistify_Subscriber_AdminEdit extends tiFy_AdminView_Edit_Form{
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->subscriber;
		
		// Définition de la classe parente
		parent::__construct( 
			array(
				'screen' => $this->master->hookname['subscriber']
			),
			$this->master->db_subscriber
		);
		
		// Paramètrages
		/// Environnement
		$this->item_action_base_link = add_query_arg( array( 'page' => $this->master->menu_slug['subscriber'], 'view' => 'edit_form' ), admin_url( '/admin.php' ) );
		
		/// Argument par défaut d'un élément
		$this->item_default_args = array( 
			'subscriber_uid' 	=> tify_generate_token(), 
			'subscriber_status' => 'registred' 
		);
		
		/// Notifications
		$this->notifications = array(
			'updated' 				=> array(
				'message'		=> __( 'L\'abonné a été enregistré avec succès', 'tify' ),
				'type'			=> 'success',
				'dismissible'	=> true
			),
			'empty_email'			=> array(
				'message'		=> __( 'L\'adresse email de l\'abonné doit être renseignée', 'tify' ),
				'type'			=> 'error'
			),
			'invalid_format_email'	=> array(
				'message'		=> __( 'Le format de l\'adresse email n\'est pas valide', 'tify' ),
				'type'			=> 'error'
			),
			'existing_email'	=> array(
				'message'		=> __( 'Cet email est déjà utilisé pour un autre abonné', 'tify' ),
				'type'			=> 'error'
			)
		);
	}
	
	/* = AFFICHAGE = */
	/** == Champs cachés == **/
	function hidden_fields(){
	?>	
		<input type="hidden" id="subscriber_id" name="subscriber_id" value="<?php echo esc_attr(  $this->item->subscriber_id );?>" />
		<input type="hidden" id="subscriber_uid" name="subscriber_uid" value="<?php echo esc_attr(  $this->item->subscriber_uid );?>" />
		<input type="hidden" id="subscriber_date" name="subscriber_date" value="<?php echo esc_attr( $this->item->subscriber_date );?>" />
	<?php
	}
	
	/** == Formulaire d'édition == **/
	function form(){
		$suscriber_list = $this->master->db_list->get_subscriber_list_ids( $this->item->subscriber_id );
	?>
		<input type="text" id="email" name="subscriber_email" value="<?php echo $this->item->subscriber_email;?>" placeholder="<?php _e( 'Adresse email de l\'abonné', 'tify' );?>">
			
		<h3><?php _e( 'Listes de diffusion', 'tify' );?></h3>
		<?php if( $this->master->db_list_rel->is_orphan( $this->item->subscriber_id ) ) :?>
		<div style="color:red;"><?php _e( 'Cet abonné est actuellement affilié à la liste des orphelins (inscription sans liste).', 'tify' );?></div>
		<?php endif;?>
		<ul id="mailing-lists">
		<?php foreach( (array) $this->master->db_list->get_items( array( 'orderby' => 'title', 'order' => 'ASC', 'status' => 'publish' ) ) as $l ) : $checked = in_array( $l->list_id, $suscriber_list ) ? true : false; ?>
			<li>
				<label>
					<input type="checkbox" name="subscriber_list[]" value="<?php echo $l->list_id;?>" <?php checked( $checked );?>/>
					<span class="title"><?php echo $l->list_title;?></span>
					<span class="description"><?php echo nl2br( $l->list_description );?></span>
					<span class="numbers"><?php echo $this->db->count_items( array( 'list_id' => $l->list_id, 'status' => 'registred', 'active' => 1 ) );?></span>
				</label>
			</li>
		<?php endforeach;?>
		</ul>
	<?php
	}
	
	/** == Affichage des actions principales de la boîte de soumission == **/
	function major_actions(){
	?>
		<div class="deleting">			
			<a href="<?php echo wp_nonce_url( 
		        					add_query_arg( 
	        							array( 
	        								'page' 				=> $_REQUEST['page'], 
	        								'action' 			=> 'trash', 
	        								$this->item_request	=> $this->item->{$this->db->primary_key}
										),
										admin_url( 'admin.php' ) 
									),
									'wistify_subscriber_trash_'. $this->item->{$this->db->primary_key} 
							);?>" title="<?php _e( 'Mise à la corbeille de l\'élément', 'tify' );?>">
				<?php _e( 'Déplacer dans la corbeille', 'tify' );?>
			</a>
		</div>	
		<div class="publishing">
			<?php submit_button( __( 'Sauver les modifications', 'tify' ), 'primary', 'submit', false ); ?>
		</div>
	<?php
	}
	
	/* = TRAITEMENT DES DONNÉES = */	
	/** == Translation des données de formulaire == **/
	public function parse_postdata( $data ){
		// Vérification
		if( empty( $data['subscriber_email'] ) ) 
			return new WP_Error( 'empty_email' );
		elseif( ! is_email( $data['subscriber_email'] ) )
			return new WP_Error( 'invalid_format_email' );	
		elseif( $this->db->email_exists( $data['subscriber_email'], $data['subscriber_id'] ) )
			return new WP_Error( 'existing_email' );	
		
		// Identifiant
		if( ! empty( $data['subscriber_id'] ) )
			 $data['subscriber_id'] = (int) $data['subscriber_id'];
		// Token
		if( empty( $data['subscriber_uid'] ) )
			 $data['subscriber_uid'] = tify_generate_token();
		// Date
		if( empty( $data['subscriber_date'] ) || ( $data['subscriber_date'] === '0000-00-00 00:00:00' ) )
			$data['subscriber_date'] = current_time( 'mysql' );
		// Date de modification
		if( $data['subscriber_date'] !== '0000-00-00 00:00:00' ) :
			$data['subscriber_modified'] = current_time( 'mysql', false );
			$data['item_meta']['_edit_last'] = $data['user_ID'];
		endif;
		
		return $data;
	}
	
	/** == Éxecution de l'action - mise à jour == **/
	protected function process_bulk_action_update(){
		$sendback = remove_query_arg( array( 'action', 'error', 'notice', 'message' ), wp_get_referer() );			
		$sendback = add_query_arg( array( 'action' => 'edit' ), $sendback );

		$data = $this->parse_postdata( $_POST );
		if( is_wp_error( $data ) ) :
			$sendback = add_query_arg( array( 'message' => $data->get_error_code() ), $sendback );	
		else :
			// Enregistrement de l'abonné	 
			$subscriber_id = $this->db->insert_item( $data );					
			// Enregistrement des liaisons abonné/liste
			/// Récupération des listes liées à l'abonné
			$original_lists = $this->master->db_list->get_subscriber_list_ids( $subscriber_id );	
			/// Mise à jour des listes liées à l'abonné
			$update_lists = ! empty( $_REQUEST['subscriber_list'] ) ? $_REQUEST['subscriber_list'] : array();
			
			/// Inscription/Désinscription à la liste orpheline (inscription sans liste)
			if( empty( $update_lists ) )
				$this->master->db_list_rel->insert_subscriber_for_list( (int) $subscriber_id, 0, 1 );
			else
				$this->master->db_list_rel->delete_subscriber_for_list( (int) $subscriber_id, 0 );
							
			//// Suppression des anciennes listes
			foreach( array_diff( $original_lists, $update_lists ) as $list_id )
				 $this->master->db_list_rel->delete_subscriber_for_list( (int) $subscriber_id, (int) $list_id );
			//// Ajout des nouvelles listes
			foreach( (array) $update_lists as $list_id )
				$this->master->db_list_rel->insert_subscriber_for_list( (int) $subscriber_id, (int) $list_id, 1 );
				
			$sendback = add_query_arg( array( 'message' => 'updated' ), $sendback );				
		endif;
		
		wp_redirect( $sendback );
		exit;
	}
}