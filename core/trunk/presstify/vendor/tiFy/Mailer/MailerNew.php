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
 
namespace tiFy\Lib\Mailer;
    
class MailerNew
{
    /* = ARGUMENTS = */
    // Adresses
    /// Destinataires
    public static $To                    = array();
    
    /// Expediteur
    public static $From                  = array();
    
    /// Destinataire de réponse
    public static $ReplyTo               = array();
    
    /// Destinataires de copie carbone
    public static $Cc                    = array();
    
    /// Destinataires de copie cachée
    public static $Bcc                   = array();    
    
    // Sujet
    public static $Subject               = '';
    
    // Message
    public static $Message               = '';
    
    // Entête du message
    public static $MessageHeader         = '';
    
    // Pied de page du message 
    public static $MessageFooter         = '';
    
    // Encapsulation du message au format HTML (true ou chaîne de caractère pour laquelle %s désignera le message)
    public static $HtmlWrap              = true;
    
    // Ajout de l'entête HTML au message (true ou chaîne de caractère)
    public static $HtmlHead              = true;
    
    // Ajout du pied de page HTML au message (true ou chaîne de caractère)
    public static $HtmlFoot              = true;
    
    // Format d'expédition du message (html ou plain ou multi)
    public static $ContentType           = 'multi';
  
    // Encodage des caractères du message
    public static $Charset               = 'UTF-8';
    
    // Coordonnées de contact de l'administrateur principal du site    
    protected static $AdminContact       = null;
    
    // Liste des paramètres autorisés
    protected static $AllowedParams      = array(
        // Paramètres de contact
        'To', 'From', 'ReplyTo', 'Cc', 'Bcc', 
        // Paramètres du mail
        'Subject', 'Message', 'MessageHeader', 'MessageFooter',
        // Formatage du message
        'HtmlWrap', 'HtmlHead', 'HtmlFoot', 'ContentType', 'Charset'
    );
    
    // Agent d'expédition des emails
    protected static $Mailer;
    
    // @deprecated
    private        
            /// Arguments des fichiers joints             
            $attachments,
                        
            /// Arguments de configuration additionnels
            $priority         = 0,                    // Priorité de l'email - Maximale : 1 | Haute : 2 | Normale : 3 | Basse : 4 | Minimale : 5
            $inline_css        = true,                // Conversion des styles CSS en attribut style des balises HTML            
            $reset_css        = true,                 // CSS de réinitialisation du message HTML
            $css            = array(),                // Chemins vers les feuilles de styles CSS
            $custom_css     = '',                     // Attributs css personnalisés (doivent être encapsulé dans une balise style ex : "<style type=\"text/css\">h1{font-size:18px;}</style>")
            $vars_format    = '\*\|(.*?)\|\*',        // Format des variables d'environnements
            $merge_vars        = array(),             // Variables d'environnements
            $additionnal    = array();                // Attributs de configuration supplémentaires requis par les moteurs
            
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
        $params = array();
        
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
    public static function setParams( $params = array() )
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
            if( $param === 'From' ) :
                self::${$param} = self::parseContact( self::${$param}, true );
            else :    
                self::${$param} = self::parseContact( self::${$param} );
            endif;
        endforeach;
        
        // Définition de l'éxpéditeur requis
        if( empty( self::$From ) ) :
             self::$From = self::getAdminContact();    
        endif;

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
        if( isset( self::$From[1] ) ) :
            $phpmailer->setFrom( self::$From[0], self::$From[1] );
        else :
            $phpmailer->setFrom( self::$From[0] );
        endif;    
            
        /// Destinataires
        foreach( (array) self::$To as $contact ) :
            if( ! isset( $contact[1] ) ) :
                $phpmailer->addAddress( $contact[0] );
            else :    
                $phpmailer->addAddress( $contact[0], $contact[1] );
            endif;
        endforeach;

        /// Adresses de réponse
        if( ! empty( self::$ReplyTo ) ) :
            foreach( self::$ReplyTo as $contact ) :
                if( ! isset( $contact[1] ) ) :
                    $phpmailer->addReplyTo( $contact[0] );
                else :    
                    $phpmailer->addReplyTo( $contact[0], $contact[1] );
                endif;            
            endforeach;
        endif;
        
        /// Copie carbone
        if( ! empty( self::$Cc ) ) :
            foreach( self::$Cc as $contact ) :
                if( ! isset( $contact[1] ) ) :
                    $phpmailer->addCC( $contact[0] );
                else :    
                    $phpmailer->addCC( $contact[0], $contact[1] );
                endif;            
            endforeach;
        endif;
        
        /// Copie cachée
        if( ! empty( self::$Cc ) ) :
            foreach( self::$Bcc as $contact ) :
                if( ! isset( $contact[1] ) ) :
                    $phpmailer->addBCC( $contact[0] );
                else :    
                    $phpmailer->addBCC( $contact[0], $contact[1] );
                endif;            
            endforeach;
        endif;  
        
