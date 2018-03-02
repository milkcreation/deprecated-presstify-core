<?php
namespace tiFy\Core\Ui\Admin\Templates\UserListTable;

class UserListTable extends \tiFy\Core\Ui\Admin\Templates\ListTable\ListTable
{
    // Paramètres
    use Traits\Params;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($id = null, $attrs = [])
    {
        parent::__construct($id, $attrs);



        // Définition de la liste des paramètres autorisés
        $this->setAllowedParamList(['roles']);

        // Définition de la liste des paramètres par défaut
        $this->setDefaultParam(
            'columns',
            [
                'cb'              => $this->header_cb(),
                'user_login'      => __('Identifiant', 'tify'),
                'display_name'    => __('Nom', 'tify'),
                'user_email'      => __('E-mail', 'tify'),
                'user_registered' => __('Enregistrement', 'tify'),
                'role'            => __('Rôle', 'tify')
            ]
        );
        $this->setDefaultParam('bulk_actions', ['delete']);
        $this->setDefaultParam('row_actions', ['edit', 'delete']);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Mise en file des scripts de l'interface d'administration
     * @see \tiFy\Core\Ui\Admin\Templates\Table\Table::admin_enqueue_scripts()
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        parent::admin_enqueue_scripts();

        \wp_enqueue_style('tiFyCoreUiAdminTemplatesUserListTable', self::tFyAppAssetsUrl('UserListTable.css', get_class()), [], 171115);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Préparation de la liste des éléments à afficher
     *
     * @return void
     */
    public function prepare_items()
    {
        $query_args = $this->parse_query_args();
        $query = new \WP_User_Query($query_args);
        $this->items = $query->get_results();

        // Pagination
        $total_items = $query->get_total();
        $per_page = $this->get_items_per_page($this->getParam('per_page_option_name'), $this->getParam('per_page'));

        $this->set_pagination_args(
            [
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil($total_items / $per_page)
            ]
        );
    }

    /**
     * Traitement des arguments de requête
     *
     * @return array Tableau associatif des arguments de requête
     */
    public function parse_query_args()
    {
        // Récupération des arguments
        $per_page = $this->get_items_per_page($this->getParam('per_page_option_name'), $this->getParam('per_page'));
        $paged = $this->get_pagenum();

        // Arguments par défaut
        $query_args = [
            'number'      => $per_page,
            'paged'       => $paged,
            'count_total' => true,
            'fields'      => 'all_with_meta',
            'orderby'     => 'user_registered',
            'order'       => 'DESC',
            'role__in'    => $this->getParam('roles', []),
            'include'     => $this->current_item_index()
        ];

        // Traitement des arguments
        if ($request_query_vars = $this->getRequestQueryVars()) :
            foreach($request_query_vars as $key => $value) :
                if (method_exists($this, "filter_query_arg_{$key}")) :
                    $query_args[$key] = call_user_func_array([$this, "filter_query_arg_{$key}"], [$value, &$query_args]);
                endif;
            endforeach;
        endif;

        return \wp_parse_args($this->getParam('query_args', []), $query_args);
    }

    /**
     * Filtrage de l'argument de requête terme de recherche
     *
     * @param string $value Valeur du terme de recherche passé en argument
     * @param array $query_args Liste des arguments de requête passé par référence
     *
     * @return string
     */
    public function filter_query_arg_s($value, &$query_args)
    {
        if (!empty($value)) :
            $query_args['search'] = '*' . wp_unslash(trim($value)) . '*';
        endif;
    }

    /**
     * Filtrage de l'argument de requête role
     *
     * @param string $value Valeur rôle passé en argument
     * @param array $query_args Liste des arguments de requête passé par référence
     *
     * @return string
     */
    public function filter_query_arg_role($value, &$query_args)
    {
        if (!empty($value)) :
            if (is_string($value)) :
                $value = array_map('trim', explode(',', $value));
            endif;

            $roles = [];
            foreach ($value as $v) :
                if (!in_array($v, $this->getParam('roles', []))) :
                    continue;
                endif;
                array_push($roles, $v);
            endforeach;

            if ($roles) :
                $query_args['role__in'] = $roles;
            endif;
        endif;
    }

    /**
     * Contenu de la colonne - Login
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function column_user_login($item)
    {
        $avatar = get_avatar($item->ID, 32);

        if (current_user_can('edit_user', $item->ID) && $this->EditBaseUri) :
            return sprintf('%1$s<strong>%2$s</strong>', $avatar,
                $this->get_item_edit_link($item, [], $item->user_login));
        else :
            return sprintf('%1$s<strong>%2$s</strong>', $avatar, $item->user_login);
        endif;
    }

    /**
     * Contenu de la colonne - date d'enregistrement
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function column_user_registered($item)
    {
        return mysql2date(__('d/m/Y à H:i', 'tify'), $item->user_registered, true);
    }

    /**
     * Contenu de la colonne - Role
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function column_role($item)
    {
        global $wp_roles;

        $user_role = reset($item->roles);
        $role_link = esc_url(add_query_arg('role', $user_role, $this->BaseUri));

        return isset($wp_roles->role_names[$user_role]) ? "<a href=\"{$role_link}\">" . translate_user_role($wp_roles->role_names[$user_role]) . "</a>" : __('Aucun', 'tify');
    }
}