<?php

namespace tiFy\Core\Field;

/**
 * Class Field
 * @package tiFy\Core\Field
 *
 * @method static AjaxAutocomplete(array $args)
 * @method static AjaxSelect(array $args)
 * @method static Button(array $args)
 * @method static Checkbox(array $args)
 * @method static DatetimeJS(array $args)
 * @method static File(array $args)
 * @method static Hidden(array $args)
 * @method static Label(array $args)
 * @method static Number(array $args)
 * @method static NumberJS(array $args)
 * @method static Password(array $args)
 * @method static Radio(array $args)
 * @method static Repeater(array $args)
 * @method static Select(array $args)
 * @method static SelectJS(array $args)
 * @method static Submit(array $args)
 * @method static Text(array $args)
 * @method static Textarea(array $args)
 * @method static ToggleSwitch(array $args)
 */
class Field extends \tiFy\App\Core
{
    /**
     * Liste des classes ou méthodes ou fonctions de rappel des controleurs de champs déclarés
     * @var array
     */
    private static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des champs
        foreach (glob(self::tFyAppDirname() . '/*', GLOB_ONLYDIR) as $filename) :
            $id = basename($filename);

            self::register(
                $id,
                "tiFy\\Core\\Field\\{$id}\\{$id}"
            );
        endforeach;

        // Déclaration des événement de déclenchement
        $this->tFyAppAddAction('init');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        do_action('tify_field_register');

        // Auto-chargement de l'initialisation globale des champs
        foreach (static::$Factory as $id => $instance) :
            if (!$classname = get_class($instance)) :
                continue;
            endif;

            // Définition des classes d'aide à la saisie
            $_id = join('_', array_map('lcfirst', preg_split('#(?=[A-Z])#', $id)));
            $instance->addIncreaseHelper('tify_field' . $_id, 'display');

            if (is_callable([$classname, 'init'])) :
                call_user_func([$classname, 'init']);
            endif;
        endforeach;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Appel de l'affichage d'un contrôleur de champ
     *
     * @param string $id Identifiant de qualification du contrôleur de champ
     * @param array $args {
     *      Liste des attributs de configuration
     *
     *      @var array $attrs Attributs de configuration du champ
     *      @var bool $echo Activation de l'affichage du champ
     *
     * return null|callable
     */
    final public static function __callStatic($id, $args = [])
    {
        if (!isset(static::$Factory[$id])) :
            return trigger_error(sprintf(__('Le champ %s n\'est pas disponible.', 'tify'), $id));
        elseif ($classname = get_class(static::$Factory[$id])) :
            $callable = [$classname, 'display'];
        else :
            $callable = static::$Factory[$id];
        endif;
        if (!is_callable($callable)) :
            return trigger_error(sprintf(__('La méthode d\'affichage du champ %s ne peut être appelée.', 'tify'), $id));
        endif;

        $echo = isset($args[1]) ? $args[1] : false;
        $attrs = reset($args);

        if ($echo) :
            call_user_func_array($callable, compact('attrs'));
        else :
            ob_start();
            call_user_func_array($callable, compact('attrs'));
            return ob_get_clean();
        endif;
    }

    /**
     * Déclaration d'un champs
     *
     * @param string Identifiant de qualification du champ
     * @param string classes ou méthodes ou fonctions de rappel
     *
     * @return void
     */
    final public static function register($id, $callback)
    {
        if (class_exists($callback)) :
            return self::$Factory[$id] = new $callback;
        else :
            return self::$Factory[$id] = (string)$callback;
        endif;
    }

    /**
     * Appel d'une méthode helper de champ
     *
     * @param string $id Identifiant de qualification du controleur de champ
     * @param string $method Nom de qualification de la méthode à appeler
     *
     * @return static
     */
    final public static function call($id, $method)
    {
        $id = join('', array_map('ucfirst', preg_split('#_#', $id)));

        $classname = get_class(static::$Factory[$id]);

        if (!isset(static::$Factory[$id])) :
            return trigger_error(sprintf(__('Le champ %s n\'est pas disponible.', 'tify'), $id));
        elseif (!$classname && ($method !== 'display')) :
            return trigger_error(sprintf(__('Le champ  %s n\'a pas de méthode %s disponible.', 'tify'), $id, $method));
        elseif ($classname) :
            $callable = [$classname, $method];
        else :
            $callable = static::$Factory[$id];
        endif;

        $args = array_slice(func_get_args(), 2);

        if (!is_callable($callable)) :
            return trigger_error(sprintf(__('Le champ  %s n\'a pas de méthode %s disponible.', 'tify'), $id, $method));
        endif;

        return call_user_func_array($callable, $args);
    }

    /**
     * Affichage d'un controleur
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args Liste des attributs de configuration
     *
     * @return static
     */
    final public static function display($id, $args = [])
    {
        return self::call($id, 'display', $args, true);
    }

    /**
     * Mise en file des scripts
     *
     * @param string $id Identifiant de qualification du champ
     * @param array $args Liste des attributs de configuration
     *
     * @return static
     */
    final public static function enqueue_scripts($id, $args = [])
    {
        return self::call($id, 'enqueue_scripts', $args);
    }
}