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
    protected $Log          = array();
    
    /**
     * Classe de rappel de journalisation
     */
    private $Logger         = null;
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $attrs = array() )
    {
        // Définition des paramètres
        foreach( array( 'id', 'title', 'desc', 'timestamp', 'hook', 'recurrence', 'args', 'log' ) as $attr ) :
            $Attr = ucfirst($attr);
            $this->{$Attr} = $attrs[$attr];
        endforeach;
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
        return wp_next_scheduled( $this->getHook() );
    }
    
    /**
     * Récupération de la classe de rappel de journalisation
     */
    final public function getLogger()
    {
        return $this->Logger;
    }
    
    /**
     * 
     */
    final public function loggerAddExtras( $extras )
    {
        if( $this->Logger->getProcessors() )
            $this->Logger->popProcessor();
        $this->Logger->pushProcessor(function ( $record ) use ( $extras ) {
            $record['extra'] = $extras;
            return $record;
        });
    }
    
    /**
     * Pré-traitement de la tâche
     */
    final public function _handle()
    {
        // Désactivation de la limitation d'exécution PHP
        set_time_limit(0);
        
        // Initialisation de la journalisation
        $output = $this->Log['format'];
        $formatter = new LineFormatter( $output );
        $stream = new RotatingFileHandler(WP_CONTENT_DIR . '/uploads/tFyLogs/'. $this->Log['name'] .'.log', $this->Log['rotate']);
        $stream->setFormatter( $formatter );
        $this->Logger = new Logger( $this->getId() );
        $this->Logger->pushHandler( $stream );
        
        // Vérrouillage
        
        // Rapport
        // @todo
        /*\add_option( 
            'tFy_cronrep-'. $this->getId(), 
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
        
        return call_user_func_array( array( $this, 'task'), func_get_args() );
    }

    /**
     * Tache planifiée
     */
    public function task()
    {
        return true;
    }
}