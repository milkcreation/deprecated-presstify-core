<?php
namespace tiFy\Core\Forms\FieldTypes\SimpleCaptchaImage;

use tiFy\Core\Forms\FieldTypes\Factory;

class SimpleCaptchaImage extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant
	public $ID 			= 'simple-captcha-image';
	
	// Support
	public $Supports 	= array( 
		'integrity',
		'label', 
		'request',
		'wrapper'
	);	

	/* = CONSTRUCTEUR = */				
	public function __construct()
	{
		// Définition des fonctions de callback
		$this->Callbacks = array(
			'field_set_params' 		=> array( $this, 'cb_field_set_params' ),
			'handle_check_field' 	=> array( $this, 'cb_handle_check_field' )
		);
	}
		
	/* = CALLBACKS = */		
	/** == Attribut de champ requis obligatoire == **/
	public function cb_field_set_params( &$field )
	{			
		if( $field->getType() !==  'simple-captcha-image' )
			return;
			
		$field->setAttr( 'required', true );
	}
	
	/** == Vérification des données du champ au moment du traitement de la requête == */
	public function cb_handle_check_field( &$errors, $field )
	{		
		if( $field->getType() !==  'simple-captcha-image' )
			return;	
			
		if( ! isset( $_SESSION ) )
			@ session_start();
		if( ! isset( $_SESSION['security_number'] ) ) :
			$errors[] = __( 'ERREUR SYSTÈME : Impossible de définir le code de sécurité' );
		elseif( (int) $field->getValue() !== $_SESSION['security_number'] ) :
			$errors[] = __( 'La valeur du champs de sécurité doit être identique à celle de l\'image', 'tify' );
		endif;
	}
	
	/* = CONTROLEURS = */
	/** == Affichage == **/
	public function display()
	{			
		$output  = "";
		
		// Affichage du champ de saisie
		$output .= "<img src=\"". self::getUrl() ."/image.php\" alt=\"".__( 'captcha introuvable', 'tify' )."\" style=\"vertical-align: middle;\" />";
		$output .= "<input type=\"text\"";
		/// ID HTML
		$output .= " id=\"". $this->getInputID() ."\"";
		/// Classe HTML
		$output .= " class=\"". join( ' ', $this->getInputClasses() ) ."\"";
		/// Name		
		$output .= " name=\"". esc_attr( $this->field()->getDisplayName() ) ."\"";
		/// Placeholder
		$output .= " placeholder=\"". esc_attr( $this->getInputPlaceholder() ) ."\"";
		/// Attributs
		foreach( (array) $this->getInputHtmlAttrs() as $k => $v ) :
			$output .= " {$k}=\"{$v}\"";
		endforeach;
		$output .= " autocomplete=\"off\"";
		$output .= " style=\"height:50px;vertical-align: middle;\"";
		/// Value
		$output .= " value=\"\"";
		/// TabIndex
		$output .= " ". $this->getTabIndex();	
		$output .= " />";
		
		return $output;
	}
}