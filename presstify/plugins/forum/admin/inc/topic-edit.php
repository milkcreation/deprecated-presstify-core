<?php
class tiFy_Forum_AdminEditTopic extends tiFy_AdminView_Edit_Form{
	/* = ARGUMENTS = */	
	private	// Référence
			$master,
			$main;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Forum_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		$this->main 	= $this->master->admin->topic;

		// Définition de la classe parente
		parent::__construct( 
			array(
				'screen' => $this->master->hookname['topic']
			),
			$this->master->db->topic
		);
		
		// 
		$this->item_request = 'topic_id';
	}
		
	/* = VUES = */
	/** == Formulaire d'édition == **/
	public function form(){
	?>
		<input type="text" id="title" name="topic_title" value="<?php echo $this->item->topic_title;?>" placeholder="<?php _e( 'Intitulé du sujet', 'tify' );?>">						
		<?php 
			tify_control_text_remaining( 
				array( 
					'id' 	=> 'content', 
					'name' 	=> 'topic_excerpt', 
					'value' => $this->item->topic_excerpt, 
					'attrs' => array( 
						'placeholder' => __( 'Brève description du sujet de forum', 'tify' )
					) 
				) 
			);
	}
}