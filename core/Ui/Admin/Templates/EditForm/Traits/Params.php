<?php 
namespace tiFy\Core\Ui\Admin\Templates\EditForm\Traits;

trait Params
{
    /**
     * Définition de la liste des champs d'édition
     *
     * @param array $fields Liste des champs d'édition définis en paramètre
     *
     * @return array
     */
    public function set_param_fields($fields = [])
    {
        return $fields;
    }

    /**
     * Définition de l'autorisation de création automatique d'un nouvel élément
     *
     * @param bool $fields Autorisation de création automatique d'un nouvel élément défini en paramètre
     *
     * @return bool
     */
    public function set_param_create_new_item($create_new_item = true)
    {
        return $create_new_item;
    }

    /**
     * Définition des attributs par défaut de l'élément
     *
     * @param array $item_defaults Attributs par défaut de l'élément défini en paramètre
     *
     * @return array
     */
    public function set_param_item_defaults($item_defaults = [])
    {
        return $item_defaults;
    }

    /**
     * Définition de l'url de la page d'affichage du gabarit liste des éléments
     *
     * @param string $list_base_url Url de la page d'affichage du gabarit liste des éléments défini en paramètre
     *
     * @return string
     */
    /** == Définition de l'url d'affichage de la liste des éléments == **/
    public function set_param_list_base_url($list_base_url = '')
    {
        return $list_base_url;
    }

    /**
     * Initialisation de la liste des champs d'édition
     *
     * @param array $fields Liste des champs d'édition existante
     *
     * @return array Tableau associatif où la clé d'index correspond à l'identifiant de la colonne et la valeur à l'intitulé
     */
    public function init_param_fields($fields = [])
    {
        if ($fields) :
            $_fields = [];
            foreach ($fields as $name => $label) :
                if (is_int($name)) :
                    $name = $label;
                endif;
                $_fields[$name] = $label;
            endforeach;

            $fields = $_fields;
        elseif (($db = $this->getDb()) && ($column_names = $db->ColNames)) :
            foreach ($column_names as $column_name) :
                $fields[$column_name] = $column_name;
            endforeach;
        endif;

        return $fields;
    }
}