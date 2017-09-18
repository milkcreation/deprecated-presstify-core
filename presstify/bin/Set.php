<?php

namespace tiFy;

use tiFy\tiFy;
use tiFy\Apps;

class Set
{

    /**
     * Liste des jeux de fonctionnalités chargés
     */
    protected static $Loaded = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Enregistrement des sets inclus dans PresstiFy
        foreach (glob(tiFy::$AbsDir . '/set/*', GLOB_ONLYDIR) as $filename) :
            $id        = basename($filename);
            $namespace = "tiFy\\Set\\{$id}";
            $base_dir  = dirname($filename);
            $bootstrap = $id;
            self::load($id, compact('namespace', 'base_dir', 'bootstrap'));
        endforeach;

        // Enregistrements des sets déclarés dans la configuration
        if ($sets = tiFy::getConfig('set', [])) :
            foreach ($sets as $id => $attrs) :
                self::load($id, $attrs);
            endforeach;
        endif;

        // Enregistrements personnalisés
        do_action('tify_set_load');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Chargement
     *
     * @param string $id
     * @param array $attrs {
     *      Attributs de configuration
     *
     *      @param string $namespace Espace de nom
     *      @param string $base_dir Répertoire de stockage
     *      @param $bootstrap Classe d'initialisation
     * }
     *
     * @return string Nom
     */
    final public static function load($id, $attrs)
    {

        $defaults = [
            'namespace' => null,
            'base_dir'  => null,
            'bootstrap' => null,
        ];
        $attrs = wp_parse_args($attrs, $defaults);
        extract($attrs);

        // Formatage de l'espace de nom
        if ( ! is_null($namespace)) :
            $namespace = trim($namespace, "\\") . "\\";
        else :
            $namespace = trim(self::getOverrideNamespace(), "\\") . "\\Set\\";
        endif;

        tiFy::classLoad($namespace, $base_dir);

        // Formatage du point d'entrée unique
        if ( ! is_null($bootstrap)) :
            $bootstrap = trim($bootstrap, "\\");
        else :
            $bootstrap = $id;
        endif;

        $classname = $namespace . $bootstrap;

        return self::$Loaded[$id] = $classname;
    }

    /**
     * Déclaration
     *
     * @param string $id Identifiant de l'extension
     * @param mixed $attrs
     *
     * @return NULL|object
     */
    public static function register($id, $attrs = [])
    {
        // Bypass
        if (! isset(self::$Loaded[$id])) :
            return;
        endif;

        $classname = self::$Loaded[$id];

        return Apps::register(
            $classname,
            'set',
            [
                'Id'     => $id,
                'Config' => $attrs,
            ]
        );
    }
}