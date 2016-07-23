<?php
class tiFy_WebAgencyCRM_AdminEditTask extends tiFy_AdminView_EditForm{
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
		<input type="hidden" name="author" value="<?php echo $this->item->task_author;?>" />
		<input type="hidden" name="date" value="<?php echo $this->item->task_date;?>" />
		<input type="hidden" name="date_gmt" value="<?php echo $this->item->task_date_gmt;?>" />
	<?php
	}
	
	/** == Formulaire d'édition == **/
	public function form(){
	?>
		<input type="text" id="title" name="title" value="<?php echo $this->item->task_title;?>" placeholder="<?php _e( 'Intitulé de la tâche', 'tify' );?>">						
		<?php 
			tify_control_text_remaining( 
				array( 
					'id' 	=> 'content', 
					'name' 	=> 'content', 
					'value' => $this->item->task_content, 
					'attrs' => array( 
						'placeholder' => __( 'Description de la tâche', 'tify' )
					) 
				) 
			);?>
			<table class="form-table">
				<tbody>
					<tr>
						<th></th>
						<td>
						
						</td>
					</tr>
				</tbody>
			</table>
			
	<?php	
	}
	
	/* = TRAITEMENT DES DONNEES = */
	public function parse_postdata( $data ){
		if( $data['title'] )
			$data['title'] = wp_unslash( $data['title'] );
		if( $data['content'] )
			$data['content'] = wp_unslash( $data['content'] );
		if( empty( $data['date'] ) || ( $data['date'] === '0000-00-00 00:00:00' ) )
			$data['date'] = current_time( 'mysql', false );
		if( empty( $data['date_gmt'] ) || ( $data['date_gmt'] === '0000-00-00 00:00:00' ) )
			$data['date_gmt'] = current_time( 'mysql', true );
		if( $data['date'] !== '0000-00-00 00:00:00' ) :
			$data['modified'] = current_time( 'mysql', false );
			$data['modified_gmt'] = current_time( 'mysql', true );
			$data['item_meta']['_edit_last'] = $data['user_ID'];
		endif;

		return $data;
	}
}