<?php
namespace tiFy\Lib\Importer;

use tiFy\Lib\Notices\Notices;

abstract class Importer
{
    /**
     * Liste des attributs d'import
     * @var array
     */
    public $Attrs = [];

    /**
     * Types de données pris en charge
     * @var array {
     *      data|meta|tax|opt
     *
     *      @var string $data Données principales
     *      @var string $met Métadonnées
     *      @var string $tax Taxonomies
     *      @var string $opt Options
     * }
     */
    protected $Types = [
        'data'
    ];

    /**
     * Cartographie des données principales
     * Tableau de valeurs : ['clé de la donnée importée 1', 'clé de la donnée importée 2', ...]
     * Tableau Multidimensionnel : [['clé de la donnée importée 1' => 'clé de la donnée d'entrée correspondante 1'], ['clé de la donnée importée 2' => 'clé de la donnée d'entrée correspondante 2'], ...]
     *
     * @var array
     */
    protected $DataMap = [];

    /**
     * Cartographie des données principales permises. Permet de limiter le mapping des données principal au colonne de la table en base de données par exemple.
     * @var array
     */
    protected $AllowedDataMap = [];

    /**
     * Cartographie des metadonnées
     * Tableau de valeurs : ['meta_key 1', 'meta_key 2', ...]
     * Tableau Multidimensionnel : [['meta_key 1' => 'clé de la donnée d'entrée correspondante 1'], ['meta_key 2' => 'clé de la donnée d'entrée correspondante 2'], ...]
     *
     * @var array
     */
    protected $MetaMap = [];

    /**
     * Cartographie des taxonomies
     *
     */
    protected $TaxMap = [];

    /**
     * Cartographie des metadonnées
     * Tableau de valeurs : ['option_key 1', 'option_key 2', ...]
     * Tableau Multidimensionnel : [['option_key 1' => 'clé de la donnée d'entrée correspondante 1'], ['option_key 2' => 'clé de la donnée d'entrée correspondante 2'], ...]
     *
     * @var array
     */
    public $OptMap = [];

    /**
     * Liste des données d'entrée brutes
     */
    public $Input = [];

    /**
     * Liste des données définies
     * @var array
     */
    protected $Setted = [];

    /**
     * Liste des données filtrées
     * @var array
     */
    protected $Filtered = [];

    /**
     * Liste des données à importer
     * @var array
     */
    protected $Imported = [];

    /**
     * Classe de rappel des notification
     * @var \tiFy\Lib\Notices\Notices
     */
    protected $Notices;

    /**
     * Valeur de clé primaire du contenu enregistré
     * @var null|int|string
     */
    private $InsertID = null;

    /**
     * Validation de la réussite de l'import
     */
    private $Success = false;

