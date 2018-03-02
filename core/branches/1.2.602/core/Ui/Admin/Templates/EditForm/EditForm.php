<?php

namespace tiFy\Core\Ui\Admin\Templates\EditForm;

use tiFy\Core\Field\Field;

class EditForm extends \tiFy\Core\Ui\Admin\Factory
{
    // Paramètres
    use Traits\Params;

    /**
     * Liste des attributs de l'élément à éditer
     * @var object
     */
    protected $item = null;

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
                'fields',
                'create_new_item',
                'item_defaults',
                'list_base_uri'
            ]
        );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Affichage de l'écran courant
     *
     * @param \WP_Screen $current_screen
     *
     * @return void
     */
    final public function current_screen($current_screen)
    {
        parent::current_screen($current_screen);

        // Création d'un nouvel élément
        if (!$item_index = $this->current_item_index()) :
            if ($this->getParam('create_new_item')) :
                if (!$item_index = $this->create_new_item()) :
                    wp_die(__('Impossible de créer un nouvel élément', 'tify'));
                else :
                    wp_safe_redirect(\add_query_arg($this->getParam('item_index_name'), $item_index));
                    exit;
                endif;
            endif;
        endif;

        // Exécution des actions
        $this->process_actions();

        // Préparation de l'élément à afficher
        $this->prepare_item();
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        \wp_enqueue_style('tiFyCoreUiAdminTemplatesForm', self::tFyAppAssetsUrl('EditForm.css', get_class()), [], 171115);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'élément courant à traiter
     *
     * @return string Identifiant de qualification
     */
    public function current_item_index()
    {
        return $this->getRequestItemIndex();
    }

    /**
     * Création d'un nouvel élément
     *
     * @return object
     */
    protected function create_new_item()
    {
        if (!$db = $this->getDb()) :
            return 0;
        endif;

        $item_defaults = $this->getParam('item_defaults', []);

        return $db->handle()->create(\wp_parse_args($item_defaults, [$db->getPrimary() => 0]));
    }

    /**
     * Préparation de l'élément à afficher
     *
     * @return void
     */
    public function prepare_item()
    {
        if (!$db = $this->getDb()) :
            return;
        endif;

        $query_args = $this->parse_query_args();
        $query = $db->query($query_args);
        $this->item = $query->item;

        $query = $db->query($this->parse_query_args());
        $this->item = reset($query->items);
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

        // Arguments par défaut
        $query_args[$db->getPrimary()] = $this->current_item_index();

        // Traitement des arguments
        if ($request_query_vars = $this->getRequestQueryVars()) :
            foreach($request_query_vars as $key => $value) :
                if (method_exists($this, "filter_query_arg_{$key}")) :
                    $query_args[$key] = call_user_func_array([$this, "filter_query_arg_{$key}"], [$value, &$query_args]);
                elseif($db->isCol($key)) :
                    $query_args[$key] = $value;
                endif;
            endforeach;
        endif;

        return \wp_parse_args($this->getParam('query_args', []), $query_args);
    }

    /**
     * Récupération des attributs par défaut d'un élément
     *
     * @return object
     */
    public function get_item_defaults()
    {
        $item_defaults = $this->getParam('item_defaults');

        return (object)$item_defaults;
    }

    /** == Traitement des données de requete == **/
    protected function parse_postdata($postdata)
    {
        return array_map('wp_unslash', $postdata);
    }

    /**
     * Récupération de la liste des champs
     *
     * @return array
     */
    public function get_fields()
    {
        return $this->getParam('fields');
    }

    /** == Récupération d'une valeur de metadonnée == **/
    public function get_meta($meta_key, $single = true)
    {
        return $this->query->get_meta($meta_key, $single);
    }

    /**
     * Affichage de la liste des champs cachés
     *
     * @return string
     */
    public function hidden_fields()
    {
?>
<input type="hidden" id="hiddenaction" name="action" value="update"/>
<input type="hidden" id="user-id" name="user_ID" value="<?php echo \get_current_user_id(); ?>"/>
<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>"/>
<?php //wp_nonce_field($this->get_item_nonce_action('update', $this->item->{$this->db()->Primary})); ?>
<?php /*<input type="hidden" id="<?php echo $this->db()->Primary; ?>" name="<?php echo $this->db()->Primary; ?>" value="<?php echo $this->item->{$this->db()->Primary}; ?>"/> */ ?>
<?php
    }

    /**
     * Affichage de l'interface de saisie
     *
     * @return string
     */
    public function display()
    {
?>
<div style="margin-right:300px; margin-top:20px;">
    <div style="float:left; width: 100%;">
        <?php $this->form(); ?>
    </div>
    <div style="margin-right:-300px; width: 280px; float:right;">
        <?php $this->submitdiv(); ?>
    </div>
</div>
<?php
    }

    /**
     * Affichage du formulaire de saisie
     *
     * @return string
     */
    public function form()
    {
?>
<div class="stuffbox">
    <h3 class="hndle ui-sortable-handle">
        <span><?php echo $this->getLabel('datas_item'); ?></span>
    </h3>
    <div class="inside">
        <?php $this->display_fields(); ?>
    </div>
</div>
<?php
    }

    /**
     * Affichage des champs de saisie
     *
     * @return string
     */
    public function display_fields()
    {
        if (!$fields = $this->get_fields()) :
            return;
        endif;
?>
<table class="form-table">
    <tbody>
    <?php
    foreach ($fields as $field_name => $field_label) :
        $this->display_field($field_name, $field_label);
    endforeach;
    ?>
    </tbody>
</table>
<?php
    }

    /**
     * Affichage d'un champ de saisie
     *
     * @param string $field_name Identifiant de qualification du champ
     * @param string $field_label Intitulé de qualification du champ
     *
     * @return string
     */
    public function display_field($field_name, $field_label)
    {
?>
<tr>
    <th scope="row">
        <label><?php echo $field_label; ?></label>
    </th>
    <td>
    <?php
        if (method_exists($this, "field_{$field_name}")) :
            echo call_user_func([$this, "field_{$field_name}"], $this->item);
        else :
            echo $this->field_default($this->item, $field_name);
        endif;
    ?>
    </td>
</tr>
<?php
    }

    /**
     * Affichage du champ de saisie par défaut
     *
     * @param object $item Attributs de l'élément courant
     * @param string $field_name Identifiant de qualification du champ
     *
     * @return string
     */
    public function field_default($item, $field_name)
    {
        $field_value = isset($item->{$field_name}) ? $item->{$field_name} : '';

        // Empêche la saisie de la clé d'index
        if ($field_name === $this->getParam('item_index_name')) :
            return "#{$field_value}";
        endif;

        // Définition du type de données de la valeur de la colonne
        $type = (($db = $this->getDb()) && $db->isCol($field_name)) ? strtoupper($db->getColAttr($field_name, 'type') ) : '';

        switch ($type) :
            default:
                return Field::Text(['name' => $field_name, 'value' => $field_value]);
                break;
            case 'DATETIME' :
                return "<input type=\"datetime\" name=\"{$field_name}\" value=\"{$field_value}\"/>";
                break;
            case 'BIGINT' :
            case 'INT' :
            case 'TINYINT' :
                return Field::Number(['name' => $field_name, 'value' => $field_value]);
                break;
            case 'LONGTEXT' :
                // @todo rendre potentiellement récursif
                if (is_array($field_value)) :
                    $output = "";
                    foreach ($field_value as $k => $v) {
                        $output .= "<label>{$k}</label><textarea name=\"{$field_name}[{$k}]\"/>{$v}</textarea><br>";
                    }
                    return $output;
                else :
                    return "<textarea name=\"{$field_name}\"/>{$field_value}</textarea>";
                endif;
                break;
        endswitch;
    }

    /**
     * Affichage de l'interface de soumission du formulaire
     *
     * @return string
     */
    public function submitdiv()
    {
?>
<div id="submitdiv" class="tify_submitdiv">
    <h3 class="hndle">
        <span><?php _e('Enregistrer', 'tify'); ?></span>
    </h3>

    <div class="inside">
        <div class="minor_options">
            <?php $this->minor_options(); ?>
        </div>
        <div class="major_options">
            <?php $this->major_options(); ?>
        </div>
    </div>
</div>
<?php
    }

    /**
     * Affichage des options principales de l'interface de soumission du formulaire
     *
     * @return string
     */
    public function major_options()
    {
        ?><div class="updating"><?php submit_button(__('Enregistrer', 'tify'), 'primary', 'submit', false); ?></div><?php
    }

    /**
     * Affichage des options secondaires de l'interface de soumission du formulaire
     *
     * @return string
     */
    public function minor_options()
    {

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
        <?php echo $this->getParam('page_title'); ?>

        <?php if ($this->getParam('create_new_item')) : ?>
        <a class="add-new-h2" href="<?php echo $this->getParam('base_uri'); ?>"><?php echo $this->getLabel('new_item'); ?></a>
        <?php endif; ?>
    </h2>

    <form method="post">
        <?php $this->hidden_fields(); ?>
        <?php $this->display(); ?>
    </form>
</div>
<?php
    }
}