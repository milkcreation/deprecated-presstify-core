<?php

namespace tiFy\Components\Api\Facebook\Mod;

use tiFy\Components\Api\Api;

class Factory extends \tiFy\App
{
    /**
     * Initialisation
     * @var array
     */
    private static $Init = [];

    /**
     * Classe de rappel du kit de développement Facebook
     * @var \Facebook\Facebook
     */
    private static $Fb = null;

    /**
     * CONTROLEURS
     */
    /**
     * Récupération d'une instance de la classe
     *
     * @return static
     */
    public static function create()
    {
        $instance = new static;

        return $instance;
    }

    /**
     * Vérification d'initialisation de classe existante
     *
     * @return bool
     */
    final protected function isInit()
    {
        return in_array(self::tFyAppClassname(), self::$Init);
    }

    /**
     * Définition d'initialisation de classe
     *
     * @return void
     */
    final protected function setInit()
    {
        array_push(self::$Init, self::tFyAppClassname());
    }

    /**
     * Récupération de la classe de rappel du kit de développement Facebook
     *
     * @return \Facebook\Facebook|null|object
     */
    public function fb()
    {
        if (! self::$Fb) :
            self::$Fb = Api::get('facebook');
        endif;

        return self::$Fb;
    }

    /**
     * Affichage de message d'erreur
     *
     * @param \WP_Error $e
     *
     * @return string
     */
    public function wp_die($e)
    {
        // Récupération des données
        $data = $e->get_error_data();

        // Affichage des erreurs
        wp_die($e->get_error_message(), (! empty($data['title']) ? $data['title'] : __('Processus en erreur', 'tify')), $e->get_error_code());
        exit;
    }
}