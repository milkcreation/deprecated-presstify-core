<?php
/** 
 * @see http://openclassrooms.com/courses/e-mail-envoyer-un-e-mail-en-php 
 * @see http://www.iana.org/assignments/message-headers/message-headers.xhtml
 * COMPATIBILITE CSS
 * @see http://www.email-standards.org/
 * @see https://www.campaignmonitor.com/css/
 * @see http://templates.mailchimp.com/resources/email-client-css-support/
 * EMAIL BOILERPLATE
 * @see http://www.emailology.org
 * @see http://templates.mailchimp.com/development/css/reset-styles/
 * @see http://templates.mailchimp.com/development/css/client-specific-styles/
 * @see http://templates.mailchimp.com/development/css/outlook-conditional-css/
 * LISTE DE MERGE TAGS
 * http://kb.mailchimp.com/merge-tags/all-the-merge-tags-cheat-sheet#Merge-tags-for-list-and-account-information
 **/
 
/**
 * COPIER LA FONCTION DANS FUNCTIONS.PHP DU THEME ACTIF
add_action( 'admin_init', 'tify_mailer_test' );
function tify_mailer_test(){
	if( ! isset( $_REQUEST['tify_mailer_test'] ) )
		return;
	
	new \tiFy\Lib\Mailer\Mailer( $_REQUEST['tify_mailer_test'] );
}
// > puis appeler l'url du site dans le navigateur http://urldusite/wp-admin/?tify_mailer_test=contact@domain.ltd
*/
 
namespace tiFy\Lib\Mailer;

class Mailer extends \tiFy\App\Factory
{
	private	// Configuration
			$not_rn			= "hotmail|live|msn",	// Serveur qui ne nécéssite pas un saut de ligne de type \r\n
			$line_break,							// Saut de ligne		
			$boundary,								// Séparateur de niveau 1
			$boundary_alt,							// Séparateur de niveau 2
			$type,									// html | plain | mixed			
			$headers,								// Entêtes du mail
			$output_html,							// Sortie du message au format HTML
			$output_text,							// Sortie du message au format Texte
			$output_debug,							// Sortie du message en mode DEBUG
									
			// Paramètres
			/// Arguments de contact
			$from,									// Expéditeur du message (unique ou premier)
			$to, 									// Destinataire(s)
			$reply, 								// Adresse(s) de réponse
			$cc, 									// Adresse(s) en copie(s)
			$bcc, 			 						// Adresse(s) en copie(s) cachée(s)
			
			/// Arguments des messages
			$subject, 								// Sujet du mail
			/// HTML
			$html,									// Message au format HTML
			$html_head,								// Balise <head/> personnalisée du message au format HTML						
			$html_body_attrs,						// Attributs de la balise body du message au format HTML
			$html_before,							// Pré-affichage du contenu du message au format HTML (immédiatement après l'ouverture de la balise <body>)
			$html_after,							// Post-affichage de contenu du message au format HTML (immédiatement avant la fermeture de la balise </body>)
			$html_body_wrap	= true,					// Encapsulation du contenu du message bool | string %s pour le contenu HTML du message
			
			/// Text
			$auto_text 		= true,					// Génération automatique du message au format texte à partir du contenu de message HTML
			$text,									// Message personnalisé au format Texte
						
			/// Arguments des fichiers joints			 
			$attachments,
						
			/// Arguments de configuration additionnels
			$priority 		= 0,					// Priorité de l'email - Maximale : 1 | Haute : 2 | Normale : 3 | Basse : 4 | Minimale : 5
			$inline_css		= true,					// Conversion des styles CSS en attribut style des balises HTML			
			$reset_css		= true,					// CSS de réinitialisation du message HTML
			$css			= array(),				// Chemins vers les feuilles de styles CSS
			$custom_css 	= '',					// Attributs css personnalisés (doivent être encapsulé dans une balise style ex : "<style type=\"text/css\">h1{font-size:18px;}</style>")
			$vars_format	= '\*\|(.*?)\|\*',		// Format des variables d'environnements
			$merge_vars		= array(),				// Variables d'environnements
			$additionnal	= array(),				// Attributs de configuration supplémentaires requis par les moteurs
			
