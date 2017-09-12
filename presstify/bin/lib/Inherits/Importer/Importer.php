<?php

namespace tiFy\Inherits\Importer;

abstract class Importer
{
    /**
     * Cartographie des données principales
     * Correspondance colonne de la table de BDD => données d'import
     */
    public $DataMap = [];

    /**
     * Cartographie des données principales permises
     * @var array
     */
    protected $AllowedDataMap = [];

    /**
     * Cartographie des metadonnées
     * Correspondance meta_key => données d'import
     */
    public $MetadataMap = [];

    /**
     * Cartographie des taxonomies
     */
    public $TaxonomyMap = [];

    /**
     * Cartographie des options
     */
    public $OptionMap = [];

    /**
     * Cartographie des données complémentaires
     */
    public $MiscMap = [];

    /**
     * Données d'entrée brutes
     */
    public $InputDatas = [];

    /**
     * Liste des données définies
     * @var array
     */
    protected $SettedDatas = [];

    /**
     * Liste des données filtrées
     * @var array
     */
    protected $FilteredDatas = [];

    /**
     * Type de données prises en charge
     */
    protected $DataType = [
        // Données de la table principale (requis)
        'data',

        // Métadonnées
        //'metadata', 

        // Taxonomies
        //'taxonomy',

        // Options
        //'option'

        // Données diverses
        //'misc'
    ];

    /**
     * Liste des données principales à insérer
     * @var array
     */
    private $Datas = [];

    /**
     * Liste des metadonnées à insérer
     * @var array
     */
    private $MetaDatas = [];

    /**
     * Liste des données de taxonomie à insérer
     * @var array
     */
    private $TaxDatas = [];

    /**
     * Liste des données d'option à insérer
     * @var array
     */
    private $OptDatas = [];

    /**
     * Liste des données complémentaires
     * @var array
     */
    private $MiscDatas = [];

    /**
     * Liste des erreurs de traitement
     * @var array
     */
    private $Errors = [];

    /**
     * Valeur de clé primaire du contenu enregistré
     */
    private $InsertID = 0;

    /**
     * CONSTRUCTEUR
     */
    public function __construct($inputdata = [], $attrs = [])
    {
        // Traitement des attributs d'import
        $this->_parseAttrs($attrs);

        // Traitement des définitions de données 
        $this->_parseSettedDatas($inputdata);
    }

    /**
     * TRAITEMENT
     */
    /**
     * Import d'un contenu
     */
    final public static function import($inputdata = [], $attrs = [])
    {
        $import = new static($inputdata, $attrs);

        // Erreurs de Pré-traitement
        if ($import->hasError()) {
            return array('insert_id' => 0, 'errors' => $import->getErrors());
        }

        // Filtrage des données principales
        $import->_filterDataValues();

        // Vérification d'intégrité des données
        $import->_checkDataValues();

        // Evénement pré-insertion global
        $import->before_insert();

        // Erreurs de traitement
        if ($import->hasError()) {
            return array('insert_id' => 0, 'errors' => $import->getErrors());
        }

        // Insertion des données           
        $insert_id = $import->insert_datas($import->getDatas());

        if (!is_wp_error($insert_id)) :
            $import->InsertID = $insert_id;

            // Evénement post-insertion des données principales
            $import->after_insert_datas($import->InsertID);

            // Traitement des métadonnées
            if ($import->getMetaDatas()) :
                $import->_filterMetaValues();
                $import->_checkMetaValues();

                foreach ($import->getMetaDatas() as $meta_key => $meta_value) :
                    $import->insert_meta($import->InsertID, $meta_key, $meta_value);
                endforeach;

                // Evénement post-insertion des metadonnées
                $import->after_insert_metadatas($import->InsertID);
            endif;

            // Traitement des termes de taxonomy
            if ($import->getTaxDatas()) :
                $import->_filterTaxValues();
                $import->_checkTaxValues();

                foreach ($import->getTaxDatas() as $taxonomy => $terms) :
                    $import->insert_taxonomy_terms($import->InsertID, $terms, $taxonomy);
                endforeach;

                // Evénement post-insertion des termes de taxonomies
                $import->after_insert_terms($import->InsertID);
            endif;

            // Traitement des options
            if ($import->getOptDatas()) :
                $import->_filterOptionValues();
                $import->_checkOptionValues();

                foreach ($import->getOptDatas() as $option_name => $option_value) :
                    $import->insert_option($import->InsertID, $option_name, $option_value);
                endforeach;

                // Evénement post-insertion des options
                $import->after_insert_options($import->InsertID);
            endif;

            // Evénement post-insertion global
            $import->after_insert($import->InsertID);

            $errors = $import->getErrors();
        else :
            $errors = $insert_id;
        endif;

        return array('insert_id' => $import->InsertID, 'errors' => $errors);
    }

