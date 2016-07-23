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
	static $instance		= 0;
	
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
		
		$ID = $this->master->forms->get_ID();
		$instance = self::$instance;
			
		// Récupération des options
		$options = $this->master->forms->get_option( 'recaptcha' );

		// Instanciation de la librairie ReCaptcha
		$recaptcha = new \ReCaptcha\ReCaptcha( $options['secretkey'] );
				
		// Affichage du champ ReCaptcha
		$output .= "<input type=\"hidden\" name=\"". $field['name'] ."\" value=\"-1\">";
		$output .= "<div id=\"g-recaptcha-{$ID}\" class=\"g-recaptcha\" data-sitekey=\"{$options['sitekey']}\" data-theme=\"{$options['theme']}\"></div>";
		
		$wp_footer = function () use ( $ID, $options, $instance ){
			if( ! $instance ) :
			?><script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $this->get_lang();?>&onload=onloadCallback_<?php echo $ID;?>&render=explicit" async defer></script><?php
			endif;
			?><script type="text/javascript">var onloadCallback_<?php echo $ID;?> = function() { grecaptcha.render('g-recaptcha-<?php echo $ID;?>',<?php echo json_encode( $options );?> );};$( document ).on( 'tify_forms_ajax_submit',function(){ onloadCallback_<?php echo $ID;?>();});</script><?php			
		};
		self::$instance++;		
		
		// Mise en file de la librairie JS
		add_action( 'wp_footer', $wp_footer, 99 );
	}
	
	/** == Contrôle d'intégrité == **/
	function cb_handle_check_request( &$errors, $field ){		
		if( $field['type'] != 'recaptcha' )
			return;

		$options = $this->master->forms->get_option( 'recaptcha' );
		
		// Instanciation de la librairie reCaptcha
		if( ! ini_get( 'allow_url_fopen' ) ) :
			// allow_url_fopen = Off
			$recaptcha = new \ReCaptcha\ReCaptcha( $options['secretkey'], new \ReCaptcha\RequestMethod\SocketPost() );
		else :
			// allow_url_fopen = On
			$recaptcha = new \ReCaptcha\ReCaptcha( $options['secretkey'] );
		endif;
				
		if( ! $private_key = $options['secretkey'] )
			wp_die( '<h1>ERREUR DE CONFIGURATION DU FORMULAIRE</h1><p>La clef privée de ReCaptcha n\'a pas été renseignée</p>', 'tify' );
		
		$resp = $recaptcha->verify( $_POST['g-recaptcha-response'], $_SERVER["REMOTE_ADDR"] );
			
		if ( ! $resp->isSuccess() )				   
			$errors[] = __( "La saisie de la protection antispam est incorrect", 'tify' );
	}
	
	/* = CONTROLEURS = */
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
}