<?php
namespace tiFy\Components\Smtp;

class Smtp extends \tiFy\Environment\Component
{
    /* = ARGUMENTS = */
    // Liste des actions à déclencher
    protected $CallActions                = array(
        'phpmailer_init'
    );
    // Ordres de priorité d'exécution des actions
    protected $CallActionsPriorityMap    = array(
        'phpmailer_init' => 0    
    );
    
    /* = DECLENCHEURS = */
    /** == Modification des paramètres SMTP de PHPMailer == **/
    public function phpmailer_init( \PHPMailer $phpmailer )
    {
        $phpmailer->IsSMTP();

        $phpmailer->Host         = self::getConfig( 'host' );
        $phpmailer->Port         = self::getConfig( 'port' );
        $phpmailer->Username     = self::getConfig( 'username' );
        $phpmailer->Password     = self::getConfig( 'password' );
        $phpmailer->SMTPAuth     = self::getConfig( 'smtp_auth' );
        if( $smtp_secure = self::getConfig( 'smtp_secure' ) ) 
            $phpmailer->SMTPSecure = $smtp_secure; // ssl | tls
    }
}