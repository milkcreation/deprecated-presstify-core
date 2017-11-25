<?php
namespace tiFy\Components\CustomColumns;

class Factory extends \tiFy\App\Factory
{
    /**
     * Instance
     * @var int
     */
    private static $Instance    = 0;

    /**
     * Liste des attributs de configuration
     * @var array
     */
    private $Attrs              = [];

    /**
     * CONSTRUCTEUR
     *
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($attrs = [])
    {
        parent::__construct();

        self::$Instance++;

        // Définition des attributs de configuration
        $defaults = [
            'title'    => '',
            'position' => 0,
            'column'   => 'tiFyColumn-' . self::$Instance,
            'sortable' => false,
        ];
        if (is_callable([$this, 'getDefaults'])) :
            $defaults = \wp_parse_args((array)call_user_func([$this, 'getDefaults']), $defaults);
        endif;
        $this->Attrs = \wp_parse_args($attrs, $defaults);

        $object_type = $this->getAttr('object_type');
        switch ($this->getAttr('object')) :
            case 'post_type' :
                // Initialisation de la vue courante
                $this->tFyAppFilterAdd("manage_edit-{$object_type}_columns", '_header');

                $this->tFyAppActionAdd("manage_{$object_type}_posts_custom_column", '_content', 25, 2);
                break;

            case 'taxonomy' :
                // Initialisation de la vue courante
                $this->tFyAppFilterAdd("manage_edit-{$object_type}_columns", '_header');

                $this->tFyAppFilterAdd("manage_{$object_type}_custom_column", '_content', 25, 3);
                break;

            case 'custom' :
                // Initialisation de la vue courante
                $this->tFyAppFilterAdd("manage_{$object_type}_columns", '_header');

                $this->tFyAppFilterAdd("manage_{$object_type}_custom_column", '_content', 25, 3);
                break;
        endswitch;
    }

    /**
     * DECLENCHEURS
     */
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
     * Récupération de la liste des attributs de configuration
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
     * @param string $name Nom de l'attribut
     * @param mixed $default Valeur par défaut en cas d'échec
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
     * Récupération d'attributs
     * @deprecated
     *
     * @param string $index Nom de l'attribut
     *
     * @return mixed
     */
    final public function getAttrs($index = null)
    {
        if (!$index) :
            return $this->getAttrList();
        elseif (isset($this->Attrs[$index])) :
            return $this->getAttr($index);
        endif;
    }

    /**
     * Récupération des attributs de configuration par défaut
     *
     * @return array
     */
    public function getDefaults()
    {
        return [];
    }

    /**
     * Déclaration de la colonne
     *
     * @param array $columns Liste de colonnes
     *
     * @return array
     */
    final public function _header($columns)
    {
        if ($position = (int)$this->getAttr('position', 0)) :
            $newcolumns = [];
            $n = 0;
            foreach ($columns as $key => $column) :
                if ($n === $position):
                    $newcolumns[$this->getAttr('column')] = $this->getAttr('title');
                endif;
                $newcolumns[$key] = $column;
                $n++;
            endforeach;
            $columns = $newcolumns;
        else :
            $columns[$this->getAttr('column')] = $this->getAttr('title');
        endif;

        return $columns;
    }

    /**
     * Pré-Affichage du contenu de la colonne
     *
     * @return void|string
     */
    final public function _content()
    {
        switch ($this->getAttr('object')) :
            case 'post_type' :
                $column_name = func_get_arg(0);
                // Bypass
                if ($column_name !== $this->getAttr('column')) :
                    return;
                endif;
                break;
            case 'taxonomy' :
                $output         = func_get_arg(0);
                $column_name    = func_get_arg(1);
                // Bypass
                if ($column_name !== $this->getAttr('column')) :
                    return $output;
                endif;
                break;

            case 'custom' :
                $output         = func_get_arg(0);
                $column_name    = func_get_arg(1);
                // Bypass
                if ($column_name !== $this->getAttr('column')) :
                    return $output;
                endif;
                break;
        endswitch;

        if (($content_cb = $this->getAttr('content_cb')) && is_callable($content_cb)) :
            call_user_func_array($content_cb, func_get_args());
        elseif (is_callable([$this, 'content'])) :
            call_user_func_array([$this, 'content'], func_get_args());
        else :
            _e('Pas de données à afficher', 'tify');
        endif;
    }
}