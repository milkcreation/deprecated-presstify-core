<?php

namespace tiFy\Components\Smtp;

class Smtp extends \tiFy\App\Component
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('phpmailer_init', null, 0);
    }

    /**
     * EVENEMENTS
     */
    /**
     * Court-circuitage de l'initialisation de PHPMailer
     *
     * @param \PHPMailer $phpmailer Instance de PHPMailer
     *
     * @return void
     */
    public function phpmailer_init(\PHPMailer $phpmailer)
    {
        // Bypass
        if (!self::tFyAppConfig('username')) :
            return;
        endif;

        $phpmailer->IsSMTP();

        $phpmailer->Host = self::tFyAppConfig('host');
        $phpmailer->Port = self::tFyAppConfig('port');
        $phpmailer->Username = self::tFyAppConfig('username');
        $phpmailer->Password = self::tFyAppConfig('password');
        $phpmailer->SMTPAuth = self::tFyAppConfig('smtp_auth');
        if ($smtp_secure = self::tFyAppConfig('smtp_secure')) :
            $phpmailer->SMTPSecure = $smtp_secure;
        endif;
    }
}