			/// Arguments de configuration spécifique aux moteurs d'envoi
			$engine			= 'wp_mail',			// Moteur d'envoi :  wp_mail | smtp
													/** @todo mail (php) **/
																
			$engine_opts	= array(				// Définition des paramètres SMTP
				// 'host'			=> 'localhost',
				// 'port'			=> 25,
				// 'username'		=> '',
				// 'password'		=> '',
				// 'auth'			=> false,
				// 'secure'			=> ''
			),
			
			/// Argument d'execution automatique			
			$auto 			= 'send',				// Action d'execution automatique send (par défaut) | debug | false	
			
			/// Résultat de raitement
			$errors,
			$success;
				
	/* = CONSTRUCTEUR = */		
	public function __construct()
	{
		if( ! $count = func_num_args() ) :
			$this->auto = false; return;
		endif;
		
		$args = func_get_args();

		if( is_array( $args[0] ) ) :
			$_args =  $args[0];
		else :
			$_args['to'] 				=  $args[0];
			if( $count >= 2 )
				$_args['subject'] 		=  $args[1];
			if( $count >= 3 )
				$_args['html'] 			=  $args[2];
			if( $count === 4 )
				$_args['attachments'] 	=  $args[3];	
		endif;
		
		$this->parse_args( $_args );
		$this->prepare();
	}		
			
	/* = PARAMETRAGE = */					
	/** == Traitement des arguments == **/
	private function parse_args( $args = array() ){
		$params = array(
			// Arguments de contact
			'from', 'to', 'reply', 'cc', 'bcc',
			// Arguments des messages
			'subject', 
			/// Format HTML
			'html', 'html_head', 'html_body_attrs', 'html_before', 'html_after', 'html_body_wrap',
			/// Format Texte
			'auto_text', 'text',
			// Arguments des fichiers joints
			'attachments',
			// Arguments de configuration additionnels
			'priority', 'reset_css', 'css', 'inline_css', 'custom_css', 'merge_vars', 'additionnal',
			//
			'engine', 'engine_opts', 'auto'
		);
		$defaults = array();
		foreach( $params as $i )
			$defaults[$i] = $this->{$i};
		extract( wp_parse_args( $args, $defaults ) );		
		
		// Traitement des arguments de contact
		foreach( array( 'from', 'to', 'reply', 'cc', 'bcc' ) as $index )
			if( ! ${$index} )
				continue;
			elseif( in_array( $index, array( 'from' ) ) )
				${$index} = $this->parse_contact( ${$index}, true );
			else
				${$index} = $this->parse_contact( ${$index} );
		
		// Définition des arguments		
		foreach( array_keys( $defaults ) as $index )
			$this->{$index} = ${$index};

		// Vérification du destinataire
		if( empty( $this->to ) ) :
			$this->to = get_option( 'admin_email' );
		endif;
		// Définition des séparateurs
		$this->set_separators();		
		
		// Assignation des arguments par défaut	requis
		/// Expéditeur
		if( empty( $this->from ) ) :
			$this->from[0] = get_option( 'admin_email' );
			if( $user = get_user_by( 'email', get_option( 'admin_email' ) ) )	
				$this->from[1] = $user->display_name;	
		endif;
		/// Object du message		
		if( empty( $this->subject ) ) :
			$this->subject = $this->test_subject();
		endif;

		/// Contenu du message		
		if( empty( $this->html ) && empty( $this->text ) ) :
			$this->prepare_test_html();
			$this->prepare_test_text();
		endif;
						
		// Définition du type de message
		$this->set_message_type();
		
		return compact( array_keys( $defaults ) );
	}
	
