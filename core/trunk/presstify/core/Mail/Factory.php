<?php 
namespace tiFy\Core\Mail;

use \tiFy\Lib\Mailer\Mailer;

class Factory extends \tiFy\Environment\App
{
    /* = ARGUMENTS = */
    // Adresses
    /// Destinataires
    protected $Recipients           = array();
    
    /// Expediteur
    protected $Sender               = '';
    
    /// Destinataire de réponse
    protected $ReplyTo              = '';
    
    /// Destinataires de copie carbone
    protected $Cc                   = array();
    
    /// Destinataires de copie cachée
    protected $Bcc                  = array();    
    
    // Sujet
    protected $Subject              = '';
    
    // Message
    protected $Message              = '';
    
    // Entête du Message
    protected $MessageHeader        = '';
    
    // Pied de page du message
    protected $MessageFooter        = '';
    
    // Deboggage
    protected $Debug                = false;  
        
    // Cartographie des paramètres de configuration
    protected static $ParamsMap = array(
        'Recipients'    => 'to',
        'Sender'        => 'from',
        'ReplyTo'       => 'reply',
        'Cc'            => 'cc',
        'Bcc'           => 'bcc',
        'Subject'       => 'subject',
        'MessageBody'   => 'html',
        'MessageHeader' => 'html_before',
        'MessageFooter' => 'html_after'        
    );    
    
    /* = CONSTRUCTEUR = */
    public function __construct( $params = array() )
    {
        // Définition des paramètres par défaut
        foreach( self::getAllowedParams() as $Param ) :
            if( method_exists( $this, 'set'. $Param ) ) :
                $this->{$Param} = call_user_func( array( $this, 'set'. $Param ) );
            else :
                $this->{$Param} = Mail::getParam($Param);
            endif;
        endforeach;
        
        // Traitement des paramètres d'envoi de l'email        
        $this->setParams( $params );
    }
    
    /* = CONTROLEURS = */
    /** == Récupération de la liste des paramètres permis == **/
    final public function getAllowedParams()
    {
        return \tiFy\Core\Mail\Mail::getAllowedParams();
    }
    
    /** == Définition des paramètres == **/
    final public function setParams( $params = array() )
    {        
       
        foreach( (array) $params as $param => $value ) :
            $param = Mail::sanitizeParam( $param );
            if( method_exists( $this, 'set'. $param ) )
                continue;

            $this->{$param} = $value;
        endforeach;
    }
    
    /** == Définition d'un paramètre == **/
    final public function setParam( $param, $value )
    {
        $param = Mail::sanitizeParam( $param );
        if( in_array( $param, self::getAllowedParams() ) )
            $this->{$param} = $value;  
    }
            
    /** == Récupération des paramètres == **/
    final public function getMappedParams()
    {
        $params = array();
        foreach( self::$ParamsMap as $Param => $map ) :
            if( method_exists( $this, 'get'. $Param ) ) :
                $params[$map] = call_user_func( array( $this, 'get'. $Param ) );
            else :
                $params[$map] = $this->{$Param};
            endif;
        endforeach;
        
        return $params;
    }
    
    /** == Récupération d'un paramètre == **/
    final public function getParam( $param )
    {
        $param = Mail::sanitizeParam( $param );
        if( ! in_array( $param, self::getAllowedParams() ) )
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
        // Récupération des paramètres
        $params = $this->getMappedParams();
        
        // Traitement des paramètres        
        if( $this->getParam( 'Debug' ) ) :
            $params['auto'] = 'debug';
        endif;
        
        new Mailer( $params );
    }
    
    /** == Définition des destinataires == **/
    public function setRecipients()
    {
        return array( 
            array(
                get_option( 'admin_email' ), __( 'Administrateur du site', 'tiFy' )
            )
        );
    }
    
    /** == Définition de l'expéditeur == **/
    public function setSender()
    {
        return array( get_option( 'admin_email' ), __( 'Administrateur du site', 'tiFy' ) );
    }
}