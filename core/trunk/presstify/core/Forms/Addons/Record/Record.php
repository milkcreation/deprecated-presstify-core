<?php
namespace tiFy\Core\Forms\Addons\Record;

use tiFy\Core\Forms\Addons\Factory;

class Record extends Factory
{
	/* = ARGUMENTS = */
	// Définition de l'identifiant
	public $ID = 'record';
	
	// Options de formulaire par défaut
	public $default_form_options 	= array( 
		'export' => true 
	);
	
	// Options de champs par défaut
	public $default_field_options 	= array(			
		'record'		=> true,
		'export'		=> true,
		'show_column'	=> true,
		'editable'		=> false
	);
		
	/* = CONSTRUCTEUR = */				
	public function __construct() 
	{	
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_successfully'	=> array( $this, 'cb_handle_successfully' )
		);
		
		// Schema
		/// Interface d'administration
		add_action( 
			'tify_admin_register',
			function(){
				tify_admin_register(
					'tify_forms_record',
					array(
						'Menu' 		=> array(
							'menu_title'	=> __( 'Formulaires', 'tify' ),
						  	'menu_slug'		=> 'tify_forms_record',
						  	'icon_url'		=> 'dashicons-clipboard'
						),
						'ListTable'	=> array(
						    'parent_slug'	=> 'tify_forms_record',
						    'menu_slug'		=> 'tify_forms_record',
							'cb'			=> '\tiFy\Core\Forms\Addons\Record\ListTable'
						)
					)
				);
			}
		);
		
		/// Base de données
		add_action( 
			'tify_db_register',
			function(){
				tify_db_register( 
					'tify_forms_record', 
					array(
						'install'		=> true,
						'name'			=> 'tify_forms_record',
						'columns'		=> array(
							'ID'				=> array(
							    'type'				=> 'BIGINT',
							    'size'				=> 20,
							    'unsigned'			=> true,
							    'auto_increment'	=> true,
							    'prefix'			=> false,		
							),
							'form_id'			=> array(
								'type'				=> 'VARCHAR',
								'size'				=> 255
							),
							'record_session'		=> array(
								'type'				=> 'VARCHAR',
								'size'				=> 32
							),
							'record_status'		=> array(
								'type'				=> 'VARCHAR',
								'size'				=> 32
							),
							'record_date'		=> array(
								'type'				=> 'DATETIME',
								'default'			=> '0000-00-00 00:00:00'
							)								
						),
						'keys'			=> array( 'form_id' => 'form_id' ),
						'meta'			=> true
					)
				);
			}
		);
	}

	/* = COURT-CIRCUITAGE = */
	/** == Enregistrement des données de formulaire en base == **/
	public function cb_handle_successfully( $handle )
	{			
		$datas = array(
			'form_id' 			=> $this->form()->getID(),
			'record_session' 	=> $this->form()->getSession(),	 
			'record_date' 		=> current_time( 'mysql' ),
			'item_meta'			=> $this->form()->getFieldsValues()
		);
		
		\tify_db_get( 'tify_forms_record' )->handle()->create( $datas );
	} 
		
	/* = CONTRÔLEURS = */	
	/** == Mise à jour == **/
	private function updateDB()
	{
		global $wpdb;
		
		// Mise à jour de la colonne de session
		if( version_compare( get_option('mktzr_forms_record_version', 0 ), 1506101121, '<' ) ) :
			if( ! check_column( $wpdb->mktzr_forms_records, 'record_session', 'varchar(32)' ) ) :
				$ddl = "ALTER TABLE $wpdb->mktzr_forms_records MODIFY COLUMN record_session varchar(32) NOT NULL DEFAULT '' ";
	      		$q = $wpdb->query( $ddl );
			endif;
		endif;
		
		update_option( 'mktzr_forms_record_version', $version );
	}
}
