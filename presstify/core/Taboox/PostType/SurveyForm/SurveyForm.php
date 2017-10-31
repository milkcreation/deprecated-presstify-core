<?php
namespace Theme\tiFy\Core\Taboox\PostType\SurveyForm;

use \tiFy\App\Factory;

class SurveyForm extends App
{
	/* = ARGUMENTS = */
	/** @see https://codex.wordpress.org/Plugin_API/Action_Reference **/
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'tify_form_register',
	);
	/** == Nom == **/
	public $name = '_survey_form_field';
	/** == Identifiant du formulaire == **/
	protected $currentSurveyFormId = 'CurrentSurveyForm';
	/** == Champs de formulaire de l'enqûete courante == **/
	protected $currentSurveyFormFields = array();
	/** == Bouton de soumission du formulaire de l'enquête courante == **/
	protected $currentSurveyFormSubmit = true;
	/** == Enquête courante == **/
	protected $currentSurvey;
	
	/* = DÉCLARATION DU FORMULAIRE DE L'ENQUÊTE COURANTE = */
	public function tify_form_register()
	{
		$this->currentSurvey = get_option( 'tify_content_hook_current_survey' );
		// Bypass
		if( ! $this->currentSurveyFormFields = \tify_meta_post_get( $this->currentSurvey, $this->name ) )
			return;
		// Préparation des champs
		$this->currentSurveyFormFields = $this->prepareFields( $this->currentSurveyFormFields );
		
		if( $this->currentSurveyFormSubmit ) :
			$this->currentSurveyFormSubmit = array(
				'label'	=> __( 'Envoyer', 'theme' ),
				'class'	=> 'Button Button--inline Button--green'
			);
			$this->currentSurveyFormFields[] = $this->getCaptcha();
		endif;
		
		tify_form_register(
			$this->currentSurveyFormId,
			array(
				'title' 	=> get_the_title( $this->currentSurvey ),
				'fields'	=> $this->currentSurveyFormFields,
				'buttons' 	=> array(
					'submit' => $this->currentSurveyFormSubmit
				),
				'addons'	=> array(
					'record' 	=> array(
						'export'	=> false
					),
					'mailer'		=> $this->prepareMailer()
				)
			)
		);
	}

	/* = PRÉPARATION DES CHAMPS DE FORMULAIRE = */
	protected function prepareFields( $fields )
	{
		$_fields = array();
		
		foreach( $fields as $n => $field ) :	
			if( $this->isSubmitButton( $field ) && ! $this->currentSurveyFormSubmit )
				continue;
			if( $this->isSelectionField( $field ) )
				$field['choices'] = $this->prepareChoices( $field['choices'] );
			if( ! empty( $field['integrity_cb'] ) && $field['integrity_cb'] == -1 )
				$field['integrity_cb'] = false;
			if( isset( $field['required'] ) )
				$field['required'] = (bool)$field['required'];			
			if( $this->isSubmitButton( $field ) ) :
				$this->currentSurveyFormSubmit = false;
				$field['options']['class'] = 'Button Button--inline Button--green';
				$field['addons'] = array(
					'record'		=> array(
						'column' 		=> false,
						'preview'		=> false
					),
					'mailer'		=> array(
						'show' 		=> false
					)
				);
				$_fields[] = $this->getCaptcha();
			else :
				$field['addons'] = array(
					'record'		=> array(
						'column' 		=> true,
						'preview'		=> true
					)
				);
			endif;
			$_fields[] = $field;
		endforeach;
		
		return $_fields;
	}
	
	/* = PRÉPARATION DES CHOIX = */
	protected function prepareChoices( $choices )
	{
		$_choices = array();
		foreach( $choices as $choice )
			$_choices[$choice['slug']] = $choice['label'];
		return $_choices;
	}
	
	/* = VÉRIFIE SI LE CHAMP EST DE TYPE SÉLECTION = */
	protected function isSelectionField( $field )
	{
		return ( in_array( $field['type'], array( 'tify_checkbox', 'radio' ) ) && ! empty( $field['choices'] ) );
	}
	
	/* = VÉRIFIE SI LE CHAMP EST UN BOUTON DE SOUMISSION = */
	protected function isSubmitButton( $field )
	{
		return ( $field['type'] === 'button' && ( ! empty( $field['value'] ) ) && $field['value'] === 'submit' );
	}
	
	/* = CAPTCHA = */
	protected function getCaptcha()
	{
		return array(
			'slug'				=> 'captcha',
			'label'				=> __( 'Code de sécurité', 'theme' ),
			'container_class'	=> 'tiFyForm-FieldContainer--recaptcha',
			'type'				=> 'recaptcha',
			'options'			=> array(
				'sitekey'	=> '6LdAgAsUAAAAAK30UzthKoDxUbDrZ4SeBAQ3DHrM',
				'secretkey'	=> '6LdAgAsUAAAAAKWuO30huPYeAUs9SNcG-OosYp8I',
				'theme'		=> 'light'
			),
			'addons'			=> array(
				'mailer'	=> array(
					'show'	=> false
				)
			)
		);
	}
	
	/* = PRÉPARATION DU MAILER = */
	protected function prepareMailer()
	{
		$mailer = array(
			'notification' 		=> array(
				'subject'			=> sprintf( __( '%s | Vous avez une nouvelle réponse à l\'enquête de satisfaction', 'theme' ), get_bloginfo('name') ),
				'to'				=> get_option( 'admin_email' )
			),
			'confirmation' 		=> false,
			'admin'				=> false
		);
    	
		return $mailer;		
	}
}