	/** == Définition des séparateurs de mail == **/
	private function set_separators(){
		$this->line_break 	= ( ! preg_match( "#[a-z0-9._-]+@(". $this->not_rn .").[a-z]{2,4}#", $this->from[0] ) ) ? "\r\n" : "\n";
		$this->boundary		= "-----=". md5( rand() );
		$this->boundary_alt = "-----=". md5( rand() );
	}

	/** == Définition du type de message == **/
	private function set_message_type(){
		if( $this->text && $this->html )
			$this->type = 'mixed';
		elseif( $this->html )
			$this->type = $this->auto_text ? 'mixed' : 'html';
		elseif( $this->text )
			$this->type = 'plain';	
	}
	
	/** == Définition des entêtes du mail == **/
	private function set_headers(){
		/// Expéditeur
		$this->headers[] = "From: ". $this->format_contact( $this->from );
		/// Réponse à
		if( $this->reply )
			$this->headers[] = "Reply-To: ". $this->format_contact( $this->reply, false );
		/// Copie Carbone
		if( $this->cc )
			$this->headers[] = "Cc: ". $this->format_contact( $this->cc, false );
		/// Copie Cachée
		if( $this->bcc )
			$this->headers[] = "Bcc: ". $this->format_contact( $this->bcc, false );		
		/// Version de MIME
		$this->headers[] = "MIME-Version: 1.0";
		/// Type de contenu
		if( $this->type == 'html' )	
			$this->headers[] = "Content-type: text/html; charset=\"". get_bloginfo( 'charset' ) ."\"";
		elseif( $this->type == 'plain' )
			$this->headers[] = "Content-Type: text/plain; charset=\"". get_bloginfo( 'charset' ) ."\"";
		else
			$this->headers[] = "Content-Type: multipart/mixed;";
		/// Encodage
		$this->headers[] = "Content-Transfer-Encoding: 8bit";
		/// Entêtes additionnelles
		$this->headers[] = "Date: ". date('r', current_time('timestamp') );
		$this->headers[] = "X-Mailer: PHP/". phpversion();
	}

	/** == Préparation du message en fonction du type de sortie attendu == **/
	private function prepare_message(){
		$output = "";
		switch( $this->type ) :
			case 'plain' : 
				$this->prepare_output_text();		
				break;
			case 'html' :
				$this->prepare_output_html();
				break;			
			case 'mixed' :
				$this->prepare_output_html();
				$this->prepare_output_text();						
				break;
		endswitch;
		
		return $output;		
	}
		
	/* = FORMAT DE SORTIE = */
	/** == HTML == **/
	/*** === Formatage du message en HTML === ***/
	private function prepare_output_html(){
		$output  = "";
		$output .= $this->html_header();
		$output .= $this->html_body();
		$output .= $this->html_footer();
		
		$output = $this->parse_merge_vars( $output );
		
		$this->output_html = $output;

		if( $this->inline_css )
			$this->inline_css();
	}
	
	/*** === Entête du message HTML === ***/
	private function html_header(){
		$output  = "";
		// Balise <HEAD/>
		if( ! $this->html_head ) :				
			$output .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">". $this->line_break;
			$output .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">". $this->line_break;
			$output .= 	"<head>". $this->line_break;
	    	$output .= 		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />". $this->line_break;
	    	$output .= 		"<title>{$this->subject}</title>". $this->line_break;
			$output .= 	$this->append_css() . $this->line_break;
			$output .= 	"</head>". $this->line_break;
		else :
			$output .= $this->html_head. $this->line_break;
		endif;
		
		// Balise <BODY/>
		if( $this->html_body_attrs && is_string( $this->html_body_attrs ) ) :
				$body_attrs = $this->html_body_attrs;
		elseif( $this->html_body_attrs && is_array( $this->html_body_attrs ) ) :
			$_attrs = array();
			foreach( $this->html_body_attrs as $property => $attrs )
				$_attrs[] = "{$property}=\"$attrs\"";
			$body_attrs = implode( ' ', $_attrs );
		else :
			$body_attrs = "style=\"background:#FFF;color:#000;font-family:Arial, Helvetica, sans-serif;font-size:12px\" link=\"#0000FF\" alink=\"#FF0000\" vlink=\"#800080\" bgcolor=\"#FFFFFF\" text=\"#000000\" yahoo=\"fix\"";
		endif;
		$output .= 	"<body {$body_attrs}>". $this->line_break;
		
		return $output;
	}
	
