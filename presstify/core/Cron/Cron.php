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

class Cron extends \tiFy\Environment\Core
{
    /**
     * Liste des actions à déclencher
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions              = array(
        'init'
    );
    
    /**
     * Listes des tâches planifiées
     * @var array
     */
    private static $Schedules           = array();
                    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des tâches planifiées configurées
        foreach( (array) self::tFyAppConfig() as $schedule_id => $schedules_attrs ) :
            self::register( $schedule_id, $schedules_attrs );
        endforeach;
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {
        do_action( 'tify_cron_register' );
        
        // Exécution d'une tâche
        if( ! defined( 'DOING_CRON' ) || ( DOING_CRON !== true ) )
            return;
        if( ! isset( $_REQUEST['tFy_doing_cron'] ) )
            return;

        if( $schedule = self::get( $_REQUEST['tFy_doing_cron'] ) ) :
            return do_action_ref_array( $schedule->getHook(), $schedule->getArgs() );
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration
     */
    public static function register( $id, $attrs = array() )
    {
        $defaults = array(
            // Identifiant unique d'accorche de la tâche planifiée
            'hook'          => 'tiFyCoreCron--'. $id,
            // Intitulé de la tâche planifiée
            'title'         => $id,
            // Description de la tâche planifiée
            'desc'          => '',
            // Date d'exécution de la tâche planifiée
            'timestamp'     => mktime( date( 'H' )-1, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) ),
            // Fréquence d'exécution de la tâche planifiée
            'recurrence'    => 'daily',
            // Arguments passés dans la tâche planifiée
            'args'          => array(),
            // Chemins de classe de surcharge
            'path'          => array(),
            // Attributs de journalisation des données
            'log'           => true
        );
        
        // Traitement des attributs
        $attrs = wp_parse_args( $attrs, $defaults );
        
        /// Identifiant unique
        $attrs['id'] = $id;
        
        /// Journalisation
        if( $attrs['log'] ) :
            $logdef = array(
                'format'    => "%datetime% %level_name% \"%message%\" %context% %extra%\n",
                'rotate'    => 10,
                'name'      => $id
            );
            $attrs['log'] = ! is_array( $attrs['log'] ) ? $logdef : wp_parse_args( $attrs['log'], $logdef );
        endif;
        extract( $attrs );
        
        // Traitement de la classe de surcharge
        if( is_string( $path ) )
            $path = (array) $path;
        $path[] = self::getOverrideNamespace(). "\\Core\\Cron\\". self::sanitizeControllerName( $id );
            
        $ScheduleClassName = self::getOverride( "\\tiFy\\Core\\Cron\\Schedule", $path );
        $schedule = self::$Schedules[$id] = new $ScheduleClassName( $attrs );
        
        //self::unregister( $id );
        
        if(! wp_get_schedule($hook)) :
            wp_schedule_event($timestamp, $recurrence, $hook, $args);
        endif;
        
        add_action($hook, array($schedule, '_handle'));
        
        return $schedule;
    }
    
    /**
     * Désenregistrement
     */
    public static function unregister( $id )
    {
        if( ! $schedule = self::get( $id ) )
            return;
        
        wp_clear_scheduled_hook( $schedule->getHook() );
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
    public static function get( $id )
    {
        if( isset( self::$Schedules[$id] ) )
        return self::$Schedules[$id];
    }
}