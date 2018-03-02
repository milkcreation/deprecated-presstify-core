<?php

namespace tiFy\Core\Column;

use tiFy\App\Core;
use League\Container\Exception\NotFoundException;

final class Column extends Core
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
        $this->appAddAction('init');
        $this->appAddAction('admin_init', null, 99);
    }

    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        // Traitement des colonnes déclarées dans le fichier configuration
        if (!self::tFyAppConfig()) :
            foreach (self::tFyAppConfig() as $object_type => $object_names) :
                if (!is_array($object_names)) :
                    continue;
                endif;

                foreach ($object_names as $object_name => $custom_columns) :
                    foreach ($custom_columns as $name => $args) :
                        $this
                            ->get($object_type, $object_name)
                            ->add($name, $args);
                    endforeach;
                endforeach;
            endforeach;
        endif;
    }

    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    public function admin_init()
    {
        // Evénement de déclaration des colonnes
        do_action('tify_custom_columns_register');
        do_action('tify_custom_columns_register_too_late');
    }

    /**
     * Récupération du controleur de type d'objet
     *
     * @param $object_type
     * @param $object_name
     *
     * @return ObjectNameFactory
     */
    public function get($object_type, $object_name)
    {
        // Définition du controleur
        switch($object_type) :
            case 'post_type' :
            case 'taxonomy' :
            case 'custom' :
                $controller = 'tiFy\Core\Column\ObjectNameFactory';
                break;
            default :
                wp_die(
                    sprintf(
                        __('Ce type de colonne %s n\'est pas valide. Seules les types %s sont permis.', 'tify'),
                        $object_type,
                        join(', ', ['post_type', 'taxonomy', 'custom'])
                    ),
                    __('tiFy\Core\Column : Ajout de colonne impossible', 'tify'),
                    500
                );
                exit;
                break;
        endswitch;

        // Récupération du conteneur de type d'objet
        $id = "tify.core.column.{$object_type}.{$object_name}";
        if (!$this->appHasContainer($id)) :
            $this->appShareContainer($id, $controller)
                ->withArgument($object_type)
                ->withArgument($object_name);
        endif;

        /**
         * @var ObjectNameFactory $ObjectNameFactory
         */
        $ObjectNameFactory = $this->appGetContainer($id);

        return $ObjectNameFactory;
    }

    /**
     * Récupération du controleur de type d'objet
     *
     * @param $object_type
     * @param $object_name
     *
     * @return ObjectNameFactory
     */
    public static function make($object_type, $object_name)
    {
        // Récupération du controleur de colonne
        try {
            /**
             * @var Column $Column Controleur de colonnes tiFy
             */
            $Column = self::tFyAppGetContainer('tiFy\Core\Column\Column');
        } catch (NotFoundException $e) {
            wp_die(
                $e->getMessage(),
                __('tiFy\Core\Column : Controleur principal introuvable', 'tify'),
                $e->getCode()
            );
        }

        return $Column->get($object_type, $object_name);
    }
}