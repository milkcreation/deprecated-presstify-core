<?php
namespace tiFy\Core\Taboox;

class Admin extends \tiFy\App\Factory
{
    /**
     * Liste des attributs de configuration
     * @var array
     */
    private $Attrs          = [];

    /**
     * @deprecated
     */
    public $page            = null;

    /**
     * @deprecated
     */
    public $args            = [];

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
     * Initialisation de la classe
     */
    final public static function _init($attrs = [])
    {
        $Inst = new static;
        $Inst->args = $Inst->Attrs = $attrs;

        return $Inst;
    }

    /**
     * Récupération de la liste de attributs de configuration
     *
     * @return array
     */
    final public function getAttrList()
    {
        return $this->Attrs;
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Nom de l'attribut de configuration
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getAttr($name, $default = '')
    {
        if (!isset($this->Attrs[$name])) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }

    /**
     *
     */
    final public function _content()
    {
        if (($content_cb = $this->getAttr('content_cb')) && is_callable($content_cb)) :
            call_user_func_array($content_cb, func_get_args());
        elseif (method_exists($this, 'form') && is_callable([$this, 'form'])) :
            call_user_func_array([$this, 'form'], func_get_args());
        else :
            _e('Pas de données à afficher', 'tify');
        endif;
    }
}