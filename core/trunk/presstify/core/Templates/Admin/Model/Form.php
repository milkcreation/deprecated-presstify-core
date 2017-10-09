<?php

namespace tiFy\Core\Templates\Admin\Model;

use tiFy\Apps;

abstract class Form
{
    use \tiFy\Core\Templates\Traits\Factory;
    use \tiFy\Core\Templates\Traits\Form\Actions;
    use \tiFy\Core\Templates\Traits\Form\Notices;
    use \tiFy\Core\Templates\Traits\Form\Params;

    /* = ARGUMENTS = */
    // Écran courant
    protected $Screen = null;

    // Configuration
    /// Url de la page d'administration
    protected $BaseUri = null;

    /// Url de la page d'édition des éléments
    protected $ListBaseUri = null;

    // Intitulé de l'objet traité
    protected $Singular = null;

    /// Message de notification
    protected $Notices = array();

    /// Champs d'édition
    protected $Fields = array();

    /// Habilitations
    protected $Cap = 'edit_posts';

    /// Arguments de requête
    protected $QueryArgs = array();

    /// Permettre la création d'un nouvel élément
    protected $NewItem = true;

    /// Titre de la page
    protected $PageTitle = null;

    /// Cartographie des paramètres
    protected $ParamsMap = array(
        'BaseUri',
        'ListBaseUri',
        'Plural',
        'Singular',
        'Notices',
        'Fields',
        'QueryArgs',
        'NewItem',
        'PageTitle'
    );

    // Élément à éditer
    protected $item = null;

