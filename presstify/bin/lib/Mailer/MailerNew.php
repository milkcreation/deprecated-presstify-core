<?php
/** 
 * @see https://emails.hteumeuleu.fr/
 * @see http://openclassrooms.com/courses/e-mail-envoyer-un-e-mail-en-php 
 * @see http://www.iana.org/assignments/message-headers/message-headers.xhtml
 * 
 * COMPATIBILITE CSS
 * @see http://www.email-standards.org/
 * @see https://www.campaignmonitor.com/css/
 * @see http://templates.mailchimp.com/resources/email-client-css-support/
 * 
 * EMAIL BOILERPLATE
 * @see http://www.emailology.org
 * @see http://templates.mailchimp.com/development/css/reset-styles/
 * @see http://templates.mailchimp.com/development/css/client-specific-styles/
 * @see http://templates.mailchimp.com/development/css/outlook-conditional-css/
 * 
 * LISTE DE MERGE TAGS
 * http://kb.mailchimp.com/merge-tags/all-the-merge-tags-cheat-sheet#Merge-tags-for-list-and-account-information
 **/

namespace tiFy\Lib\Mailer;

use tiFy\Core\Mail\Queue;

class MailerNew
{
    /* = ARGUMENTS = */
    // Adresses
    /// Destinataires
    public static $To                   = array();
    
    /// Expediteur
    public static $From                 = array();
    
    /// Destinataire de réponse
    public static $ReplyTo              = array();
    
    /// Destinataires de copie carbone
    public static $Cc                   = array();
    
    /// Destinataires de copie cachée
    public static $Bcc                  = array();
    
    // Pièces jointes
    public static $Attachments          = array();
    
    // Sujet
    public static $Subject              = '';
    
    // Message
    public static $Message              = '';
    
    // Entête du message
    public static $MessageHeader        = '';
    
    // Pied de page du message 
    public static $MessageFooter        = '';
    
    // Variables d'environnement
    public static $MergeTags            = array();
    
    // Format des variables d'environnements
    public static $MergeTagsFormat      = '\*\|(.*?)\|\*';
    
    // Personnalisation des styles CSS
    public static $CustomCss            = '';
    
    // Format d'expédition du message (html ou plain ou multi)
    public static $ContentType          = 'multi';
  
    // Encodage des caractères du message
    public static $Charset              = 'UTF-8';
    
    // @todo Priorité null (default), 1 = High, 3 = Normal, 5 = low
    public static $Priority             = null;
    
    // @todo Serveur d'envoi
    public static $Smtp                 = false;
    
    // Coordonnées de contact de l'administrateur principal du site    
    protected static $AdminContact      = null;
        
    // Agent d'expédition des emails
    protected static $Mailer;
    
    // Contenu du corps du message HTML
    protected static $HtmlBodyContent;
    
    // Liste des paramètres autorisés
    protected static $AllowedParams     = array(
        // Paramètres de contact
        'To', 'From', 'ReplyTo', 'Cc', 'Bcc', 
        // Paramètres du mail
        'Attachments', 'Subject', 'Message', 'MessageHeader', 'MessageFooter',
        // Variables d'environnement
        'MergeTags', 'MergeTagsFormat',
        // Formatage du message
        'CustomCss', 'ContentType', 'Charset',
        // Paramètres d'expédition
        'Priority', 'Smtp'
    );
    
    // @deprecated
    private        
            /// Arguments des fichiers joints             
            $attachments,
            $priority           = 0,                    // Priorité de l'email - Maximale : 1 | Haute : 2 | Normale : 3 | Basse : 4 | Minimale : 5
            $additionnal        = array();              // Attributs de configuration supplémentaires requis par les moteurs
            
    /* = PARAMETRAGE = */    
    /** == Formatage == **/
    public static function sanitizeName( $name )
    {
        return implode( array_map( 'ucfirst', explode( '_', $name ) ) );
    }
    
    /** == Récupération de la liste de paramètres permis == **/
    public static function getAllowedParams()
    {
        return self::$AllowedParams;
    }
    
