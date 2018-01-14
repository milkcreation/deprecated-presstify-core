<?php
namespace tiFy\Core\Labels;

class Labels extends \tiFy\App\Core
{
    /**
     * Liste des classes de rappel
     * @var \tiFy\Core\Labels\Factory[]
     */
    public static $Factory = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if ($labels = self::tFyAppConfig()) :
            foreach ($labels as $id => $attrs) :
                self::register($id, $attrs);
            endforeach;
        endif;

        // Déclaration des événements
        $this->appAddAction('init', null, 9);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        do_action('tify_labels_register');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'intitulés
     *
     * @param $id Identifiant de qualification unique
     * @param array $attrs Attribut de configuration
     *
     * @return \tiFy\Core\Labels\Factory
     */
    public static function register($id, $attrs = [])
    {
        return self::$Factory[$id] = new Factory($id, $attrs);
    }

    /**
     * Récupération des intitulés
     *
     * @param $id Identifiant de qualification
     *
     * @return null|\tiFy\Core\Labels\Factory
     */
    public static function get($id)
    {
        if (isset(self::$Factory[$id])) :
            return self::$Factory[$id];
        endif;
    }
}