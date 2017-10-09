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

class Cron extends \tiFy\App\Core
{

    // Configurer la tâche cron
    // Dans le fichier wp-config.php
    // define('DISABLE_WP_CRON', true);

    // Sur le serveur
    // $ crontab -e
    // $ */5 * * * * curl -I https://%site_url%/wp-cron.php?doing_wp_cron > /dev/null 2>&1

    /**
     * Listes des tâches planifiées
     * @var array
     */
    private static $Schedules = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {
        // Enregistrement de tâche planifiée
        do_action('tify_cron_register');

        // Nettoyage des anciennes valeurs de cron
        if (! get_option('tFyCron_sanitize', '')) :
            foreach ((array)_get_cron_array() as $timestamp => $cron) :
                foreach ($cron as $hook => $attrs) :
                    if (preg_match('#tiFyCoreCron--#', $hook)) :
                        wp_clear_scheduled_hook($hook);
                    endif;
                endforeach;
            endforeach;
            add_option('tFyCron_sanitize', '1.2.427');
        endif;

        // Exécution d'une tâche à la volée (test)
        if (!isset($_REQUEST['tFyCronDoing'])) :
            return;
        endif;
        if ($schedule = self::get($_REQUEST['tFyCronDoing'])) :
            call_user_func_array([$schedule, '_handle'], $schedule->getArgs());
            exit;
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     */
    public function tFyAppOnInit()
    {
        // Définition des actions
        $this->tFyAppActionAdd('init');

        // Déclaration des tâches planifiées configurées
        foreach ((array)self::tFyAppConfig() as $schedule_id => $schedules_attrs) :
            self::register($schedule_id, $schedules_attrs);
        endforeach;
    }

    /**
     * Déclaration
     */
    public static function register($id, $attrs = [])
    {
        $defaults = [
            // Intitulé de la tâche planifiée
            'title'         => $id,
            // Description de la tâche planifiée
            'desc'          => '',
            // Date d'exécution de la tâche planifiée
            'timestamp'     => mktime(date('H') - 1, 0, 0, date('m'), date('d'), date('Y')),
            // Fréquence d'exécution de la tâche planifiée
            'recurrence'    => 'daily',
            // Arguments passés dans la tâche planifiée
            'args'          => [],
            // Chemins de classe de surcharge
            'path'          => [],
            // Attributs de journalisation des données
            'log'           => true,
            // Désenregistrement
            'unregister'    => false
        ];

        // Traitement des attributs de configuration
        $attrs = wp_parse_args($attrs, $defaults);

        // Identifiant unique
        $attrs['id'] = $id;

        // Identifiant unique d'accorche de la tâche planifiée
        $attrs['hook'] = 'tFyCron_' . $id;

        // Traitement de la récurrence
        $recurrences = wp_get_schedules();
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
        if (is_string($attrs['path'])) :
            $attrs['path'] = (array)$attrs['path'];
        endif;
        $attrs['path'][] = self::getOverrideNamespace() . "\\Core\\Cron\\" . self::sanitizeControllerName($id);

        // Traitement de la journalisation
        if ($attrs['log']) :
            $logdef = [
                'format' => "%datetime% %level_name% \"%message%\" %context% %extra%\n",
                'rotate' => 10,
                'name'   => $id
            ];
            $attrs['log'] = !is_array($attrs['log']) ? $logdef : wp_parse_args($attrs['log'], $logdef);
        endif;

        // Instanciation de la classe de programmation
        $ScheduleClassName = self::getOverride("\\tiFy\\Core\\Cron\\Schedule", $attrs['path']);
        self::$Schedules[$id] = new $ScheduleClassName($attrs);

        // Ajustement de la récurrence
        if (($schedule = wp_get_schedule($attrs['hook'])) && ($schedule !== $attrs['recurrence'])) :
            self::unregister($id);
        endif;

        if (!wp_get_schedule($attrs['hook'])) :
            wp_schedule_event($attrs['timestamp'], $attrs['recurrence'], $attrs['hook'], $attrs['args']);
        endif;

        add_action($attrs['hook'], [self::$Schedules[$id], '_handle']);

        return self::$Schedules[$id];
    }

    /**
     * Désenregistrement
     */
    public static function unregister($id)
    {
        if (!$schedule = self::get($id)) :
            return;
        endif;

        wp_clear_scheduled_hook($schedule->getHook());
    }


    /**
     * Récupération de la liste des tâches planifiées déclarées
     * @return array
     */
    public static function getList()
    {
        return self::$Schedules;
    }

    /**
     * Récupération d'une tâche planifiée déclarée
     * @return object
     */
    public static function get($id)
    {
        if (isset(self::$Schedules[$id])) :
            return self::$Schedules[$id];
        endif;
    }
}