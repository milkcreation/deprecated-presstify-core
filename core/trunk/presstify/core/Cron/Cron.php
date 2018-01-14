<?php
/**
 * @name Cron
 * @package PresstiFy
 * @subpackage Core
 * @namespace tiFy\Core\Cron
 * @desc Gestion de tâches planifiées
 * @author Jordy Manner
 * @copyright Tigre Blanc Digital
 * @version 1.2.369
 * @see https://developer.wordpress.org/plugins/cron/hooking-into-the-system-task-scheduler/
 */
namespace tiFy\Core\Cron;

use tiFy\Core\Templates\Templates;

class Cron extends \tiFy\App\Core
{
    // Configurer la tâche cron
    // Dans le fichier wp-config.php
    // define('DISABLE_WP_CRON', true);

    // Sur le serveur
    // $ crontab -e
    // $ * * * * * curl -I http(s)://%site_url%/wp-cron.php?doing_wp_cron > /dev/null 2>&1

    // Test de la tâche planifiée
    // http(s)://%site_url%/?tFyCronDoing=%task_id%

    /**
     * Listes des attributs de configuration des tâches planifiées
     * @var array
     */
    private static $Schedules = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des tâches planifiées configurées
        foreach ((array)self::tFyAppConfig() as $schedule_id => $schedules_attrs) :
            self::register($schedule_id, $schedules_attrs);
        endforeach;

        // Déclaration des événements
        $this->appAddAction('init');
        $this->appAddAction('tify_templates_register');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Enregistrement de tâche planifiée
        do_action('tify_cron_register');

        // Exécution d'une tâche à la volée (test)
        if (!isset($_REQUEST['tFyCronDoing'])) :
            return;
        endif;

        if ($schedule = self::get($_REQUEST['tFyCronDoing'])) :
            do_action_ref_array($schedule['hook'], [$schedule]);
            exit;
        endif;
    }

    /**
     * Déclaration de templates
     */
    public function tify_templates_register()
    {
        Templates::register(
            'tFyCoreCronList',
            [
                'cb'            => 'tiFy\Core\Cron\Admin\ViewList',
                'admin_menu'    => [
                    'menu_slug'     => 'tFyCoreCronList',
                    'parent_slug'   => 'tools.php',
                    'page_title'    => __('Gestion des tâches planifiées', 'tify'),
                    'menu_title'    => __('Tâches planifiées', 'tify')
                ]
            ],
            'admin'
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     */
    final public static function register($id, $attrs = [])
    {
        if (isset(self::$Schedules[$id])) :
            return;
        endif;

        $defaults = [
            // Intitulé de la tâche planifiée
            'title'         => $id,
            // Description de la tâche planifiée
            'desc'          => '',
            // Date d'exécution de la tâche planifiée
            'timestamp'     => date('U', mktime(2, 0, 0, 5, 27, 2003)),
            // Fréquence d'exécution de la tâche planifiée
            'recurrence'    => 'daily',
            // Execution du traitement de la tâche planifiée
            'handle'        => '',
            // Attributs de journalisation des données
            'log'           => true,
            // Attributs complémentaires passés en argument de la tâche planifiée
            'args'          => [],
            // Désenregistrement
            'unregister'    => false
        ];

        // Traitement des attributs de configuration
        $attrs = wp_parse_args($attrs, $defaults);

        // Activaction/Désactivation du désenregistrement
        if ($attrs['unregister']) :
            $unregister = true;
        else :
            $unregister = false;
        endif;
        unset($attrs['unregister']);

        // Identifiant unique
        $attrs['id'] = $id;

        // Identifiant unique d'accorche de la tâche planifiée
        $attrs['hook'] = 'tFyCron_' . $id;

        // Date GMT d'exécution de la tâche
        $attrs['timestamp'] = get_gmt_from_date(date('Y-m-d H:i:s', $attrs['timestamp']), 'U');

        // Traitement de la récurrence
        $recurrences = \wp_get_schedules();
        if (is_string($attrs['recurrence']) && ! isset($recurrences[$attrs['recurrence']])) :
            $attrs['recurrence'] = 'daily';
        elseif(is_array($attrs['recurrence'])) :
            if (!isset($attrs['recurrence']['id'])) :
                $attrs['recurrence'] = 'daily';
            else :
                $r = \wp_parse_args(
                    $attrs['recurrence'],
                    [
                        'interval'  => DAY_IN_SECONDS,
                        'display'   => __('Once Daily')
                    ]
                );
                add_filter(
                    'cron_schedules',
                    function() use ($r)
                    {
                        return [
                            $r['id'] => [
                                'interval'  => $r['interval'],
                                'display'   => $r['display']
                            ]
                        ];
                    });

                $attrs['recurrence'] = $r['id'];
            endif;
        endif;

        // Traitement de la classe de surcharge
        if (!$attrs['handle']) :
            $classname = self::getOverride("\\tiFy\\Core\\Cron\\Schedule", self::getOverrideNamespace() . "\\Core\\Cron\\" . self::sanitizeControllerName($id));
            $attrs['handle'] = $classname . '::_handle';
        endif;

        // Traitement de la journalisation
        if ($attrs['log']) :
            $logdef = [
                'format'    => "%datetime% %level_name% \"%message%\" %context% %extra%\n",
                'rotate'    => 10,
                'name'      => $id,
                'basedir'   => WP_CONTENT_DIR . '/uploads/log'
            ];
            $attrs['log'] = !is_array($attrs['log']) ? $logdef : \wp_parse_args($attrs['log'], $logdef);
        endif;

        // Définition des attributs de configuration
        self::$Schedules[$id] = $attrs;

        // Ajustement de la récurrence
        if (($schedule = \wp_get_schedule($attrs['hook'], [$attrs])) && ($schedule !== $attrs['recurrence'])) :
            self::unregister($id);
        elseif($unregister) :
            self::unregister($id);
        endif;

        if (!\wp_get_schedule($attrs['hook'], [$attrs])) :
            \wp_schedule_event($attrs['timestamp'], $attrs['recurrence'], $attrs['hook'], [$attrs]);
            self::$Schedules[$id] = $attrs;
        endif;

        \add_action($attrs['hook'], $attrs['handle']);

        return self::$Schedules[$id];
    }

    /**
     * Désenregistrement
     */
    final public static function unregister($id)
    {
        if (!$crons = _get_cron_array()) :
            return;
        endif;
        if (!$schedule = self::get($id)) :
            return;
        endif;

        foreach ($crons as $timestamp => $cron) :
            if (!isset($cron[$schedule['hook']])) :
                continue;
            endif;
            foreach ($cron[$schedule['hook']] as $key => $attrs) :
                \wp_unschedule_event($timestamp, $schedule['hook'], $attrs['args']);
            endforeach;
        endforeach;

        unset(self::$Schedules[$id]);
    }

    /**
     * Récupération de la liste des tâches planifiées déclarées
     * @return array
     */
    final public static function getList()
    {
        return self::$Schedules;
    }

    /**
     * Récupération d'une tâche planifiée déclarée
     *
     * @param string $id Identifiant unique de qualification de la tâche planifiée
     *
     * @return array
     */
    final public static function get($id)
    {
        if (isset(self::$Schedules[$id])) :
            return self::$Schedules[$id];
        endif;
    }
}