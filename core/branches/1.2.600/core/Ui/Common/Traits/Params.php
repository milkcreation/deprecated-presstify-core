<?php 
namespace tiFy\Core\Ui\Common\Traits;

trait Params
{
    /**
     * Listes des paramètres autorisés
     * @var array Tableau indexés
     */
    private $AllowedParams    = [
        'base_uri',
        'singular',
        'plural',
        'page_title',
        'notices',
        'capability',
        'item_index_name',
        'query_args'
    ];

    /**
     * Liste des paramètres par défaut
     * @var array Tableau associatifs
     */
    private $DefaultParams = [];

    /**
     * Liste des paramètres définis
     * @var array Tableau associatifs
     */
    private $Params = [];

    /**
     * Listes des paramètres initialisés
     * @var array Tableau indexés
     */
    private $Initialized = [];

    /**
     * CONTROLEURS
     */
    /**
     * Déclaration d'une liste d'autorisation de définition de paramètres.
     *
     * @param array $params Tableau indexés des identifiants de qualification des paramètre autorisés.
     *
     * @return void
     */
    protected function setAllowedParamList($params = [])
    {
        foreach ($params as $param) :
            $this->setAllowedParam($param);
        endforeach;
    }

    /**
     * Déclaration d'une autorisation de définition de paramètre.
     *
     * @param string $name Identifiant de qualification d'un paramètre autorisé.
     *
     * @return void
     */
    protected function setAllowedParam($name)
    {
        if (!in_array($name, $this->AllowedParams)) :
            array_push($this->AllowedParams, $name);
        endif;
    }

    /**
     * Récupération de la liste des paramètres autorisés
     *
     * @return array
     */
    protected function getAllowedParamList()
    {
        return $this->AllowedParams;
    }

    /**
     * Vérifie si un paramètre fait partie de la liste des paramètres autorisés
     *
     * @param string $name Identifiant de qualification d'un paramètre
     *
     * @return bool
     */
    protected function isAllowedParam($name)
    {
        return in_array($name, $this->getAllowedParamList());
    }

    /**
     * Déclaration d'une liste des paramètres par défaut.
     *
     * @param array $params Tableau associatifs des paramètres par défaut.
     *
     * @return void
     */
    protected function setDefaultParamList($params = [])
    {
        foreach ($params as $name => $value) :
            if (is_int($name)) :
                $name = $value;
                $value = null;
            endif;
            $this->setDefaultParam($name, $value);
        endforeach;
    }

    /**
     * Déclaration d'un paramètre par défaut.
     *
     * @param string $name Identifiant de qualification du paramètre.
     * @param string $value Valeur par défaut du paramètre.
     *
     * @return void
     */
    protected function setDefaultParam($name, $value)
    {
        $this->DefaultParams[$name] = $value;
    }

    /**
     * Définition de la liste des paramètres
     *
     * @param array $params
     *
     * @return void
     */
    protected function setParamList($params = [])
    {
        foreach ($params as $name => $value) :
            $this->setParam($name, $value);
        endforeach;
    }

    /**
     * Définition d'un paramètre
     *
     * @param string $name Identifiant de qualification du paramètre
     * @param mixed $value Valeur du paramètre
     *
     * @return void
     */
    protected function setParam($name, $value)
    {
        if ($this->isAllowedParam($name)) :
            $this->Params[$name] = $value;
        endif;
    }

    /**
     * Récupération de la liste des paramètres définis
     *
     * @return array
     */
    protected function getParamList()
    {
        return $this->Params;
    }

    /**
     * Récupération d'un paramètre
     *
     * @return void|mixed
     */
    protected function getParam($name, $default = '')
    {
        if (!isset($this->Params[$name])) :
            return $default;
        endif;

        return $this->Params[$name];
    }

    /**
     * Initialisation des paramètres
     *
     * @param array $params Liste des paramètre à initialiser
     * @param bool $reinit Force la réinitialisation des paramètres déja chargé
     *
     * @return void
     */
    protected function initParams($params = [], $reinit = false)
    {
        if(!$allowed = $this->getAllowedParamList()) :
            return;
        endif;

        // Liste des paramètres passés en attribut de configuration
        $attrs_params = $this->getAttr('params', []);

        foreach($allowed as $name) :
            // Limite l'initialisation à la liste des paramètres passés en arguments
            if ($params && !in_array($name, $params)) :
                continue;
            endif;

            // Limite l'initialisation à la liste paramètres qui n'ont pas été pré-initialisés
            if (!$reinit && in_array($name, $this->Initialized)) :
                continue;
            endif;

            // Récupération de la valeur du paramètre défini en argument
            if (isset($attrs_params[$name])) :
                $this->Params[$name] = $attrs_params[$name];
            endif;

            // Récupération de la valeur du paramètre par défaut
            if (!isset($this->Params[$name]) && isset($this->DefaultParams[$name])) :
                $this->Params[$name] = $this->DefaultParams[$name];
            endif;

            // Récupération de la valeur du paramètre défini en méthode de surcharge
            if (method_exists($this, "set_param_{$name}")) :
                if (isset($this->Params[$name])) :
                    $this->Params[$name] = call_user_func([$this, "set_param_{$name}"], $this->Params[$name]);
                else :
                    $this->Params[$name] = call_user_func([$this, "set_param_{$name}"]);
                endif;
            endif;

            // Initialisation de la valeur des paramètres
            if (method_exists($this, "init_param_{$name}")) :
                if (isset($this->Params[$name])) :
                    $this->Params[$name] = call_user_func([$this, "init_param_{$name}"], $this->Params[$name]);
                else :
                    $this->Params[$name] = call_user_func([$this, "init_param_{$name}"]);
                endif;
            endif;

            array_push($this->Initialized, $name);
        endforeach;
    }

