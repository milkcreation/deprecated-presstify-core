<?php 
namespace tiFy\Core\Mail;

use \tiFy\Lib\Mailer\MailerNew;

class Mail extends \tiFy\Environment\Core
{
    /* = ARGUMENTS = */    
        // Liste des actions à déclencher
    protected $tFyAppActions                = array(
        'password_change_email',
        'email_change_email',
        'retrieve_password_title',
        'wp_mail'
    ); 
    
    // Cartographie des méthodes de rappel des actions
    protected $tFyAppActionsMethods    = array();
        
    // Ordres de priorité d'exécution des actions
    protected $tFyAppActionsPriority    = array(
        'password_change_email'     => 99,
        'email_change_email'        => 99,
        'retrieve_password_title'   => 99
    );
    
    // Nombre d'arguments autorisés
    protected $tFyAppActionsArgs       = array(
        'password_change_email'     => 3,
        'email_change_email'        => 3,
        'retrieve_password_title'   => 3,
        'retrieve_password_message' => 4
    );
    
    // Paramètre globaux des emails
    protected static $GlobalParams      = array();
    
    // Mails natifs de Wordpress
    protected static $WpMail            = array();
    
    // Classe de rappel des mails personnalisés
    protected static $Registered        = array();
    
    // Classe de rappel des mails personnalisés
    protected static $Factory           = array();
            
    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();

         // Définition des paramètres généraux
        foreach( (array) self::tFyAppConfig( 'global' ) as $param => $value ) :
            self::$GlobalParams[$param] = $value;
        endforeach;        
        
        // Déclaration des emails natif de wordpress
        foreach( (array) self::tFyAppConfig( 'wp' ) as $id => $attrs ) :
            self::$WpMail[self::sanitizeName( $id )] = $attrs;
        endforeach;
        
        // Déclaration des emails personnalisés
        foreach( (array) self::tFyAppConfig( 'custom' ) as $id => $attrs ) :
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

        return self::$Registered[$id] = array( $className, $attrs );   
    }
    
    /** == Récupération d'un email presonnalisé == **/
    public static function get( $id )
    {
        if( isset( self::$Factory[$id] ) )
            return self::$Factory[$id]; 
        if( ! isset( self::$Registered[$id] ) )
            return;
        
        $className = self::$Registered[$id][0];    
        $attrs = self::$Registered[$id][1];
        $attrs = wp_parse_args( $attrs, self::$GlobalParams );

        return self::$Factory[$id] = new $className( $attrs );           
    }
    
    /** == == **/
    public static function sanitizeName( $name )
    {
        return implode( array_map( 'ucfirst', explode( '_', $name ) ) );
    }
    
    /* = DECLENCHEURS = */
    /** == Attributs de l'email natif de changement de mot de passe d'un utilisateur == **/
    final public function password_change_email( $pass_change_email, $user, $userdata )
    {
        $pass_change_email['subject'] = '<Wp id="PasswordChange">'. $pass_change_email['subject'] .'</Wp>';
        
        return $pass_change_email;
    }
    
    /** == Attributs de l'email natif de changement d'adresse email d'un utilisateur == **/
    final public function email_change_email( $email_change_email, $user, $userdata )
    {
        $email_change_email['subject'] = '<Wp id="EmailChange">'. $email_change_email['subject'] .'</Wp>';
        
        return $email_change_email;
    }
    
    /** == Sujet de l'email de récupération mot de passe oublié == **/
    final public function retrieve_password_title( $title, $user_login, $user_data  )
    {
        return '<Wp id="RetrievePassword">'. $title .'</Wp>';
    }
    
    /** ==  == **/
    final public function wp_mail( $attrs = array() )
    {
        extract( $attrs );
        
        if( ! preg_match( '/^<Wp\sid=\"(.*)\">(.*)<\/Wp>$/', $subject, $matches ) )
            return $attrs;
        // re-formatage du sujet et extraction de l'id du mail natif WP
        list( $original, $id, $subject ) = $matches;
        
        $attrs = compact( 'to', 'subject', 'message', 'headers', 'attachments' );
        
        // Bypass
        if( ! in_array( $id, array( 'PasswordChange', 'EmailChange', 'RetrievePassword' ) ) ) :
            return $attrs;
        endif;
        if( ! isset( self::$WpMail[$id] ) ) :
            return $attrs;
        endif;
        
        if( empty( $headers ) ) :
            $headers = array();
        elseif ( ! is_array( $headers ) ) :
			$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        endif;  
        
        $attrs = wp_parse_args( $attrs, self::$WpMail[$id] );
        $attrs = wp_parse_args( $attrs, self::$GlobalParams );
        $attrs = MailerNew::wp_mail( $attrs );       
        
        return $attrs;
    }
}