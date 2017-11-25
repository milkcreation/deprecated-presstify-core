<?php 
namespace tiFy\Core\Ui\Admin\Templates\ListTable\Traits;

use tiFy\Core\Ui\Ui;

trait Params
{
    /**
     * Définition de l'url d'édition d'un élément
     *
     * @param string $edit_base_uri Url d'édition d'un élément définie en paramètre
     *
     * @return string
     */
    public function set_param_edit_base_uri($edit_base_uri = '')
    {
        return $edit_base_uri;
    }

    /**
     * Définition de la liste des colonnes de la table
     *
     * @param array $columns Liste des colonnes définies en paramètre
     *
     * @return array Tableau indexé ou Tableau associatif (recommandé) où la clé d'index correspond à l'identifiant de la colonne et la valeur à l'intitulé
     */
    public function set_param_columns($columns = [])
    {
        return $columns;
    }

    /**
     * Définition de la colonne principale de la table (utilisé pour brancher les actions sur un élément ...)
     *
     * @param string $primary_column Identifiant de la colonne principale définie en paramètre
     *
     * @return string Identifiant de la colonne
     */
    public function set_param_primary_column($primary_column = '')
    {
        return $primary_column;
    }

    /**
     * Définition de la listes des colonnes de la table pouvant être ordonnées
     *
     * @param array $sortable_columns Liste des colonnes pouvant être ordonnées définies en paramètre
     *
     * @return array
     */
    public function set_param_sortable_columns($sortable_columns = [])
    {
        return $sortable_columns;
    }

    /**
     * Définition de la liste des colonnes la table pouvant être masquées
     *
     * @param array $hidden_columns Liste des colonnes pouvant être masquées définies en paramètre
     *
     * @return array
     */
    public function set_param_hidden_columns($hidden_columns = [])
    {
        return $hidden_columns;
    }

    /**
     * Définition de la liste des classes CSS de la balise table
     *
     * @param array $table_classes Liste des classes CSS de la balise table définies en paramètre
     *
     * @return array
     */
    public function set_param_table_classes($table_classes = [])
    {
        return $table_classes;
    }


    /**
     * Définition du nombre d'éléments à afficher par page
     *
     * @param int $per_page Nombre d'éléments à afficher par page définies en paramètre
     *
     * @return int
     */
    public function set_param_per_page($per_page = 20)
    {
        return $per_page;
    }

    /**
     * Définition du nom d'enregistrement de l'option du nombre d'éléments par page en base de données
     *
     * @param string $per_page_option_name Nom d'enregistrement de l'option du nombre d'éléments par page en base de données défini en paramètre
     *
     * @return string
     */
    public function set_param_per_page_option_name($per_page_option_name = '')
    {
        return $per_page_option_name;
    }

    /**
     * Définition de la liste des vues filtrées
     *
     * @param array $views Liste des vues filtrées définies en paramètre
     *
     * @return array
     */
    public function set_param_views($views = [])
    {
        return $views;
    }

    /**
     * Définition de la liste des actions groupées
     *
     * @param array $bulk_actions Liste des actions groupées définies en paramètre
     *
     * @return array
     */
    public function set_param_bulk_actions($bulk_actions = [])
    {
        return $bulk_actions;
    }

    /**
     * Définition de la liste des actions sur un élément
     *
     * @param array $row_actions Liste des actions sur un élément définies en paramètre
     *
     * @return array
     */
    public function set_param_row_actions($row_actions = [])
    {
        return $row_actions;
    }

    /**
     * Définition de la liste du type d'affichage des actions sur un élément
     *
     * @param bool $row_actions_always_visible Liste des actions sur un élément définies en paramètre
     *
     * @return array
     */
    public function set_param_row_actions_always_visible($row_actions_always_visible = false)
    {
        return $row_actions_always_visible;
    }

