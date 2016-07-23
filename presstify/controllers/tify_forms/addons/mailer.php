<?php
/*
Addon Name: Mailer
Addon ID: mailer
Callback: tiFy_Forms_Addon_Mailer
Version: 1.150815
Author: Jordy Manner
Author URI: http://profile.milkcreation.fr/jordy.manner
*/

/**
 * OPTIONS DE L'ADDON
	array(
 		'debug' 				=> false, // true : Envoi de l'email | false : force l'affichage de l'email plutôt que l'envoi
		'notification' 			=> array( // Envoi un email de notification aux administrateurs du site
 			'from' 				=> (string|array) // Email de l'expediteur
 				array( 
  					'name' 	=> [value] // Nom de l'expediteur (optionnel) | défaut : Nom du blog
  					'email' => [value] // Doit être une adresse email valide | défaut : Adresse de l'administrateur du site
  				)
  			),
  			'to' 				=> // (string|array|array_multi) Email(s) de destinataire(s) | Peut être la valeur d'un champs du formulaire %%[field_slug]%% à condition qu'il s'agissent d'une adresse email
	 			array(
	 				array( 
	  					'name' 	=> [value] // Nom du destinataire1 (optionnel) | défaut : Nom du blog
	  					'email' => [value] // Adresse email du destinataire1 | défaut : Adresse de l'administrateur du site
	 				),
	 				...
	 			)
  			),
			'cc' 				=> // (string|array|array_multi) Email(s) de destinataire(s) en copie (optionnel)
	 			array(	
	 				array( 
	  					'name' 	=> [value] // Nom du destinataire1 en copie (optionnel)
	  					'email' => [value] // Adresse email du destinataire1 en copie
	 				)
					...
				)
  			),
 			'bcc' 				=> // Email(s) de destinataire(s) en copie cachée
 				array(
	 				array( 
	  					'name' 	=> [value] // Nom du destinataire1 en copie cachée
	  					'email' => [value] // Adresse email du destinataire1 en copie cachée
	 				)
					...
				)
  			),  			
  			'subject'			=> [value], // Peut être la valeur d'un champs du formulaire %%[field_slug]%%
			'html'				=> [value], // Personnalisation du message avec les variable de formulaire (optionnel)
			'html_header'		=> [value], // Ajout d'une entête de mail (optionnel)			
			'html_footer'		=> [value] // Ajout d'un pied de page de mail (optionnel)
  		), 
		'confirmation' 	=> false array( // Envoi un email de confirmation à l'expediteur du message
  			'from' 				=> (string|array) // Email de l'expediteur
 				array( 
  					'name' 	=> [value] // Nom de l'expediteur (optionnel) | défaut : Nom du blog
  					'email' => [value] // Doit être une adresse email valide | défaut : Adresse de l'administrateur du site
  				)
  			),
  			'to' 				=> // (string|array|array_multi) Email(s) de destinataire(s) | Peut être la valeur d'un champs du formulaire %%[field_slug]%% à condition qu'il s'agissent d'une adresse email
 				array(
	 				array( 
	  					'name' 	=> [value] // Nom du destinataire1 (optionnel) | défaut : Nom du blog
	  					'email' => [value] // Adresse email du destinataire1 | défaut : Adresse de l'administrateur du site
	 				),
	 				...
	 			)
  			),
			'cc' 				=> // (string|array|array_multi) Email(s) de destinataire(s) en copie (optionnel)
 				array(	
	 				array( 
	  					'name' 	=> [value] // Nom du destinataire1 en copie (optionnel)
	  					'email' => [value] // Adresse email du destinataire1 en copie
	 				)
					...
				)
  			),
 			'bcc' 				=> // Email(s) de destinataire(s) en copie cachée
 				array(
	 				array( 
	  					'name' 	=> [value] // Nom du destinataire1 en copie cachée
	  					'email' => [value] // Adresse email du destinataire1 en copie cachée
	 				)
					...
				)
  			),  			
  			'subject'			=> [value], // Peut être la valeur d'un champs du formulaire %%[field_slug]%%
			'html'				=> [value], // Personnalisation du message avec les variable de formulaire (optionnel)
			'html_header'		=> [value], // Ajout d'une entête de mail (optionnel)			
			'html_footer'		=> [value] // Ajout d'un pied de page de mail (optionnel)
  		)
 */
