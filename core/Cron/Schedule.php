<?php
namespace tiFy\Core\Cron;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

class Schedule
{
    /**
     * Identifiant unique de la tâche planifiée
     */
    protected $Id           = null;

    /**
     * Intitulé de la tâche planifiée
     */
    protected $Title        = '';

    /**
     * Description de la tâche planifiée
     */
    protected $Desc         = '';

    /**
     * Date d'exécution de la tâche planifiée
     */
    protected $Timestamp    = null;

    /**
     * Identifiant d'accorche de la tâche planifiée
     */
    private $Hook           = null;

    /**
     * Fréquence d'exécution de la tâche planifiée
     */
    private $Recurrence     = null;

    /**
     * Arguments passés dans la tâche planifiée
     */
    protected $Args         = null;

    /**
     * Activation de la journalisation
     */
    protected $Log          = [];

    /**
     * Classe de rappel de journalisation
     * @var \Monolog\Logger
     */
    private $Logger         = null;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($attrs = [])
    {
        // Définition des paramètres
        foreach (['id', 'title', 'desc', 'timestamp', 'hook', 'recurrence', 'args', 'log'] as $attr) :
            if (!isset($attrs[$attr])) :
                continue;
            endif;
            $Attr = ucfirst($attr);
            $this->{$Attr} = $attrs[$attr];
        endforeach;

        // Initialisation de la journalisation
        $this->initLogger();
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'identifiant unique de la tâche planifiée
     */
    final public function getId()
    {
        return $this->Id;
    }

    /**
     * Récupération de l'intitulé de la tâche planifiée
     */
    final public function getTitle()
    {
        return $this->Title;
    }

    /**
     * Récupération de la description de la tâche planifiée
     */
    final public function getDesc()
    {
        return $this->Desc;
    }

    /**
     * Récupération de la date d'exécution de la tâche planifiée
     */
    final public function getTimestamp()
    {
        return $this->Timestamp;
    }

    /**
     * Récupération de l'identifiant d'accroche de la tâche planifiée
     */
    final public function getHook()
    {
        return $this->Hook;
    }

    /**
     * Récupération de la fréquence d'execution de la tâche planifiée
     */
    final public function getRecurrence()
    {
        return $this->Recurrence;
    }

    /**
     * Récupération des attributs de configuration de la planification
     */
    final public function getLog()
    {
        return $this->Log;
    }

    /**
     * Récupération des arguments de tâche planifiée
     */
    final public function getArgs()
    {
        return $this->Args;
    }

    /**
     * Récupération de la date de la prochaine date d'exécution
     */
    final public function nextTask()
    {
        return wp_next_scheduled($this->getHook());
    }

    /**
     * Initialisation de la journalisation
     */
    final public function initLogger()
    {
        if (!$attrs = $this->getLog()) :
            return;
        endif;

        $output = $attrs['format'];
        $formatter = new LineFormatter($output);
        $stream = new RotatingFileHandler($attrs['basedir'] . '/' . $attrs['name'] . '.log', $attrs['rotate']);
        $stream->setFormatter($formatter);
        $this->Logger = new Logger($this->getId());
        if ($timezone = get_option('timezone_string')) :
            $this->Logger->setTimezone(new \DateTimeZone($timezone));
        endif;
        $this->Logger->pushHandler($stream);
    }

    /**
     * Récupération de la classe de rappel de journalisation
     *
     * @return \Monolog\Logger
     */
    final public function getLogger()
    {
        return $this->Logger;
    }
    
    /**
     * 
     */
    final public function loggerAddExtras($extras)
    {
        if ($this->Logger->getProcessors()) :
            $this->Logger->popProcessor();
        endif;

        $this->Logger->pushProcessor(function ($record) use ($extras) {
            $record['extra'] = $extras;

            return $record;
        });
    }
    
    /**
     * Pré-traitement de la tâche
     */
    final public static function _handle()
    {
        // Instanciation de la classe
        $Inst = new static(func_get_arg(0));

        // Désactivation de la limitation d'exécution PHP
        set_time_limit(0);

        /**
         * Verrouillage
         * @todo
         */

        /**
         * Rapport
         * @todo
         */
        /*\add_option( 
            'tFy_cronrep-'. $Inst->getId(),
            array( 
                'start'     => current_time('timestamp'),
                'end'       => '',
                'last'      => '',
                'total'     => 0,
                'current'   => 0,
                'extras'    => array(
                    
                )
            ),
            HOUR_IN_SECONDS
        );*/

        return call_user_func([$Inst, 'handle']);
    }

    /**
     * Traitement de la tâche planifiée
     */
    public function handle()
    {
        return true;
    }
}