    /**
     * Traitement des attributs
     */
    final public function _parseAttrs($attrs = [])
    {
        // Cartographie des données
        /// Données principales
        if (in_array('data', $this->DataType)) :
            $this->DataMap = isset($attrs['data_map']) ? $attrs['data_map'] : $this->setDataMap();
        endif;

        /// Métadonnées
        if (in_array('metadata', $this->DataType)) :
            $this->MetadataMap = isset($attrs['metadata_map']) ? $attrs['metadata_map'] : $this->setMetadataMap();
        endif;

        /// Taxonomies
        if (in_array('taxonomy', $this->DataType)) :
            $this->TaxonomyMap = isset($attrs['taxonomy_map']) ? $attrs['taxonomy_map'] : $this->setTaxonomyMap();
        endif;

        /// Options
        if (in_array('option', $this->DataType)) :
            $this->OptionMap = isset($attrs['option_map']) ? $attrs['option_map'] : $this->setOptionMap();
        endif;

        /// Données complémentaires
        if (in_array('misc', $this->DataType)) :
            $this->MiscMap = isset($attrs['misc_map']) ? $attrs['misc_map'] : $this->setMiscMap();
        endif;


        // Traitement de surcharge des attributs d'import
        $this->parseAttrs($attrs);
    }

    /**
     * Traitement des définitions de données
     */
    final public function _parseSettedDatas($inputdata = [])
    {
        $this->InputDatas = !empty($inputdata) ? $inputdata : $this->setInputDatas();
        $this->InputDatas = $this->parseInputDatas($this->InputDatas);

        // Traitement des données d'entrée principales
        if (in_array('data', $this->DataType)) :
            $data = [];

            /// Données cartographiées
            if ($this->DataMap) :
                foreach ($this->DataMap as $key => $map) :
                    if (is_numeric($key)) :
                        $key = $map;
                    endif;

                    // Bypass
                    if (!empty($this->AllowedDataMap) && !in_array($key, $this->AllowedDataMap)) :
                        continue;
                    endif;

                    if (isset($this->InputDatas[$map])) :
                        $data[$key] = $this->InputDatas[$map];
                    elseif (method_exists($this, 'set_data_' . $key)) :
                        $data[$key] = call_user_func(array($this, 'set_data_' . $key));
                    else :
                        $data[$key] = call_user_func(array($this, 'set_datas'), $key);
                    endif;
                endforeach;

            /// Données non cartographiées
            else :
                foreach ($this->InputDatas as $key => $value) :
                    // Bypass
                    if (!empty($this->AllowedDataMap) && !in_array($key, $this->AllowedDataMap)) :
                        continue;
                    endif;
                    $data[$key] = $value;
                endforeach;

                if ($matches = preg_grep('/^set_data_(.*)/', get_class_methods($this))) :
                    foreach ($matches as $method) :
                        $key = preg_replace('/^set_data_/', '', $method);
                        // Bypass
                        if (!empty($this->AllowedDataMap) && !in_array($key, $this->AllowedDataMap)) :
                            continue;
                        elseif (isset($data[$key])) :
                            continue;
                        endif;
                        $data[$key] = call_user_func(array($this, 'set_data_' . $key));
                    endforeach;
                endif;
            endif;

            $this->Datas = $data;
        endif;

        // Traitement des metadonnées
        if (in_array('metadata', $this->DataType)) :
            $metadata = [];

            /// Données cartographiées
            if ($this->MetadataMap) :
                foreach ($this->MetadataMap as $key => $map) :
                    if (is_numeric($key)) :
                        $key = $map;
                    endif;

                    if (isset($this->InputDatas[$map])) :
                        $metadata[$key] = $this->InputDatas[$map];
                    elseif (method_exists($this, 'set_meta_' . $key)) :
                        $metadata[$key] = call_user_func(array($this, 'set_meta_' . $key));
                    else :
                        $metadata[$key] = call_user_func(array($this, 'set_metas'), $key);
                    endif;
                endforeach;
            else :
                if ($matches = preg_grep('/^set_meta_(.*)/', get_class_methods($this))) :
                    foreach ($matches as $method) :
                        $key = preg_replace('/^set_meta_/', '', $method);
                        $metadata[$key] = call_user_func(array($this, 'set_meta_' . $key));
                    endforeach;
                endif;
            endif;

            $this->MetaDatas = $metadata;
        endif;

        // Traitement des metadonnées
        if (in_array('taxonomy', $this->DataType)) :
            $taxonomy = [];

            /// Données cartographiées
            if ($this->TaxonomyMap) :
                foreach ($this->TaxonomyMap as $key => $map) :
                    if (is_numeric($key)) :
                        $key = $map;
                    endif;

                    if (isset($this->InputDatas[$map])) :
                        $taxonomy[$key] = $this->InputDatas[$map];
                    elseif (method_exists($this, 'set_tax_' . $key)) :
                        $taxonomy[$key] = call_user_func(array($this, 'set_tax_' . $key));
                    else :
                        $taxonomy[$key] = call_user_func(array($this, 'set_taxs'), $key);
                    endif;
                endforeach;
            else :
                if ($matches = preg_grep('/^set_tax_(.*)/', get_class_methods($this))) :
                    foreach ($matches as $method) :
                        $key = preg_replace('/^set_tax_/', '', $method);
                        $taxonomy[$key] = call_user_func(array($this, 'set_tax_' . $key));
                    endforeach;
                endif;
            endif;

            $this->TaxDatas = $taxonomy;
        endif;

        // Traitement des options
        if (in_array('option', $this->DataType)) :
            $option = [];

            /// Données cartographiées
            if ($this->OptionMap) :
                foreach ($this->OptionMap as $key => $map) :
                    if (is_numeric($key)) :
                        $key = $map;
                    endif;

                    if (isset($this->InputDatas[$map])) :
                        $option[$key] = $this->InputDatas[$map];
                    elseif (method_exists($this, 'set_option_' . $key)) :
                        $option[$key] = call_user_func(array($this, 'set_option_' . $key));
                    else :
                        $option[$key] = call_user_func(array($this, 'set_options'), $key);
                    endif;
                endforeach;
            else :
                if ($matches = preg_grep('/^set_option_(.*)/', get_class_methods($this))) :
                    foreach ($matches as $method) :
                        $key = preg_replace('/^set_option_/', '', $method);
                        $option[$key] = call_user_func(array($this, 'set_option_' . $key));
                    endforeach;
                endif;
            endif;

            $this->OptDatas = $option;
        endif;

        // Traitement des données complémentaires
        if (in_array('misc', $this->DataType)) :
            $misc = [];

            /// Données cartographiées
            if ($this->MiscMap) :
                foreach ($this->MiscMap as $key => $map) :
                    if (is_numeric($key)) :
                        $key = $map;
                    endif;

                    if (isset($this->InputDatas[$map])) :
                        $option[$key] = $this->InputDatas[$map];
                    elseif (method_exists($this, 'set_misc_' . $key)) :
                        $option[$key] = call_user_func(array($this, 'set_misc_' . $key));
                    else :
                        $option[$key] = call_user_func(array($this, 'set_miscs'), $key);
                    endif;
                endforeach;
            else :
                if ($matches = preg_grep('/^set_misc_(.*)/', get_class_methods($this))) :
                    foreach ($matches as $method) :
                        $key = preg_replace('/^set_misc_/', '', $method);
                        $option[$key] = call_user_func(array($this, 'set_misc_' . $key));
                    endforeach;
                endif;
            endif;

            $this->MiscDatas = $misc;
        endif;

        $this->SettedDatas = compact($this->DataType);
    }

