<?php
namespace tiFy\Core\Forms\Addons\Mailer;

use tiFy\Core\Forms\Addons\Factory;
use tiFy\Lib\Mailer\Mailer as tiFyMailer;
use tiFy\Core\Forms\Form\Helpers;

class Mailer extends Factory
{
	/* = ARGUMENTS = */
	/// Identifiant
	public $ID = 'mailer';
	
	// Définition des options de champ de formulaire par défaut
	public $default_field_options = array( 
		// Affichage de l'intitulé et de la valeur de saisie du champ dans le corps du mail
		'show' 		=> false 		
	);
	
	/* = CONSTRUCTEUR = */
	public function __construct() 
	{	
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			/// Envoi d'un message de notification à l'administrateur du site		
			/// @see tiFy\Lib\Mailer\Mailer
			'notification' 		=> array(
				'subject'			=> sprintf( __( 'Vous avez une nouvelle demande de contact sur le site %s', 'tify' ), get_bloginfo('name') )	
			),
			/// Envoi d'un message de confirmation de reception à l'emetteur de la demande	
			/// @see tiFy\Lib\Mailer\Mailer
			'confirmation' 		=> false		
		);
		
		// Définition des fonctions de court-circuitage
		$this->callbacks = array(
			'handle_successfully'	=> array( $this, 'cb_handle_successfully' )
		);
		
        parent::__construct();			
    }
	
	/* = COURT-CIRCUITAGE = */
	/** == Avant la redirection == **/
	public function cb_handle_successfully( &$handle )
	{
		// Envoi du message de notification
		if( $options = $this->getFormAttr( 'notification' ) ) :
			$options = $this->parseOptions( $options );		
			new tiFyMailer( $options );	
		endif;

		// Envoi du message de confirmation
		if( $options = $this->getFormAttr( 'confirmation' ) ) :
			$options = $this->parseOptions( $options );		
			new tiFyMailer( $options );	
		endif;
	}
	
	/* = CONTROLEUR = */
	/** == Traitement des options == **/
	final protected function parseOptions( $options )
	{
		$options = Helpers::parseMergeVars( $options, $this->form() );
				
		// @todo Fichiers attachés
		
		if( empty( $options['html'] ) )
			$options['html'] = $this->defaultHTML( $options );			
			
		return $options;
	}

	/** == Préparation du message html == **/
	public function defaultHTML( $options )
	{
		// htmlentities( stripslashes( $value ), ENT_COMPAT, 'UTF-8' )
		$output  = '';
		$output .= 	'<table cellpadding="0" cellspacing="10" border="0" align="center">';
		$output .=		'<tbody>';
		$output .= 			'<tr>';
		$output .= 				'<td width="600" valign="top" colspan="2">' .sprintf( __( 'Nouvelle demande sur le site %1$s, <a href="%2$s">%2$s<a>'), get_bloginfo('name'), esc_url( get_bloginfo('url') ) ). '</td>';
		$output .= 			'</tr>';	
		$output .= 			'<tr>';
		$output .= 				'<td width="600" valign="top" colspan="2"><h3>'. $options['subject'] .'</h3></td>';
		$output .= 			'</tr>';
			
		foreach( (array) $this->form()->fields() as $field ) :
			if( $this->getFieldAttr( $field, 'ignore', false ) || 
				! $field->typeSupport( 'request' ) || 
				in_array( $field->getType(), array( 'password', 'file' ) ) 
			)
				continue;	
					
			$output .= 		'<tr>';			
			$output .= ( $label = $field->getLabel() ) 
						? 		'<td width="200" valign="top">'. $label .'</td>' .
								'<td width="400" valign="top">' 
										
						: 		'<td colspan="2" width="600" valign="top">';		
			$output .= 				$field->getValue();		
			$output .= 			'</td>';
			$output .= 		'</tr>';
		endforeach;
		$output .= 		'</tbody>';
		$output .= 	'</table>';
	
		return $output;
	 }
}