        // Sujet du message
        $phpmailer->Subject = self::$Subject;
        
        $phpmailer->CharSet = self::$Charset;
               
        if( in_array( self::$ContentType, array( 'html', 'multi' ) ) ) :
            $phpmailer->isHTML(true);
        
            $phpmailer->Body    = self::messageHtmlPrepare();
            
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
        $contact[0] = get_option( 'admin_email' );
        if( $user = get_user_by( 'email', get_option( 'admin_email' ) ) ) :   
            $contact[1] = $user->display_name;
        endif;
        
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
        $message  = "<h1>". sprintf( __( 'Ceci est un test d\'envoi de mail depuis le site %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."</h1>";
        $message .= "<p>". __( 'Si ce mail, vous est parvenu c\'est qu\'il vous a été expédié depuis le site : ' ) ."</p>";
        $message .= "<p><a href=\"". site_url( '/' ) ."\" title=\"". sprintf( __( 'Lien vers le site internet - %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."\">". get_bloginfo( 'blogname' ) ."</a><p><br>";
        $message .= "<p>". __( 'Néanmoins, il pourrait s\'agir d\'une erreur, si vous n\'étiez pas concerné par ce mail je vous prie d\'accepter nos excuses.', 'tify' ) ."</p>";
        $message .= "<p>". __( 'Vous pouvez dès lors en avertir l\'administrateur du site à cette adresse : ', 'tify' ) ."</p>";
        $message .= "<p><a href=\"mailto:". get_option( 'admin_email' ) ."\" title=\"". sprintf( __( 'Contacter l\'administrateur du site - %s', 'tify' ), get_bloginfo( 'blogname' ) ) ."\">". get_option( 'admin_email' ) ."</a></p><br>";
        $message .= "<p>". __( 'Celui-ci fera en sorte qu\'une telle erreur ne se renouvelle plus.', 'tify' ) ."</p><br>";
        $message .= "<p>". __( 'Merci de votre compréhension', 'tify' ) ."</p>";
                
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
    public static function parseContact( $contact, $single = false )
    {
        $output = "";
        if( is_array( $contact ) ) :
            // Tableau indexé
            if( array_keys( $contact ) === range(0, count( $contact )-1 ) ) :
                /// Format array( [email], [(optional) name] )
                if( is_string( $contact[0] ) && is_email( $contact[0] ) ) :
                    if( count( $contact ) === 1 ) :
                        return $contact;
                    elseif( ( count( $contact ) === 2 ) && is_string( $contact[1] ) ) :
                        return $contact;
                    endif;
                endif;
                $contact = array_map( 'self::parseContact', $contact );
                
                foreach( $contact as $key => $value ) :
                    if( is_null( $value ) ) :
                        unset( $contact[$key] );
                    endif;
                endforeach;
                    
                if( ! empty( $contact ) ) :
                    return $contact;
                endif;
            
            // Tableau Associatif
            /// Format array( 'email' => [email], 'name' => [name] );
            elseif( isset( $contact['email'] ) && is_email( $contact['email'] ) && ! empty( $contact['name'] ) && is_string( $contact['name'] ) ) :
                return array( $contact['email'], (string) $contact['name'] );
            
            /// Format array( 'email' => [email] );
            elseif( isset( $contact['email'] ) ) :
                return self::parseContactString( $contact['email'] );
            endif;
        elseif( is_string( $contact )  ) : 
            return self::parseContactString( $contact );
        endif;            
    }

    /** == Traitement d'une chaine de contact == **/
    public static function parseContactString( $contact )
    {
        if( ! is_string( $contact ) )
            return null;
        
        $contact  = array_map( 'trim', explode( ',', $contact ) );
        
        $contacts = array();
        foreach( $contact as $c ) :
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
            elseif ( !empty( $c ) ) :
                $email = $c;
            endif;
            
            if( ! empty( $email ) && is_email( $email ) ) :
                $contacts[] = array( $email, $name );
            endif;
        endforeach;

        if( empty( $contacts ) ) :
            return null;
        else :
            return $contacts;
        endif;        
    }
    
    /** == Formatage d'un contact == **/
    public static function formatContactArray( $contact )
    {    
        if( is_null( $contact[1] ) ) :
            return $contact[0];
        else :
            return "{$contact[1]} <{$contact[0]}>";
        endif;
    }
        
    /** == Préparation du message HTML == **/
    public static function messageHtmlPrepare()
    {
        $output = "";

        if( self::$HtmlWrap ) :
            $output .=  "<body style=\"background:#FFF;color:#000;font-family:Arial, Helvetica, sans-serif;font-size:12px\" link=\"#0000FF\" alink=\"#FF0000\" vlink=\"#800080\" bgcolor=\"#FFFFFF\" text=\"#000000\" yahoo=\"fix\">";
            $output .=      self::messageHtmlWrap();
            $output .=  "</body>";
        endif;

        if( self::$HtmlHead ) :
            $output = self::messageHtmlHead() . $output;
        endif;

        if( self::$HtmlFoot ) :
            $output .= self::messageHtmlFoot();
        endif;

        return $output;
    }
    
    /** == Encapsulation du corps du message HTML == **/
    protected static function messageHtmlWrap()
    {
        $output     = "";        
        if( is_bool( self::$HtmlWrap ) ) :            
            $output .=      "<div id=\"body_style\" style=\"padding:15px\">";
            $output .=             "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" bgcolor=\"#FFFFFF\" width=\"600\" align=\"center\">";
            $output .=                 "<tr>";
            $output .=                  "<td width=\"600\">". self::$Message ."</td>";
            $output .=              "</tr>";
            $output .=          "</table>";
            $output .=      "</div>";
        elseif( is_string( self::$HtmlWrap ) ) :
            $output .= sprintf( self::$HtmlWrap, self::$Message );
        endif;

        return $output;
    }
    
    /** == Entête du message HTML == **/
    protected static function messageHtmlHead()
    {
        $output  = "";
        if( is_bool( self::$HtmlHead ) ) :                
            $output .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
            $output .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
            $output .=     "<head>";
            $output .=         "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=". self::$Charset ."\" />";
            $output .=         "<title>". self::$Subject ."</title>";
            //$output .=     $this->append_css();
            $output .=     "</head>";
        elseif( is_string( self::$HtmlHead ) ) :
            $output .= self::$HtmlHead;
        endif;
        
        return $output;
    }
    
    /** == Pied de page du message HTML == **/
    protected static function messageHtmlFoot()
    {
        $output  = "";
        if( is_bool( self::$HtmlFoot ) ) :
            $output .= "</html>";
        elseif( is_string( self::$HtmlFoot ) ) :
            $output .= self::$HtmlFoot;
        endif;
        
        return $output;
    }
    
    /** == Affichage du mail en mode debug == **/
    public static function debug( $params = array() )
    {
        self::setParams( $params );
        $mailer = self::setMailer();
        $mailer->preSend();

        $recipients = array_map( 'self::formatContactArray', $mailer->getToAddresses() );
        $headers = explode( $mailer->LE, $mailer->createHeader() );
                
        $output  =  "";
        $output .=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
        $output .=  "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
        $output .=      "<head>";
        $output .=          "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />";
        $output .=          "<title>". $mailer->Subject ."</title>";
        //$output .=     $this->append_css();
        $output .=      "</head>";
        $output .=          "<body style=\"width:100%;margin:0;padding:0;background:#FFF;color:#000;font-family:Arial, Helvetica, sans-serif;font-size:12px;\" link=\"#0000FF\" alink=\"#FF0000\" vlink=\"#800080\" bgcolor=\"#FFFFFF\" text=\"#000000\" yahoo=\"fix\">";
        $output .=              "<table cellspacing=\"0\" border=\"0\" bgcolor=\"#EEEEEE\" width=\"100%\" align=\"center\" style=\"border-bottom:solid 1px #AAA;\">";        
        $output .=                  "<tbody>";
        $output .=                      "<tr>";
        $output .=                          "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
        $output .=                              "<h3 style=\"margin-bottom:10px;\">". self::$Subject ."</h3>";
        //$output .=                              __( 'à', 'tify' ) ." ". htmlspecialchars( implode( ', ', $recipients ) );
        $output .=                              "<hr style=\"display:block;margin:10px 0 5px;background-color:#CCC; height:1px; border:none;\">";
        $output .=                          "</td>";
        $output .=                      "</tr>";
        
        foreach(  $headers as $value ) :
            $output .=                  "<tr>";
            $output .=                      "<td style=\"line-height:1.1em;padding:3px 10px;color:#000;font-size:13px\">";
            $output .=                          htmlspecialchars( $value );
            $output .=                      "</td>";
            $output .=                  "</tr>";            
        endforeach;
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
            $output .= self::messageHtmlWrap();
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
    
    /** == Envoi du message == **/
    public static function send( $params )
    {
        self::setParams( $params );
        $mailer = self::setMailer();
        $mailer->Send();
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
    
    /*** === Ajout des Feuilles de style CSS === ***/
    private function append_css(){
        $output = "";
        if( $this->reset_css )
            $output .= "<style type=\"text/css\">". $this->line_break . file_get_contents( $this->Dirname .'/css/reset.emailology.org.css' ) . $this->line_break ."</style>";
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
            
        $xmldoc = new \DOMDocument( '1.0', self::$Charset );
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
        
    /** == Traitement des variables d'environnement == **/
    private function parse_merge_vars( $output )
    {
        $defaults = array(
            'SITE:URL'              => site_url('/'),
            'SITE:NAME'             => get_bloginfo( 'name' ),
            'SITE:DESCRIPTION'      => get_bloginfo( 'description' ),
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
}