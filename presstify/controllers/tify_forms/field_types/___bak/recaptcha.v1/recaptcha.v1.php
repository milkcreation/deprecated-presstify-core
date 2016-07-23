<?php
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
 				'sitekey' 		=>  '{sitekey}, 		// https://www.google.com/recaptcha/admin#whyrecaptcha
				'secretkey' 	=>  '{secretkey}, 	// https://www.google.com/recaptcha/admin#whyrecaptcha
				'lang'			=> 'fr' 						// en | nl | fr | de | pt | ru | es | tr					
				'theme' 		=> 'red' 						// red | white | blackglass | clean | custom
			)
		)
	)
	... 
 */

class tify_forms_field_recaptcha{
	public $lib_path;
	
	/**
	 * Initialisation
	 */
	function __construct( MKCF $mkcf ){
		// MKCF	
		$this->mkcf 	= $mkcf;
		
		// Chemin vers la librairie Recaptcha
		$this->lib_path = dirname( __FILE__ ) .'/assets/recaptcha-php-1.11/recaptchalib.php';
		
		// Déclaration du type de champs
		$this->set_type();		
		
		// Callbacks
		$this->mkcf->callbacks->field_type_set( 'form_set_options', 'recaptcha', array( &$this, 'form_set_options' ) );
		$this->mkcf->callbacks->field_type_set( 'form_before_output_display', 'recaptcha', array( &$this, 'form_before_output_display' ) );
		$this->mkcf->callbacks->field_type_set( 'field_set', 'recaptcha', array( &$this, 'field_set' ) );
		$this->mkcf->callbacks->field_type_set( 'field_type_output_display', 'recaptcha', array( &$this, 'field_type_output_display' ) );		
		$this->mkcf->callbacks->field_type_set( 'handle_check_request', 'recaptcha', array( &$this, 'handle_check_request' ) );
	}
	
