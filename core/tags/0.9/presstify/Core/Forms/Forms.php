<?php
use tiFy\Environment\Core;

class tiFy_Forms extends Core
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'after_setup_tify',
		'init'
	);		
	// Ordres de priorité d'exécution des actions
	protected $CallActionsPriorityMap	= array(
		'after_setup_tify' 	=> 11,
		'init'				=> 99
	);
	
	// Configuration
	public	// Chemins 
			$dir,
			$uri,
			
			// Configuration
			$registred_addon 		= array(),
			$registred_callback		= array(),
			$registred_button 		= array(),
			$registred_error 		= array(),
			$registred_dir 			= array(),
			$registred_field_type	= array(),
			$registred_form			= array(),
			$registred_integrity	= array(),					
			
			// Contrôleurs			 
			$addons,
			$callbacks,
			$buttons,
			$datas,
			$dirs,
			$errors,
			$fields,
			$field_types,
			$functions,
			$forms,
			$handle,	 
			$integrity,
			$steps;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{		
		parent::__construct();

		global $tify_forms;

		if( ! $tify_forms )
			$tify_forms = $this;
		
		// Définition des chemins
		$this->dir 		= $this->Dirname;
		$this->uri		= $this->Url .'/';

		// Initialisation des classe de contrôle
		/// Addons
		require_once $this->dir .'/inc/class-addons.php';
		$this->addons 		= new tiFy_Forms_Addons( $this );
		/// Boutons
		require_once $this->dir .'/inc/class-buttons.php';
		$this->buttons		= new tiFy_Forms_Buttons( $this );
		/// Fonctions de rappel
		require_once $this->dir .'/inc/class-callbacks.php';
		$this->callbacks	= new tiFy_Forms_Callbacks( $this );
		/// Gestion des données
		require_once $this->dir .'/inc/class-datas.php';
		$this->datas		= new tiFy_Forms_Datas( $this );
		/// Répertoires de stockage	
		require_once $this->dir .'/inc/class-dirs.php';
		$this->dirs			= new tiFy_Forms_Dirs( $this );
		/// Gestion des erreurs
		require_once $this->dir .'/inc/class-errors.php';
		$this->errors 		= new tiFy_Forms_Errors( $this );
		/// Champs de formulaire
		require_once $this->dir .'/inc/class-fields.php';
		$this->fields 		= new tiFy_Forms_Fields( $this );
		/// Types de Champs de formulaire
		require_once $this->dir .'/inc/class-field_types.php';
		$this->field_types 	= new tiFy_Forms_FieldTypes( $this );
		/// Formulaire	
		require_once $this->dir .'/inc/class-forms.php';
		$this->forms 		= new tiFy_Forms_Forms( $this );
		/// Fonctions utiles
		require_once $this->dir .'/inc/class-functions.php';
		$this->functions	= new tiFy_Forms_Functions( $this );
		/// Traitement des formulaires
		require_once $this->dir .'/inc/class-handle.php';
		$this->handle 		= new tiFy_Forms_Handle( $this );
		/// Vérification d'intégrité
		require_once $this->dir .'/inc/class-integrity.php';
		$this->integrity 	= new tiFy_Forms_Integrity( $this );
		/// Gestion des étapes
		require_once $this->dir .'/inc/class-steps.php';
		$this->steps 		= new tiFy_Forms_Steps( $this );
	}

	/* = DECLENCHEURS = */
	/** == Initialisation l'interface d'administration == **/
	final public function after_setup_tify()
	{
		// Déclaration des addons
		do_action( 'tify_form_register_addon' );

		// Définition des répertoires de dépôts
		$this->registred_dir = array(
			'temp'		=> array(
				'dirname'	=> WP_CONTENT_DIR. '/uploads/tify_forms/temp',
				'cleaning'	=> true
			),
			'upload'		=> array(
				'dirname'	=> WP_CONTENT_DIR. '/uploads/tify_forms/upload',
			),
			'export'		=> array(
				'dirname'	=> WP_CONTENT_DIR. '/uploads/tify_forms/export',
				'cleaning'	=> 3600
			)
		);
		$this->dirs->init();
		
		// Déclaration des types de champs	
		$this->field_types->init();
		
		// Instanciation des addons
		$this->addons->init();
	}
	
	/** == Déclaration des formulaires == **/
	// !!! Déclencher au moment de l'init pour intéragir avec les balises de conditionnement wp et récupération des type de post et taxonomy
	final public function init()
	{
		foreach( (array) self::getConfig() as $ID => $attrs ) :
			if( ! isset( $attrs['ID'] ) )
				$attrs['ID'] = $ID;

			$this->register_form( $attrs );
		endforeach;
		
		do_action( 'tify_form_register' );

		if( empty( $this->registred_form ) )
			return;
		
		// Initialisation des formulaires (requis)
		$this->forms->init();

		// Traitement des formulaires en soumission (requis)
		$this->handle->proceed();
	}
	
	/* = VUES = */	
	/** == Affichage du formulaire == **/
	function display( $form_id = null, $echo = false )
	{
		if( is_null( $form_id ) )	
			$form_id = 1;
		// Traitement des options du formulaire	
		$output  = "";
		$output .= "\n<div id=\"tify_form-{$form_id}\" class=\"tify_form\">";
		$output .= $this->forms->display( $form_id, $echo );
		$output .= "\n</div>";

		return $output;
	}
	
	/* = CONTRÔLEURS = */	
	/** == Déclaration d'un formulaire == **/
	function register_form( $form = array() )
	{
		array_push( $this->registred_form, $form );
		
		return $form['ID'];
	}
}