	/*** === Corps du message HTML === ***/
	private function html_body(){
		$output  = "";
		
		$output .= 	$this->html_before;
		$output .=  $this->html_body_content();
		$output .= 	$this->html_after;		
		
		return $output;
	}
	
	private function html_body_content()
	{
		$output	 = "";
		
		if( $this->html_body_wrap ) :
			if( is_bool( $this->html_body_wrap ) ) :
				$output .= 	"<div id=\"body_style\" style=\"padding:15px\">". $this->line_break;
				$output .= 		"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bgcolor=\"#FFFFFF\" width=\"600\" align=\"center\">". $this->line_break;
		        $output .= 			"<tr>". $this->line_break;
		        $output .= 				"<td width=\"600\">{$this->html}</td>". $this->line_break;
		        $output .= 			"</tr>". $this->line_break;
		        $output .= 		"</table>". $this->line_break;
				$output .= 	"</div>". $this->line_break;
			elseif( is_string( $this->html_body_wrap ) ) :
				$output .= sprintf( $this->html_body_wrap, $this->html );
			endif;
		else :
			$output .=  $this->html . $this->line_break;
		endif;
		
		return $output;
	}
	
	/*** === Pied de page du message HTML === ***/
	private function html_footer(){
		$output  = "";
		$output .= 	"</body>";
		$output .= "</html>";
		
		return $output;
	}
	
	/*** === Ajout des Feuilles de style CSS === ***/
	private function append_css(){
		$output = "";
		if( $this->reset_css )
			$output .= "<style type=\"text/css\">". $this->line_break . file_get_contents( self::tFyAppDirname() . '/css/reset.emailology.org.css' ) . $this->line_break ."</style>";
		if( ! empty( $this->css ) )
			foreach( (array) $this->css as $filename )
				if( file_exists( $filename ) )
					$output .= "<style type=\"text/css\">". $this->line_break . file_get_contents( $filename ) . $this->line_break ."</style>";
		if( ! empty( $this->custom_css ) )
			$output .= $this->custom_css;
		
		return $output;		
	}
	
	/*** === Convertion des styles CSS en attribut style des balises HTML === ***/
	private function inline_css( ){
		if ( version_compare( phpversion(), '5.4', '<=' ) )
			return;
			
	    $xmldoc = new \DOMDocument( '1.0', get_bloginfo( 'charset' ) );
	    $xmldoc->strictErrorChecking = false;
	    $xmldoc->formatOutput = true;
	    @$xmldoc->loadHTML( $this->output_html );
	    $xmldoc->normalizeDocument();
	
	    // need to check all objects exist
	    $head = $xmldoc->documentElement->getElementsByTagName( 'head' );
	
	    if ($head->length > 0) :
	        $style = $head->item(0)->getElementsByTagName('style');
	
	        if ( $style->length > 0 ) :
	            $style = $head->item(0)->removeChild( $style->item(0) );
	
	            $css = trim( $style->nodeValue );
	            $html = $xmldoc->saveHTML();
	
	            $e = new \Pelago\Emogrifier( $html, $css );
	
	            $this->output_html = $e->emogrify();
			endif;
		endif;
	}
	
