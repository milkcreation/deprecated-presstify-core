<?php
class tiFy_Forum_AdminEditContribution extends tiFy_AdminView_Edit_Form{
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Forum_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->contribution;
		
		// Définition de la classe parente
		parent::__construct( 
			array(
				'screen' => $this->master->hookname['contribution']
			),
			$this->master->db->contribution
		);
	}
	
	/* = VUES = */
	/** == Champs cachés == **/
	public function hidden_fields(){
		wp_nonce_field( $this->action_prefix .'_'. $this->item->{$this->db->primary_key} ); ?>
		<input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id();?>" />
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url( wp_get_referer() ); ?>" />		
		<input type="hidden" id="item_id" name="item_id" value="<?php echo esc_attr( $this->item->{$this->db->primary_key} );?>" />
	<?php
	}
	
	/** == Formulaire d'édition == **/
	public function form(){
	?>
		<input type="text" id="title" name="list_title" value="<?php echo $this->item->topic_title;?>" placeholder="<?php _e( 'Intitulé du sujet', 'tify' );?>">						
		<?php 
			tify_control_text_remaining( 
				array( 
					'id' 	=> 'content', 
					'name' 	=> 'contrib_content', 
					'value' => $this->item->contrib_content, 
					'attrs' => array( 
						'placeholder' => __( 'Saisissez votre texte', 'tify' )
					) 
				) 
			);
	}
		
	/** == == **/
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
									$this->action_prefix .'_'. $this->item->{$this->db->primary_key} 
							);?>" title="<?php _e( 'Mise à la corbeille de l\'élément', 'tify' );?>">
				<?php _e( 'Déplacer dans la corbeille', 'tify' );?>
			</a>
		</div>
		<div class="publishing">
			<?php submit_button( __( 'Sauver les modifications', 'tify' ), 'primary', 'submit', false ); ?>
		</div>
	<?php
	}
}