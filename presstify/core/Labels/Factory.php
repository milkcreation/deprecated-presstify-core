<?php
namespace tiFy\Core\Labels;

class Factory
{
    /**
     * Identifiant de qualification
     * @var string
     */
    protected $Id = null;

    /**
     * Tableau associatif de la liste des intitulés
     * @var array
     */
    protected $Labels = [];

    /**
     * @var string|void
     */
    protected $Plural = '';

    /**
     * @var string|void
     */
    protected $Singular = '';

    /**
     * @var bool
     */
    protected $Gender = false;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $labels Liste des attributs de configuration
     *
     * @return void
     */
    public function __construct($id, $labels = [])
    {
        // Rétrocompatibilité
        if(is_array($id) && empty($labels)) :
            $labels = $id;
            $id = md5(json_encode($labels));
        endif;

        // Arguments par défaut
        $this->Plural = __('éléments', 'tify');
        $this->Singular = __('élément', 'tify');

        // Pré-traitement des intitulés
        $this->Labels = $this->parse($labels);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Pré-traitement des intitulés
     *
     * @param array $labels
     *
     * @return array
     */
    final protected function parse($labels = [])
    {
        // Définition des arguments
        foreach (['plural', 'singular', 'gender'] as $attr) :
            if (isset($labels[$attr])) :
                $var = self::ucFirst($attr);
                $this->{$var} = $labels[$attr];
            endif;
        endforeach;

        // Traitement des arguments par défaut
        $defaults = [
            'singular'                   => $this->Singular,
            'plural'                     => $this->Plural,
            'name'                       => self::ucFirst($this->Plural),
            'singular_name'              => $this->Singular,
            'menu_name'                  => _x(self::ucFirst($this->Plural), 'admin menu', 'tify'),
            'name_admin_bar'             => _x($this->Singular, 'add new on admin bar', 'tify'),
            'add_new'                    => !$this->Gender ? __(sprintf('Ajouter un %s', $this->Singular), 'tify') : __(sprintf('Ajouter une %s', $this->Singular), 'tify'),
            'add_new_item'               => !$this->Gender ? __(sprintf('Ajouter un %s', $this->Singular), 'tify') : __(sprintf('Ajouter une %s', $this->Singular), 'tify'),
            'new_item'                   => !$this->Gender ? __(sprintf('Créer un %s', $this->Singular), 'tify') : __(sprintf('Créer une %s', $this->Singular), 'tify'),
            'edit_item'                  => $this->default_edit_item(),
            'view_item'                  => !$this->Gender ? __(sprintf('Voir ce %s', $this->Singular), 'tify') : __(sprintf('Voir cette %s', $this->Singular), 'tify'),
            'all_items'                  => !$this->Gender ? __(sprintf('Tous les %s', $this->Plural), 'tify') : __(sprintf('Toutes les %s', $this->Plural), 'tify'),
            'search_items'               => !$this->Gender ? __(sprintf('Rechercher un %s', $this->Singular), 'tify') : __(sprintf('Rechercher une %s', $this->Singular), 'tify'),
            'parent_item_colon'          => !$this->Gender ? __(sprintf('%s parent', self::ucFirst($this->Singular)), 'tify') : __(sprintf('%s parente', self::ucFirst($this->Singular)), 'tify'),
            'not_found'                  => !$this->Gender ? __(sprintf('Aucun %s trouvé', $this->Singular), 'tify') : __(sprintf('Aucune %s trouvée', $this->Singular), 'tify'),
            'not_found_in_trash'         => !$this->Gender ? __(sprintf('Aucun %s dans la corbeille', $this->Singular), 'tify') : __(sprintf('Aucune %s dans la corbeille', $this->Singular), 'tify'),
            'update_item'                => !$this->Gender ? __(sprintf('Mettre à jour ce %s', $this->Singular), 'tify') : __(sprintf('Mettre à jour cette %s', $this->Singular), 'tify'),
            'new_item_name'              => !$this->Gender ? __(sprintf('Créer un %s', $this->Singular), 'tify') : __(sprintf('Créer une %s', $this->Singular), 'tify'),
            'popular_items'              => !$this->Gender ? __(sprintf('%s populaires', self::ucFirst($this->Plural)), 'tify') : __(sprintf('%s populaires', self::ucFirst($this->Plural)), 'tify'),
            'separate_items_with_commas' => !$this->Gender ? __(sprintf('Séparer les %s par une virgule', $this->Plural), 'tify') : __(sprintf('Séparer les %s par une virgule', $this->Plural), 'tify'),
            'add_or_remove_items'        => !$this->Gender ? __(sprintf('Ajouter ou supprimer des %s', $this->Plural), 'tify') : __(sprintf('Ajouter ou supprimer des %s', $this->Plural), 'tify'),
            'choose_from_most_used'      => !$this->Gender ? __(sprintf('Choisir parmi les %s les plus utilisés', $this->Plural), 'tify') : __(sprintf('Choisir parmi les %s les plus utilisées', $this->Plural), 'tify'),
            'datas_item'                 => $this->default_datas_item(),
            'import_items'               => __(sprintf('Importer des %s', $this->Plural), 'tify'),
            'export_items'               => __(sprintf('Export des %s', $this->Plural), 'tify')
        ];

        return \wp_parse_args($labels, $defaults);
    }

    /**
     * @return string
     */
    public function default_edit_item()
    {
        return sprintf(__('Éditer %s %s', 'tify'), $this->getDeterminant($this->Singular, $this->Gender), $this->Singular);
    }

    /**
     * @return string
     */
    public function default_datas_item()
    {
        if (self::isFirstVowel($this->Singular)) :
            $determinant = __('de l\'', 'tify');
        elseif ($this->Gender) :
            $determinant = __('de la', 'tify');
        else :
            $determinant = __('du', 'tify');
        endif;

        return sprintf(__('Données %s %s', 'tify'), $determinant, $this->Singular);
    }

    /**
     * @param $label
     * @param string $value
     */
    public function set($label, $value = '')
    {
        $this->Labels[$label] = $value;
    }

    /**
     * @param null $label
     * @param string $default
     *
     * @return array|mixed|string
     */
    public function get($label = null, $default = '')
    {
        if (!$label) :
            return $this->Labels;
        elseif (isset($this->Labels[$label])) :

            return $this->Labels[$label];
        endif;

        return $default;
    }

    /**
     * Mise en majuscule de la première lettre d'une chaîne de caractère, même si celle-ci contient un accent
     *
     * @param string $string Chaîne de caractère à traiter
     *
     * @return string
     */
    final public static function ucFirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_substr($string, 1);
    }

    /**
     * Permet de vérifier si la première lettre d'une chaîne de caractère est une voyelle
     *
     * @param string $string Chaîne de caractère à traiter
     *
     * @return string
     */
    final public static function isFirstVowel($string)
    {
        $first = strtolower(mb_substr(\remove_accents($string), 0, 1));

        return (in_array($first, ['a', 'e', 'i', 'o', 'u', 'y']));
    }

    /**
     * Récupération du déterminant de qualification d'une chaîne de caractère
     *
     * @param string $string Chaîne de caractère à traiter
     * @param bool $gender Genre de la chaîne de caractère à traiter (false : masculin, true : féminin)
     *
     * @return string
     */
    private function getDeterminant($string, $gender = false)
    {
        if (self::isFirstVowel($string)) :
            return __("l'", 'tify');
        else :
            return $gender ? __("la", 'tify') : __("le", 'tify');
        endif;
    }
}