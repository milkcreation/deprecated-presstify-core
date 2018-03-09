<?php

/**
 * @name Session
 * @desc Gestion d'enregistrement de données de session
 * @package presstiFy
 * @namespace tiFy\Core\User\Login
 * @version 1.1
 * @subpackage Core
 * @since 1.2.535
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\User\Session;

use tiFy\Core\Db\Db;
use tiFy\Core\Cron\Cron;

final class Session extends \tiFy\App
{
    /**
     * Liste des noms de qualification de sessions déclarées
     * @var string[]
     */
    private static $SessionNames = [];

    /**
     * Classe de rappel de la base de données
     * @var \tiFy\Core\Db\Factory
     */
    private static $Db;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('init', null, 0);
        //$this->appAddAction('wp_footer');
    }

    /**
     * Debug
     */
    public function wp_footer()
    {
        if (!empty(self::$SessionNames)) :
            foreach (self::$SessionNames as $name) :
                if (!$session = self::get($name)) :
                    continue;
                endif;
                ?><div style="position:fixed;right:0;bottom:0;width:300px;">
                <ul>
                    <li>name : <?php echo $name; ?></li>
                    <li>key : <?php echo $session->getSession('session_key'); ?></li>
                    <li>datatest : <?php echo $session->get('rand_test'); ?></li>
                </ul>
                </div><?php
            endforeach;
        endif;
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        do_action('tify_user_session_register');

        // Initialisation de la base de données
        if (!empty(self::$SessionNames)) :
            $this->initDb();

            $this->appAddAction('tify_cron_register');
        endif;
    }

    /**
     * Déclaration de tâche planifiée
     *
     * @return void
     */
    public function tify_cron_register()
    {
        Cron::register(
            '_tiFySessionCleanup',
            [
                'title'         => __('Nettoyage de sessions', 'tiFy'),
                'desc'          => __('Suppression de la liste des sessions arrivée à expiration.', 'tiFy'),
                'recurrence'    => 'twicedaily',
                'handle'        => [$this, 'cleanup'],
            ]
        );
    }

    /**
     * Déclaration d'une session
     *
     * @return null|StoreInterface
     */
    public static function register($name, $attrs = [])
    {
        if (self::has($name)) :
            return null;
        endif;

        // Déclaration du controleur d'injection
        self::tFyAppShareContainer("tify.user.session.{$name}", new Store($name, $attrs));

        // Définition
        if ($store = self::get($name)) :
            array_push(self::$SessionNames, $name);

            return $store;
        endif;

        return null;
    }

    /**
     * Récupération d'une session
     *
     * @param string $name Nom de qualification de la session
     *
     * @return bool
     */
    public static function has($name)
    {
        return self::tFyAppHasContainer("tify.user.session.{$name}");
    }

    /**
     * Récupération d'une session
     *
     * @param string $name Nom de qualification de la session
     *
     * @return null|object|StoreInterface
     */
    public static function get($name)
    {
        if (self::has($name)) :
            return self::tFyAppGetContainer("tify.user.session.{$name}");
        endif;
    }

    /**
     * Nettoyage des sessions arrivée à expiration
     *
     * @return void
     */
    public function cleanup()
    {
        if (!defined('WP_SETUP_CONFIG') && !defined('WP_INSTALLING')) :
            self::$Db->handle()->query(self::$Db->handle()->prepare("DELETE FROM " . self::$Db->getName() . " WHERE session_expiry < %d", time()));
        endif;
    }

    /**
     * Initialisation de la table de base de données
     * @see https://github.com/kloon/woocommerce-large-sessions
     *
     * @return \tiFy\Core\Db\Factory
     */
    private function initDb()
    {
        self::$Db = Db::register(
            '_tiFySession',
            [
                'install'    => false,
                'name'       => 'tify_session',
                'primary'    => 'session_key',
                'col_prefix' => 'session_',
                'meta'       => false,
                'columns'    => [
                    'id'     => [
                        'type'           => 'BIGINT',
                        'size'           => 20,
                        'unsigned'       => true,
                        'auto_increment' => true
                    ],
                    'name'     => [
                        'type'           => 'VARCHAR',
                        'size'           => 255,
                        'unsigned'       => false,
                        'auto_increment' => false
                    ],
                    'key'    => [
                        'type' => 'CHAR',
                        'size' => 32,
                        'unsigned'       => false,
                        'auto_increment' => false
                    ],
                    'value'  => [
                        'type' => 'LONGTEXT'
                    ],
                    'expiry' => [
                        'type'     => 'BIGINT',
                        'size'     => 20,
                        'unsigned' => true
                    ]
                ],
                'keys'       => ['session_id' => ['cols' => 'session_id', 'type' => 'UNIQUE']],
            ]
        );
        self::$Db->install();

        return self::$Db;
    }

    /**
     * Récupération de la base de données
     *
     * @return \Exception|\tiFy\Core\Db\Factory
     */
    public static function getDb()
    {
        if (!self::$Db instanceof \tiFy\Core\Db\Factory) :
            return new \Exception(__('La table de base de données de stockage des sessions est indisponible.', 'tify'), 500);
        endif;

        return self::$Db;
    }
}