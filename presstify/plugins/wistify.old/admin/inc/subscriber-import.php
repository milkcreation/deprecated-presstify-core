<?php
class tiFy_Wistify_Subscriber_AdminImport extends tiFy_AdminView_Import{
	/* = ARGUMENTS = */	
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */	
	public function __construct( tiFy_Wistify_Master $master ){
		// Définition des classe de référence
		$this->master 	= $master;
		
		// Instanciation de la classe parente
		parent::__construct(
			array( 
				'screen'	=> $this->master->hookname['subscriber']
			),
			$this->master->db_subscriber
		);
		
		// Paramètres du fichier d'example
		$this->sample = array(
			'name'	=> 'import-abonnes-wistify',
			'rows'	=> array(
				array( 'chimpanzee@wistify.com' ),				
				array( 'mandrill@wistify.com' ),
				array( 'marmoset@wistify.com' ),
				array( 'bonobo@wistify.com' )
			)
		);
		
		// Paramètres d'import des données
		$this->column_map = array(
			'subscriber_email'	=> array(
		 		'title'				=> __( 'Email' ),		 		
		 		'integrity_cb'		=> 'is_email',
		 		'format_cb'			=> 'trim',
		 		'single'			=> true				
			)		
		);
		//$this->header = true;
		//$upload_dir = wp_upload_dir(); $this->filename = $upload_dir['basedir'].'/import-abonnes-exemple.txt';
	}
	
	/* = TRAITEMENT = */	
	/** == Traitement des données avant insertion == **/
	public function parse_importdata( $item ){
		if( ! isset( $item->subscriber_uid ) )
			$item->subscriber_uid = tify_generate_token();
		if( ! isset( $item->subscriber_date ) )
			$item->subscriber_date = current_time( 'mysql' );
		if( ! isset( $item->subscriber_status ) )
			$item->subscriber_status = 'registred';
		
		return $item;
	}
	
	/** == Post-traitement de l'import de données d'une ligne == **/
	public function postprocess_importdata( $item_id ){
		// Relation Abonné / Liste de diffusion		
		$list_id = isset( $this->import_options['list_id'] ) ? $this->import_options['list_id'] : 0;
		
		// Suppression des abonnés importés de la liste orpheline
		if( $list_id )
			$this->master->db_list_rel->delete_subscriber_for_list( $item_id, 0 );
		
		// Ajout des abonnés dans la liste de destination			
		$this->master->db_list_rel->insert_subscriber_for_list( $item_id, $list_id, 1 );
		
		return $item_id;
	}
		
	/* = AFFICHAGE = */
	/*** === Affichage des options d'import == **/
	public function display_import_options(){
	?>
	<ul>
		<li>
			<label>
				<?php _e( 'Liste de diffusion', 'tify' );?>
			</label>
			<?php wistify_mailing_lists_dropdown( 
						array(
							'orderby' 			=> 'title',
							'show_option_none' 	=> __( 'Aucune', 'tify' ),
							'option_none_value' => 0
						)
					);?>
		</li>
	</ul>
	<?php
	}	
}