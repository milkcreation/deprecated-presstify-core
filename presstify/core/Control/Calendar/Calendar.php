<?php
/**
 * @name Calendar
 * @desc Controleur d'affichage de calendrier
 * @package presstiFy
 * @namespace tiFy\Core\Control\Calendar
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\Calendar;

/**
 * @Overrideable \App\Core\Control\Calendar\Calendar
 *
 * <?php
 * namespace \App\Core\Control\Calendar
 *
 * class Calendar extends \tiFy\Core\Control\Calendar\Calendar
 * {
 *
 * }
 */

class Calendar extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     * @var string
     */
    protected $ID = 'calendar';

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des Actions Ajax
        $this->tFyAppAddAction(
            'wp_ajax_tiFyControlCalendar',
            'wp_ajax'
        );
        $this->tFyAppAddAction(
            'wp_ajax_nopriv_tiFyControlCalendar',
            'wp_ajax'
        );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public static function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tify_control-calendar',
            self::tFyAppAssetsUrl('Calendar.css', get_class()),
            ['spinkit-pulse'],
            170519
        );
        \wp_register_script(
            'tify_control-calendar',
            self::tFyAppAssetsUrl('Calendar.js', get_class()),
            ['jquery'],
            170519,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    public static function enqueue_scripts()
    {
        \wp_enqueue_style('tify_control-calendar');
        \wp_enqueue_script('tify_control-calendar');
    }

    /**
     * Récupération ajax du calendrier
     *
     * @return string
     */
    public static function wp_ajax()
    {
        self::display(
            [
                'id'       => $_POST['id'],
                'selected' => $_POST['selected']
            ]
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    public static function display($attrs = [], $echo = true)
    {
        // Incrémentation du nombre d'instance
        self::$Instance++;

        // Traitement des attributs de configuration
        $defaults = [
            'id'       => 'tiFyCalendar--' . self::$Instance,
            'selected' => 'today'
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $path = [
            self::getOverrideNamespace() . "\\Core\\Control\\Calendar\\" . $attrs['id']
        ];
        $className = self::getOverride('\tiFy\Core\Control\Calendar\Display', $path);

        $display = new $className($attrs);

        $output = $display->output();

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }
}