    /**
     * Filtrage de valeur des données principales
     */
    final public function _filterDataValues()
    {
        if (isset($this->SettedDatas['data'])) :
            $data = (array)$this->SettedDatas['data'];
        else :
            return;
        endif;

        foreach ($data as $key => &$value) :
            if (method_exists($this, 'filter_data_' . $key)) :
                $value = call_user_func(array($this, 'filter_data_' . $key), $value);
            else :
                $value = call_user_func(array($this, 'filter_datas'), $value, $key);
            endif;
        endforeach;

        $this->Datas = $this->FilteredDatas['data'] = $data;
    }

    /**
     * Filtrage de valeur des metadonnées
     */
    final public function _filterMetaValues()
    {
        if (isset($this->SettedDatas['metadata'])) :
            $metadatas = (array)$this->SettedDatas['metadata'];
        else :
            return;
        endif;

        $insert_id = $this->getInsertId();

        foreach ($metadatas as $key => &$value) :
            if (method_exists($this, 'filter_meta_' . $key)) :
                $value = call_user_func(array($this, 'filter_meta_' . $key), $value, $insert_id);
            else :
                $value = call_user_func(array($this, 'filter_metas'), $value, $key, $insert_id);
            endif;

        endforeach;

        $this->MetaDatas = $this->FilteredDatas['metadata'] = $metadatas;
    }

