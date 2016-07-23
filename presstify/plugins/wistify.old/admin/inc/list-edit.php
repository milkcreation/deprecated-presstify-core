<?php
class tiFy_Wistify_List_AdminEdit extends tiFy_AdminView_Edit_Form{
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->list;
		
		// Définition de la classe parente
		parent::__construct( 
			array(
				'screen' => $this->master->hookname['list']
			),
			$this->master->db_list
		);
		
		// Paramètrages
		/// Environnement
		$this->item_action_base_link = add_query_arg( array( 'page' => $this->master->menu_slug['list'], 'view' => 'edit_form' ), admin_url( '/admin.php' ) );
		
		/// Argument par défaut d'un élément
		$this->item_default_args = array(
			'list_uid' 		=> tify_generate_token(), 
			'list_status' 	=> 'publish'
		);
		
		/// Notifications
		$this->notifications = array(
			'updated' 						=> array(
				'message'		=> __( 'La liste de diffusion a été enregistrée avec succès', 'tify' ),
				'type'			=> 'success',
				'dismissible'	=> true
			),
			'existing_list' 				=> array(
				'message'		=> __( 'Il y a déjà une autre liste de diffusion pourtant le même nom', 'tify' ),
				'type'			=> 'error'
			)
		);
	}
		
	/* = AFFICHAGE = */
	/** == Champs cachés == **/
	public function hidden_fields(){
	?>		
		<input type="hidden" id="list_id" name="list_id" value="<?php echo esc_attr( $this->item->list_id );?>" />
		<input type="hidden" id="list_uid" name="list_uid" value="<?php echo esc_attr( $this->item->list_uid );?>" />
		<input type="hidden" id="list_date" name="list_date" value="<?php echo esc_attr( $this->item->list_date );?>" />
		<input type="hidden" id="list_status" name="list_status" value="<?php echo esc_attr( $this->item->list_status );?>" />
	<?php
	}
	
	/** == Formulaire d'édition == **/
	public function form(){
	?>
		<input type="text" id="title" name="list_title" value="<?php echo $this->item->list_title;?>" placeholder="<?php _e( 'Intitulé de la liste de diffusion', 'tify' );?>">						
		<?php tify_control_text_remaining( array( 'id' => 'content', 'name' => 'list_description', 'value' => $this->item->list_description, 'attrs' => array( 'placeholder' => __( 'Brève description de la liste de diffusion', 'tify' ) ) ) );
	}
	
	/** == Affichage des actions secondaire de la boîte de soumission du formulaire == **/
	public function minor_actions(){
	?>
		<div class="access" style="padding:10px 0;">
			<strong><?php _e( 'Publique :', 'tify' );?></strong>
				<?php 
				tify_control_switch( 
					array(
						'name'				=> 'list_public',
						'value_on'			=> 1,
						'value_off'			=> 0,
						'checked' 			=> (int) $this->item->list_public
					)
				);
				?>
			<em style="color:#AAA; display:block;margin-top:5px;"><?php _e( 'Si une liste n\'est pas déclarée publique elle sera considérée comme privée. <br>Ce qui signifie que les abonnés n\'auront alors pas la possibilité de se désinscrire directement.<br>Une demande de désinscription sera soumise à validation du modérateur des listes de diffusion privées', 'tify' );?></em>
		</div>
	<?php
	}
	
	/** == Affichage des actions principale de la boîte de soumission du formulaire == **/
	public function major_actions(){
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
									'wistify_mailing_list_trash_'. $this->item->{$this->db->primary_key} 
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
	/** == Traitement des données de requete == **/
	function parse_postdata( $data ){
		// Identifiant
		if( ! empty( $data['list_id'] ) )
			 $data['list_id'] = (int) $data['list_id'];
		
		// Vérification
		if( ! empty( $data['list_title'] ) && $this->db->get_item_var( 'title', array( 'exclude' => $data['list_id'], 'title' => $data['list_title'] ) ) ) 
			return new WP_Error( 'existing_list' ); 		
		
		// Token
		if( empty( $data['list_uid'] ) )
			 $data['list_uid'] = tify_generate_token();	 
		// Date
		if( empty( $data['list_date'] ) || ( $data['list_date'] === '0000-00-00 00:00:00' ) )
			$data['list_date'] = current_time( 'mysql' );
		// Date de modification
		if( $data['list_date'] !== '0000-00-00 00:00:00' ) :
			$data['list_modified'] = current_time( 'mysql', false );
			$data['item_meta']['_edit_last'] = $data['user_ID'];
		endif; 
		// Status
		if( ! empty( $data['list_title'] ) && ( $data['list_status'] === 'auto-draft' ) )
			 $data['list_status'] = 'publish';
		if( ! isset( $data['list_public'] ) )
			 $data['list_public'] = 1; 
		
		return $data;
	}	
}