    /**
     * Définition de l'activation d'ajout automatique des actions sur un élément dans la colonne principale
     *
     * @param bool $handle_row_actions Activation d'ajout automatique des actions sur un élément définie en paramètre
     *
     * @return bool
     */
    public function set_param_handle_row_actions($handle_row_actions = true)
    {
        return $handle_row_actions;
    }

    /**
     * Définition de l'intitulé lorque la table est vide
     *
     * @param string $no_items Intitulé lorque la table est vide défini en paramètre
     *
     * @return string
     */
    public function set_param_no_items($no_items = '')
    {
        return $no_items;
    }

    /**
     * Définition des colonnes affichées lors de la prévisualisation des données d'un élément
     *
     * @param array $preview_item_columns Liste des acolonnes affichées lors de la prévisualisation des données d'un élément définies en paramètre
     *
     * @return array
     */
    public function set_param_preview_item_columns($preview_item_columns = [])
    {
        return $preview_item_columns;
    }

    /**
     * Définition du mode d'affichage de la prévisualisation d'un élément
     *
     * @param string $preview_item_mode Mode d'affichage de la prévisualisation d'un élément défini en paramètre (dialog|inline)
     *
     * @return string
     */
    public function set_param_preview_item_mode($preview_item_mode = 'dialog')
    {
        return $preview_item_mode;
    }

    /**
     * Définition de la liste des variables passées en argument dans la requête ajax de prévisualisation d'un élément
     *
     * @param array $preview_item_ajax_args Liste des variables passées en argument dans la requête ajax de prévisualisation d'un élément définies en paramètre
     *
     * @return array
     */
    public function set_param_preview_item_ajax_args($preview_item_ajax_args = [])
    {
        return $preview_item_ajax_args;
    }

    /**
     * Initialisation de l'url d'édition d'un élément
     *
     * @param string $edit_base_uri Url d'édition d'un élément existante
     *
     * @return string
     */
    public function init_param_edit_base_uri($edit_base_uri = '')
    {
        if (!$edit_base_uri) :
            if($edit = $this->getHandle('edit')) :
                $edit_base_uri = $edit->getAttr('base_uri', '');
            endif;
        endif;

        return $edit_base_uri;
    }

    /**
     * Initialisation de la liste des colonnes de la table
     *
     * @param array $columns Liste des colonnes existante
     *
     * @return array Tableau associatif où la clé d'index correspond à l'identifiant de la colonne et la valeur à l'intitulé
     */
    public function init_param_columns($columns = [])
    {
        if ($columns) :
            $_columns = [];
            foreach ($columns as $name => $label) :
                if (is_int($name)) :
                    $name = $label;
                endif;
                $_columns[$name] = $label;
            endforeach;

            $columns = $_columns;
        elseif (($db = $this->getDb()) && ($column_names = $db->ColNames )) :
            $columns['cb'] = "<input id=\"cb-select-all-1\" type=\"checkbox\" />";
            foreach ($column_names as $column_name) :
                $columns[$column_name] = $column_name;
            endforeach;
        endif;

        return $columns;
    }

    /**
     * Initialisation de la colonne principale de la table (utilisé pour brancher les actions sur un élément ...)
     *
     * @param string $primary_column Identifiant de la colonne principale existante
     *
     * @return string Identifiant de la colonne
     */
    public function init_param_primary_column($primary_column = '')
    {
        if ($primary_column) :
            \add_filter('list_table_primary_column', function($default) use ($primary_column){ return $primary_column; }, 10, 1);
        endif;

        return $primary_column;
    }

    /**
     * Initialisation de la liste des colonnes la table pouvant être masquées
     *
     * @param array $hidden_columns Liste des colonnes la table pouvant être masquées existantes
     *
     * @return string
     */
    public function init_param_hidden_columns($hidden_columns = [])
    {
        if ($hidden_columns) :
            \add_filter('hidden_columns', function($hidden, $screen, $use_defaults) use ($hidden_columns){ return $hidden_columns; }, 10, 3);
        endif;

        return $hidden_columns;
    }

