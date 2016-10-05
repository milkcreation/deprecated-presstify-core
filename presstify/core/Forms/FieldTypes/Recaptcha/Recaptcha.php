<?php
namespace tiFy\Core\Forms\FieldTypes\Recaptcha;

use tiFy\Core\Forms\FieldTypes\Factory;

class Recaptcha extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'recaptcha';
	
	// Support
	public $Supports 	= array( 
		'label', 
		'request',
		'wrapper'
	);
	
	// Instance
	static $Instance;
	
	/* = CONSTRUCTEUR = */				
	public function __construct()
	{
		// Options par défaut
		$this->Defaults = array(
			'sitekey'		=> false,
			'secretkey'		=> false, 	
			'lang'			=> $this->getLanguage(),
			'theme' 		=> 'light'	
		);
		
		// Définition des fonctions de callback
		$this->Callbacks = array(
			'field_set_params' 				=> array( $this, 'cb_field_set_params' ),
			'handle_check_field'			=> array( $this, 'cb_handle_check_field' )
		);
		
		parent::__construct();
	}
	
	/* = COURT-CIRCUITAGE = */
	/** == Attribut de champ requis obligatoire == **/
	public function cb_field_set_params( &$field )
	{			
		if( $field->getType() !==  'recaptcha' )
			return;
			
		$field->setAttr( 'required', true );
	}
				
	/** == Contrôle d'intégrité == **/
	public function cb_handle_check_field( &$errors, $field )
	{
		if( $field->getType() !==  'simple-captcha-image' )
			return;

		$options = $this->getOptions();
		
		// Instanciation de la librairie reCaptcha
		if( ! ini_get( 'allow_url_fopen' ) ) :
			// allow_url_fopen = Off
			$recaptcha = new \ReCaptcha\ReCaptcha( $options['secretkey'], new \ReCaptcha\RequestMethod\SocketPost );
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
	/** == Affichage == **/
	public function display()
	{
		$ID = preg_replace( '/-/', '_', sanitize_key( $this->form()->getID() ) );
		$instance = self::$Instance;
			
		// Récupération des options
		$options = $this->getOptions();		
				
		// Instanciation de la librairie ReCaptcha
		if( ! ini_get( 'allow_url_fopen' ) ) :
			// allow_url_fopen = Off			
			new \ReCaptcha\ReCaptcha( $options['secretkey'], new \ReCaptcha\RequestMethod\SocketPost );
		else :
			// allow_url_fopen = On
			new \ReCaptcha\ReCaptcha( $options['secretkey'] );
		endif;
			
		// Chargement des scripts dans le pied de page
		add_action( 
			'wp_footer', 
			function () use ( $ID, $options, $instance )
			{
				if( ! $instance ) :
				?><script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $this->getLanguage();?>&onload=onloadCallback_<?php echo $ID;?>&render=explicit" async defer></script><?php
				endif;
				?><script type="text/javascript">var onloadCallback_<?php echo $ID;?> = function() { grecaptcha.render('g-recaptcha-<?php echo $ID;?>',<?php echo json_encode( $options );?> );};$( document ).on( 'tify_forms_ajax_submit',function(){ onloadCallback_<?php echo $ID;?>();});</script><?php			
			},
			99
		);
		self::$Instance++;		
		
		// Affichage du champ ReCaptcha
		$output  = "";
		$output .= "<input type=\"hidden\" name=\"". esc_attr( $this->field()->getDisplayName() ) ."\" value=\"-1\">";
		$output .= "<div id=\"g-recaptcha-{$ID}\" class=\"g-recaptcha\" data-sitekey=\"{$options['sitekey']}\" data-theme=\"{$options['theme']}\"></div>";
		
		return $output;
	}
		
	/** == Récupération de la langue == **/
	private function getLanguage()
	{
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