	/** == TEXTE == **/
	/*** === Formatage du message en mode Texte === ***/
	private function prepare_output_text()
	{
		if( $text = $this->text ) :	
			$text = nl2br( $text );
		elseif( $this->auto_text ) :
			$text = $this->html;
		endif;
		
		$text = $this->parse_merge_vars( $text );
		$Html2Text = new \Html2Text\Html2Text( $text );		
		
		$text = $Html2Text->getText();
		
		$this->output_text = $text;
	}
	
	/** == EMAIL DE TEST == **/
	/*** === Sujet de l'email de test === ***/
	private function test_subject()
	{
		return sprintf( __( 'Test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) );
	}
	
	/*** === Message HTML de l'email de test === ***/
	private function prepare_test_html()
	{
		$this->html  = "<h1>". sprintf( __( 'Ceci est un test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."</h1>";
		$this->html .= "<p>". __( 'Si ce mail, vous est parvenu c\'est qu\'il vous a été expédié depuis le site : ' ) ."</p>";
		$this->html .= "<p><a href=\"". site_url( '/' ) ."\" title=\"". sprintf( __( 'Lien vers le site internet - %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."\">". get_bloginfo( 'blogname' ) ."</a><p><br>";
		$this->html .= "<p>". __( 'Néamoins, il pourrait s\'agir d\'une erreur, si vous n\'étiez pas concerné par ce mail je vous prie d\'accepter nos excuses.', 'tify' ) ."</p>";
		$this->html .= "<p>". __( 'Vous pouvez dès lors en avertir l\'administrateur du site à cette adresse : ', 'tify' ) ."</p>";
		$this->html .= "<p><a href=\"mailto:". get_option( 'admin_email' ) ."\" title=\"". sprintf( __( 'Contacter l\'administrateur du site - %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."\">". get_option( 'admin_email' ) ."</a></p><br>";
		$this->html .= "<p>". __( 'Celui-ci fera en sorte qu\'une telle erreur ne se renouvelle plus.', 'tify' ) ."</p><br>";
		$this->html .= "<p>". __( 'Merci de votre compréhension', 'tify' ) ."</p>";
				
		$this->prepare_output_html();
	}
	
	/*** === Message texte de l'email de test == **/
	private function prepare_test_text(){
		$this->text  = sprintf( __( 'Ceci est un test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) ) . $this->line_break;
		$this->text .= __( 'Si ce mail, vous est parvenu c\'est qu\'il vous a été expédié depuis le site : ' ) . $this->line_break;
		$this->text .= site_url( '/' ) . $this->line_break. $this->line_break;
		$this->text .= __( 'Néamoins, il pourrait s\'agir d\'une erreur, si vous n\'étiez pas concerné par ce mail je vous prie d\'accepter nos excuses.', 'tify' ) . $this->line_break;
		$this->text .= __( 'Vous pouvez dès lors en avertir l\'administrateur du site à cette adresse : ', 'tify' ) . $this->line_break;
		$this->text .= get_option( 'admin_email' ) . $this->line_break. $this->line_break;
		$this->text .= __( 'Celui-ci fera en sorte qu\'une telle erreur ne se renouvelle plus.', 'tify' ) . $this->line_break . $this->line_break;
		$this->text .= __( 'Merci de votre compréhension', 'tify' ) . $this->line_break;
		
		$this->prepare_output_text();
	}
	
	/** == DEBUG == **/
	/*** === Affichage du mail en mode debug === ***/
	private function prepare_output_debug(){
		$before = 	"<table cellspacing=\"0\" border=\"0\" bgcolor=\"#EEEEEE\" width=\"100%\" align=\"center\" style=\"border-bottom:solid 1px #AAA;\">";		
		$before .= 		"<tbody>";
		$before .= 		"<tr>";
		$before .= 			"<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
		$before .= 				"<h3 style=\"margin-bottom:10px;\">". $this->subject ."</h3>";
		$before .= 				__( 'à', 'tify' ) ." ". htmlspecialchars( $this->format_contact( $this->to, false ) );
		$before .=				"<hr style=\"display:block;margin:10px 0 5px;background-color:#CCC; height:1px; border:none;\">";
		$before .= 			"</td>";
		$before .= 		"</tr>";	
		foreach( $this->headers as $header => $value ) :
			$before .= 		"<tr>";
			$before .= 			"<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
			$before .= 			htmlspecialchars( $value );
			$before .= 			"</td>";
			$before .= 		"</tr>";			
		endforeach;
		$before .= 		"</tbody>";
		$before .= 	"</table>";			
						
		if( in_array( $this->type, array( 'html', 'mixed' ) ) ) :
			$html_before  = $before;
			if( $this->type === 'mixed' ) :
				$html_before .= 	"<table cellspacing=\"0\" border=\"0\" width=\"600\" align=\"center\" style=\"margin:30px auto;\">";
				$html_before .= 		"<tbody>";
				$html_before .= 			"<tr>";
				$html_before .= 				"<td style=\"text-align:center;font-size:18px;font-family:courier\">------------ VERSION HTML ------------</td>";
				$html_before .= 			"</tr>";
				$html_before .= 		"</tbody>";
				$html_before .= 	"</table>";	
			endif;
				
			$this->html_before = $html_before . $this->html_before;
		endif;
	
		if( in_array( $this->type, array( 'mixed' ) ) ) :	
			$text_before  = 	"<table cellspacing=\"0\" border=\"0\" width=\"600\" align=\"center\" style=\"margin:30px auto;\">";
			$text_before .= 		"<tbody>";
			$text_before .= 			"<tr>";
			$text_before .= 				"<td style=\"text-align:center;font-size:18px;font-family:courier\">------------ VERSION TEXTE ------------</td>";
			$text_before .= 			"</tr>";
			$text_before .= 		"</tbody>";
			$text_before .= 	"</table>";
		endif;
	
		$this->prepare_message();
		
		$this->output_debug  = "";
		if( in_array( $this->type, array( 'html', 'mixed' ) ) )	
			$this->output_debug .= $this->output_html;
		if( $this->type === 'mixed' )	
			$this->output_debug .= $text_before;
		if( $this->type === 'plain' )	
			$this->output_debug .= $this->html_header() . $before;
		if( $this->type !== 'html' )
			$this->output_debug .= "<table cellspacing=\"0\" border=\"0\" width=\"600\" align=\"center\" style=\"margin-bottom:60px;\" ><tbody><tr><td>". $this->output_text . "</td></tr></tbody></table>";	
		if( $this->type === 'plain' )
			$this->output_debug .= $this->html_footer();
	}
	
	/* = CONTROLEURS = */
	/** ==  == **/
	private function parse_contact( $contact = null, $single = false ){
		// Bypass
		if( ! $contact )
			return;

		$output = "";
		if( is_array( $contact ) ) :
			// Tableau indexé
			if( array_keys( $contact ) === range(0, count( $contact ) - 1 ) ) :
				/// Format array( [email], [(optional) name] );
				if( is_string( $contact[0] ) && is_email( $contact[0] ) ) :
					if( count( $contact ) === 1 ) :
						return $contact;
					elseif( ( count( $contact ) === 2 ) && is_string( $contact[1] ) ) :
						return $contact;
					endif;
				endif;
				$callback = function( $c ){ return $this->parse_contact( $c, true ); };
				$contact = array_map( $callback, $contact );
				
				foreach( $contact as $key => $value )
					if( is_null( $value ) )
						unset( $contact[$key] );
					
				if( ! empty( $contact ) ) :
					return $single ? current( $contact ) : $contact;
				endif;
			// Tableau Associatif
			/// Format array( 'email' => [email], 'name' => [name] );
			elseif( isset( $contact['email'] ) && is_email( $contact['email'] ) && ! empty( $contact['name'] ) && is_string( $contact['name'] ) ) :
				return $single ? array( $contact['email'], (string) $contact['name'] ) : array( array( $contact['email'], (string) $contact['name'] ) );
			/// Format array( 'email' => [email] );
			elseif( $email = $this->parse_contact_string( $contact['email'] ) ) :
				return $single ? current( $email ) : $email;
			endif;
		elseif( $email = $this->parse_contact_string( $contact ) ) :		
			return $single ? current( $email ) : $email;
		endif;			
	}

	/** == Traitement d'une chaine de contact == **/
	private function parse_contact_string( $contact ){
		if( ! is_string( $contact ) )
			return null;
		
		$contacts = array_map( 'trim', explode( ',', $contact ) );
		
		$return = array();
		foreach( $contacts as $c ) :
			$email = ''; $name = null;
			$bracket_pos = strpos( $c, '<' );
			if ( $bracket_pos !== false ) :
				if ( $bracket_pos > 0 ) :
					$name = substr( $c, 0, $bracket_pos - 1 );
					$name = str_replace( '"', '', $name );
					$name = trim( $name );
				endif;
				$email = substr( $c, $bracket_pos + 1 );
				$email = str_replace( '>', '', $email );
				$email = trim( $email );
			elseif ( '' !== trim( $c ) ) :
				$email = trim( $contact );
			endif;
			
			if( ! empty( $email ) && is_email( $email ) )
				$return[] = ( ! empty( $name ) ) ? array( $email, $name ) : array( $email );
		endforeach;
		
		return $return;		
	}

	/** == Formatage de contact == **/
	private function format_contact( $contact, $single = true ){
		if( ! $single ) :
			return implode( ',', array_map( array( $this, 'format_contact' ), $contact ) );
		endif;	
        if( empty( $contact[1] ) )
            return $contact[0];
		else
            return "$contact[1] <$contact[0]>";
	} 
	
	/** == Traitement des variables d'environnement == **/
	private function parse_merge_vars( $output )
	{
		$defaults = array(
			'SITE:URL'			=> site_url('/'),
			'SITE:NAME'			=> get_bloginfo( 'name' ),
			'SITE:DESCRIPTION'	=> get_bloginfo( 'description' ),
		);
		$merge_vars = wp_parse_args( $this->merge_vars, $defaults );
					
		$callback = function( $matches ) use( $merge_vars ){
			if( ! isset( $matches[1] ) )
					return $matches[0];
			
			if( isset( $merge_vars[$matches[1]] ) )
				return $merge_vars[$matches[1]];
			
			return $matches[0];
		};
	
		$output = preg_replace_callback( '/'. $this->vars_format .'/', $callback, $output );
		
		return $output;
	}
	
	/** == Execution automatique des actions == **/
	private function execute_auto(){
		switch( $this->auto ) :
			default :
				return;
				break;
			case 'send' :
				$this->send();
				break;
			case 'debug' :
				$this->debug();
				break;
		endswitch;
	}	
			
	/* = METHODES PUBLIQUES = */
	/** == Préparation de l'email == **/
	public function prepare( $args = array() ){
		if( $args )
			$this->parse_args( $args );	
				
		// Definition des entêtes du mail
		$this->set_headers();
		
		// Définition du message de l'email
		/// Sujet
		$this->subject = wp_unslash( $this->subject );
		
		/// Préparation du message
		if( ! $this->auto )
			return;
		
		$this->execute_auto();
	}
	
	/** == Récupération de la sortie au format HTML == **/
	public function get_html(){
		if( ! $this->output_html )
			$this->prepare_output_html();
		
		return $this->output_html;
	}
	
	/** == Récupération de la sortie au format HTML == **/
	public function get_text(){
		if( ! $this->output_text )
			$this->prepare_output_text();
		
		return $this->output_text;
	}
	
	/** == Récupération de la sortie au format HTML == **/
	public function get_debug(){
		if( ! $this->output_debug )
			$this->prepare_output_debug();

		return $this->output_debug;
	}
	
	/** == Affichage en mode DEBUG == **/
	public function debug(){
		echo $this->get_debug();
		exit;
	}
	
	/** == Envoi du mail == **/
	public function send()
	{
		$this->prepare_message();
		
		switch( $this->engine ) :
			default :
				break;
			case 'wp_mail' :
				global $phpmailer;	
										
				switch( $this->type ) :
					case 'html' :
						$message = $this->output_html;
						break;
					case 'plain' :
						$message = $this->output_text;
						break;
					case 'mixed' :		
						$message 	= $this->output_html;
						$txt		= $this->output_text;
						add_action( 
							'phpmailer_init', 
							function( $mailer ) use ( $message, $txt )
							{
								$mailer->isHTML(true);
								$mailer->Body 		= $mailer->normalizeBreaks( $message );
								$mailer->AltBody 	= $mailer->normalizeBreaks( $txt );
							}
						);
						break;
				endswitch;				

				// Expédition du message
				if( ! wp_mail( $this->format_contact( $this->to, false ), $this->subject, $message, $this->headers, $this->attachments ) ) :
					return $this->errors = new \WP_Error( 'tiFyMailer_Error', $phpmailer->ErrorInfo );
				else :
					return $this->success = true;
				endif;
				break;
			case 'smtp' :
				$mailer = new \PHPMailer;
				
				// Configuration SMTP
				$mailer->isSMTP();	
				$mailer->Host 		= isset( $this->engine_opts['host'] ) ? $this->engine_opts['host'] : 'localhost';
				$mailer->SMTPAuth	= isset( $this->engine_opts['auth'] ) ? $this->engine_opts['auth'] : false;
				$mailer->Username 	= isset( $this->engine_opts['username'] ) ? $this->engine_opts['username'] : '' ;
				$mailer->Password 	= isset( $this->engine_opts['password'] ) ? $this->engine_opts['password'] : '' ;
				$mailer->Port 		= isset( $this->engine_opts['port'] ) ? (int) $this->engine_opts['port'] : 25 ;
				$mailer->SMTPSecure 	= isset( $this->engine_opts['secure'] ) ? $this->engine_opts['secure'] : '';

				// Encodage des caractères
				$mailer->CharSet = get_bloginfo( 'charset' );				
				
				// Informations de contact
				call_user_func_array( array( $mailer, 'setFrom' ), $this->from );
				foreach( (array) $this->to as $to )
					call_user_func_array( array( $mailer, 'addAddress' ), $to );
				if( ! empty( $this->reply ) )
					foreach( (array) $this->reply as $reply )
						call_user_func_array( array( $mailer, 'addReplyTo' ), $reply );
				if( ! empty( $this->cc ) )
					foreach( (array) $this->cc as $cc )
						call_user_func_array( array( $mailer, 'addCC' ), $cc );
				if( ! empty( $this->bcc ) )
					foreach( (array) $this->bcc as $bcc )
						call_user_func_array( array( $mailer, 'addBCC' ), $bcc );
				
				$mailer->Subject = $this->subject;
				
				switch( $this->type ) :
					case 'html' :
						$mailer->isHTML(true);
						$mailer->Body 		= $mailer->normalizeBreaks( $this->output_html );
						break;
					case 'plain' :
						$mailer->Body 		= $mailer->normalizeBreaks( $this->output_text );
						break;
					case 'mixed' :
						$mailer->isHTML(true);
						$mailer->Body 		= $mailer->normalizeBreaks( $this->output_html );
						$mailer->AltBody 	= $mailer->normalizeBreaks( $this->output_text );						
						break;
				endswitch;
				
				// Expédition du message				
				if( ! $mailer->send() ) :
					return $this->errors = new \WP_Error( 'tiFyMailer_Error', $mailer->ErrorInfo );
				else :
					return $this->success = true;
				endif;
				break;
		endswitch;
	}
}