    /**
     * Initialisation de la liste des classes CSS de la balise table
     *
     * @param array $table_classes Liste des classes CSS de la balise table existantes
     *
     * @return array
     */
    public function init_param_table_classes($table_classes = [])
    {
        if (!$table_classes) :
            return ['widefat', 'fixed', 'striped', $this->getParam('plural')];
        endif;

        return $table_classes;
    }

    /**
     * Initialisation de l'intitulé lorque la table est vide
     *
     * @param string $no_items Intitulé lorque la table est vide existant
     *
     * @return string
     */
    public function init_param_no_items($no_items = '')
    {
        if (!$no_items) :
            $no_items = $this->getLabel('not_found', '');
        endif;

        return $no_items;
    }

    /**
     * Initialisation de l'intitulé lorque la table est vide
     *
     * @param string $per_page_option_name Intitulé lorque la table est vide existant
     *
     * @return string
     */
    public function init_param_per_page_option_name($per_page_option_name = '')
    {
        if (!$per_page_option_name) :
            $per_page_option_name = '_per_page_' . $this->getId();
        endif;
        $per_page_option_name = sanitize_key($per_page_option_name);

        \add_filter('set-screen-option', function($none, $option, $value) use ($per_page_option_name){ return ($per_page_option_name === $option) ? $value : $none; }, 10, 3);

        $per_page = $this->get_items_per_page($per_page_option_name, $this->getParam('per_page'));
        \add_filter($per_page_option_name, function() use ($per_page){ return $per_page; }, 0);

        return $per_page_option_name;
    }

    /**
     * Initialisation de la liste des vues filtrées.
     *
     * @param array $views Liste des vues filtrées existantes
     *
     * @return array
     */
    public function init_param_views($views = [])
    {
        if ($views) :
            $_views = [];
            foreach($views as $id => $attrs) :
                if(is_int($id)) :
                    if ($link = $this->parseView($attrs, [])) :
                        $_views[$attrs] = $link;
                    endif;
                elseif(is_array($attrs)) :
                    if ($link = $this->parseView($attrs, [])) :
                        $_views[$id] = $link;
                    endif;
                elseif(is_string($attrs)) :
                    $_views[$id] = $attrs;
                endif;
            endforeach;

            $views = $_views;
        endif;

        return $views;
    }

    /**
     * Initialisation de la liste des actions groupées
     *
     * @param array $bulk_actions Liste des actions groupées existantes
     *
     * @return array
     */
    public function init_param_bulk_actions($bulk_actions = [])
    {
        if ($bulk_actions) :
            $_bulk_actions = [];
            foreach ($bulk_actions as $action => $attr ) :
                if (is_int($action)) :
                    $_bulk_actions[$attr] = $attr;
                else :
                    $_bulk_actions[$action] = $attr;
                endif;
            endforeach;

            $bulk_actions = $_bulk_actions;
        endif;

        return $bulk_actions;
    }

    /**
     * Initialisation de la liste des actions sur un élément
     *
     * @param array $row_actions Liste des actions sur un élément existantes
     *
     * @return array
     */
    public function init_param_row_actions($row_actions = [])
    {
        if ($row_actions) :
            $_row_actions = [];
            foreach ($row_actions as $action => $attr ) :
                if (is_int($action)) :
                    $_row_actions[$attr] = [];
                else :
                    $_row_actions[$action] = $attr;
                endif;
            endforeach;

            $row_actions = $_row_actions;
        endif;

        return $row_actions;
    }

    /**
     * Initialisation de la liste des variables passées en argument dans la requête ajax de prévisualisation d'un élément
     *
     * @param array $preview_item_ajax_args Liste des variables passées en argument dans la requête ajax de prévisualisation d'un élément existantes
     *
     * @return array
     */
    public function init_param_preview_ajax_datas($preview_item_ajax_args = [])
    {
        return $preview_item_ajax_args;
    }
}