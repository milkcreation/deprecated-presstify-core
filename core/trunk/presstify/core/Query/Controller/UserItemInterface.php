<?php

namespace tiFy\Core\Query\Controller;

interface UserItemInterface
{
    /**
     * Récupération de l'identifiant de qualification Wordpress de l'utilisateur
     * @return int
     */
    public function getId();

    /**
     * Récupération de l'identifiant de connection de l'utilisateur
     * @return string
     */
    public function getLogin();

    /**
     * Récupération du mot de passe encrypté
     * @return string
     */
    public function getPass();

    /**
     * Récupération du surnom
     * @return string
     */
    public function getNicename();

    /**
     * Récupération de l'email
     * @return string
     */
    public function getEmail();

    /**
     * Récupération de l'url du site internet associé à l'utilisateur
     * @return string
     */
    public function getUrl();

    /**
     * Récupération de la date de création du compte utilisateur
     * @return string
     */
    public function getRegistered();

    /**
     * Récupération du nom d'affichage public
     * @return string
     */
    public function getDisplayName();

    /**
     * Récupération du prénom
     * @return string
     */
    public function getFirstName();

    /**
     * Récupération du nom de famille
     * @return string
     */
    public function getLastName();

    /**
     * Récupération du pseudonyme
     * @return string
     */
    public function getNickname();

    /**
     * Récupération des renseignements biographiques
     * @return string
     */
    public function getDescription();

    /**
     * Vérifie si l'utilisateur est connecté
     * @bool
     */
    public function isLoggedIn();

    /**
     * Récupération de la liste des roles
     * @return array
     */
    public function getRoles();

    /**
     * Vérification de l'appartenance à un role
     *
     * @param string $role Identifiant de qualification du rôle
     *
     * @return bool
     */
    public function hasRole($role);
}