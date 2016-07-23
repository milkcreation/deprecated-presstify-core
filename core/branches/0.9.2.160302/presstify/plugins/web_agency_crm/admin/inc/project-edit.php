<?php
class tiFy_WebAgencyCRM_AdminEditProject extends tiFy_AdminView_EditForm{
	/* = ARGUMENTS = */	
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_WebAgencyCRM_Master $master, tiFy_Query $query ){
		// Définition des classe de référence
		$this->master 	= $master;

		// Instanciation de la classe parente
       	parent::__construct( $query );
	}
	
	/* = CHARGEMENT = */
	public function current_screen( $current_screen ){
		wp_enqueue_style( 'tify_controls-text_remaining' );
		wp_enqueue_script( 'tify_controls-text_remaining' );
		wp_enqueue_style( 'tify_wacrm-project_edit', $this->master->admin->uri .'/css/project-edit.css', array(), '151017' );
	} 
			
	/* = VUES = */
	/** == Champs cachés == **/
	public function hidden_fields(){
	?>
		<input type="hidden" name="author" value="<?php echo $this->item->project_author;?>" />
		<input type="hidden" name="date" value="<?php echo $this->item->project_date;?>" />
		<input type="hidden" name="date_gmt" value="<?php echo $this->item->project_date_gmt;?>" />
	<?php
	}
	
	/** == Formulaire d'édition == **/
	public function form(){
	?>
		<input type="text" id="title" name="title" value="<?php echo $this->item->project_title;?>" placeholder="<?php _e( 'Intitulé du projet', 'tify' );?>">						
		<?php 
			tify_control_text_remaining( 
				array( 
					'id' 	=> 'content', 
					'name' 	=> 'content', 
					'value' => $this->item->project_content, 
					'attrs' => array( 
						'placeholder' => __( 'Description du project', 'tify' )
					) 
				) 
			);?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php _e( 'Client', 'tify');?></th>
					<td>
						<?php $this->master->template->customer_dropdown( 
								array( 
									'show_option_none' 	=> __( 'Choix du commanditaire', 'tify' ),
									'orderby' 			=> 'nickname', 
									'order' 			=> 'ASC',	
									'selected' 			=> $this->item->project_customer_id 
								) 
							);?>
					</td>
				</tr>
				<?php /*<tr>
					<th><?php _e( 'Mandataire', 'tify');?></th>
					<td><?php $this->master->template->customer_dropdown( array( 'show_option_none' => __( 'Choix du mandataire', 'tify' ) ) );?></td>
				</tr> */ ?>
				<tr>
					<th><?php _e( 'Référence', 'tify');?></th>
					<td><input type="text" id="reference" name="ref" value="<?php echo $this->item->project_ref;?>" placeholder="<?php _e( 'Référence du projet', 'tify' );?>"></td>
				</tr>
			</tbody>
		</table>			
	<?php
	}
	
	/* = TRAITEMENT DES DONNEES = */
	/** == Prétraitement des données avant l'enregistrement == **/
	public function parse_postdata( $data ){
		if( $data['title'] )
			$data['title'] = wp_unslash( $data['title'] );
		if( $data['content'] )
			$data['content'] = wp_unslash( $data['content'] );
		if( empty( $data['date'] ) || ( $data['date'] === '0000-00-00 00:00:00' ) )
			$data['date'] = current_time( 'mysql', false );
		if( empty( $data['date_gmt'] ) || ( $data['date_gmt'] === '0000-00-00 00:00:00' ) )
			$data['date_gmt'] = current_time( 'mysql', true );		
		
		return $data;
	}
}