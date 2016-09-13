<?php
/*
FieldType Name: Simple Captcha Image
FieldType ID: simple-captcha-image
Callback: tiFy_Forms_FieldType_SimpleCaptchaImage
Version: 1.150817
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

/** 
 * @usage
 * 
	array(
		'ID' => #,
		'title' => 'Sample de Formulaire',
		'prefix' => 'sample',
		'fields' => array(
			array(
				'slug'			=> 'captcha',
				'type'			=> 'simple-captcha-image'
			)
 	... 
 * 
 	Modification de l'image de texture du captcha ( dimensions de l'image 100x50 ) :
	--------------------------------------------------------------------------------
	add_filter( "tify_forms_sci_background_image", create_function( '', "return '". get_template_directory_uri() ."/image/texture.jpg';" ) );
 * 
 	Modification de la couleur du texte (au format rgb):
	--------------------------------------------------------------------------------
	add_filter( "tify_forms_sci_text_color", create_function( '', "return 'array( 222, 222, 222 );" ) );
 */

class tiFy_Forms_FieldType_SimpleCaptchaImage extends tiFy_Forms_FieldType{
	/* = ARGUMENTS = */
	public	// Configuration
			$dir,
			$uri;

	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ){
		// Définition du type de champ
		$this->attrs = array( 
			'slug'			=> 'simple-captcha-image',
			'label' 		=> __( 'Captcha Image', 'tify' ),
			'section' 		=> 'misc',
			'supports'		=> array( 'label', 'request', 'integrity-cb' )
		);

		// Définition des fonctions de callback
		$this->callbacks = array(
			'field_set' 					=> array( $this, 'cb_field_set' ),
			'field_type_output_display' 	=> array( $this, 'cb_field_type_output_display' ),
			'handle_check_request' 			=> array( $this, 'cb_handle_check_request' )
		);
		
		parent::__construct( $master );
		
		// Définition des chemins
		$this->dir 	= dirname( __FILE__ );
		$this->uri	= plugin_dir_url( __FILE__ );
	}
		
	/* = CALLBACKS = */		
	/** == Attribut de champ requis obligatoire == **/
	function cb_field_set( &$field ){		
		// Bypass
		if( $field['type'] != 'simple-captcha-image' )
			return;	
		
		$field['required'] = true;
	}
	
	/** == Affichage du captcha == **/
	function cb_field_type_output_display( &$output, $field ){
		// Bypass
		if( $field['type'] != 'simple-captcha-image' )
			return;	
				
		$output .= "<img src=\"". $this->uri ."image.php\" alt=\"".__( 'captcha introuvable', 'tify' )."\" style=\"vertical-align: middle;\" />";
		$output .= "<input type=\"text\" name=\"". esc_attr( $this->master->fields->get_name( $field ) ) ."\" value=\"\" size=\"8\" autocomplete=\"off\" placeholder=\"{$field['placeholder']}\" style=\"height:50px;vertical-align: middle;\" />";
	}
	
	/** == Vérification des données du champ au moment du traitement de la requête == */
	function cb_handle_check_request( &$errors, $field ){
		// Bypass
		if( $field['type'] != 'simple-captcha-image' )
			return;
		if( ! $this->master->handle->parsed_request['fields'][ $field['slug'] ] )
			return;
		
		if( ! isset( $_SESSION ) )
			@ session_start();
		if( ! isset( $_SESSION['security_number'] ) ) :
			$errors[] = __( 'ERREUR SYSTÈME : Impossible de définir le code de sécurité' );
		elseif( $this->master->handle->parsed_request['fields'][ $field['slug'] ]['value'] != $_SESSION['security_number'] ) :
			$errors[] = __( 'La valeur du champs de sécurité doit être identique à celle de l\'image', 'tify' );
		endif;
			
		return;
	}	
}