    /**
     * Définition de l'url de la page d'affichage du gabarit
     *
     * @param string $base_uri Url de la page d'affichage du gabarit définie en paramètre
     *
     * @return string
     */
    public function set_param_base_uri($base_uri = '')
    {
        return $base_uri;
    }

    /**
     * Définition de l'intitulé de qualification d'un élément.
     *
     * @param string $singular Intitulé de qualification d'un élément défini en paramètre
     *
     * @return string
     */
    public function set_param_singular($singular = '')
    {
        return $singular;
    }

    /**
     * Définition de l'intitulé de qualification d'une liste d'éléments.
     *
     * @param string $plural Intitulé de qualification d'une liste d'éléments défini en paramètre
     *
     * @return string
     */
    public function set_param_plural($plural = '')
    {
        return $plural;
    }

    /**
     * Définition du titre de la page
     *
     * @param string $page_title Titre de la page défini en paramètre
     *
     * @return string
     */
    public function set_param_page_title($page_title = '')
    {
        return $page_title;
    }

    /**
     * Définition de la liste des messages de notification.
     *
     * @param array $notices Liste des messages de notification définis en paramètre
     *
     * @return array
     */
    public function set_param_notices($notices = [])
    {
        return $notices;
    }

    /**
     * Définition de l'habilitation d'accès à l'édition d'un élément
     *
     * @param string $capability Habilitation d'accès à l'édition d'un élément définie en paramètre
     *
     * @return string
     */
    public function set_param_capability($capability = '')
    {
        return $capability;
    }

    /**
     * Définition de la qualification de l'index de récupération d'un élément
     *
     * @param string $item_index_name Qualification de l'index de récupération d'un élément défini en paramètre
     *
     * @return string
     */
    public function set_param_item_index_name($item_index_name = '')
    {
        return $item_index_name;
    }

    /**
     * Définition de la liste des arguments de requête de récupération d'élément
     *
     * @param array $query_args Liste des arguments de requête de récupération d'élément définis en paramètre
     *
     * @return array
     */
    public function set_param_query_args($query_args = [])
    {
        return $query_args;
    }

    /**
     * Initialisation de l'url de la page d'affichage du gabarit
     *
     * @param string $base_uri Url de la page d'affichage du gabaritexistante
     *
     * @return string
     */
    public function init_param_base_uri($base_uri = '')
    {
        if (!$base_uri) :
            $base_uri = $this->getAttr('base_uri', set_url_scheme('//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
        endif;

        return $base_uri;
    }

    /**
     * Initialisation de l'intitulé de qualification d'un élément.
     *
     * @param string $singular Intitulé de qualification d'un élément existant
     *
     * @return string
     */
    public function init_param_singular($singular = '')
    {
        if (!$singular) :
            $singular = $this->getLabel('singular', '');
        endif;

        return $singular;
    }

    /**
     * Initialisation de l'intitulé de qualification d'une liste d'éléments.
     *
     * @param string $plural Intitulé de qualification d'une liste d'éléments existant
     *
     * @return string
     */
    public function init_param_plural($plural = '')
    {
        if (!$plural) :
            $plural = $this->getLabel('plural', '');
        endif;

        return $plural;
    }

    /**
     * Initialisation  du titre de la page
     *
     * @param string $page_title Titre de la page défini en paramètre
     *
     * @return string
     */
    public function init_param_page_title($page_title = '')
    {
        if (!$page_title) :
            $page_title = $this->getLabel('singular', '');
        endif;

        return $page_title;
    }

    /**
     * Initialisation de l'habilitation d'accès à l'édition d'un élément
     *
     * @param string $capability Habilitation d'accès à l'édition d'un élément définie en paramètre
     *
     * @return string
     */
    public function init_param_capability($capability = '')
    {
        if (!$capability) :
            $capability = 'manage_options';
        endif;

        return $capability;
    }

    /**
     * Initialisation de la liste des messages de notification.
     *
     * @param array $notices Liste des messages de notification existants
     *
     * @return array
     */
    public function init_param_notices($notices = [])
    {
        if($notices) :
            $_notices = [];
            foreach ($notices as $id => $attrs) :
                if (is_int($id)) :
                    $id = (string) $attrs;
                    $attrs = [];
                endif;
                $_notices[$id] = $attrs;
            endforeach;

            $notices = $_notices;
        endif;

        return $notices;
    }

    /**
     * Initialisation de la qualification de l'index de récupération d'un élément
     *
     * @param string $item_index_name Qualification de l'index de récupération d'un élément existante
     *
     * @return string
     */
    public function init_param_item_index_name($item_index_name = [])
    {
        if(!$item_index_name && ($db = $this->getDb())) :
            $item_index_name = $db->getPrimary();
        endif;

        return $item_index_name;
    }
}