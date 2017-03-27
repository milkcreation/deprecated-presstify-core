<?php 
namespace tiFy\Core\Mail;

class Mail extends \tiFy\Environment\Core
{
    /* = ARGUMENTS = */	
	// Classe de rappel des mails déclarés
	protected static $Registered        = array();
	
	// Paramètres par défaut des emails
    /// Destinataires
    private static $Recipients          = array();
    
    /// Expediteur
    private static $Sender              = '';
    
    /// Destinataire de réponse
    private static $ReplyTo             = '';
    
    /// Destinataires de copie carbone
    private static $Cc                  = array();
    
    /// Destinataires de copie cachée
    private static $Bcc                 = array();    
    
    // Sujet
    private static $Subject             = '';
    
    // Message
    private static $Message             = '';
    
    // Entête du Message
    private static $MessageHeader       = '';
    
    // Pied de page du message
    private static $MessageFooter       = '';
    
    // Deboggage
    private static $Debug               = false;  
    
    // Liste des paramètres autorisés
    private static $AllowedParams       = array(
        'Recipients', 'Sender', 'ReplyTo', 'Cc', 'Bcc', 'Subject',
        'MessageBody', 'MessageHeader', 'MessageFooter',
        'Debug'
    );
    	
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();
        
         // Définition des paramètres généraux
        foreach( (array) self::getConfig( 'params' ) as $param => $value ) :
            $param = self::sanitizeParam( $param );
            if( ! in_array( $param, self::getAllowedParams() ) )
                continue;
            self::${$param} = $value;
        endforeach;        
        
        // Déclaration des éléments de configuration
        foreach( (array) self::getConfig( 'registered' ) as $id => $attrs ) :
            self::register( $id, $attrs );
        endforeach;
     
        do_action( 'tify_mail_register' );
    }
    
    /* = CONTROLEURS = */
    /** == Déclaration d'un email == **/
    public static function register( $id, $attrs = array() )
    {
        // Bypass
        if( isset( self::$Registered[$id] ) )
            return;
        
        $className = self::getOverride( "\\tiFy\\Core\\Mail\\Factory", array( "\\". self::getOverrideNamespace() ."\\Core\\Mail\\". self::sanitizeControllerName( $id ) ) );
        
        return self::$Registered[$id] = new $className( $attrs );   
    }
    
    /** == Récupération d'un email == **/
    public static function get( $id )
    {
        if( isset( self::$Registered[$id] ) )
            return self::$Registered[$id];           
    }
    
    /** == Récupération de la liste de paramètres permis == **/
    public static function getAllowedParams()
    {
        return self::$AllowedParams;
    }
    
    /** == == **/
    public static function sanitizeParam( $param )
    {
        return implode( array_map( 'ucfirst', explode( '_', $param ) ) );
    }
    
    /** == Récupération d'un paramètre général == **/
    public static function getParam( $param )
    {
        // Bypass
        if( ! in_array( $param, self::getAllowedParams() ) )
            return;        
        if( ! isset( self::${$param} ) )
            return;
        
        return self::${$param};
    }
}