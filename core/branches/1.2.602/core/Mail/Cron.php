<?php
namespace tiFy\Core\Mail;

use tiFy\Lib\Mailer\MailerNew;

class Cron extends \tiFy\Core\Cron\Schedule
{
    /**
     * Traitement de la tâche planifiée
     */
    public function handle()
    {
        // Traitement de la file des emails en attente d'expédition
        if (!Queue::getDb()) :
            Queue::initDb();
        endif;

        // Récupération des emails à expédier
        if ($emails = Queue::getDb()->select()->rows(
                [
                    'sending'   => [
                        'value'     => current_time('timestamp'),
                        'compare'   => '<='
                    ],
                    'orderby'   => 'sending',
                    'order'     => 'ASC'
                ]
            )
        ) :
            foreach ($emails as $e) :
                $params = unserialize(base64_decode($e->mq_params));
                Queue::getDb()->handle()->delete_by_id($e->mq_id);
                MailerNew::send($params);
                $this->getLogger()->notice(__('Email expédié avec succès', 'tify'), $params);
            endforeach;
        endif;
    }
}