    /**
     * Filtrage de valeur des taxonomies
     */
    final public function _filterTaxValues()
    {
        if (isset($this->SettedDatas['taxonomy'])) :
            $taxonomies = (array)$this->SettedDatas['taxonomy'];
        else :
            return;
        endif;

        $insert_id = $this->getInsertId();

        foreach ($taxonomies as $key => &$value) :
            if (method_exists($this, 'filter_tax_' . $key)) :
                $value = call_user_func(array($this, 'filter_tax_' . $key), $value, $insert_id);
            else :
                $value = call_user_func(array($this, 'filter_taxs'), $value, $key, $insert_id);
            endif;
        endforeach;

        $this->TaxDatas = $this->FilteredDatas['taxonomy'] = $taxonomies;
    }

    /**
     * Filtrage de valeur des options
     */
    final public function _filterOptionValues()
    {
        if (isset($this->SettedDatas['option'])) :
            $options = (array)$this->SettedDatas['option'];
        else :
            return;
        endif;

        $insert_id = $this->getInsertId();

        foreach ($options as $key => &$value) :
            if (method_exists($this, 'filter_option_' . $key)) :
                $value = call_user_func(array($this, 'filter_option_' . $key), $value, $insert_id);
            else :
                $value = call_user_func(array($this, 'filter_options'), $value, $key, $insert_id);
            endif;
        endforeach;

        $this->OptDatas = $this->FilteredDatas['option'] = $options;
    }

    /**
     * Vérification de valeur des données principales
     */
    final public function _checkDataValues()
    {
        if (!$data = $this->getDatas()) {
            return;
        }

        foreach ($data as $key => $value) :
            if (method_exists($this, 'check_data_' . $key)) :
                call_user_func(array($this, 'check_data_' . $key), $value);
            else :
                call_user_func(array($this, 'check_datas'), $value, $key);
            endif;
        endforeach;
    }

