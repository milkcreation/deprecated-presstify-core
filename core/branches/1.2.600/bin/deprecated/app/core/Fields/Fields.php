<?php
namespace tiFy\Core\Fields;

use tiFy\Core\Field\Field;

class Fields extends \tiFy\App\Core
{
    /**
     * CONTROLEURS
     */
    /**
     * Appel de l'affichage d'un contrôleur de champ
     * @deprecated
     *
     * @param string $id Identifiant de qualification du contrôleur de champ
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * @return null|callable
     */
    final public static function __callStatic($id, $args)
    {
        $args[1] = isset($args[1]) ? $args[1] : true;

        return Field::__callStatic($id, $args);
    }

    /**
     * Déclaration d'un champs
     * @deprecated
     *
     * @param string Identifiant de qualification du champ
     * @param string classes ou méthodes ou fonctions de rappel
     *
     * @return void
     */
    final public static function register($id, $callback)
    {
        Field::register($id, $callback);
    }

    /**
     * Appel d'une méthode helper de contrôleur
     * @deprecated
     *
     * @param string $id Identifiant de qualification du controleur de champ
     * @param string $méthod Nom de qualification de la méthode à appeler
     *
     * @return static
     */
    final public static function call($id, $method)
    {
        return Field::call($id, $method);
    }

    /**
     * Affichage d'un controleur
     * @deprecated
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args Liste des attributs de configuration
     *
     * @return static
     */
    final public static function display($id, $args = [])
    {
        echo Field::call($id, 'display', $args);
    }

    /**
     * Mise en file des scripts
     * @deprecated
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args Liste des attributs de configuration
     *
     * @return static
     */
    final public static function enqueue_scripts($id, $args = [])
    {
        return Field::call($id, 'enqueue_scripts', $args);
    }
}