    /** == Vérifie l'existance d'un paramètre == **/
    public static function isAllowedParam( $param )
    {
        $param = self::sanitizeName( $param );
        
        return in_array( $param, self::$AllowedParams );
    }
    
    /** == Récupération des paramètres == **/
    public static function getParams()
    {
        $params = [];
        
        foreach( self::getAllowedParams() as $param ) :
            $params[$param] = self::${$param};
        endforeach;        
        
        return $params;
    }
    
    /** == Récupération d'un paramètre == **/
    public static function getParam( $param )
    {
        // Bypass
        if( ! in_array( $param, self::getAllowedParams() ) )
            return;        
        if( ! isset( self::${$param} ) )
            return;
        
        return self::${$param};
    }
    
    /** == Traitement des arguments == **/
    public static function setParams($params = [])
    {
        // Cartographie des paramètres
        $_params = array();
        array_walk( $params, function( $v, $k ) use (&$_params){ $_params[self::sanitizeName($k)] = $v;});
                
        foreach( self::getAllowedParams() as $param ) :
            if( ! isset( $_params[$param] ) )
                continue;
            self::${$param} = $_params[$param];
        endforeach;
        
        // Traitement des arguments de contact
        foreach( array( 'From', 'To', 'ReplyTo', 'Cc', 'Bcc' ) as $param ) :
            if( empty( self::${$param} ) )
                continue; 
            self::${$param} = self::parseContact(self::${$param});
        endforeach;

        // Traitement des pièces jointes
        if( ! empty( self::$Attachments ) ) :
            foreach( self::$Attachments as $n => $attachment ) :
                self::$Attachments[$n] = self::parseAttachment( $attachment );
            endforeach;
        endif;
        
        // Définition de l'expéditeur (requis)
        if( empty( self::$From ) ) :
             self::$From = array( self::getAdminContact() );    
        endif;
        self::$From = current( self::$From );
        
        // Définition du destinataire requis
        if( empty( self::$To ) ) :
            self::$To = array( self::getAdminContact() );
        endif;

        // Sujet de l'email        
        if( empty( self::$Subject ) ) :
            self::$Subject = self::getDefaultSubject();
        endif;
        
        // Encodage des caractères
        if( empty( self::$Charset ) ) :
            self::$Charset = get_bloginfo( 'charset' );
        endif;
        
        // Traitement du message au format HTML
        if( in_array( self::$ContentType, array( 'html', 'multi' ) ) ) :
            /// Message de l'email        
            if( empty( self::$Message ) ) :
                self::$Message = self::getDefaultMessageHtml();
            endif;
                        
            // Entête du message de l'email        
            if( ! empty( self::$MessageHeader ) ) :
                self::$Message = self::$MessageHeader . self::$Message;
            endif;
        
            // Pied de page du message de l'email        
            if( ! empty( self::$MessageFooter ) ) :
                self::$Message .= self::$MessageFooter;
            endif;
                        
        // Au format Texte    
        else :
            /// Message de l'email
            if( empty( self::$Message ) ) :
                self::$Message = self::getDefaultMessageText();
            endif;
        endif;    
                       
        return self::getParams();
    }
    