    /**
     * Vérification de valeur des metadonnées
     */
    final public function _checkMetaValues()
    {
        if (!$metadatas = $this->getMetaDatas()) {
            return;
        }

        $insert_id = $this->getInsertId();

        foreach ($metadatas as $key => &$value) :
            if (method_exists($this, 'check_meta_' . $key)) :
                call_user_func(array($this, 'check_meta_' . $key), $value, $insert_id);
            else :
                call_user_func(array($this, 'check_metas'), $value, $key, $insert_id);
            endif;
        endforeach;
    }

    /**
     * Vérification de valeur des metadonnées
     */
    final public function _checkTaxValues()
    {
        if (!$taxonomies = $this->getTaxDatas()) {
            return;
        }

        $insert_id = $this->getInsertId();

        foreach ($taxonomies as $key => &$value) :
            if (method_exists($this, 'check_tax_' . $key)) :
                call_user_func(array($this, 'check_tax_' . $key), $value, $insert_id);
            else :
                call_user_func(array($this, 'check_taxs'), $value, $key, $insert_id);
            endif;
        endforeach;
    }

    /**
     * Vérification de valeur d'options
     */
    final public function _checkOptionValues()
    {
        if (!$options = $this->getOptDatas()) {
            return;
        }

        $insert_id = $this->getInsertId();

        foreach ($options as $key => &$value) :
            if (method_exists($this, 'check_option_' . $key)) :
                call_user_func(array($this, 'check_option_' . $key), $value, $insert_id);
            else :
                call_user_func(array($this, 'check_options'), $value, $key, $insert_id);
            endif;
        endforeach;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Vérification d'existance erreurs de traitement
     */
    final public function hasError()
    {
        return !empty($this->Errors);
    }

    /**
     * Récupération des erreurs de traitement
     */
    final public function getErrors()
    {
        $WP_Error = $this->Errors ? new \WP_Error : '';
        foreach ($this->Errors as $code => $message) :
            $WP_Error->add($code, $message);
        endforeach;

        return $WP_Error;
    }

    /**
     * Ajout d'une erreur de traitement
     */
    final public function addError($code = null, $message = '')
    {
        if (!$code && !$message) {
            return;
        }
        if ($code) :
            $this->Errors[$code] = $message;
        else :
            $this->Errors[] = $message;
        endif;
    }

    /**
     * Récupération de la valeur de clé primaire du contenu enregistré
     */
    final public function getInsertId()
    {
        return $this->InsertID;
    }

    /**
     * Récupération des données principales à insérer
     */
    final public function getDatas()
    {
        if (in_array('data', $this->DataType) && isset($this->Datas)) {
            return $this->Datas;
        }
    }

    /**
     * Récupération de la valeur d'une donnée principale à insérer
     */
    final public function getData($key)
    {
        if (in_array('data', $this->DataType) && isset($this->Datas[$key])) {
            return $this->Datas[$key];
        }
    }

    /**
     * Récupération des metadonnées à insérer
     */
    final public function getMetaDatas()
    {
        if (in_array('metadata', $this->DataType) && isset($this->MetaDatas)) {
            return $this->MetaDatas;
        }
    }

    /**
     * Récupération de la valeur d'une métadonnée à insérer
     */
    final public function getMetaData($meta_key)
    {
        if (in_array('metadata', $this->DataType) && isset($this->MetaDatas[$meta_key])) {
            return $this->MetaDatas[$meta_key];
        }
    }

    /**
     * Récupération des taxonomy à insérer
     */
    final public function getTaxDatas()
    {
        if (in_array('taxonomy', $this->DataType) && isset($this->TaxDatas)) {
            return $this->TaxDatas;
        }
    }

    /**
     * Récupération des termes d'une taxonomy à insérer
     */
    final public function getTaxData($taxonomy)
    {
        if (in_array('taxonomy', $this->DataType) && isset($this->TaxDatas[$taxonomy])) {
            return $this->TaxDatas[$taxonomy];
        }
    }

    /**
     * Récupération des options à insérer
     */
    final public function getOptDatas()
    {
        if (in_array('option', $this->DataType) && isset($this->OptDatas)) {
            return $this->OptDatas;
        }
    }

    /**
     * Récupération de la valeur d'une métadonnée à insérer
     */
    final public function getOptData($option_name)
    {
        if (in_array('option', $this->DataType) && isset($this->OptDatas[$option_name])) {
            return $this->OptDatas[$option_name];
        }
    }

    /**
     * Récupération des données complémentaires
     */
    final public function getMiscDatas()
    {
        if (in_array('misc', $this->DataType) && isset($this->MiscDatas)) {
            return $this->MiscDatas;
        }
    }

    /**
     * Récupération de la valeur d'une donnée complémentaire
     */
    final public function getMiscData($misc_name)
    {
        if (in_array('misc', $this->DataType) && isset($this->MiscDatas[$misc_name])) {
            return $this->MiscDatas[$misc_name];
        }
    }

    /**
     * SURCHARGE
     */
    /**
     * Traitement des attributs d'import
     */
    public function parseAttrs($attrs = [])
    {
        return;
    }

    /**
     * Définition des données d'entrée
     */
    public function setInputDatas()
    {
        return [];
    }

    /**
     * Traitement des données d'entrée
     */
    public function parseInputDatas($datas)
    {
        return $datas;
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
    public function setMetadataMap()
    {
        return [];
    }

    /**
     * Définition de la cartographie des taxonomy
     */
    public function setTaxonomyMap()
    {
        return [];
    }

    /**
     * Définition de la cartographie des options
     */
    public function setOptionMap()
    {
        return [];
    }

    /**
     * Définition de la cartographie des données complémentaires
     */
    public function setMiscMap()
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
    public function set_options($option_name)
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
    public function filter_options($option_value, $option_name, $insert_id)
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
    public function check_options($option_value, $option_name, $insert_id)
    {
        return;
    }

    /**
     * Evénement pré-insertion de l'ensemble des éléments (metadonnées|termes de taxonomies|options)
     */
    public function before_insert()
    {
    }

    /**
     * Insertion des données principales
     */
    public function insert_datas($datas)
    {
        return new \WP_Error('tiFyInheritsImport_NoDataInsertCb',
            __('Méthode d\'enregistrement des données d\'import non définie', 'tify'));
    }

    /**
     * Evénement post-insertion des données principales
     */
    public function after_insert_datas($insert_id)
    {
    }

    /**
     * Insertion d'une métadonnée
     */
    public function insert_meta($insert_id, $meta_key, $meta_value)
    {
        return new \WP_Error('tiFyInheritsImport_NoMetaDataInsertCb',
            __('Méthode d\'enregistrement des metadonnées d\'import non définie', 'tify'));
    }

    /**
     * Evénement post-insertion des metadonnées
     */
    public function after_insert_metadatas($insert_id)
    {
    }

    /**
     * Insertion des termes d'une taxonomie
     */
    public function insert_taxonomy_terms($insert_id, $terms, $taxonomy)
    {
        return new \WP_Error('tiFyInheritsImport_NoTaxonomyTermsInsertCb',
            __('Méthode d\'enregistrement des termes de taxonomie d\'import non définie', 'tify'));
    }

    /**
     * Evénement post-insertion des termes de taxonomies
     */
    public function after_insert_terms($insert_id)
    {
    }

    /**
     * Insertion d'une option
     */
    public function insert_option($insert_id, $option_name, $option_value)
    {
        return new \WP_Error('tiFyInheritsImport_NoOptionInsertCb',
            __('Méthode d\'enregistrement des options d\'import non définie', 'tify'));
    }

    /**
     * Evénement post-insertion des options
     */
    public function after_insert_options($insert_id)
    {
    }

    /**
     * Evénement post-insertion de l'ensemble des éléments (metadonnées|termes de taxonomies|options)
     */
    public function after_insert($insert_id)
    {
    }
}