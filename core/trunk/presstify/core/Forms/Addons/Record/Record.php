<?php
namespace tiFy\Core\Forms\Addons\Record;

use tiFy\Core\Forms\Addons\Factory;
use tiFy\Core\Db\Db;

class Record extends Factory
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'admin_init',
		'tify_admin_register',
		'tify_db_register'	
	); 
	
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
		'show_column'	=> false,
		'editable'		=> false
	);
	
	// Argument de base de données
	private static $DbAttrs = array(
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
	);
		
	/* = CONSTRUCTEUR = */				
	public function __construct() 
	{	
		parent::__construct();
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_successfully'	=> array( $this, 'cb_handle_successfully' )
		);
	}

	/* = DECLENCHEURS = */
	/** == == **/
	public function admin_init()
	{		
		new Upgrade( 'tify_core_forms_addon_record' );
	}
	
	/** == Définition de l'interface d'administration == **/
	public function tify_admin_register()
	{
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
					'cb'			=> 'tiFy\Core\Forms\Addons\Record\ListTable'
				)
			)
		);
	}
	
	/** == Définition de la base de données (admin uniquement) == **/
	public function tify_db_register()
	{
		tify_db_register( 
			'tify_forms_record', 
			self::$DbAttrs
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
		
		// Définition de la base de données (front)
		if( ! Db::has( 'tify_forms_record' ) )
			Db::register( 'tify_forms_record', self::$DbAttrs );
		
		Db::get( 'tify_forms_record' )->handle()->create( $datas );
	} 
}