class tiFy_Forms_Addon_Mailer extends tiFy_Forms_Addon{
	/* = ARGUMENTS = */
	
	/* = CONSTRUCTEUR = */				
	public function __construct( tiFy_Forms $master ) {
		// Définition des options de formulaire par défaut
		$this->default_form_options = array(
			'debug' 		=> false,		
			'notification' 	=> array(
				'send' 				=> true,
				'from' 				=> array( 
					'name' 				=> get_bloginfo( 'name' ),
					'email' 			=> get_option( 'admin_email' )
				),
				'to'				=>  array( 
					array(
						'name' 			=> get_bloginfo( 'name' ),
						'email' 		=> get_option( 'admin_email' )
					)
				),
				'reply'				=>  array(
					array(
						'name' 			=> get_bloginfo( 'name' ),
						'email' 		=> get_option( 'admin_email' )
					)
				),
				'cc' 				=> false,
				'bcc' 				=> false,
				'subject'			=> sprintf( __( 'Vous avez une nouvelle demande de contact sur le site %s', 'tify' ), get_bloginfo('name') ),
				'html'				=> '',
				'html_before'		=> '',
				'html_after'		=> ''	
			),
			'confirmation' 	=> array(
				'send' 				=> false,
				'from' 				=> array( 
					'name' 				=> get_bloginfo('name'),
					'email' 			=> get_option( 'admin_email' )
				),
				'to'				=>  array( 
					array(
						'name' 			=> get_bloginfo( 'name' ),
						'email' 		=> get_option( 'admin_email' )
					)
				),
				'reply'				=>  array(
					array(
						'name' 			=> get_bloginfo('name'),
						'email' 		=> get_option( 'admin_email' )
					)
				),
				'cc' 				=> false,
				'bcc' 				=> false,
				'subject'			=> sprintf( __( 'Votre demande de contact sur le site %s', 'tify' ), get_bloginfo('name') ),
				'html'				=> '',
				'html_before'		=> '',
				'html_after'		=> ''
			)
		);
		
		// Définition des options de champ de formulaire par défaut
		$this->default_field_options = array( 
			'ignore' 		=> false 		// Permet d'ignorer l'affichage du champ dans l'envoi de mail
		);
		
		// Définition des fonctions de callback
		$this->callbacks = array(
			'handle_before_redirect'	=> array( $this, 'cb_handle_before_redirect' )
		);
		
        parent::__construct( $master );			
    }
	
	/* = CALLBACKS = */
	/** == Traitement des emails == **/
	function cb_handle_before_redirect( &$parsed_request, $original_request ){
		// Récupération du controleur de mail
		tify_require_lib( 'mailer' );
		
		// Envoi du message de confirmation
		if( ( $options = $this->master->addons->get_form_option( 'confirmation', 'mailer' ) ) && $options['send'] ) :
			// Préparation du mail
			if( $options = $this->parse_options( $options, $parsed_request['fields'] ) )
				$tiFy_Mailer = new tiFy_Mailer( $options );	
		endif;
		
		// Envoi du message de notification
		if( ( $options = $this->master->addons->get_form_option( 'notification', 'mailer' ) ) && $options['send'] ) :	
			// Préparation du mail
			if( $options = $this->parse_options( $options, $parsed_request['fields'] ) )	
				$tiFy_Mailer = new tiFy_Mailer( $options );				
		endif; 
		
		return $parsed_request;
	}
	
	/** == Traitement des options == **/
	function parse_options( $options, $fields ){
		// Arguments de contact
		/// Destinataire
		if( ! $to = $this->parse_contact( $options['to'], $fields ) )
			return;		
		/// Expéditeur
		$from = $this->parse_contact( $options['from'], $fields );		
		/// Réponse à
		$reply = $this->parse_contact( $options['reply'], $fields );
		/// Destinataire en copie
		$cc = $this->parse_contact( $options['cc'], $fields );
		/// Destinataire en copie cachée
		$bcc = $this->parse_contact( $options['bcc'], $fields );
		
		// Sujet
		$subject = $this->master->functions->translate_field_value( $options['subject'], $fields, $options['subject'] );
		
		// Message
		$html 			= call_user_func_array( array( $this, 'html_prepare' ), array( $subject, $fields ) );
		$html_before 	= $options['html_before'];
		$html_after 	= $options['html_after'];
		
		// Attachments
		$attachments = array();
		foreach( $fields as $field ) :
			if( $field['type'] != 'file' ) continue;
			if( ! $file = unserialize( @ base64_decode( $field['value'] ) ) ) 
				continue;
			array_push( $attachments, WP_CONTENT_DIR. "/uploads/tify_forms/upload/". $file['name'] );
		endforeach;	
		
		$auto = ( $this->master->addons->get_form_option( 'debug', 'mailer' ) ) ? 'debug' : 'send';		
		
		$_options = compact( 'from', 'to', 'reply', 'cc', 'bcc', 'subject', 'html', 'html_before', 'html_before', 'attachments', 'auto' );
		
		return $_options;
	}

