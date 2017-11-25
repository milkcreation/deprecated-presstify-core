<?php
namespace tiFy\Core\Ui\Admin\Templates\PostListTable;

class PostListTable extends \tiFy\Core\Ui\Admin\Templates\ListTable\ListTable
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($id, $attrs)
    {
        parent::__construct($id, $attrs);

        // Définition de la liste des paramètres par défaut
        $this->setDefaultParam(
            'columns',
            [
                'cb'              => $this->header_cb(),
                'post_title'      => __('Titre', 'tify'),
                'post_type'       => __('Type de post', 'tify'),
                'post_date'       => __('Date', 'tify')
            ]
        );
        $this->setDefaultParam(
            'views',
            ['all', 'publish', 'trash']
        );
        if ($this->getRequestQueryVar('status') !== 'trash') :
            $this->setDefaultParam(
                'bulk_actions',
                ['trash']
            );
        else :
            $this->setDefaultParam(
                'bulk_actions',
                ['untrash', 'delete']
            );
        endif;
        if ($this->getRequestQueryVar('status') !== 'trash') :
            $this->setDefaultParam(
                'row_actions',
                ['edit', 'trash']
            );
        else :
            $this->setDefaultParam(
                'row_actions',
                ['untrash', 'delete']
            );
        endif;
        $this->setDefaultParam(
            'query_args',
            ['status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit']]
        );
    }

    /**
     * Récupération de la liste des attributs de configuration du lien vers la vue filtrée affichant tous les éléments
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien de vue filtrée (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title de la balise du lien de vue filtrée
     *      @var string $class Classes CSS de l'attribut class de la balise du lien de vue filtrée
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien de vue filtrée
     *      @var string $href Url de l'attribut href de la balise du lien de vue filtrée
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien de vue filtrée
     *      @var array $remove_query_args Tableau indexé des arguments supprimés de l'url de requête du lien de vue filtrée
     *      @var int $count_items Nombre d'élément correspondant à la vue filtrée
     *      @var bool $current Définie si la vue courante correspond à la vue filtrée
     *      @var bool $hide_empty Masque le lien si aucun élément ne correspond à la vue filtrée
     *      @var bool|string $show_count Affiche le nombre d'éléments correspondant dans le lien de la vue filtrée false|true|'(%d)' où %d correspond au nombre d'éléments
     * }
     *
     * @return array
     */
    public function get_view_attrs_all($attrs = [])
    {
        if ($db = $this->getDb()) :
            $count = $db->select()->count(['status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit']]);
        endif;

        $defaults = [
            'content'     => __('Tous', 'tify'),
            'count_items' => $count,
            'show_count'  => true,
            'current'     => !$this->getRequestQueryVar('status', '')
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration du lien vers la vue filtrée affichant les éléments publiés
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien de vue filtrée (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title de la balise du lien de vue filtrée
     *      @var string $class Classes CSS de l'attribut class de la balise du lien de vue filtrée
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien de vue filtrée
     *      @var string $href Url de l'attribut href de la balise du lien de vue filtrée
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien de vue filtrée
     *      @var array $remove_query_args Tableau indexé des arguments supprimés de l'url de requête du lien de vue filtrée
     *      @var int $count_items Nombre d'élément correspondant à la vue filtrée
     *      @var bool $current Définie si la vue courante correspond à la vue filtrée
     *      @var bool $hide_empty Masque le lien si aucun élément ne correspond à la vue filtrée
     *      @var bool|string $show_count Affiche le nombre d'éléments correspondant dans le lien de la vue filtrée false|true|'(%d)' où %d correspond au nombre d'éléments
     * }
     *
     * @return array
     */
    public function get_view_attrs_publish($attrs = [])
    {
        if ($db = $this->getDb()) :
            $count = $db->select()->count(['status' => 'publish']);
        endif;

        $defaults = [
            'content'     => _n('Publié', 'Publiés', ($count > 1 ? 2 : 1), 'tify'),
            'count_items' => $count,
            'show_count'  => true,
            'query_args'  => ['status' => 'publish'],
            'current'     => $this->getRequestQueryVar('status', '') === 'publish'
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Récupération de la liste des attributs de configuration du lien vers la vue filtrée affichant les éléments à la corbeille
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du lien
     *
     *      @var string $content Contenu du lien de vue filtrée (chaîne de caractère ou éléments HTML)
     *      @var string $title Intitulé de l'attribut title de la balise du lien de vue filtrée
     *      @var string $class Classes CSS de l'attribut class de la balise du lien de vue filtrée
     *      @var array $attrs Liste des attributs complémentaires de la balise du lien de vue filtrée
     *      @var string $href Url de l'attribut href de la balise du lien de vue filtrée
     *      @var array $query_args Tableau associatif des arguments passés en requête dans l'url du lien de vue filtrée
     *      @var array $remove_query_args Tableau indexé des arguments supprimés de l'url de requête du lien de vue filtrée
     *      @var int $count_items Nombre d'élément correspondant à la vue filtrée
     *      @var bool $current Définie si la vue courante correspond à la vue filtrée
     *      @var bool $hide_empty Masque le lien si aucun élément ne correspond à la vue filtrée
     *      @var bool|string $show_count Affiche le nombre d'éléments correspondant dans le lien de la vue filtrée false|true|'(%d)' où %d correspond au nombre d'éléments
     * }
     *
     * @return array
     */
    public function get_view_attrs_trash($attrs = [])
    {
        if ($db = $this->getDb()) :
            $count = $db->select()->count(['status' => 'trash']);
        endif;

        $defaults = [
            'content'     => __('Corbeille', 'tify'),
            'count_items' => $count,
            'hide_empty'  => true,
            'show_count'  => true,
            'query_args'  => ['status' => 'trash'],
            'current'     => $this->getRequestQueryVar('status', '') === 'trash'
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Contenu de la colonne - Titre
     * @see \WP_List_Table::column_cb()
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function column_post_title($item)
    {
        return "<strong>{$item->post_title}</strong>";
    }
}