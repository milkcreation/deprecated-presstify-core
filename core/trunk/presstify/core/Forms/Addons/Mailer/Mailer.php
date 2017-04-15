<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\Mailer;

use tiFy\Lib\Mailer\MailerNew;
use tiFy\Core\Forms\Form\Helpers;

class Mailer extends \tiFy\Core\Forms\Addons\Factory
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
		parent::__construct();
		
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			/// Envoi d'un message de notification à l'administrateur du site
			'notification' 		=> array(
				'subject'			=> sprintf( __( 'Vous avez une nouvelle demande de contact sur le site %s', 'tify' ), get_bloginfo('name') )
			),
			/// Envoi d'un message de confirmation de reception à l'emetteur de la demande
			'confirmation' 		=> false,
			'admin'				=> true
		);
		
		// Définition des fonctions de court-circuitage
		$this->callbacks = array(
			'handle_successfully'	=> array( $this, 'cb_handle_successfully' )
		);
    }
    
    /* = DECLENCHEURS = */
    /** == == **/
    public function afterInit()
    {
    	if( $this->getFormAttr( 'admin' ) ) :
    		$id = @ sanitize_html_class( base64_encode( $this->form()->getUID() ) );
    		\tify_options_register_node(
				array(
					'id' 		=> 'tiFyFormMailer_'. $id,
					'title' 	=> $this->form()->getTitle(),
					'cb'		=> 'tiFy\Core\Forms\Addons\Mailer\Taboox\Option\MailOptions\Admin\MailOptions',
					'args'		=> array( 'id' => 'tiFyFormMailer_'. $id )
				)
			);
    		$notification = $this->getFormAttr( 'notification' );
    		$confirmation = $this->getFormAttr( 'confirmation' );
    		
    		if( get_option( 'tiFyFormMailer_'. $id .'-notification' ) === 'off' ) :
    			$this->setFormAttr( 'notification', false );
    		elseif( $to = get_option( 'tiFyFormMailer_'. $id .'-recipients' ) ) :
    			$notification['to'] = $to;
    			$this->setFormAttr( 'notification', $notification );
    		endif;

    		if( get_option( 'tiFyFormMailer_'. $id .'-confirmation' ) === 'off' ) :
    			$this->setFormAttr( 'confirmation', false );
    		elseif( $from = get_option( 'tiFyFormMailer_'. $id .'-sender' ) ) :
    			$confirmation['from'] = $from;
    			$this->setFormAttr( 'confirmation', $confirmation );
    		endif; 
    	endif;
    }
	
	/* = COURT-CIRCUITAGE = */
	/** == Avant la redirection == **/
	public function cb_handle_successfully( &$handle )
	{
		// Envoi du message de notification
		if( $options = $this->getFormAttr( 'notification' ) ) :
			$options = $this->parseOptions( $options );	
			MailerNew::send( $options );	
		endif;

		// Envoi du message de confirmation
		if( $options = $this->getFormAttr( 'confirmation' ) ) :
			$options = $this->parseOptions( $options );	
			MailerNew::send( $options );
		endif;
	}
	
	/* = CONTROLEUR = */
	/** == Traitement des options == **/
	final protected function parseOptions( $options )
	{
		$options = Helpers::parseMergeVars( $options, $this->form() );
		
		if( ! isset( $options['subject'] ) )
			$options['subject'] = sprintf( __( 'Nouvelle demande sur le site %1$s', 'tify' ), get_bloginfo('name') );
		if( ! isset( $options['to'] ) )
			$options['to'] = get_option( 'admin_email' );
		
		if( empty( $options['message'] ) )
			$options['message'] = $this->defaultHTML( $options );			
			
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
		$output .= 				'<td width="600" valign="top" colspan="2">'. sprintf( __( 'Nouvelle demande sur le site %1$s, <a href="%2$s">%2$s<a>', 'tify' ), get_bloginfo('name'), esc_url( get_bloginfo('url') ) ). '</td>';
		$output .= 			'</tr>';	
		$output .= 			'<tr>';
		$output .= 				'<td width="600" valign="top" colspan="2"><h3>'. $options['subject'] .'</h3></td>';
		$output .= 			'</tr>';
			
		foreach( (array) $this->form()->fields() as $field ) :
			if( ! $this->getFieldAttr( $field, 'show', false ) || 
				! $field->typeSupport( 'request' ) || 
				in_array( $field->getType(), array( 'password', 'file' ) ) 
			)
				continue;	
					
			$output .= 		'<tr>';			
			$output .= ( $label = $field->getLabel() ) 
						? 		'<td width="200" valign="top">'. $label .'</td>' .
								'<td width="400" valign="top">' 
										
						: 		'<td colspan="2" width="600" valign="top">';		
			$output .= 				$field->getDisplayValue();		
			$output .= 			'</td>';
			$output .= 		'</tr>';
		endforeach;
		$output .= 		'</tbody>';
		$output .= 	'</table>';
		
		return $output;
	 }
}