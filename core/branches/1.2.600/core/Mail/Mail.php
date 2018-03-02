<?php 
namespace tiFy\Core\Mail;

use \tiFy\Lib\Mailer\MailerNew;

class Mail extends \tiFy\App\Core
{
    /**
     * Paramètre globaux des emails
     * @var array
     */
    protected static $GlobalParams = [];

    /**
     * Mails natifs de Wordpress
     * @var array
     */
    protected static $WpMail = [];

    /**
     * Listes des attributs des mails personnalisés déclarés
     * @var array
     */
    protected static $Registered = [];

    /**
     * @var array
     */
    // Classe de rappel des mails personnalisés
    protected static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Chargement des controleurs
        new Queue;

        // Déclenchement d'événements
        $this->appAddFilter('password_change_email', null, 99, 3);
        $this->appAddFilter('email_change_email', null, 99, 3);
        $this->appAddFilter('retrieve_password_title', null, 99, 3);
        $this->appAddFilter('wp_mail');

        // Définition des paramètres généraux
        foreach ((array)self::tFyAppConfig('global') as $param => $value) :
            self::$GlobalParams[$param] = $value;
        endforeach;

        // Déclaration des emails natif de wordpress
        foreach ((array)self::tFyAppConfig('wp') as $id => $attrs) :
            self::$WpMail[self::sanitizeName($id)] = $attrs;
        endforeach;

        // Déclaration des emails personnalisés
        foreach ((array)self::tFyAppConfig('custom') as $id => $attrs) :
            self::register($id, $attrs);
        endforeach;

        do_action('tify_mail_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     *
     * @param string $id Identifiant de qualification unique
     * @param array $attrs Attributs de configuration
     *
     * @return array
     */
    public static function register( $id, $attrs = array() )
    {
        // Bypass
        if( isset( self::$Registered[$id] ) ) :
            return;
        endif;

        $classname = self::getOverride(
            'tiFy\Core\Mail\Factory',
            [
                self::getOverrideNamespace() . "\\Core\\Mail\\" . self::sanitizeControllerName($id)
            ]
        );

        return self::$Registered[$id] = [$classname, $attrs];
    }

    /**
     * Récupération d'un email personnalisé
     *
     * @param string $id Identifiant de qualification d'un email déclaré
     *
     * @return \tiFy\Core\Mail\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
        if (!isset(self::$Registered[$id])) :
            return;
        endif;

        $className = self::$Registered[$id][0];
        $attrs = self::$Registered[$id][1];
        $attrs = \wp_parse_args($attrs, self::$GlobalParams);

        return self::$Factory[$id] = new $className($attrs);
    }
    
    /** == == **/
    public static function sanitizeName( $name )
    {
        return implode( array_map( 'ucfirst', explode( '_', $name ) ) );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Attributs de l'email natif de changement de mot de passe d'un utilisateur
     */
    final public function password_change_email($pass_change_email, $user, $userdata)
    {
        $pass_change_email['subject'] = '<Wp id="PasswordChange">' . $pass_change_email['subject'] . '</Wp>';

        return $pass_change_email;
    }

    /**
     * Attributs de l'email natif de changement d'adresse email d'un utilisateur
     */
    final public function email_change_email($email_change_email, $user, $userdata)
    {
        $email_change_email['subject'] = '<Wp id="EmailChange">' . $email_change_email['subject'] . '</Wp>';

        return $email_change_email;
    }

    /**
     * Sujet de l'email de récupération mot de passe oublié
     */
    final public function retrieve_password_title($title, $user_login, $user_data)
    {
        return '<Wp id="RetrievePassword">' . $title . '</Wp>';
    }

    /**
     * Expédition des emails Wordpress
     *
     * @param array $attrs
     *
     * @return array
     */
    final public function wp_mail($attrs = array())
    {
        /**
         * @var string $to
         * @var string $subject
         * @var string $message
         * @var array $headers
         * @var array $attachments
         */
        extract($attrs);

        if (!preg_match('/^<Wp\sid=\"(.*)\">(.*)<\/Wp>$/', $subject, $matches)) :
            return $attrs;
        endif;

        // re-formatage du sujet et extraction de l'id du mail natif WP
        list($original, $id, $subject) = $matches;

        $attrs = compact('to', 'subject', 'message', 'headers', 'attachments');

        // Bypass
        if (!in_array($id, array('PasswordChange', 'EmailChange', 'RetrievePassword'))) :
            return $attrs;
        endif;
        if (!isset(self::$WpMail[$id])) :
            return $attrs;
        endif;

        if (empty($headers)) :
            $headers = array();
        elseif (!is_array($headers)) :
            $headers = explode("\n", str_replace("\r\n", "\n", $headers));
        endif;

        $attrs = wp_parse_args($attrs, self::$WpMail[$id]);
        $attrs = wp_parse_args($attrs, self::$GlobalParams);
        $attrs = MailerNew::wp_mail($attrs);

        return $attrs;
    }
}