    /**
     * Interruption de l'execution
     */
    private $Stop = false;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // initialisation de la classe de rappel des notifications
        $this->Notices = new Notices();
    }

    /**
     * TRAITEMENT
     */
    /**
     * Import d'un contenu
     *
     * @param array $input Données d'entrée brutes
     * @param array $attrs Attributs de configuration de l'import
     *
     * @return array {
     * }
     */
    final public static function import($input = [], $attrs = [])
    {
        // Instanciation de la classe d'import
        $import = new static();

        while(!$import->Stop) :
            // Traitement des attributs d'import
            $import->_parseAttrs($attrs);
            if ($import->Stop) break;

            // Traitement des données d'entrées brutes
            $import->_parseInput($input);
            if ($import->Stop) break;

            // Définition des données
            $import->_setValues();
            if ($import->Stop) break;

            // Evénement pré-insertion global
            $import->before_insert($import->getInsertId());
            if ($import->Stop) break;

            // Filtrage des données principales
            $import->_filterValues('data');
            if ($import->Stop) break;

            // Vérification d'intégrité des données principales
            $import->_checkValues('data');
            if ($import->Stop) break;

            // Evénement pré-insertion des données principales
            $import->before_insert_datas($import->getInsertId());
            if ($import->Stop) break;

            // Import des données principales
            $import->insert_datas($import->getDataList(), $import->getInsertId());
            if ($import->Stop) break;

            // Evénement post-insertion des données principales
            $import->after_insert_datas($import->getInsertId());
            if ($import->Stop) break;

            if ($insert_id = $import->getInsertId()) :
                foreach (['meta', 'tax', 'opt'] as $type) :
                    if(!$import->hasType($type)) :
                        continue;
                    endif;
                    $Type = ucfirst($type);

                    // Filtrage des données par type
                    $import->_filterValues($type);
                    if ($import->Stop) break2;

                    // Vérification d'intégrité des données par type
                    $import->_checkValues($type);
                    if ($import->Stop) break2;

                    // Evénement pré-insertion des données par type
                    call_user_func([$import, "before_insert_{$type}s"], $insert_id);
                    if ($import->Stop) break2;

                    // Import des données par type
                    $list = call_user_func([$import, "get{$Type}List"]);
                    foreach ($list as $key => $value) :
                        call_user_func([$import, "insert_{$type}"], $key, $value, $insert_id);
                        if ($import->Stop) break3;
                    endforeach;

                    // Evénement post-insertion des données principales
                    call_user_func([$import, "after_insert_{$type}s"], $insert_id);
                    if ($import->Stop) break2;
                endforeach;
            endif;

            // Evénement post-insertion global
            $import->after_insert($import->getInsertId());
            if ($import->Stop) break;

            $import->setStop();
        endwhile;

        return $import->getResponse();
    }

    /**
     * Traitement des attributs d'imports
     *
     * @param array $attrs Liste des attributs d'import
     *
     * @return void
     */
    private function _parseAttrs($attrs = [])
    {
        $this->Attrs = !empty($attrs) ? $attrs : $this->setAttrs();
        $this->Attrs = $this->parseAttrs($this->Attrs);
    }

    /**
     * Traitement des données brutes d'entrées
     *
     * @param array $input Données d'entrées brutes passées en arguments
     *
     * @return void
     */
    private function _parseInput($input = [])
    {
        $this->Input = !empty($input) ? $input : $this->setInput();
        $this->Input = $this->parseInput($this->Input);
    }

    /**
     * Définition des valeurs de données de tout type
     */
    private function _setValues()
    {
        foreach ((array)$this->Types as $type) :
            $Type = ucfirst($type);
            ${$type} = [];

            // Récupération de la cartographies des données
            $Map = "{$Type}Map";
            if ($customMap = call_user_func([$this, "set{$Map}"])) :
                $this->{$Map} = $customMap;
            endif;

            // Définition des données cartographiées
            if ($this->{$Map}) :
                foreach ((array)$this->{$Map} as $key => $map) :
                    if (is_numeric($key)) :
                        $key = $map;
                    endif;

                    // Bypass - Limitation aux données principales permises.
                    if (($type==='data') && !empty($this->AllowedDataMap) && !in_array($key, $this->AllowedDataMap)) :
                        continue;
                    endif;

                    if (isset($this->Input[$map])) :
                        ${$type}[$key] = $this->Input[$map];
                    elseif (method_exists($this, "set_{$type}_{$key}")) :
                        ${$type}[$key] = call_user_func([$this, "set_{$type}_{$key}"]);
                    else :
                        ${$type}[$key] = call_user_func([$this, "set_{$type}s"], $key);
                    endif;
                endforeach;

            // Définition des données non cartographiées
            else :
                if ($type==='data') :
                    foreach ($this->Input as $key => $value) :
                        // Bypass
                        if (!empty($this->AllowedDataMap) && !in_array($key, $this->AllowedDataMap)) :
                            continue;
                        endif;

                        ${$type}[$key] = $value;
                    endforeach;
                endif;

                if ($matches = preg_grep("#^set_" . $type . "_(.*)#", get_class_methods($this))) :
                    foreach ($matches as $method) :
                        $key = preg_replace("#^set_" . $type . "_#", '', $method);

                        // Bypass - Limitation aux données principales permises.
                        if (($type==='data') && !empty($this->AllowedDataMap) && !in_array($key, $this->AllowedDataMap)) :
                            continue;
                        endif;

                        if (method_exists($this, "set_{$type}_{$key}")) :
                            ${$type}[$key] = call_user_func([$this, "set_{$type}_{$key}"]);
                        else :
                            ${$type}[$key] = call_user_func([$this, "set_{$type}s"], $key);
                        endif;
                    endforeach;
                endif;
            endif;
        endforeach;

        $this->Setted = compact($this->Types);
    }

    /**
     * Filtrage des valeurs des données par type
     *
     * @param string $type data|meta|tax|opt
     *
     * @return array
     */
    private function _filterValues($type, $insert_id = 0)
    {
        if (!isset($this->Setted[$type])) :
            return;
        endif;

        ${$type} = $this->getSetList($type);

        foreach (${$type} as $key => &$value) :
            $value = call_user_func([$this, "filter_{$type}s"], $value, $key, $insert_id);

            if (method_exists($this, "filter_{$type}_{$key}")) :
                $value = call_user_func([$this, "filter_{$type}_{$key}"], $value, $insert_id);
            endif;
        endforeach;

        $this->Filtered[$type] = ${$type};
    }

    /**
     * Contrôle des valeurs des données par type
     *
     * @param string $type data|meta|tax|opt
     *
     * @return void
     */
    private function _checkValues($type, $insert_id = 0)
    {
        if (!isset($this->Filtered[$type])) :
            return;
        endif;

        ${$type} = $this->Filtered[$type];

        foreach (${$type} as $key => $value) :
            call_user_func([$this, "check_{$type}s"], $value, $key, $insert_id);

            if (method_exists($this, "check_{$type}_{$key}")) :
                call_user_func([$this, "check_{$type}_{$key}"], $value, $insert_id);
            endif;
        endforeach;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la liste des attributs d'import
     *
     * @return array
     */
    final public function getAttrList()
    {
        return $this->Attrs;
    }

    /**
     * Récupération d'une valeur d'attribut d'import
     *
     * @param string $key Clé d'index de la valeur à récupérer
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getAttr($key, $default = '')
    {
        if (isset($this->Attrs[$key])) :
            return $this->Attrs[$key];
        else :
            return $default;
        endif;
    }

    /**
     * Récupération de la liste des données d'entrée brute
     *
     * @return array
     */
    final public function getInputList()
    {
        return $this->Input;
    }

    /**
     * Récupération d'une valeur de donnée d'entrée brute
     *
     * @param string $key Clé d'index de la valeur à récupérer
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getInput($key, $default = '')
    {
        if (isset($this->Input[$key])) :
            return $this->Input[$key];
        else :
            return $default;
        endif;
    }

    /**
     * Récupération de la valeur de clé primaire du contenu enregistré
     *
     * @return null|string|int
     */
    final public function getInsertId()
    {
        return $this->InsertID;
    }

    /**
     * Définition de la valeur de clé primaire du contenu enregistré
     *
     * @param int|string Valeur de la clé primaire
     *
     * @return null|string|int
     */
    final public function setInsertId($value)
    {
        return $this->InsertID = $value;
    }

    /**
     * Récupération de la validation de réussite de l'import
     *
     * @return bool
     */
    final public function getSuccess()
    {
        return $this->Success;
    }

    /**
     * Définition de la validation de réussite de l'import
     *
     * @param bool
     *
     * @return void
     */
    final public function setSuccess($success)
    {
        $this->Success = $success;
    }

    /**
     * Récupération de la liste des données definies
     *
     * @param string $type data|meta|tax|opt
     *
     * @return array
     */
    final public function getSetList($type)
    {
        if (isset($this->Setted[$type])) :
            return $this->Setted[$type];
        endif;
    }

    /**
     * Récupération d'une valeur de donnée définie
     *
     * @param string $key Clé d'index de la valeur à récupérer
     * @param mixed $default Valeur de retour par défaut
     * @param string $type data|meta|tax|opt
     *
     * @return mixed
     */
    final public function getSet($key, $default = '', $type)
    {
        if(!$TypeSetted = $this->getSetList($type)) :
            return $default;
        endif;

        if (isset($TypeSetted[$key])) :
            return $TypeSetted[$key];
        else :
            return $default;
        endif;
    }

    /**
     * Vérification de prise en charge d'un type de données
     *
     * @param string $type data|meta|tax|opt
     *
     * @return bool
     */
    final public function hasType($type)
    {
        return in_array($type, $this->Types);
    }

    /**
     * Récupération des données filtrées par type
     *
     * @param string $type data|meta|tax|opt
     *
     * @return array
     */
    final public function getList($type)
    {
        if (!$this->hasType($type)) :
            return;
        endif;

        return $this->Filtered[$type];
    }

    /**
     * Récupération de la valeur de retour d'une donnée filtrée
     *
     * @param string $key
     * @param mixed $default Valeur de retour par défaut
     * @param string $type data|meta|tax|opt
     *
     * @return mixed
     */
    final public function get($key, $default = '', $type)
    {
        if (!$this->hasType($type)) :
            return $default;
        endif;

        $list = $this->getList($type);
        if (isset($list[$key])) :
            return $list[$key];
        else :
            return $default;
        endif;
    }

    /**
     * Récupération de la liste des données principales filtrées
     *
     * @return array
     */
    final public function getDataList()
    {
        return $this->getList('data');
    }

    /**
     * Récupération de la valeur d'une donnée principale filtrée
     */
    final public function getData($key, $default = '')
    {
        return $this->get($key, $default, 'data');
    }

    /**
     * Récupération de la liste des metadonnées filtrées
     *
     * @return array
     */
    final public function getMetaList()
    {
        return $this->getList('meta');
    }

    /**
     * Récupération de la valeur d'une métadonnée filtrée
     */
    final public function getMeta($meta_key, $default = '')
    {
        return $this->get($meta_key, $default, 'meta');
    }

    /**
     * Récupération de la liste des termes de taxonomies filtrés
     *
     * @return array
     */
    final public function getTaxList()
    {
        return $this->getList('tax');
    }

    /**
     * Récupération des termes d'une taxonomy filtrés
     */
    final public function getTax($taxonomy, $default = '')
    {
        return $this->get($taxonomy, $default, 'tax');
    }

    /**
     * Récupération de la liste des options filtrées
     *
     * @return array
     */
    final public function getOptList()
    {
        return $this->getList('opt');
    }

    /**
     * Récupération de la valeur d'une option filtrée
     */
    final public function getOpt($option_name, $default = '')
    {
        return $this->get($option_name, $default, 'opt');
    }

    /**
     * Définition de l'interruption de l'import
     */
    final public function setStop()
    {
        $this->Stop =  true;
    }

    /**
     * Récupération de la réponse
     */
    final public function getResponse()
    {
        $response = [];
        $response['insert_id'] = $this->getInsertId();
        $response['success'] = $this->getSuccess();
        foreach ($this->Notices->getList() as $type => $notices) :
            $response['notices'][$type] = $notices;
        endforeach;

        return $response;
    }

    /**
     * METHODES DE SURCHARGE
     */
    /**
     * Définition des attributs d'import
     */
    public function setAttrs()
    {
        return [];
    }

    /**
     * Traitement des attributs d'import
     */
    public function parseAttrs($attrs = [])
    {
        return $attrs;
    }

    /**
     * Définition des données d'entrée
     */
    public function setInput()
    {
        return [];
    }

    /**
     * Traitement des données d'entrée
     */
    public function parseInput($input = [])
    {
        return $input;
    }

    /**
     * Définition de la cartographie des données principales
     */
    public function setDataMap()
    {
        return [];
    }

    /**
     * Définition de la cartographie des metadonnées
     */
    public function setMetaMap()
    {
        return [];
    }

    /**
     * Définition de la cartographie des taxonomy
     */
    public function setTaxMap()
    {
        return [];
    }

    /**
     * Définition de la cartographie des options
     */
    public function setOptMap()
    {
        return [];
    }

    /**
     * Définition par défaut des valeurs d'entrée de données principales
     */
    public function set_datas($key)
    {
        return '';
    }

    /**
     * Définition par défaut des valeurs d'entrée de metadonnées
     */
    public function set_metas($meta_key)
    {
        return '';
    }

    /**
     * Définition par défaut des valeurs d'entrée de termes de taxonomie
     */
    public function set_taxs($taxonomy)
    {
        return '';
    }

    /**
     * Définition par défaut des valeurs d'entrée d'options
     */
    public function set_opts($option_name)
    {
        return '';
    }

    /**
     * Filtrage par défaut des valeurs de données principales à insérer
     */
    public function filter_datas($value, $key)
    {
        return $value;
    }

    /**
     * Filtrage par défaut des valeurs de metadonnées à insérer
     */
    public function filter_metas($meta_value, $meta_key, $insert_id)
    {
        return $meta_value;
    }

    /**
     * Filtrage par défaut des valeurs de terme de taxonomie à insérer
     */
    public function filter_taxs($terms, $taxonomy, $insert_id)
    {
        return $terms;
    }

    /**
     * Filtrage par défaut des valeurs d'options à insérer
     */
    public function filter_opts($option_value, $option_name, $insert_id)
    {
        return $option_value;
    }

    /**
     * Vérification par défaut des valeurs de données principales avant insertion
     */
    public function check_datas($value, $key)
    {
        return;
    }

    /**
     * Vérification par défaut des valeurs de metadonnées avant insertion
     */
    public function check_metas($meta_value, $meta_key, $insert_id)
    {
        return;
    }

    /**
     * Vérification par défaut des valeurs de termes de taxonomie avant insertion
     */
    public function check_taxs($terms, $taxonomy, $insert_id)
    {
        return;
    }

    /**
     * Vérification par défaut des valeurs d'options avant insertion
     */
    public function check_ops($option_value, $option_name, $insert_id)
    {
        return;
    }

    /**
     * Evénement pré-insertion global
     *
     * @param int $insert_id Identifiant de qualification de l'élément enregistré
     *
     * @return void
     */
    public function before_insert($insert_id)
    {
        return;
    }

    /**
     * Evénement post-insertion global
     *
     * @param int $insert_id Identifiant de qualification de l'élément enregistré
     *
     * @return void
     */
    public function after_insert($insert_id)
    {
        return;
    }

    /**
     * Evénement pré-insertion des données principales
     */
    public function before_insert_datas($insert_id)
    {
        return;
    }

    /**
     * Insertion des données principales
     */
    public function insert_datas($datas, $insert_id)
    {
        return $this->Notices->addError(__('Méthode d\'enregistrement des données d\'import non définie', 'tify'), 'tiFyInheritsImport_NoDataInsertCb');
    }

    /**
     * Evénement post-insertion des données principales
     *
     * @param int $insert_id Identifiant de qualification de l'élément enregistré
     *
     * @return void
     */
    public function after_insert_datas($insert_id)
    {
        return;
    }

    /**
     * Evénement pré-insertion des metadonnées
     */
    public function before_insert_metas($insert_id)
    {
        return;
    }

    /**
     * Insertion d'une métadonnée
     */
    public function insert_meta($meta_key, $meta_value, $insert_id)
    {
        return $this->Notices->addWarning(__('Méthode d\'enregistrement des metadonnées d\'import non définie', 'tify'), 'tiFyInheritsImport_NoMetaDataInsertCb');
    }

    /**
     * Evénement post-insertion des metadonnées
     */
    public function after_insert_metas($insert_id)
    {
        return;
    }

    /**
     * Evénement pré-insertion des termes de taxonomies
     */
    public function before_insert_taxs($insert_id)
    {
        return;
    }

    /**
     * Insertion des termes d'une taxonomie
     */
    public function insert_tax($taxonomy, $terms, $insert_id)
    {
        return $this->Notices->addWarning(__('Méthode d\'enregistrement des termes de taxonomie d\'import non définie', 'tify'), 'tiFyInheritsImport_NoTaxonomyTermsInsertCb');
    }

    /**
     * Evénement post-insertion des termes de taxonomies
     */
    public function after_insert_taxs($insert_id)
    {
        return;
    }

    /**
     * Evénement pré-insertion des options
     */
    public function before_insert_opts($insert_id)
    {
        return;
    }

    /**
     * Insertion d'une option
     */
    public function insert_opt($option_name, $option_value, $insert_id)
    {
        return $this->Notices->addWarning(__('Méthode d\'enregistrement des options d\'import non définie', 'tify'), 'tiFyInheritsImport_NoOptionInsertCb');
    }

    /**
     * Evénement post-insertion des options
     */
    public function after_insert_opts($insert_id)
    {
        return ;
    }
}