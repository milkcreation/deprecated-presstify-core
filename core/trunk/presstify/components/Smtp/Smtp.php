<?php
namespace tiFy\Components\Smtp;

class Smtp extends \tiFy\App\Component
{
    /* = ARGUMENTS = */
    // Liste des actions à déclencher
    protected $tFyAppActions                = array(
        'phpmailer_init'
    );
    // Ordres de priorité d'exécution des actions
    protected $tFyAppActionsPriority    = array(
        'phpmailer_init' => 0    
    );
    
    /* = DECLENCHEURS = */
    /** == Modification des paramètres SMTP de PHPMailer == **/
    public function phpmailer_init( \PHPMailer $phpmailer )
    {
        if( ! self::tFyAppConfig( 'username' ) )
            return;
        
        $phpmailer->IsSMTP();

        $phpmailer->Host         = self::tFyAppConfig( 'host' );
        $phpmailer->Port         = self::tFyAppConfig( 'port' );
        $phpmailer->Username     = self::tFyAppConfig( 'username' );
        $phpmailer->Password     = self::tFyAppConfig( 'password' );
        $phpmailer->SMTPAuth     = self::tFyAppConfig( 'smtp_auth' );
        if( $smtp_secure = self::tFyAppConfig( 'smtp_secure' ) ) 
            $phpmailer->SMTPSecure = $smtp_secure; // ssl | tls
    }
}