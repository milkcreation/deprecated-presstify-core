<?php

namespace tiFy\Core\User\Session;

interface StoreInterface
{
    /**
     * Récupération du nom de qualification de la session
     *
     * @return string
     */
    public function getName();

    /**
     * Récupération de l'identifiant de qualification.
     *
     * @return string
     */
    public function getKey();

    /**
     * Récupération d'un, plusieurs ou tous les attributs de qualification de la session.
     *
     * @param array $session_args Liste des attributs de retour session_key|session_expiration|session_expiring|cookie_hash. Renvoi tous si vide.
     *
     * @return mixed
     */
    public function getSession($session_args = []);

    /**
     * Récupération de la prochaine date de définition d'expiration de session
     *
     * @return int
     */
    public function nextSessionExpiration();

    /**
     * Récupération de la prochaine date de définition de session expirée
     *
     * @return int
     */
    public function nextSessionExpiring();

    /**
     * Initialisation des attributs de qualification de session
     *
     * @return array
     */
    public function initSession();

    /**
     * Récupération du nom de qualification du cookie d'enregistrement de correspondance de session
     *
     * @return string
     */
    public function getCookieName();

    /**
     * Récupération du hashage de cookie
     *
     * @param int|string $session_key Identifiant de qualification de l'utilisateur courant
     * @param int $expiration Timestamp d'expiration du cookie
     *
     * @return string
     */
    public function getCookieHash($session_key, $expiration);

    /**
     * Récupération du cookie de session
     *
     * @return mixed
     */
    public function getCookie();

    /**
     * Définition d'un cookie de session
     *
     * @param $string $name Identifiant de qualification de l'attribut de session
     * @param $string $value Valeur d'affectation de l'attribut de session
     *
     * @return void
     */
    public function setCookie();

    /**
     * Suppression du cookie de session
     *
     * @return void
     */
    public function clearCookie();

    /**
     * Récupération de la classe de rappel de la table de base de données
     *
     * @return \tiFy\Core\Db\Factory
     */
    public function getDb();

    /**
     * Récupération de la liste des variables de session enregistrés en base.
     *
     * @param mixed $session_key Clé de qualification de la session
     *
     * @return array
     */
    public function getDbDatas($session_key);

    /**
     * Mise à jour de la date d'expiration de la session en base.
     *
     * @param mixed $session_key Clé de qualification de la session
     * @param string $expiration Timestamp d'expiration de la session
     *
     * @return void
     */
    public function updateDbExpiration($session_key, $expiration);

    /**
     * Enregistrement des variables de session en base.
     *
     * @return void
     */
    public function save();

    /**
     * Destruction de la session
     *
     * @return void
     */
    public function destroy();

    /**
     * Récupération de la liste de toutes les données de session.
     *
     * @return array
     */
    public function all();

    /**
     * Récupération d'une donnée de session.
     *
     * @param string $name Identifiant de qualification de la variable
     * @param mixed $default Valeur de retour par défaut
     *
     * @return array|string
     */
    public function get($name, $default = '');

    /**
     * Définition d'une donnée de session.
     *
     * @param string $name Identifiant de qualification de la variable
     * @param mixed $value Valeur de la variable
     *
     * @return $this
     */
    public function put($name, $value);

    /**
     * Récupération d'une donnée et procède à sa suppression.
     *
     * @param  string  $key Identifiant de qualification de la variable
     * @param  string  $default Valeur de retour par défaut
     *
     * @return mixed
     */
    public function pull($key, $default = null);
}