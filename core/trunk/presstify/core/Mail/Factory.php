<?php 
namespace tiFy\Core\Mail;

use \tiFy\Lib\Mailer\MailerNew;

class Factory extends \tiFy\App\Factory
{
    /* = ARGUMENTS = */
    /// Destinataires
    public $To                      = array();
    
    /// Expediteur
    public $From                    = array();
    
    /// Destinataire de réponse
    public $ReplyTo                 = array();
    
    /// Destinataires de copie carbone
    public $Cc                      = array();
    
    /// Destinataires de copie cachée
    public $Bcc                     = array();    
    
    // Sujet
    public $Subject                 = '';
    
    // Message
    public $Message                 = '';
    
    // Entête du message
    public $MessageHeader           = '';
    
    // Pied de page du message 
    public $MessageFooter           = '';
    
    // Variables d'environnement
    public $MergeTags               = array();
    
    // Format des variables d'environnements
    public $MergeTagsFormat         = '\*\|(.*?)\|\*';
    
    // Personnalisation des styles CSS
    public $CustomCSS               = '';
    
    // Format d'expédition du message (html ou plain ou multi)
    public $ContentType             = 'multi';
  
    // Encodage des caractères du message
    public $Charset                 = 'UTF-8';
            
    /* = CONSTRUCTEUR = */
    public function __construct( $params = array() )
    {        
        // Traitement des paramètres d'envoi de l'email        
        $this->setParams( $params );
    }
    
    /* = CONTROLEURS = */    
    /** == Définition des paramètres == **/
    final public function setParams( $params = array() )
    {          
        foreach( (array) $params as $param => $value ) :
            $this->setParam( $param, $value );
        endforeach;
    }
    
    /** == Définition d'un paramètre == **/
    final public function setParam( $param, $value )
    {
        $param = MailerNew::sanitizeName( $param );
        if( MailerNew::isAllowedParam( $param ) ) :
            $this->{$param} = $value;  
        endif;
    }
            
    /** == Récupération des paramètres == **/
    final public function getParams()
    {
        $params = array();
        foreach( MailerNew::getAllowedParams() as $param ) :
            $params[$param] = $this->getParam( $param );
        endforeach;
        
        return $params;
    }
    
    /** == Récupération d'un paramètre == **/
    final public function getParam( $param )
    {
        $param = MailerNew::sanitizeName( $param );
        if( ! MailerNew::isAllowedParam( $param ) )
            return;
        
        if( method_exists( $this, 'get'. $param ) ) :
            return call_user_func( array( $this, 'get'. $param ) );
        elseif( isset( $this->{$param} ) ) :
            return $this->{$param};
        endif;      
    }
    
    /** == Envoi de l'email == **/
    final public function send()
    {        
        return MailerNew::send( $this->getParams() );
    }
    
    /** == Envoi de l'email == **/
    final public function debug()
    {        
        echo MailerNew::debug( $this->getParams() );
        exit;
    }
}