	/**
	 * Définition des attributs du type de champ
	 */
	function set_type(){		
		$this->mkcf->fields->set_type(
			array(
				'slug'			=> 'recaptcha',
				'label' 		=> __( 'ReCaptcha', 'tify' ),
				'section' 		=> 'misc',
				'supports'		=> array( 'label', 'integrity-check', 'request' )
			)
		);
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
	
	/**
	 * Définition des options de formulaire
	 */
	function form_set_options( &$options, $mkcf ){
		$_options['recaptcha'] = array(
			'sitekey'	=> false, 	// Clé publique ( https://www.google.com/recaptcha/admin#whyrecaptcha )
			'secretkey'	=> false, 	// Requis voir ( https://www.google.com/recaptcha/admin#whyrecaptcha )
			'lang'		=> $this->get_lang(),		// Lanque du module  en | nl | fr | de | pt | ru | es | tr ( https://developers.google.com/recaptcha/docs/customization )
			'theme' 	=> 'white'		// Thème ( https://developers.google.com/recaptcha/docs/customization )
		);
		$options['recaptcha'] = wp_parse_args( ( isset( $options['recaptcha'] ) ? $options['recaptcha'] : array() ), $_options['recaptcha'] );
		if( ! in_array( $options['recaptcha']['theme'], array( 'red', 'white', 'blackglass', 'clean', 'custom' ) ) )
			$options['recaptcha']['theme'] = $_options['recaptcha']['theme'];
	}
	
	/**
	 * Préaffichage du formulaire
	 */
	function form_before_output_display( &$output, $form, $mkcf ){
		$options = $form['options']['recaptcha'];		

		$output .= "<script type=\"text/javascript\">\n";
 		$output .= "\tvar RecaptchaOptions = { \n";
		$output .= "\t\tlang: '". $options['lang'] ."',";
 		$output .= "\t\ttheme: '". $options['theme'] ."'";
		if( $options['theme'] == 'custom' )
			$output .= "\t\t, custom_theme_widget: 'recaptcha_widget-". $mkcf->forms->get_prefix() ."'";
 		$output .= "\t}\n";
 		$output .= "</script>";
	}
	
	/**
	 * Suppression de l'attribut de champ requis
	 */
	function field_set( &$field, $mkcf ){
		// Bypass
		if( $field['type'] != 'recaptcha' )
			return;		
		$field['required'] = false;
	}
			
	/**
	 * Affichage du champ
	 */
	function field_type_output_display( &$output, $field, $mkcf ){
		// Bypass
		if( $field['type'] != 'recaptcha' )
			return;
		require_once( $this->lib_path );
				
		$options = $mkcf->forms->get_option( 'recaptcha' );
	
		if( $options['theme'] == 'custom' ) :
			$_output  = "<div id=\"recaptcha_widget-". $mkcf->forms->get_prefix() ."\" style=\"display:none\">\n";
			$_output .= "\t<div id=\"recaptcha_image\"></div>\n";
			$_output .= "\t<div class=\"recaptcha_only_if_incorrect_sol\" style=\"color:red\">". __( 'Saisie incorrecte, essayez à nouveau', 'tify' ) ."</div>\n";
			$_output .= "\t\t<span class=\"recaptcha_only_if_image\">". __( 'Saisissez le texte afficher', 'tify' ) ."</span>\n";
			$_output .= "\t\t<span class=\"recaptcha_only_if_audio\">". __( 'Entrez les nombre que vous entendez', 'tify' ) ."</span>\n";
			$_output .= "\t\t<input type=\"text\" id=\"recaptcha_response_field\" name=\"recaptcha_response_field\" />\n";
			$_output .= "\t\t<div><a href=\"javascript:Recaptcha.reload()\">". __( 'Nouveau test', 'tify' ) ."</a></div>\n";
			$_output .= "\t\t<div class=\"recaptcha_only_if_image\"><a href=\"javascript:Recaptcha.switch_type('audio')\">". __( 'Test audio', 'tify' ) ."</a></div>\n";
			$_output .= "\t\t<div class=\"recaptcha_only_if_audio\"><a href=\"javascript:Recaptcha.switch_type('image')\">". __( 'Test visuel', 'tify' ) ."</a></div>\n";
			$_output .= "\t\t<div><a href=\"javascript:Recaptcha.showhelp()\">". __( 'Aide', 'tify' ) ."</a></div>\n";
			$_output .= "\t</div>\n";
			$_output .= "\t<script type=\"text/javascript\" src=\"http://www.google.com/recaptcha/api/challenge?k=". $field['options']['sitekey'] ."\"></script>\n";
			$_output .= "\t<noscript>\n";
			$_output .= "\t\t<iframe src=\"http://www.google.com/recaptcha/api/noscript?k=". $field['options']['sitekey'] ."\" height=\"300\" width=\"500\" frameborder=\"0\"></iframe><br>\n";
			$_output .= "\t\t<textarea name=\"recaptcha_challenge_field\" rows=\"3\" cols=\"40\"></textarea>\n";
			$_output .= "\t\t<input type=\"hidden\" name=\"recaptcha_response_field\" value=\"manual_challenge\">\n";
			$_output .= "\t</noscript>\n";
			// Court-circuitage de l'affichage personnalisé du captcha
			$output .= apply_filters( 'mktzr_forms_field_recaptcha_custom_html', $_output, $field, $mkcf );
		else :
			$output .= recaptcha_get_html( $options['sitekey'] );
		endif;
	}

	/**
	 * Contrôle d'intégrité
	 */
	function handle_check_request( &$errors, $field, $mkcf ){
		if( $field['type'] != 'recaptcha' )
			return;
		
		require_once( $this->lib_path );
		
		$options = $mkcf->forms->get_option( 'recaptcha' );
		if( ! $secretkey = $options['secretkey'] )
			wp_die( '<h1>ERREUR DE CONFIGURATION DU FORMULAIRE</h1><p>La clef privée de ReCaptcha n\'a pas été renseignée</p>', 'tify' );

		$resp = recaptcha_check_answer( $options['secretkey'],
										$_SERVER["REMOTE_ADDR"],
										$_POST["recaptcha_challenge_field"],
										$_POST["recaptcha_response_field"]);
			
		if ( ! $resp->is_valid )				   
			$errors[] = __( "La saisie de la protection antispam est incorrect", 'tify' );
	}
}