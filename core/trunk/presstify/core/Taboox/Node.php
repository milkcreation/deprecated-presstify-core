<?php
namespace tiFy\Core\Taboox;

use tiFy\Core\Taboox\Taboox;

class Node extends \tiFy\Core\Taboox\Factory
{
    /**
     * Liste des classes de rappel des méthodes d'aides à la saisie.
     * @var array
     */
    protected $Helpers  = [];

    /**
     * Classe de rappel de l'interface d'administration
     */
    protected $AdminUi  = null;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function init()
    {
        $cb = $this->getAttr('cb');
        if (empty($cb)) :
            $cb = '\tiFy\Core\Taboox\Admin';
        endif;
        $args = $this->getAttr('args');

        // Classe de rappel
        if (is_string($cb) && class_exists($cb)) :
            $this->AdminUi = new $cb($args);

            /**
             * @todo
             */
            if ($this->AdminUi instanceof \tiFy\Core\Taboox\Admin) :
                $this->AdminUi->args = $args;
            endif;

        // Méthode ou fonction de rappel
        elseif (is_callable($cb)) :
            $args['content_cb'] = $cb;
            $admin_ui = '\tiFy\Core\Taboox\Admin';
            $this->AdminUi = $admin_ui::_init($args);
        endif;

        /**
        $AdminFormClass->ScreenID        = $hookname;
        $AdminFormClass->page             = self::$Boxes[$hookname]['page'];
        $AdminFormClass->env            = self::$Boxes[$hookname]['env'];
        $AdminFormClass->args             = $node['args'];
        */

        if (is_callable([$this->AdminUi, 'init'])) :
            call_user_func([$this->AdminUi, 'init']);
        endif;

        foreach ($this->Helpers as $helper) :
            if (!class_exists($helper)) :
                continue;
            endif;

            new $helper;
        endforeach;
    }

    /**
     * Initialisation de l'interface d'administration
     */
    public function admin_init()
    {
        if (is_callable([$this->AdminUi, 'admin_init'])) :
            call_user_func([$this->AdminUi, 'admin_init']);
        endif;
    }

    /**
     * Chargement de l'écran courant
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        if (is_callable([$this->AdminUi, 'current_screen'])) :
            call_user_func([$this->AdminUi, 'current_screen'], $current_screen);
        endif;
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        if (is_callable([$this->AdminUi, 'admin_enqueue_scripts'])) :
            call_user_func([$this->AdminUi, 'admin_enqueue_scripts']);
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des arguments de configuration
     *
     * @param array $attrs {
     *      Attributs de configuration
     *
     *      @var string $id Identifiant du greffon.
     *      @var string $title Titre du greffon.
     *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
     *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
     *      @var string $parent Identifiant du greffon parent.
     *      @var string $cap Habilitation d'accès au greffon.
     *      @var bool $show Affichage/Masquage du greffon.
     *      @var int $position Ordre d'affichage du greffon.
     *      @var string $object post_type|taxonomy|user|option
     *      @var string $object_type
     *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
     * }
     *
     * @return array
     */
    protected function parseAttrs($attrs = [])
    {
        // Définition des attributs par défaut
        $defaults = [
            'id'            => null,
            'title'         => '',
            'cb'            => '',
            'args'          => [],
            'parent'        => 0,
            'cap'           => 'manage_options',
            'show'          => true,
            'position'      => 99,
            'object'        => null,
            'object_type'   => null,
            'helpers'       => \__return_null()
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        // Initialisation des valeurs des attributs requis
        if (!$attrs['id']) :
            $attrs['id'] = md5(serialize($attrs));
        endif;

        if (!$attrs['title']) :
            $attrs['title'] = $attrs['id'];
        endif;

        if (isset($attrs['order'])) :
            $attrs['position'] = $attrs['order'];
        endif;

        // Récupération de l'identifiant d'accroche de la page d'affichage
        $hookname = $this->Hookname;

        // Auto-enregistrement de la boîte à onglets
        if (!$box = Taboox::getBox($hookname)) :
            Taboox::registerBox($hookname, $attrs['object']);
        else :
            $attrs['object'] = $box->getObject();
            $attrs['object_type'] = $box->getObjectType();
        endif;

        // Traitement des classes de rappel des méthodes d'aide à la saisie
        if ($attrs['helpers']) :
            $helpers = is_string($attrs['helpers']) ? array_map('trim', explode(',', $attrs['helpers'])) : (array)$attrs['helpers'];

            if (!empty($helpers)) :
                foreach ($helpers as $helper) :
                    if (!in_array($helpers, $this->Helpers)) :
                        array_push($this->Helpers, $helper);
                    endif;
                endforeach;
            endif;
        endif;

        return $attrs;
    }

    /**
     * Récupérération de la classe de rappel de l'interface d'administration
     *
     * @return \tiFy\Core\Taboox\Admin;
     */
    final public function getAdminUi()
    {
        return $this->AdminUi;
    }

    /**
     * Affichage du contenu de l'interface d'administration
     *
     * @return \tiFy\Core\Taboox\Admin::_content();
     */
    final public function getAdminUiContent()
    {
        ob_start();
        call_user_func_array([$this->AdminUi, '_content'], func_get_args());
        return ob_get_clean();
    }
}