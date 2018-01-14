<?php

namespace tiFy\Core\Templates\Front;

use \tiFy\Core\Templates\Templates;

class Factory extends \tiFy\Core\Templates\Factory
{
    /**
     * Contexte d'exécution
     */
    protected static $Context = 'front';

    /**
     * Liste des modèles prédéfinis
     */
    protected static $Models = [
        'AjaxListTable',
        'EditForm',
        'ListTable',
    ];

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification du template
     * @param array $attrs Attributs de configuration du template
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        parent::__construct($id, $attrs);

        // Déclaration des événements de déclenchement
        $this->appAddAction('init');
        $this->appAddAction('template_redirect');
        $this->appAddAction('wp_enqueue_scripts');
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
        // Bypass
        if (!$callback = $this->getAttr('cb')) :
            return;
        endif;

        $className = false;
        if (preg_match('/\\\/', $callback)) :
            $className = self::getOverride($callback);
        elseif (in_array($callback, self::$Models)) :
            $className = "\\tiFy\\Core\\Templates\\" . ucfirst($this->getContext()) . "\\Model\\{$callback}\\{$callback}";
        endif;

        if (!$className || !class_exists($className)) :
            return;
        endif;

        // Définition du modèle de base du template
        $this->setModel($className);

        // Instanciation du template
        $this->Template = new $className($this->getAttr('args', null));

        // Création des méthodes dynamiques
        $factory = $this;
        $this->Template->template = function () use ($factory) {
            return $factory;
        };
        $this->Template->db = function () use ($factory) {
            return $factory->getDb();
        };
        $this->Template->label = function ($label = '') use ($factory) {
            if (func_num_args()) {
                return $factory->getLabel(func_get_arg(0));
            }
        };
        $this->Template->getConfig = function ($attr, $default = '') use ($factory) {
            if (func_num_args()) {
                return call_user_func_array([$factory, 'getAttr'], func_get_args());
            }
        };

        if (!$this->getAttr('base_url')) :
            $this->setAttr('base_url', \site_url($this->getAttr('route')));
        endif;

        // Fonction de rappel d'affichage du template
        if (!$this->getAttr('render_cb', '')) :
            $this->setAttr('render_cb', 'render');
        endif;

        // Déclenchement des actions dans le template
        if (method_exists($this->Template, '_init')) :
            call_user_func([$this->Template, '_init']);
        endif;
        if (method_exists($this->Template, 'init')) :
            call_user_func([$this->Template, 'init']);
        endif;
    }

    /**
     * Court-circuitage de l'affichage
     *
     * @return void
     */
    final public function template_redirect()
    {
        // Bypass
        if (!$this->Template) :
            return;
        endif;
        $rewrite_base = parse_url(home_url());

        if (isset($rewrite_base['path'])) :
            $rewrite_base = trailingslashit($rewrite_base['path']);
        else :
            $rewrite_base = '/';
        endif;

        if (!preg_match('/^' . preg_quote($rewrite_base . ltrim($this->getAttr('route'), '//'), '/') . '\/?$/',
            Front::getRoute())) :
            return;
        endif;

        Templates::$Current = $this;

        // Déclenchement des actions dans le template           
        if (method_exists($this->Template, '_current_screen')) :
            call_user_func([$this->Template, '_current_screen']);
        endif;
        if (method_exists($this->Template, 'current_screen')) :
            call_user_func([$this->Template, 'current_screen']);
        endif;

        if ($template = $this->getAttr('template_part')) :
            get_template_part($template);
            exit;
        else :
            $this->render();
            exit;
        endif;
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    final public function wp_enqueue_scripts()
    {
        // Bypass
        if (!$this->Template) :
            return;
        endif;

        // Déclenchement des actions dans le template     
        if (method_exists($this->Template, '_wp_enqueue_scripts')) :
            call_user_func([$this->Template, '_wp_enqueue_scripts']);
        endif;
        if (method_exists($this->Template, 'wp_enqueue_scripts')) :
            call_user_func([$this->Template, 'wp_enqueue_scripts']);
        endif;
    }
}