    /** == Définition de l'agent d'expédition des email == **/
    public static function setMailer()
    {
        $phpmailer = new \PHPMailer( true );
    	do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

        /// Expéditeur  
        if( isset( self::$From ) ) :
            $phpmailer->setFrom( self::$From['email'], self::$From['name'] );
        endif;    
            
        /// Destinataires
        foreach( (array) self::$To as $contact ) :
            $phpmailer->addAddress( $contact['email'], $contact['name'] );
        endforeach;

        /// Adresses de réponse
        if( ! empty( self::$ReplyTo ) ) :
            foreach( self::$ReplyTo as $contact ) :
                $phpmailer->addReplyTo( $contact['email'], $contact['name'] );            
            endforeach;
        endif;
        
        /// Copie carbone
        if( ! empty( self::$Cc ) ) :
            foreach( self::$Cc as $contact ) :
                $phpmailer->addCC( $contact['email'], $contact['name'] );          
            endforeach;
        endif;
        
        /// Copie cachée
        if( ! empty( self::$Bcc ) ) :
            foreach( self::$Bcc as $contact ) :
                $phpmailer->addBCC( $contact['email'], $contact['name'] );
            endforeach;
        endif;  

        // Pièces jointes
        if( ! empty( self::$Attachments ) ) :
            foreach( self::$Attachments as $attachment ) :
                if( empty( $attachment['path'] ) ) :
                    continue;
                endif;
                extract( $attachment );
                $phpmailer->addAttachment( $path, $name, $encoding, $type, $disposition );
            endforeach;
        endif;
        
        // Sujet du message
        $phpmailer->Subject = self::$Subject;
        
        $phpmailer->CharSet = self::$Charset;
               
        if( in_array( self::$ContentType, array( 'html', 'multi' ) ) ) :
            $phpmailer->isHTML(true);
        
            $phpmailer->Body    = self::prepareMessageHtml();
            
            if( self::$ContentType === 'multi' ) :
                $html2text = new \Html2Text\Html2Text( self::$Message );
                $phpmailer->AltBody = $html2text->getText();
            endif;
        else :
            $phpmailer->isHTML(false);
        
            $html2text = new \Html2Text\Html2Text( self::$Message );
            $phpmailer->Body = $html2text->getText();
        endif;

        return self::$Mailer = $phpmailer;
    }
    
    /** == Récupération des coordonnées de contact de l'administrateur principal du site == **/
    public static function getAdminContact()
    {
        if( ! empty( self::$AdminContact ) )
            return self::$AdminContact;
        
        $contact = array();
        $contact['email'] = get_option( 'admin_email' );
        $contact['name'] = ( $user = get_user_by( 'email', get_option( 'admin_email' ) ) ) ? $user->display_name : null;
        
        return self::$AdminContact = $contact;
    }
    