    // Notification courante
    protected $CurrentNotice = null;

    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        Apps::register($this);
    }

    /* = METHODES MAGIQUES = */
    /** == Appel des méthodes dynamiques == **/
    final public function __call($name, $arguments)
    {
        if (in_array($name, array('template', 'db', 'label', 'getConfig'))) :
            return call_user_func_array($this->{$name}, $arguments);
        endif;
    }

    /* = DECLARATION DES PARAMETRES = */
    /** == Définition de l'url d'affichage de la liste des éléments == **/
    public function set_list_base_url()
    {
        return '';
    }

    /** == Définition l'intitulé des objets traités == **/
    public function set_plural()
    {
        return null;
    }

    /** == Définition l'intitulé d'un objet traité == **/
    public function set_singular()
    {
        return null;
    }

    /** == Définition des messages de notification == **/
    public function set_notices()
    {
        return array();
    }

    /** == Définition des champs de formulaire == **/
    public function set_fields()
    {
        return array();
    }

    /** == Définition des arguments de requête == **/
    public function set_query_args()
    {
        return array();
    }

    /** == Permettre l'ajout d'un nouvel élément == **/
    public function set_add_new_item()
    {
        return true;
    }

    /** == Définition du titre de la page == **/
    public function set_page_title()
    {
        return '';
    }

    /* = DECLENCHEURS = */
    /** == Affichage de l'écran courant == **/
    final public function _current_screen($current_screen)
    {
        // Définition de l'écran courant
        $this->Screen = $current_screen;

        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Traitement
        /// Exécution des actions
        $this->process_bulk_actions();

        /// Affichage des messages de notification
        if ($code = $this->current_notice()) :
            $notice_attrs = $this->Notices[$code];
            add_action('admin_notices', function () use ($notice_attrs) {
                ?>
                <div class="notice notice-<?php echo $notice_attrs['notice']; ?><?php echo $notice_attrs['dismissible'] ? ' is-dismissible' : ''; ?>">
                    <p><?php echo $notice_attrs['message'] ?></p>
                </div>
                <?php

            });
        endif;

        /// Récupération des éléments à afficher
        $this->prepare_item();
    }

    /* = TRAITEMENT = */
    /** == Récupération de l'élément à traité == **/
    public function current_item()
    {
        if (!empty($_REQUEST[$this->db()->Primary])) {
            return (int)$_REQUEST[$this->db()->Primary];
        }

        return 0;
    }

    /** == Récupération de l'action courante == **/
    public function current_action()
    {
        if (isset($_REQUEST['action']) && -1 != $_REQUEST['action']) {
            return $_REQUEST['action'];
        }
        if (isset($_REQUEST['action2']) && -1 != $_REQUEST['action2']) {
            return $_REQUEST['action2'];
        }

        return false;
    }

    /** == Récupération de la notification courante == **/
    public function current_notice()
    {
        if($this->CurrentNotice)
            return $this->CurrentNotice;

        foreach ((array)$this->Notices as $nid => $nattr) :
            if (! isset($_REQUEST[$nattr['query_arg']]) || ($_REQUEST[$nattr['query_arg']] !== $nid))
                continue;
            return $nid;
        endforeach;

        return false;
    }

    /**
     * Définition de la notification courante
     *
     * @param string $code
     *
     * @return null|string
     */
    public function set_current_notice($code)
    {
        if (isset($this->Notices[$code]))
            return $this->CurrentNotice = $code;
    }

    /** == Préparation de l'object à éditer == **/
    public function prepare_item()
    {
        // Initialisation de l'élément à éditer
        /// Vérification des habilitations
        if (!current_user_can($this->Cap)) {
            wp_die(__('Vous n\'êtes pas autorisé à modifier ce contenu.', 'tify'));
        }

        // Traitement de l'élément courant
        if ($this->NewItem && !($item_id = $this->current_item())) :
            $item_id = $this->get_default_item_to_edit();

            // Vérification
            if (!$item_id) :
                wp_die(__('ERREUR SYSTEME : Impossible de créer un nouvel élément', 'tify'));
            elseif (!$this->db()->select()->row_by_id($item_id)) :
                wp_die(__('Vous tentez de modifier un contenu qui n’existe pas. Peut-être a-t-il été supprimé ?!',
                    'tify'));
            endif;
            // Traitement des actions
            if (!$this->current_item()) :
                wp_safe_redirect(add_query_arg($this->db()->Primary, $item_id));
                exit;
            endif;
        endif;

        $query = $this->db()->query($this->parse_query_args());
        $this->item = reset($query->items);
    }

    /** == Traitement des arguments de requête == **/
    public function parse_query_args()
    {
        // Arguments par défaut
        $query_args = array(
            $this->db()->Primary => $this->current_item(),
        );

        // Traitement des arguments
        foreach ((array)$_REQUEST as $key => $value) :
            if (method_exists($this, 'parse_query_arg_' . $key)) :
                call_user_func_array(array($this, 'parse_query_arg_' . $key), array(&$query_args, $value));
            elseif ($this->db()->isCol($key)) :
                $query_args[$key] = $value;
            endif;
        endforeach;

        return wp_parse_args($this->QueryArgs, $query_args);
    }

    /** == Éxecution des actions == **/
    protected function process_bulk_actions()
    {
        if (defined('DOING_AJAX') && (DOING_AJAX === true)) {
            return;
        }

        if (method_exists($this, 'process_bulk_action_' . $this->current_action())) :
            call_user_func(array($this, 'process_bulk_action_' . $this->current_action()));
        elseif (!empty($_REQUEST['_wp_http_referer'])) :
            wp_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI'])));
            exit;
        endif;
    }

    /** == Éxecution de l'action - mise à jour == **/
    protected function process_bulk_action_update()
    {
        check_admin_referer($this->get_item_nonce_action($this->current_action(), $this->current_item()));

        $data = $this->parse_postdata($_POST);

        $sendback = remove_query_arg(array('action', 'action2'), wp_get_referer());
        $sendback = add_query_arg(array($this->db()->Primary => $this->current_item()), $sendback);
        if (is_wp_error($data)) :
            $sendback = add_query_arg(array('message' => $data->get_error_code()), $sendback);
        else :
            $this->db()->handle()->record($data);
            $sendback = add_query_arg(array('message' => 'updated'), $sendback);
        endif;

        wp_redirect($sendback);
        exit;
    }

    /** == Éxecution de l'action - mise à la corbeille == **/
    protected function process_bulk_action_trash()
    {
        check_admin_referer($this->get_item_nonce_action($this->current_action(), $this->current_item()));

        // Traitement de l'élément
        /// Conservation du statut original
        if ($this->db()->hasMeta() && ($original_status = $this->db()->select()->cell_by_id($this->item_id,
                'status'))) {
            $this->db()->meta()->update($this->item_id, '_trash_meta_status', $original_status);
        }
        /// Modification du statut
        $this->db()->handle()->update($this->item_id, array('status' => 'trash'));

        // Traitement de la redirection
        $sendback = remove_query_arg(array('action', 'action2'), wp_get_referer());
        $sendback = add_query_arg('message', 'trashed', $sendback);

        wp_redirect($sendback);
        exit;
    }

    /** == Traitement des données de requete == **/
    protected function parse_postdata($postdata)
    {
        return array_map('wp_unslash', $postdata);
    }

    /* = HELPERS = */
    /** == Création d'un élément par défaut == **/
    protected function get_default_item_to_edit()
    {
        return $this->db()->handle()->create(wp_parse_args($this->item_defaults, array($this->db()->Primary => 0)));
    }

    /* = CONTROLEUR = */
    /** == Liste des champs == **/
    public function get_fields()
    {
        return $this->Fields;
    }

    /** == Récupération d'une valeur de metadonnée == **/
    public function get_meta($meta_key, $single = true)
    {
        return $this->query->get_meta($meta_key, $single);
    }

    /* = AFFICHAGE */
    /** == Champs cachés == **/
    public function hidden_fields()
    {

    }

    /** == Affichage de l'interface de saisie == **/
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

    /** == Formulaire de saisie == **/
    public function form()
    {
        return $this->display_rows();
    }

    /** == Affichage des champs de saisie sous forme de table == **/
    public function display_rows()
    {
        ?>
        <div class="stuffbox">
            <h3 class="hndle ui-sortable-handle">
                <span><?php echo $this->label('datas_item'); ?></span>
            </h3>
            <div class="inside">
                <table class="form-table">
                    <tbody>
                    <?php
                    foreach ((array)$this->get_fields() as $field_name => $title) :
                        $this->display_row($field_name, $title);
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /** == Affichage d'une ligne de saisie == **/
    public function display_row($field_name, $title)
    {
        ?>
        <tr>
            <th scope="row">
                <label>
                    <?php echo $title; ?>
                </label>
            </th>
            <?php
            if (method_exists($this, 'field_' . $field_name)) :
                echo "<td>";
                echo call_user_func(array($this, 'field_' . $field_name), $this->item);
                echo "</td>";
            else :
                echo "<td>";
                echo $this->field_default($this->item, $field_name);
                echo "</td>";
            endif;
            ?>
        </tr>
        <?php
    }

    /** == Affichage des champs de saisie par défaut == **/
    public function field_default($item, $field_name)
    {
        $value = isset($item->{$field_name}) ? $item->{$field_name} : '';

        if ($field_name === $this->db()->Primary) {
            return "#{$value}";
        }

        $col_type = strtoupper($this->db()->getColAttr($field_name, 'type'));

        switch ($col_type) :
            default:
                return "<input type=\"text\" name=\"{$field_name}\" value=\"{$value}\"/>";
                break;
            case 'DATETIME' :
                return "<input type=\"datetime\" name=\"{$field_name}\" value=\"{$value}\"/>";
                break;
            case 'BIGINT' :
            case 'INT' :
            case 'TINYINT' :
                return "<input type=\"number\" name=\"{$field_name}\" value=\"{$value}\"/>";
                break;
            case 'LONGTEXT' :
                /** @todo rendre récursif * */
                if (is_array($value)) :
                    $output = "";
                    foreach ($value as $k => $v) {
                        $output .= "<label>{$k}</label><textarea name=\"{$field_name}[{$k}]\"/>{$v}</textarea><br>";
                    }
                    return $output;
                else :
                    return "<textarea name=\"{$field_name}\"/>{$value}</textarea>";
                endif;
                break;
        endswitch;
    }

    /** == Affichage de la boîte de soumission du formulaire == **/
    public function submitdiv()
    {
        ?>
        <div id="submitdiv" class="tify_submitdiv">
            <?php wp_nonce_field($this->get_item_nonce_action('update', $this->item->{$this->db()->Primary})); ?>
            <input type="hidden" id="hiddenaction" name="action" value="update"/>
            <input type="hidden" id="user-id" name="user_ID" value="<?php echo get_current_user_id(); ?>"/>
            <input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url(wp_get_referer()); ?>"/>
            <input type="hidden" id="<?php echo $this->db()->Primary; ?>" name="<?php echo $this->db()->Primary; ?>"
                   value="<?php echo $this->item->{$this->db()->Primary}; ?>"/>
            <h3 class="hndle">
                <span><?php _e('Enregistrer', 'tify'); ?></span>
            </h3>
            <div class="inside">
                <div class="minor_actions">
                    <?php $this->minor_actions(); ?>
                </div>
                <div class="major_actions">
                    <?php $this->major_actions(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /** == Affichage des actions secondaire de la boîte de soumission du formulaire == **/
    public function minor_actions()
    {

    }

    /** == Affichage des actions principale de la boîte de soumission du formulaire == **/
    public function major_actions()
    {
        ?>
        <div class="updating"><?php submit_button(__('Enregistrer', 'tify'), 'primary', 'submit', false); ?></div><?php
    }

    /** == Rendu == **/
    public function render()
    {
        ?>
        <div class="wrap">
            <h2>
                <?php echo $this->label('edit_item'); ?>
                <?php if ($this->NewItem) : ?>
                    <a class="add-new-h2"
                       href="<?php echo $this->BaseUri; ?>"><?php echo $this->label('new_item'); ?></a>
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