<?php
namespace tiFy\Core\Mail;

use tiFy\Core\Db\Db;
use tiFy\Core\Cron\Cron;

class Queue extends \tiFy\App\Factory
{
    /**
     * Classe de rappel de la base de données
     * @var \tiFy\Core\Db\Factory
     */
    protected static $Db = null;

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration de la tâche planifiée
     */
    final public static function tify_cron_register()
    {
        Cron::register(
            '_tFyMail_Cron',
            [
                // Intitulé de la tâche planifiée
                'title'         => __('File d\'expédition des emails', 'tify'),
                // Description de la tâche planifiée
                'desc'          => __('Expédition des emails en partance dans la file d\'attente.', 'tify'),
                // Fréquence d'exécution de la tâche planifiée
                'recurrence'    => [
                    'id'            => 'every_minute',
                    'interval'      => 60,
                    'display'       => __('Chaque minute', 'tify')
                ],
                // Execution du traitement de la tâche planifiée
                'handle'        => 'tiFy\Core\Mail\Cron::_handle',
                // Attributs de journalisation des données
                'log'           => [
                    'name'          => 'mail'
                ]
            ]
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Initialisation
     */
    public function tFyAppOnInit()
    {
        // Déclaration des événements
        $this->tFyAppActionAdd('tify_cron_register');
    }

    /**
     * Initialisation de la table de base de données
     */
    final public static function initDb()
    {
        self::$Db = Db::register(
            'tFyCoreMailQueue',
            [
                'name'          => 'tify_mail_queue',
                'install'       => true,
                'col_prefix'    => 'mq_',
                'meta'          => true,
                'columns'       => [
                    'id'                => [
                        'type'              => 'BIGINT',
                        'size'              => 20,
                        'unsigned'          => true,
                        'auto_increment'    => true
                    ],
                    'session_id'        => [
                        'type'              => 'VARCHAR',
                        'size'              => 32,
                        'default'           => null
                    ],
                    'date_created'      => [
                        'type'              => 'DATETIME',
                        'default'           => '0000-00-00 00:00:00'
                    ],
                    'date_created_gmt'  => [
                        'type'              => 'DATETIME',
                        'default'           => '0000-00-00 00:00:00'
                    ],
                    'sending'           => [
                        'type'              => 'VARCHAR',
                        'size'              => 10,
                    ],
                    'params'            => [
                        'type'              => 'LONGTEXT'
                    ]
                ]
            ]
        );
    }

    /**
     * Récupération de la table de base de données
     */
    final public static function getDb()
    {
        return self::$Db;
    }

    /**
     * Ajout d'un élément dans la file d'attente
     *
     * @param array $params Paramètre d'expédition du mail. @see \tiFy\Lib\MailerNew
     * @param string $sending Date de programmation d'envoi du mail au format timestamp. Par défaut, envoi immédiat current_time('timestamp').
     * @param array $item_meta Données complémentaires d'envoi du mail
     *
     * @return null|int
     */
    final public static function add($params, $sending = '', $item_meta = [])
    {
        if (!self::getDb()) :
            self::initDb();
        endif;

        // Données de file d'attente
        $id = 0;
        $session_id = uniqid('tFymq_', true);
        $date_created = current_time('mysql');
        $date_created_gmt = current_time('mysql', true);
        if (!$sending) :
            $sending = current_time('timestamp');
        endif;
        $params = base64_encode(serialize($params));

        $data = compact(['id', 'session_id', 'date_created', 'date_created_gmt', 'sending', 'params', 'item_meta']);

        return self::getDb()->handle()->create($data);
    }
}