<?php
/*
FieldType Name: reCaptcha
FieldType ID: recaptcha
Callback: tiFy_Forms_FieldType_reCaptcha
Version: 1.150817
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

/**
 * Configuration :
 	...
 	array(
		'ID' 		=> {form_id},
		'title' 	=> '{form_title}',
		'prefix' 	=> '{form_prefix}',
		'fields' 	=> array(
			...
			array(
				'slug'			=> '{field_slug}',
				'label' 		=> '{field_label}',
				'type' 			=> 'recaptcha',
			),
			...
		),
		'options' => array(
			'recaptcha' => array(
 				'sitekey' 		=> 'sitekey from recaptcha', 		// https://www.google.com/recaptcha/admin
				'secretkey' 	=> 'secretkey  from recaptcha', 	// https://www.google.com/recaptcha/admin
				'lang'			=> 'fr', 							// @see https://developers.google.com/recaptcha/docs/language					
				'theme' 		=> 'light' 							// light | dark
			)
		)
	)
	... 
 */

class tiFy_Forms_FieldType_reCaptcha extends tiFy_Forms_FieldType{
	/* = ARGUMENTS = */
	public 	$lib_path;
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition du type de champ
		$this->attrs = array(
			'slug'			=> 'recaptcha',
			'label' 		=> __( 'ReCaptcha', 'tify' ),
			'section' 		=> 'misc',
			'supports'		=> array( 'label', 'integrity-check', 'request' )
		);
				
		// Définition des fonctions de callback
		$this->callbacks = array(
			'form_set_options'				=> array( $this, 'cb_form_set_options' ),
			'field_set' 					=> array( $this, 'cb_field_set' ),
			'field_type_output_display'		=> array( $this, 'cb_field_type_output_display' ),
			'handle_check_request'			=> array( $this, 'cb_handle_check_request' )
		);
		
		parent::__construct( $master );	
		
		// Chemin vers la librairie Recaptcha
		$this->lib_path = dirname( __FILE__ ) .'/recaptcha-master/src/ReCaptcha';
	}
	
	/* = CALLBACKS = */
	/** == Définition des options de formulaire == **/
	function cb_form_set_options( &$options ){
		$_options['recaptcha'] = array(
			'sitekey'		=> false,
			'secretkey'		=> false, 	
			'lang'			=> $this->get_lang(),
			'theme' 		=> 'light'
		);
		$options['recaptcha'] = wp_parse_args( ( isset( $options['recaptcha'] ) ? $options['recaptcha'] : array() ), $_options['recaptcha'] );
	}
	
	/** == Court-circuitage des attributs de champ == **/
	function cb_field_set( &$field ){
		// Bypass
		if( $field['type'] != 'recaptcha' )
			return;
			
		$field['required'] = true;
	}
			
	/** == Affichage du champ == **/
	function cb_field_type_output_display( &$output, $field ){		
		// Bypass
		if( $field['type'] != 'recaptcha' )
			return;

		// Récupération des options
		$options = $this->master->forms->get_option( 'recaptcha' );

		// Instanciation de la librairie ReCaptcha
		$this->autoload();
		$recaptcha = new \ReCaptcha\ReCaptcha( $options['secretkey'] );
		
		// Affichage du champ ReCaptcha
		$output .= "<input type=\"hidden\" name=\"". $field['name'] ."\" value=\"-1\">";
		$output .= "<div class=\"g-recaptcha\" data-sitekey=\"{$options['sitekey']}\" data-theme=\"{$options['theme']}\"></div>";
		
		// Mise en file de la librairie JS
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
	}
	
	/** == Contrôle d'intégrité == **/
	function cb_handle_check_request( &$errors, $field ){		
		if( $field['type'] != 'recaptcha' )
			return;

		$options = $this->master->forms->get_option( 'recaptcha' );
		
		// Instanciation de la librairie reCaptcha
		$this->autoload();
		$recaptcha = new \ReCaptcha\ReCaptcha( $options['secretkey'] );
				
		if( ! $private_key = $options['secretkey'] )
			wp_die( '<h1>ERREUR DE CONFIGURATION DU FORMULAIRE</h1><p>La clef privée de ReCaptcha n\'a pas été renseignée</p>', 'tify' );
		
		$resp = $recaptcha->verify( $_POST['g-recaptcha-response'], $_SERVER["REMOTE_ADDR"] );
			
		if ( ! $resp->isSuccess() )				   
			$errors[] = __( "La saisie de la protection antispam est incorrect", 'tify' );
	}
	
	/* = CONTROLEURS = */
	/** == Chargement de la librairie ReCaptcha == **/
	function autoload(){
		require_once $this->lib_path . '/ReCaptcha.php';
		require_once $this->lib_path . '/RequestMethod.php';
		require_once $this->lib_path . '/RequestParameters.php';
		require_once $this->lib_path . '/Response.php';
		require_once $this->lib_path . '/RequestMethod/Post.php';
		require_once $this->lib_path . '/RequestMethod/Socket.php';
		require_once $this->lib_path . '/RequestMethod/SocketPost.php';
	}

	function get_lang(){
		global $locale;	

		switch( $locale ) :
			default :
				list( $lang, $indice ) = preg_split( '/_/', $locale, 2 );
				break;
			case 'zh_CN':
				$lang =  'zh-CN';
				break;
			case 'zh_TW':
				$lang =  'zh-TW';
				break;
			case 'en_GB' :
				$lang =  'en-GB';
				break;
			case 'fr_CA' :
				$lang =  'fr-CA';
				break;
			case 'de_AT' :
				$lang =  'de-AT';
				break;
			case 'de_CH' :
				$lang =  'de-CH';
				break;
			case 'pt_BR' :
				$lang =  'pt-BR';
				break;
			case 'pt_PT' :
				$lang =  'pt-PT';
				break;
			case 'es_AR' :
			case 'es_CL' :
			case 'es_CO' :
			case 'es_MX' :
			case 'es_PE' :
			case 'es_PR' :
			case 'es_VE' :
				$lang =  'es-419';
				break;
		endswitch;

		return $lang;
	}	
		
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	/** == Mise en file de la librairie JS ReCaptcha == **/
	function wp_footer(){
		static $instance;		
		
		if( ! $instance ) :
			$options = $this->master->forms->get_option( 'recaptcha' );
		?><script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $this->get_lang();?>"></script><?php
			$instance ++;			
		endif;
	}
}

/** 
 * @todo
 * 
Class tiFy_Forms_FieldType_reCaptcha{				
	public function __construct( tiFy_Forms $master ){
		if( version_compare( phpversion(), '5.3', '<' ) ) :
			require_once dirname( __FILE__ ) .'/recaptcha.v1.php';
		else :
			require_once dirname( __FILE__ ) .'/recaptcha.v2.php';
			new tiFy_Forms_FieldType_reCaptchaV2( $master );
		endif;
	}
}*/