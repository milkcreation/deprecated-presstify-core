<?php

namespace tiFy\Core\Query\Controller;

interface TermInterface
{
    /**
     * Instanciation
     *
     * @param string|int|\WP_User|null $id Login utilisateur Wordpress|Identifiant de qualification Wordpress|Objet utilisateur Wordpress|Utilisateur Wordpress courant
     *
     * @return null|self|object
     */
    public static function make($id = null);

    /**
     * Instanciation selon un attribut particulier
     *
     * @param string $key Identifiant de qualification de l'attribut. défaut login.
     * @param string $value Valeur de l'attribut
     *
     * @return null|self|object
     */
    public static function by($key = 'login', $value);

    /**
     * Récupération de l'identifiant de qualification Wordpress du post
     * @return int
     */
    public function getId();
}