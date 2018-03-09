<?php

namespace tiFy\Core\Query\Controller;

use Illuminate\Support\Fluent;
use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractUserItem extends Fluent implements UserItemInterface
{
    use TraitsApp;

    /**
     * Objet User Wordpress
     * @var \WP_User
     */
    protected $object;

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

    /**
     * Vérifie si l'utilisateur est connecté
     *
     * @bool
     */
    public function isLoggedIn()
    {
        return (\get_current_user_id()) && (\get_current_user_id() === $this->getId());
    }

    /**
     * Récupération de la liste des roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->getUser()->roles;
    }

    /**
     * Vérification de l'appartenance à un roles
     *
     * @param string $role Identifiant de qualification du rôle
     *
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }
}