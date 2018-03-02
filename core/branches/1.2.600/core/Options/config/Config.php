<?php

namespace App\Core\Options;

use \tiFy\Core\Options\Options;

class Config extends \tiFy\App\Config
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('tify_options_register_node');
    }

    /**
     * CONFIGURATION
     */
    /**
     * Définition globale des attributs de configuration
     *
     * @param mixed $attrs Liste des attributs existants
     *
     * @return array|mixed
     */
    public function sets($attrs = [])
    {
        return [
            // @var string $hookname Identifiant de qualification de la page d'accroche d'affichage.
            'hookname'   => 'settings_page_tify_options',

            // @var string $menu_slug Identifiant de qualification du menu.
            'menu_slug'  => 'tify_options',

            // @var string $cap Habilitation d'accès
            'cap'        => 'manage_options',

            // @var string $page_title Intitulé de la page
            'page_title' => "<?php _e('Options du thème', 'tify'); ?>",

            // @var string $menu_title
            'menu_title' => "<?php bloginfo('name'); ?>",

            // @var array $admin_page Attributs de configuration de la page des options
            'admin_page' => [],

            // @var array $admin_bar Attributs de configuration de la barre d'administration
            'admin_bar'  => [],

            // @var array $box Attributs de configuration de la boite à onglet
            'box'        => [],

            // @var array $nodes Liste des greffons
            'nodes'      => [],

            // @var string $render Style d'affichage de la page (standard|metaboxes|@todo méthode personnalisée|@todo function personnalisée).
            'render'     => 'standard',
        ];
    }

    /**
     * DECLENCHEUR
     */
    /**
     * Déclaration de greffon de boite à onglet de saisie
     */
    public function tify_options_register_node()
    {
        Options::registerNode(
        // Attributs de configuration du greffon
        // @see tiFy\Core\Taboox\Taboox::registerNode
            [
                // @var string $id Identifiant du greffon.
                'id'     => '%%node_id%%',
                // @var string $title Titre du greffon.
                'title'  => __('Mon greffon personnalisé', 'Theme'),
                // @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.

                // @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.

                // @var string $parent Identifiant du greffon parent.

                // @var string $cap Habilitation d'accès au greffon.

                // @var bool $show Affichage/Masquage du greffon.

                // @var int $position Ordre d'affichage du greffon.

                // @var string $object post_type|taxonomy|user|option
                'object' => 'option'
                // @var string $object_type

                // @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
            ]
        );
    }
}