    /** == Sujet de l'email de test == **/
    public static function getDefaultSubject()
    {
        return sprintf( __( 'Test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) );
    }
    
    /** == Message HTML de test == **/
    public static function getDefaultMessageHtml()
    {
        $message  = "";
        $message .= "<div id=\"body_style\" style=\"padding:15px\">";
        $message .=     "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bgcolor=\"#FFFFFF\" width=\"600\" align=\"center\">";
        $message .=         "<tr>";
        $message .=             "<td width=\"600\">";
        $message .=                 "<div>";
        $message .=                     "<h1>". sprintf( __( 'Test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."</h1>";
        $message .=                     "<p>". __( 'Si ce mail, vous est parvenu c\'est qu\'un test d\'expédition a été envoyé depuis le site : ' ) ."</p>";
        $message .=                     "<p><a href=\"". site_url( '/' ) ."\" title=\"". sprintf( __( 'Lien vers le site internet - %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."\">". get_bloginfo( 'blogname' ) ."</a><p><br>";
        $message .=                     "<p>". __( 'Néanmoins, il pourrait s\'agir d\'une erreur, si vous n\'étiez pas concerné par ce mail je vous prie d\'accepter nos excuses.', 'tify' ) ."</p>";
        $message .=                     "<p>". __( 'Vous pouvez toutefois avertir l\'administrateur du site à cette adresse : ', 'tify' ) ."</p>";
        $message .=                     "<p><a href=\"mailto:". get_option( 'admin_email' ) ."\" title=\"". sprintf( __( 'Contacter l\'administrateur du site - %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."\">". get_option( 'admin_email' ) ."</a></p><br>";
        $message .=                     "<p>". __( 'Celui-ci fera en sorte qu\'une telle erreur ne se renouvelle plus.', 'tify' ) ."</p><br>";
        $message .=                     "<p>". __( 'Merci de votre compréhension', 'tify' ) ."</p>";
        $message .=                 "</div>";
        $message .=             "</td>";
        $message .=          "</tr>";
        $message .=      "</table>";
        $message .=  "</div>";
        
        return $message;
    }
    
    /** == Message texte de test == **/
    public static function getDefaultMessageText()
    {
        $message  = sprintf( __( 'Ceci est un test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) );
        $message .= __( 'Si ce mail, vous est parvenu c\'est qu\'il vous a été expédié depuis le site : ' );
        $message .= site_url( '/' );
        $message .= __( 'Néamoins, il pourrait s\'agir d\'une erreur, si vous n\'étiez pas concerné par ce mail je vous prie d\'accepter nos excuses.', 'tify' );
        $message .= __( 'Vous pouvez dès lors en avertir l\'administrateur du site à cette adresse : ', 'tify' );
        $message .= get_option( 'admin_email' );
        $message .= __( 'Celui-ci fera en sorte qu\'une telle erreur ne se renouvelle plus.', 'tify' );
        $message .= __( 'Merci de votre compréhension', 'tify' );
        
        return $message;
    }
    
    /** == Traitement == **/
    public static function parseContact( $contact, $depth = 0 )
    {
        $output = "";
        
        if( is_string( $contact ) && preg_match( '/,/', $contact ) ) :
            $contact  = array_map( 'trim', explode( ',', $contact ) );
        endif;       
        
        if( is_string( $contact )  ) : 
            $contact = self::parseContactString( $contact );
        
            return ! $depth ? array( $contact ) : $contact;
        elseif( is_array( $contact ) ) :
            // Tableau indexé
            if( array_keys( $contact ) === range(0, count( $contact )-1 ) ) :
                /// Format array( [email], [(optional) name] )
                if( is_string( $contact[0] ) && is_email( $contact[0] ) ) :
                    if( count( $contact ) === 1 ) :
                        $contact = array( 'email' => $contact[0], 'name' => null );
        
                        return ! $depth ? array( $contact ) : $contact;
                    elseif( ( count( $contact ) === 2 ) && is_string( $contact[1] ) && ! is_email( $contact[1] ) ) :
                        $contact = array( 'email' => $contact[0], 'name' => $contact[1] );
                    
                        return ! $depth ? array( $contact ) : $contact;
                    endif;
                endif;
                if( $depth < 1 ) :
                    return array_map( function( $contact ){ return self::parseContact( $contact, 1 ); }, $contact );
                endif;    
                
            // Tableau Associatif
            /// Format array( 'email' => [email], 'name' => [name] );
            elseif( isset( $contact['email'] ) && is_email( $contact['email'] ) ) :
                if( empty( $contact['name'] ) || ! is_string( $contact['name'] ) ) :
                    $contact['name'] = null;
                endif;
                
                return ! $depth ? array( $contact ) : $contact;
            endif;
        endif;            
    }

    /** == Traitement d'une chaine de contact == **/
    public static function parseContactString( $contact )
    {
        $email = ''; $name = null;
        $bracket_pos = strpos( $contact, '<' );
        if ( $bracket_pos !== false ) :
            if ( $bracket_pos > 0 ) :
                $name = substr( $contact, 0, $bracket_pos - 1 );
                $name = str_replace( '"', '', $name );
                $name = trim( $name );
            endif;
            $email = substr( $contact, $bracket_pos + 1 );
            $email = str_replace( '>', '', $email );
            $email = trim( $email );
        elseif ( ! empty( $contact ) ) :
            $email = $contact;
        endif;
        
        if( ! empty( $email ) && is_email( $email ) ) :
            $contact = array( 'email' => $email, 'name' => $name );
        endif;
        
        return $contact;        
    }
    
    /** == Traitement des pièces jointes == **/
    public static function parseAttachment($attachment)
    {
        $defaults = array(
            'path'          => '',
            'name'          => '',
            'encoding'      => 'base64',
            'type'          => '',
            'disposition'   => 'attachment'
        );

        if (is_string($attachment)) :
            $attachment = [
                'path'          => $attachment
            ];
        endif;

        return \wp_parse_args($attachment, $defaults);
    }
    
    /** == Formatage d'un contact == **/
    public static function formatContactArray( $contact )
    {    
        if( is_null( $contact['name'] ) ) :
            return $contact['email'];
        else :
            return "{$contact['name']} <{$contact['email']}>";
        endif;
    }
        
    /** == Préparation du message HTML == **/
    public static function prepareMessageHtml()
    { 
        if ( version_compare( phpversion(), '5.4', '<=' ) )
            return self::$Message;
        
        // Récupération de la structure du DOM original du message
        $ori = new \DOMDocument;
        $ori->loadHTML( mb_convert_encoding( self::$Message, 'HTML-ENTITIES', self::$Charset ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        $ori->encoding = self::$Charset;    
        
        // Création de la balise DOCTYPE
        $implementation = new \DOMImplementation;
        if( ! $ori->doctype ) :            
            $doctype = $implementation->createDocumentType( 'html', '', '' );
        else :
            $doctype = $implementation->createDocumentType( $ori->doctype->name, $ori->doctype->publicId, $ori->doctype->systemId );     
        endif;
        $dom = $implementation->createDocument( '', '', $doctype );
        $dom->encoding = self::$Charset;
        $dom->strictErrorChecking = false;
        
        // Création de la balise HTML
        $_html = $ori->getElementsByTagName('html');
        if( ! $_html->length ) :
            $html = $dom->createElement( 'html' );            
        else :
            $html = $dom->appendChild( $dom->importNode( $_html->item(0), false ) );
        endif;
        $html = $dom->appendChild( $html );
        
        // Création de la balise HEAD
        $_head = $ori->documentElement->getElementsByTagName( 'head' );
        if( ! $_head->length  ) :
            $head = $dom->createElement( 'head' );
            // Meta Content
            $node = $head->appendChild( $dom->createElement( 'meta' ) );
            $node->setAttribute( 'http-equiv', 'Content-Type' );
            $node->setAttribute( 'content', 'text/html; charset='. self::$Charset );
            // Meta Title
            $node = $head->appendChild( $dom->createElement( 'title', self::$Subject ) );
        else :
            $head = $dom->appendChild( $dom->importNode( $_head->item(0), true ) );
        endif; 
        
        /// Insertion des styles CSS personnalisés               
        if( self::$CustomCss ) :
            $node = $head->appendChild( $dom->createElement( 'style', self::$CustomCss ) );
            $node->setAttribute( 'type', 'text/css' );
        endif;
        $head = $html->insertBefore( $head, $html->firstChild );
        
        // Création de la balise BODY
        $_body = $ori->documentElement->getElementsByTagName( 'body' );        
        if( ! $_body->length ) :
            $attrs = array(
                'style'     => 'background:#FFF;color:#000;font-family:Arial,Helvetica,sans-serif;font-size:12px',
                'link'      => '#0000FF',
                'alink'     => '#FF0000',
                'vlink'     => '#800080',
                'bgcolor'   => '#FFFFFF',
                'text'      => '#000000',
                'yahoo'     => 'fix'
            );
            $body = $dom->createElement( 'body' );
            foreach( $attrs as $key => $value ) :
                $body->setAttribute( $key, $value );
            endforeach; 
            
            $message = new \DOMDocument;            
            $message->loadHTML( mb_convert_encoding( self::$Message, 'HTML-ENTITIES', self::$Charset ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            $message->encoding = self::$Charset;
            
            $body->appendChild( $dom->importNode( $message->documentElement, true ) );
        else :                    
            $body = $dom->importNode( $_body->item(0), true );
        endif;
        $body = $html->appendChild( $body );        

        // Traitement de la sortie        
        $dom->normalizeDocument();
        $dom->formatOutput = true;
        $html = $dom->saveHTML();        

        // Traitement des variables d'environnement
        $html = self::parseMergeTags( $html, self::$MergeTags, self::$MergeTagsFormat );
        
        // Mise en ligne du CSS
        $e = new \Pelago\Emogrifier( $html );    
        $html = $e->emogrify();
        self::$HtmlBodyContent = $e->emogrifyBodyContent();        
        
        return $html;     
    }
    
    /** == Traitement des variables d'environnement == **/
    public static function parseMergeTags( $output, $tags, $format = '\*\|(.*?)\|\*' )
    {
        $defaults = array(
            'SITE:URL'              => site_url('/'),
            'SITE:NAME'             => get_bloginfo( 'name' ),
            'SITE:DESCRIPTION'      => get_bloginfo( 'description' ),
        );
        $tags = wp_parse_args( $tags, $defaults );
                    
        $callback = function( $matches ) use( $tags ){
            if( ! isset( $matches[1] ) )
                    return $matches[0];
            
            if( isset( $tags[$matches[1]] ) )
                return $tags[$matches[1]];
            
            return $matches[0];
        };
    
        $output = preg_replace_callback( '/'. $format .'/', $callback, $output );
        
        return $output;
    }
    
    /** == Translation des paramètres au format wp_mail == **/
    public static function wp_mail( $params )
    {       
        self::$ContentType = 'plain';
        self::setParams( $params );

        $to = array_map( 'self::formatContactArray', self::$To );
        
        $subject = self::$Subject;        
        $message = self::$Message;
        
        $headers = array();
        $headers[] = 'From:'. self::formatContactArray( self::$From );

        $attachments = array();
        
        return compact( 'to', 'subject', 'message', 'headers', 'attachments' );       
    }

    /**
     * Prévisualisation du mail
     */
    public static function preview($params = [])
    {
        self::setParams($params);
        $mailer = self::setMailer();
        $mailer->preSend();
                
        $output  =  "";
        $output .=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
        $output .=  "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
        $output .=      "<head>";
        $output .=          "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />";
        $output .=          "<title>". $mailer->Subject ."</title>";
        $output .=      "</head>";
        $output .=          "<body style=\"width:100%;margin:0;padding:0;background:#FFF;color:#000;font-family:Arial, Helvetica, sans-serif;font-size:12px;\" link=\"#0000FF\" alink=\"#FF0000\" vlink=\"#800080\" bgcolor=\"#FFFFFF\" text=\"#000000\" yahoo=\"fix\">";
        $output .=              "<table cellspacing=\"0\" border=\"0\" bgcolor=\"#EEEEEE\" width=\"100%\" align=\"center\" style=\"border-bottom:solid 1px #AAA;\">";        
        $output .=                  "<tbody>";
        $output .=                      "<tr>";
        $output .=                          "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
        $output .=                              "<h3 style=\"margin-bottom:10px;\">". self::$Subject ."</h3>";
        $output .=                              "<hr style=\"display:block;margin:10px 0 5px;background-color:#CCC; height:1px; border:none;\">";
        $output .=                          "</td>";
        $output .=                      "</tr>";
        
        $headers = explode( $mailer->LE, $mailer->createHeader() );
        foreach ($headers as $value) :
            $output .=                  "<tr>";
            $output .=                      "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
            $output .=                          htmlspecialchars($value);
            $output .=                      "</td>";
            $output .=                  "</tr>";
        endforeach;

        // To
        if ($Adresses = $mailer->getToAddresses()) :
            $output .=                  "<tr>";
            $output .=                      "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
            $output .=                          "To: ";
            $adress = [];
            foreach($Adresses as $Adress) :
                $adress[] = isset($Adress[1]) ? htmlspecialchars("{$Adress[1]} <{$Adress[0]}>") : "{$Adress[0]}";
            endforeach;
            $output .=                          join(', ', $adress);
            $output .=                      "</td>";
            $output .=                  "</tr>";
        endif;

        // CC
        if ($Adresses = $mailer->getCcAddresses()) :
            $output .=                  "<tr>";
            $output .=                      "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
            $output .=                          "Cc: ";
            $adress = [];
            foreach($Adresses as $Adress) :
                $adress[] = isset($Adress[1]) ? htmlspecialchars("{$Adress[1]} <{$Adress[0]}>") : "{$Adress[0]}";
            endforeach;
            $output .=                          join(', ', $adress);
            $output .=                      "</td>";
            $output .=                  "</tr>";
        endif;

        // BCC
        if ($Adresses = $mailer->getBccAddresses()) :
            $output .=                  "<tr>";
            $output .=                      "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
            $output .=                          "Bcc: ";
            $adress = [];
            foreach($Adresses as $Adress) :
                $adress[] = isset($Adress[1]) ? htmlspecialchars("{$Adress[1]} <{$Adress[0]}>") : "{$Adress[0]}";
            endforeach;
            $output .=                          join(', ', $adress);
            $output .=                      "</td>";
            $output .=                  "</tr>";
        endif;

        // ReplyTo
        if ($Adresses = $mailer->getReplyToAddresses()) :
            $output .=                  "<tr>";
            $output .=                      "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
            $output .=                          "ReplyTo: ";
            $adress = [];
            foreach($Adresses as $Adress) :
                $adress[] = isset($Adress[1]) ? htmlspecialchars("{$Adress[1]} <{$Adress[0]}>") : "{$Adress[0]}";
            endforeach;
            $output .=                          join(', ', $adress);
            $output .=                      "</td>";
            $output .=                  "</tr>";
        endif;

        $output .=                  "</tbody>";
        $output .=              "</table>";            
                        
        if( self::$ContentType === 'multi' ) :
            $output .=          "<table cellspacing=\"0\" border=\"0\" width=\"600\" align=\"center\" style=\"margin:30px auto;\">";
            $output .=              "<tbody>";
            $output .=                  "<tr>";
            $output .=                      "<td style=\"text-align:center;font-size:18px;font-family:courier\">------------ VERSION HTML ------------</td>";
            $output .=                  "</tr>";
            $output .=              "</tbody>";
            $output .=          "</table>";    
        endif; 
        if( in_array( self::$ContentType, array( 'html', 'multi' ) ) ) :
            $output .= self::$HtmlBodyContent;
        endif;
        
        if( self::$ContentType === 'multi' ) :
            $output .=          "<table cellspacing=\"0\" border=\"0\" width=\"600\" align=\"center\" style=\"margin:30px auto;\">";
            $output .=              "<tbody>";
            $output .=                  "<tr>";
            $output .=                      "<td style=\"text-align:center;font-size:18px;font-family:courier\">------------ VERSION TEXTE ------------</td>";
            $output .=                  "</tr>";
            $output .=              "</tbody>";
            $output .=          "</table>";    
        endif;
        if( self::$ContentType === 'plain' ) :
            $output .= $mailer->Body;
        elseif( self::$ContentType === 'multi' ) :
            $output .= $mailer->AltBody;
        endif;
        
        $output .=          "</body>";
        $output .=      "</html>";
        
        return $output;
    }

    /**
     * Mise en file du mail
     *
     * @param array $params Paramètres de configuration du mail
     * @param array $sending Date de programmation d'expédition du mail. Par defaut, envoi immédiat.
     * @param array $extras Données complémentaires
     *
     * @return null|int
     */
    public static function queue($params = [], $sending = '', $extras = [])
    {
        self::setParams($params);
        $mailer = self::setMailer();
        $mailer->preSend();

        return Queue::add(self::getParams(), $sending, $extras);
    }

    /**
     * Envoi du message
     *
     * @param array $params Paramètres de configuration du mail
     *
     * @return void
     */
    public static function send($params = [])
    {
        self::setParams($params);
        $mailer = self::setMailer();
        try {
            return $mailer->Send();
        } catch (phpmailerException $e) {
            wp_die($e->getMessage(), __('Erreur lors de l\'expedition du message', 'tify'), 500);

            return false;
        }
    }
}