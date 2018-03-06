<?php

namespace tiFy\Core\Query\Controller;

use tiFy\App\Traits\App as TraitsApp;
use Illuminate\Support\Fluent;

abstract class AbstractUser extends Fluent implements UserInterface
{
    use TraitsApp;

    /**
     * Objet User Wordpress
     * @var \WP_User
     */
    protected $object;

    /**
     * Type d'objet Wordpress
     * @var string
     */
    private $objectType = 'user';

    /**
     * Controleur
     * @var string
     */
    const CONTROLLER = '';

    /**
     * Identifiant de qualification du rôle de l'utilisateur Wordpress relatif
     * @var array|string
     */
    const OBJECTNAME = [];

    /**
     * CONSTRUCTEUR
     *
     * @param \WP_User $wp_user
     *
     * @return void
     */
    public function __construct(\WP_User $wp_user)
    {
        $this->object = $wp_user;

        parent::__construct($this->object->to_array());
    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __clone()
    {

    }

    /**
     * Court-circuitage de l'implémentation
     *
     * @return void
     */
    protected function __wakeup()
    {

    }

    /**
     * Instanciation
     *
     * @param string|int|\WP_User|null $id Login utilisateur Wordpress|Identifiant de qualification Wordpress|Objet utilisateur Wordpress|Utilisateur Wordpress courant
     *
     * @return null|static|object
     */
    public static function make($id = null)
    {
        if (!$id) :
            $user = wp_get_current_user();
        elseif (is_int($id)) :
            $user = get_userdata($id);
        elseif (is_string($id)) :
            return static::by('login', $id);
        else :
            $user = $id;
        endif;

        if (!$user instanceof \WP_User) :
            return null;
        endif;

        if ($objectName = static::getObjectName()) :
            $roles =  (array)$objectName;
            if (!in_array(reset($user->roles), $roles)) :
                return null;
            endif;
        endif;

        $name = 'tify.query.user.' . $user->ID;
        if (self::tFyAppHasContainer($name)) :
            return self::tFyAppGetContainer($name);
        endif;

        $Instance = ($controller = static::getController()) ? new $controller($user): new static($user);
        self::tFyAppShareContainer($name, $Instance);

        return $Instance;
    }

    /**
     * Instanciation selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut login.
     * @param string $value Valeur de l'attribut
     *
     * @return null|static|object
     */
    public static function by($key = 'login', $value)
    {
        $args = [
            'search' => $value,
            'number' => 1
        ];

        switch($key) :
            default :
            case 'user_login' :
            case 'login':
                $args['search_columns'] = ['user_login'];
                break;
            case 'user_email' :
            case 'email' :
                $args['search_columns'] = ['user_email'];
                break;
        endswitch;

        $user_query = new \WP_User_Query($args);
        if ($users = $user_query->get_results()) :
            return static::make(reset($users));
        endif;

        return null;
    }

    /**
     * Récupération du controleur
     *
     * @return string
     */
    public static function getController()
    {
        return static::CONTROLLER;
    }

    /**
     * Récupération de l'identifiant de qualification du rôle de l'utilisateur Wordpress relatif
     *
     * @return string|array
     */
    public static function getObjectName()
    {
        return static::OBJECTNAME;
    }

    /**
     * Récupération de l'objet utilisateur Wordpress associé
     *
     * @return \WP_User
     */
    public function getUser()
    {
        return $this->object;
    }

    /**
     * Récupération de l'identifiant de qualification Wordpress de l'utilisateur
     *
     * @return int
     */
    public function getId()
    {
        return (int)$this->get('ID', 0);
    }

    /**
     * Récupération de l'identifiant de connection de l'utilisateur
     *
     * @return string
     */
    public function getLogin()
    {
        return (string)$this->get('user_login', '');
    }

    /**
     * Récupération du mot de passe encrypté
     *
     * @return string
     */
    public function getPass()
    {
        return (string)$this->get('user_pass', '');
    }

    /**
     * Récupération du surnom
     *
     * @return string
     */
    public function getNicename()
    {
        return (string)$this->get('user_nicename', '');
    }

    /**
     * Récupération de l'email
     *
     * @return string
     */
    public function getEmail()
    {
        return (string)$this->get('user_email', '');
    }

    /**
     * Récupération de l'url du site internet associé à l'utilisateur
     *
     * @return string
     */
    public function getUrl()
    {
        return (string)$this->get('user_url', '');
    }

    /**
     * Récupération de la date de création du compte utilisateur
     *
     * @return string
     */
    public function getRegistered()
    {
        return (string)$this->get('user_registered', '');
    }

    /**
     * Récupération du nom d'affichage public
     *
     * @return string
     */
    public function getDisplayName()
    {
        return (string)$this->get('display_name', '');
    }

    /**
     * Récupération du prénom
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getUser()->first_name;
    }

    /**
     * Récupération du nom de famille
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getUser()->last_name;
    }

    /**
     * Récupération du pseudonyme
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->getUser()->nickname;
    }

    /**
     * Récupération des renseignements biographiques
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getUser()->description;
    }
}