	/** == Préparation du message html == **/
	 function html_prepare( $subject, $fields ){
		$output  = '';
		$output .= 	'<table cellpadding="0" cellspacing="10" border="0" align="center">';
		$output .=		'<tbody>';
		$output .= 			'<tr>';
		$output .= 				'<td width="600" valign="top" colspan="2">' .sprintf( __( 'Nouvelle demande sur le site %1$s, <a href="%2$s">%2$s<a>'), get_bloginfo('name'), esc_url( get_bloginfo('url') ) ). '</td>';
		$output .= 			'</tr>';	
		$output .= 			'<tr>';
		$output .= 				'<td width="600" valign="top" colspan="2"><h3>'. htmlentities( $subject, ENT_COMPAT, 'UTF-8' ) .'</h3></td>';
		$output .= 			'</tr>';
			
		foreach( $fields as $field ) :
			if( $field['type'] == 'hidden' ) continue;
			if( $field['type'] == 'file' ) continue;
			if( $field['add-ons']['mailer']['ignore'] ) continue;	
					
			$output .= 		'<tr>';
			
			if( $field['label'] ) :
				$output .= 		'<td width="200" valign="top">'. htmlentities( stripslashes( $field['label'] ), ENT_COMPAT, 'UTF-8' ) .'</td>';
				$output .= 		'<td width="400" valign="top">';
			else :
				$output .= 		'<td colspan="2" width="600" valign="top">';
			endif;		
			if( is_string( $field['value'] ) ) :
				$output .=  htmlspecialchars_decode( stripslashes( $this->master->fields->translate_value( $field['value'], $field['choices'], $field ) ), ENT_COMPAT );
			elseif( is_array( $field['value'] ) ) :
				$n = 0;
				foreach( $field['value'] as $value ) :				
					if( $n++) $output .= ', ';
					$output .= '<img src="'. tify_get_directory_uri() .'/plugins/forms/images/checked.png" align="top" width="16" height="16"/>&nbsp;';		
					$output .= htmlentities( stripslashes( $this->master->fields->translate_value( $value, $field['choices'], $field ) ), ENT_COMPAT, 'UTF-8' );
				endforeach;	
			endif;		
			$output .= 			'</td>';
			$output .= 		'</tr>';
		endforeach;
		/*
		$output .= "<tr>";
		$output .= "<td width="600" valign="top" colspan="2">".sprintf( __('Répondre à : <a href="%1$s">%1$s<a>', 'tify' ), $this->master->_submit['request']['email']['value'] )."</td>";
		$output .= "</tr>";
		*/
		$output .= 		'</tbody>';
		$output .= 	'</table>';
	
		return $output;
	 }

	/** == Traitement des contact == **/
	function parse_contact( $contact, $fields ){
		$output = array();
		if( is_array( $contact ) ) :
			if( ! isset( $contact['email'] ) ) :
				foreach( $contact as &$c ) :
					if( $email = $this->parse_contact( $c, $fields ) ) :
						$output[] = $email;
					else :
						$output[] = $c;
					endif;
				endforeach;
			else :
				if( isset( $contact['name'] ) ) :
					if( $name = $this->master->functions->translate_field_value( $contact['name'], $fields ) ) :
						$output['name'] = $name;
					else :
						$output['name'] = $contact['name'];
					endif;
				endif;
				if( ( $email = $this->master->functions->translate_field_value( $contact['email'], $fields ) ) && is_email( $email ) ) :
					$output['email'] = $email;
				else :
					$output['email'] = $contact['email'];
				endif;
			endif;
		else :
			if( ( $email = $this->master->functions->translate_field_value( $contact, $fields ) ) && is_email( $email ) ) :
				$output = $email;
			else :
				$output = $contact;
			endif;
		endif;
		
		return $output;
	}
}