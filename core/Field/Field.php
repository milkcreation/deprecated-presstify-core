<?php

/**
 * @name Field
 * @desc Gestion de controleurs d'affichage
 * @package presstiFy
 * @namespace \tiFy\Core\Field
 * @version 1.1
 * @subpackage Core
 * @since 1.2.596
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Field;

use tiFy\App\Core;

/**
 * Class Field
 * @package tiFy\Core\Field
 *
 * @method static \tiFy\Components\Fields\Button\Button Button(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Checkbox\Checkbox Checkbox(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\DatetimeJs\DatetimeJs DatetimeJs(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\File\File File(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Hidden\Hidden Hidden(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Label\Label Label(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Number\Number Number(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\NumberJs\NumberJs NumberJs(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Password\Password Password(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Radio\Radio Radio(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Repeater\Repeater Repeater(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Select\Select Select(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\SelectJs\SelectJs SelectJs(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Submit\Submit Submit(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Text\Text Text(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\Textarea\Textarea Textarea(string $id = null, array $attrs = [])
 * @method static \tiFy\Components\Fields\ToggleSwitch\ToggleSwitch ToggleSwitch(string $id = null, array $attrs = [])
 */
class Field extends Core
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des controleurs d'affichage natifs
        foreach(glob($this->appAbsDir() . '/components/Fields/*/', GLOB_ONLYDIR) as $filename) :
            $name = basename($filename);

            self::register($name, "tiFy\\Components\\Fields\\{$name}\\{$name}::make");
        endforeach;

        require_once $this->appAbsDir() . '/components/Fields/Helpers.php';

        // Déclaration des événements
        $this->appAddAction('init');
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Déclaration des champs personnalisés
        do_action('tify_field_register');
    }

    /**
     * Déclaration d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     * @param mixed $callable classe ou méthode ou fonction de rappel
     *
     * @return null|callable|\tiFy\Core\Layout\AbstractFactory
     */
    public static function register($name, $callable)
    {
        if (self::has($name)) :
            return null;
        elseif (is_callable($callable)) :
            self::tFyAppAddContainer("tify.field.{$name}", $callable);
        elseif (class_exists($callable)) :
            self::tFyAppAddContainer("tify.field.{$name}", $callable);
        else :
            return null;
        endif;

        $return = self::get($name);

        return $return;
    }

    /**
     * Vérification d'existance d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     *
     * @return bool
     */
    public static function has($name)
    {
        return self::tFyAppHasContainer("tify.field.{$name}");
    }

    /**
     * Récupération d'un controleur d'affichage
     *
     * @param string $name Nom de qualification du controleur d'affichage
     *
     * @return mixed|\tiFy\Core\Layout\AbstractFactory
     */
    public static function get($name)
    {
        if (self::has($name)) :
            return self::tFyAppGetContainer("tify.field.{$name}");
        endif;

        return null;
    }

    /**
     * Affichage ou récupération du contenu d'un controleur natif
     *
     * @param string $name Nom de qualification du controleur d'affichage
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * @return null|callable
     */
    public static function __callStatic($name, $arguments)
    {
        if(!$callable = self::get($name)) :
            return null;
        endif;

        return call_user_func_array($callable, $arguments);
    }

    /**
     * Mise en file des scripts d'un controleur
     *
     * @param string $name Identifiant de qualification du controleur d'affichage
     * @param array $args Liste des attributs de configuration
     *
     * @return null|callable
     */
    public static function enqueue($name, $args = [])
    {
        if(!$callable = self::get($name)) :
            return null;
        endif;

        if (!is_object($callable) || !method_exists($callable, 'enqueue_scripts')) :
            return null;
        endif;

        return $callable->enqueue_scripts($args);
    }

    /**
     * @deprecated
     *
     * {@inheritdoc}
     *
     * @return null|callable
     */
    public static function enqueue_scripts($name, $args = [])
    {
        return self::enqueue($name, $args = []);
    }
}