<?php
namespace tiFy\Core\Ui\Admin\Templates\ListTable;

class ListTable extends \tiFy\Core\Ui\Admin\Factory
{
    // Gabarit commun
    use \tiFy\Core\Ui\Common\Traits\Templates\ListTable\ListTable;
    use \tiFy\Core\Ui\Common\Traits\Templates\ListTable\WpListTable;

    // Paramètres
    use Traits\Params;

    // Vues filtrées
    use Traits\Views;

    // Actions groupées
    use Traits\BulkActions;

    // Actions sur un élément
    use Traits\RowActions;

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
        $this->setAllowedParamList(
            [
                'edit_base_uri',
                'columns',
                'primary_column',
                'sortable_columns',
                'hidden_columns',
                'preview_item_mode',
                'preview_item_columns',
                'preview_item_ajax_args',
                'table_classes',
                'per_page',
                'per_page_option_name',
                'no_items',
                'views',
                'bulk_actions',
                'row_actions',
                'row_actions_always_visible'
            ]
        );
    }

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
        parent::init();

        // Pré-initialisation des paramètres
        $this->initParams(
            [
                'per_page',
                'per_page_option_name'
            ]
        );

        // Définition de l'action Ajax de prévisualisation
        add_action('wp_ajax_' . $this->getId() . '_preview_item', [$this, 'wp_ajax_preview_item']);
    }

    /**
     * Affichage de l'écran courant
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    public function current_screen($current_screen)
    {
        parent::current_screen($current_screen);

        // Initialisation de l'émulation de la classe de table native de Wordpress
        $this->_wp_list_table_init(
            [
                'plural'   => $this->getParam('plural'),
                'singular' => $this->getParam('singular'),
                'ajax'     => $this->getParam('ajax'),
                'screen'   => $this->getScreen()
            ]
        );

        // Activation de l'interface de gestion du nombre d'éléments par page
        $current_screen->add_option('per_page', ['option' => $this->getParam('per_page_option_name')]);

        // Exécution des actions
        $this->process_actions();

        // Préparation de la liste des éléments à afficher
        $this->prepare_items();
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        parent::admin_enqueue_scripts();

        if ($preview_item_mode = $this->getParam('preview_item_mode')) :
            wp_enqueue_script('tiFyCoreUiAdminTemplatesListTablePreviewItem', self::tFyAppAssetsUrl('ListTable-previewItem.js', get_class()), ['jquery', 'url'], 171118, true);
            wp_localize_script(
                'tiFyCoreUiAdminTemplatesListTablePreviewItem',
                'tiFyUiAdminListTablePreviewItem',
                [
                    'action' => $this->getID() . '_preview_item',
                    'mode'   => $preview_item_mode,
                    'nonce_action' => '_wpnonce',
                    'item_index_name' => $this->getParam('item_index_name')
                ]
            );

            if ($preview_item_mode === 'dialog') :
                wp_enqueue_script('jquery-ui-dialog');
                wp_enqueue_style('wp-jquery-ui-dialog');
            endif;
        endif;
    }

    /**
     * Récupération ajax de la prévisualisation d'un élément
     *
     * @return string
     */
    public function wp_ajax_preview_item()
    {
        $this->initParams();

        if (!$item_index = $this->getRequestItemIndex()) :
            die(0);
        endif;

        check_ajax_referer($this->getActionNonce('preview_item', $item_index));

        $this->prepare_items();
        $item = current($this->items);
        $this->preview_item($item);
        die();
    }

    /**
     * CONTROLEURS
     */
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
            $page_title = $this->getLabel('all_items', '');
        endif;

        return $page_title;
    }

    /**
     * Récupération d'élément courant à traiter
     *
     * @return null|array Identifiant de qualification ou Tableau indexé de la liste des identifiants de qualification
     */
    public function current_item_index()
    {
        if ($item_indexes = $this->getRequestItemIndex()) :
            if (!is_array($item_indexes)) :
                return array_map('trim', explode(',', $item_indexes));
            else :
                return $item_indexes;
            endif;
        endif;
    }

    /**
     * Préparation de la liste des éléments à afficher
     *
     * @return void
     */
    public function prepare_items() 
    {
        if (!$db = $this->getDb()) :
            return;
        endif;

        $query_args = $this->parse_query_args();
        $query = $db->query($query_args);
        $this->items = $query->items;

        // Définition des arguments de pagination
        $total_items = $query->found_items;
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
        if (!$db = $this->getDb()) :
            return;
        endif;

        // Récupération des arguments
        $per_page   = $this->get_items_per_page($this->getParam('per_page_option_name'), $this->getParam('per_page'));
        $paged      = $this->get_pagenum();

        // Arguments par défaut
        $query_args = [
            'per_page' => $per_page,
            'paged'    => $paged,
            'order'    => 'DESC',
            'orderby'  => $db->getPrimary()
        ];
        $query_args = \wp_parse_args($this->getParam('query_args', []), $query_args);

        // Traitement des arguments de requête
        if ($request_query_vars = $this->getRequestQueryVars()) :
            foreach($request_query_vars as $key => $value) :
                if (method_exists($this, "filter_query_arg_{$key}")) :
                    $query_args[$key] = call_user_func_array([$this, "filter_query_arg_{$key}"], [$value, &$query_args]);
                elseif($db->isCol($key)) :
                    $query_args[$key] = $value;
                endif;
            endforeach;
        endif;

        return $query_args;
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
        if(!empty($value)) :
            $query_args['s'] = wp_unslash( trim( $value ) );
        endif;

        return $value;
    }

    /**
     * Récupération de la liste des vues filtrées
     * @see \WP_List_Table::get_views()
     *
     * @return array
     */
    public function get_views()
    {
        return $this->parseViews($this->getParam('views'));
    }

    /**
     * Récupération de la liste des actions groupées
     * @see \WP_List_Table::get_bulk_actions()
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        return $this->getParam('bulk_actions');
    }

    /**
     * Récupération de la liste des colonnes
     * @see \WP_List_Table::get_columns()
     *
     * @return array
     */
    public function get_columns() 
    {
        return apply_filters( "manage_" . $this->getId() . "_columns", $this->getParam('columns'));
    }

    /**
     * Récupération de la liste des colonnes
     * @see \WP_List_Table::get_sortable_columns()
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        return $this->getParam('sortable_columns');
    }

    /**
     * Récupération de la liste des colonnes de prévisualisation d'un élément
     *
     * @return array
     */
    public function get_preview_item_columns()
    {
        if (!$preview_item_columns = $this->getParam('preview_item_columns')) :
            $preview_item_columns = $this->get_columns();
            unset($preview_item_columns['cb']);
        endif;

        return $preview_item_columns;
    }

    /**
     * Récupération de la liste des classe CSS de la balise table.
     * @see \WP_List_Table::get_sortable_columns()
     *
     * @return array List of CSS classes for the table tag.
     */
    protected function get_table_classes()
    {
        return $this->getParam('table_classes');
    }

    /**
     * Récupération du contenu de la table lorsque la liste des éléments est vide
     * @see \WP_List_Table::no_items()
     *
     * @return string
     */
    public function no_items() 
    {
        echo $this->getParam('no_items');
    }

    /**
     * Génération et affichage des actions sur un élément
     * @see \WP_List_Table::handle_row_actions()
     *
     * @param object $item Attributs de l'élément courant
     * @param string $column_name Identifiant de qualification de la colonne courante
     * @param string $primary Identifiant de qualification de la colonne principale
     *
     * @return string
     */
    public function handle_row_actions($item, $column_name, $primary)
    {
        if (!$row_actions = $this->getParam('row_actions')) :
            return;
        endif;

        if ($primary !== $column_name) :
            return;
        endif;
        
        return $this->parseRowActions($item, $row_actions);
    }

    /**
     * Récupération de l'entête de colonne.
     *
     * @return void
     */
    public function header_cb()
    {
        return "<input id=\"cb-select-all-1\" type=\"checkbox\" />";
    }

    /**
     * Contenu par défaut des colonnes
     * @see \WP_List_Table::column_default()
     *
     * @param object $item Attributs de l'élément courant
     * @param string $column_name Identifiant de qualification de la colonne courante
     *
     * @return string
     */
    public function column_default($item, $column_name)
    {
        $custom_columns_content = apply_filters_ref_array("manage_" . $this->getId() . "_custom_column", [null, $column_name, $item]);
        if (!is_null($custom_columns_content)) :
            return $custom_columns_content;
        endif;

        // Bypass 
        if (!isset($item->{$column_name})) :
            return;
        endif;

        // Définition du type de données de la valeur de la colonne
        $type = (($db = $this->getDb()) && $db->isCol($column_name)) ? strtoupper($db->getColAttr($column_name, 'type') ) : '';

        switch($type) :
            default:
                if(is_array($item->{$column_name})) :
                    return join(', ', $item->{$column_name});
                else :    
                    return $item->{$column_name};
                endif;
                break;
            case 'DATETIME' :
                return \mysql2date(get_option('date_format') . ' @ ' . get_option('time_format'), $item->{$column_name});
                break;
        endswitch;
    }
    
    /**
     * Contenu de la colonne - Case à cocher
     * @see \WP_List_Table::column_cb()
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function column_cb($item)
    {
        return (($db = $this->getDb()) && ($primary = $db->getPrimary()) && isset($item->{$primary})) ? sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $primary, $item->{$primary}) : parent::column_cb($item);
    }

    /**
     * Affichage des champs cachés
     *
     * @return string
     */
    public function hidden_fields()
    {
        if($preview_item_mode = $this->getParam('preview_item_mode')) :
            ?><input type="hidden" id="PreviewItemAjaxData" value="<?php echo rawurlencode(json_encode($this->getParam('preview_ajax_datas')));?>" /><?php
        endif;
    }

    /**
     * Aperçu des données des éléments
     *
     * @return string
     */
    public function preview_items()
    {
        switch($this->getParam('preview_item_mode')) :
            case 'dialog' :
                ?><div id="Item-previewContainer" class="hidden" style="max-width:800px; min-width:800px;"><div class="Item-previewContent"></div></div><?php
                break;
            case 'row' :
                ?><table class="hidden"><tbody><tr id="Item-previewContainer"><td class="Item-previewContent" colspan="<?php echo count($this->get_columns());?>"><h3><?php _e( 'Chargement en cours ...', 'tify' );?></h3></td></tr></tbody></table><?php
                break;
        endswitch;
    }

    /**
     * Affichage de l'aperçu des données d'un élément
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function preview_item($item)
    {
        if (!$preview_item_columns = $this->get_preview_item_columns()) :
            return;
        endif;
?>
<table class="form-table">
    <tbody>
    <?php foreach ($preview_item_columns as $column_name => $column_label) :?>
        <tr valign="top">
            <th scope="row">
                <label><strong><?php echo $column_label;?></strong></label>
            </th>
            <td>
            <?php echo $this->preview_item_default($item, $column_name); ?>
            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
<div class="clear"></div>
<?php
    }

    /**
     * Affichage de l'aperçu des données d'un élément par défaut
     *
     * @param object $item Attributs de l'élément courant
     * @param string $column_name Identifiant de qualification de la colonne
     *
     * @return string
     */
    public function preview_item_default($item, $column_name)
    {
        if (method_exists($this, "preview_item_{$column_name}")) :
            return call_user_func([$this, "preview_item_{$column_name}"], $item);
        elseif (method_exists($this, '_column_' . $column_name)) :
            return call_user_func([$this, '_column_' . $column_name], $item);
        elseif (method_exists($this, 'column_' . $column_name)) :
            return call_user_func([$this, 'column_' . $column_name], $item);
        else :
            return $this->column_default($item, $column_name);
        endif;
    }

    /**
     * Affichage de la page
     *
     * @return string
     */
    public function render()
    {
?>
<div class="wrap">
    <h2>
        <?php echo $this->getParam('page_title');?>
        
        <?php if($edit_base_uri = $this->getParam('edit_base_uri')) : ?>
            <a class="add-new-h2" href="<?php echo $edit_base_uri;?>"><?php echo $this->getLabel('add_new');?></a>
        <?php endif;?>
    </h2>
    
    <?php $this->views(); ?>
    
    <form method="get" action="">
        <?php if($base_uri_query_vars = $this->getBaseUriQueryVars()) : ?>
            <?php foreach ($base_uri_query_vars as $k => $v) : ?>
                <input type="hidden" name="<?php echo $k;?>" value="<?php echo $v;?>" />
            <?php endforeach; ?>
        <?php endif; ?>

        <?php $this->hidden_fields();?>
    
        <?php $this->search_box($this->getLabel('search_items'), $this->getId());?>

        <?php $this->display();?>

        <?php $this->preview_items();?>
    </form>
</div>
<?php
    }
}