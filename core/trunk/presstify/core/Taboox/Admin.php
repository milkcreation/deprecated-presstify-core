<?php
namespace tiFy\Core\Taboox;

class Admin extends \tiFy\Core\Taboox\Factory
{
    /**
     * ID l'écran courant d'affichage du formulaire
     * @var \WP_Screen::$id;
     */
    protected $ScreenID;

    /**
     * Paramètres
     * @todo depreciation
     *
     * @var unknown $screen
     * @var unknown $page
     * @var unknown $env
     * @var array $args
     */
    public $screen, $page, $env, $args = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    public function admin_init()
    {

    }

    /**
     * Chargement de la page courante
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {

    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {

    }

    /**
     * CONTROLEURS
     */
    /**
     *
     */
    final public function _content()
    {
        if (($content_cb = $this->getAttr('content_cb')) && is_callable($content_cb)) :
            call_user_func_array($content_cb, func_get_args());
        elseif (is_callable([$this, 'form'])) :
            call_user_func_array([$this, 'form'], func_get_args());
        else :
            _e('Pas de données à afficher', 'tify');
        endif;
    }
}