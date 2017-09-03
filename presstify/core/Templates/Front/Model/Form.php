<?php

namespace tiFy\Core\Templates\Front\Model;

abstract class Form
{

    use \tiFy\Core\Templates\Traits\Factory;
    use \tiFy\Core\Templates\Traits\Form\Actions;
    use \tiFy\Core\Templates\Traits\Form\Notices;
    use \tiFy\Core\Templates\Traits\Form\Params;

    /**
     * Url de la page d'affichage de l'interface d'administration
     * @var string
     */
    protected $BaseUri;

    /**
     * Url de la page d'affichage de la liste des éléments de l'interface d'administration
     * @var string
     */
    protected $ListBaseUri;

    /**
     * Intitulé d'un élement unique
     * @var string
     */
    protected $Singular;

    /**
     * Liste des message de notification
     * @var array
     */
    protected $Notices = [];

    /**
     * Liste des champs d'édition de l'interface
     * @var array
     */
    protected $Fields = [];

    /**
     * Habilitations d'accès des utilisateurs à la page d'administration
     * @var string
     */
    protected $Cap = 'edit_posts';

    /**
     * Liste des arguments de requête des récupération de l'élément à éditer
     * @var array
     */
    protected $QueryArgs = [];

    /**
     * Permettre la création d'un nouvel élément
     * @todo Vérifier
     * @var bool
     */
    protected $NewItem = true;

    /**
     * Attributs par défaut d'un élément
     * @var array
     */
    protected $DefaultItemArgs = [];

    /**
     * Carographie des paramètres autorisés
     * @var string[]
     */
    protected $ParamsMap = [
        'BaseUri',
        'ListBaseUri',
        'Singular',
        'Notices',
        'Fields',
        'QueryArgs',
        'NewItem',
        'DefaultItemArgs',
    ];

    /**
     * Attributs de l'élément courant
     * @var object
     */
    protected $item = null;


    private $DbQuery = null;

    /* = METHODES MAGIQUES = */
    /** == Appel des méthodes dynamiques == **/
    final public function __call($name, $arguments)
    {
        if (in_array($name, ['template', 'db', 'label', 'getConfig'])) :
            return call_user_func_array($this->{$name}, $arguments);
        endif;
    }

    /* = PARAMETRAGE = */
    /** == Définition des messages de notification == **/
    public function set_notices()
    {
        return [];
    }

    /** == Définition l'intitulé de l'objet traité == **/
    public function set_singular()
    {
        return null;
    }

    /**
     * Définition des champs de formulaire
     *
     * @return array [
     *      "$attr" => "$label"
     *      ...
     * ]
     */
    public function set_fields()
    {
        return [];
    }

    /** == Définition des arguments de requête == **/
    public function set_query_args()
    {
        return [];
    }

    /** == Permettre l'ajout d'un nouvel élément == **/
    public function set_add_new_item()
    {
        return true;
    }

    /** ==Définition des attributs par défaut de l'élément == **/
    public function set_default_item_args()
    {
        return [];
    }

    /* = DECLENCHEURS = */
    /** == Affichage de l'écran courant == **/
    final public function _current_screen()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Traitement
        /// Exécution des actions
        $this->process_bulk_actions();


        /// Affichage des messages de notification
        foreach ((array)$this->Notices as $nid => $nattr) :
            if ( ! isset($_REQUEST[$nattr['query_arg']]) || ($_REQUEST[$nattr['query_arg']] !== $nid)) {
                continue;
            }

            add_action('alert_notices', function () use ($nattr) {
                ?>
                <div class="alert alert-<?php echo $nattr['notice']; ?><?php echo $nattr['dismissible'] ? ' is-dismissible' : ''; ?>">
                    <p><?php echo $nattr['message'] ?></p>
                </div>
                <?php

            });
        endforeach;

        /// Récupération des éléments à afficher
        $this->prepare_item();
    }

    /* = TRAITEMENT = */
    /** == Récupération de l'élément à traité == **/
    public function current_item()
    {
        if ( ! empty($_REQUEST[$this->db()->getPrimary()])) {
            return (int)$_REQUEST[$this->db()->getPrimary()];
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

    /* = PARAMETRAGE = */
    /** == Préparation de l'object à éditer == **/
    public function prepare_item()
    {
        $this->DbQuery = $this->db()->query();
        $query_items   = $this->DbQuery->query($this->parse_query_args());

        $this->item = reset($query_items);
    }

    /** == Traitement des arguments de requête == **/
    public function parse_query_args()
    {
        // Arguments par défaut
        $query_args = [
            $this->db()->getPrimary() => $this->current_item(),
        ];

        // Traitement des arguments
        foreach ((array)$_REQUEST as $key => $value) :
            if (method_exists($this, 'parse_query_arg_' . $key)) :
                call_user_func_array([$this, 'parse_query_arg_' . $key],
                    [&$query_args, $value]);
            elseif ($this->db()->isCol($key)) :
                $query_args[$key] = $value;
            endif;
        endforeach;

        return wp_parse_args($this->QueryArgs, $query_args);
    }

    /** == Éxecution des actions == **/
    protected function process_bulk_actions()
    {
        // Vérification des habilitations
        if ( ! current_user_can($this->Cap)) {
            wp_die(__('Vous n\'êtes pas autorisé à modifier ce contenu.',
                'tify'));
        }

        // Traitement de l'élément courant
        if ( ! $item_id = $this->current_item()) {
            $item_id = $this->get_default_item_to_edit();
        }

        // Vérification
        if ( ! $item_id) {
            wp_die(__('ERREUR SYSTEME : Impossible de créer un nouvel élément',
                'tify'));
        } elseif ( ! $this->db()->select()->row_by_id($item_id)) {
            wp_die(__('Vous tentez de modifier un contenu qui n’existe pas. Peut-être a-t-il été supprimé ?!',
                'tify'));
        }

        // Traitement des actions
        if ( ! $this->current_item()) :
            wp_safe_redirect(add_query_arg($this->db()->getPrimary(),
                $item_id));
            exit;
        elseif (method_exists($this,
            'process_bulk_action_' . $this->current_action())) :
            call_user_func([
                $this,
                'process_bulk_action_' . $this->current_action(),
            ]);
        elseif ( ! empty($_REQUEST['_wp_http_referer'])) :
            wp_redirect(remove_query_arg(['_wp_http_referer', '_wpnonce'],
                $_REQUEST['_wp_http_referer']));
            exit;
        endif;
    }

    /** == Éxecution de l'action - mise à jour == **/
    protected function process_bulk_action_update()
    {
        check_admin_referer($this->get_item_nonce_action($this->current_action(),
            $this->current_item()));

        $data = $this->parse_postdata($_POST);

        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
        $sendback = add_query_arg([
            $this->db()
                 ->getPrimary() => $this->current_item(),
        ], $sendback);
        if (is_wp_error($data)) :
            $sendback = add_query_arg(['message' => $data->get_error_code()],
                $sendback);
        else :
            $this->db()->handle()->record($data);
            $sendback = add_query_arg(['message' => 'updated'], $sendback);
        endif;

        wp_redirect($sendback);
        exit;
    }

    /** == Éxecution de l'action - mise à la corbeille == **/
    protected function process_bulk_action_trash()
    {
        check_admin_referer($this->get_item_nonce_action($this->current_action(),
            $this->current_item()));

        // Traitement de l'élément
        /// Conservation du statut original
        if ($this->db()->hasMeta() && ($original_status = $this->db()
                                                               ->select()
                                                               ->cell_by_id($this->item_id,
                                                                   'status'))) {
            $this->db()
                 ->meta()
                 ->update($this->item_id, '_trash_meta_status',
                     $original_status);
        }
        /// Modification du statut
        $this->db()->handle()->update($this->item_id, ['status' => 'trash']);

        // Traitement de la redirection
        $sendback = remove_query_arg(['action', 'action2'], wp_get_referer());
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
        if ($this->NewItem) {
            return $this->db()->handle()->create($this->DefaultItemArgs);
        }
    }

    /* = CONTROLEUR = */
    /** == Liste des champs == **/
    public function get_fields()
    {
        return $this->Fields;
    }


    /** == Récupération d'une valeur de metadonnée == **/
    public function get_meta($meta_key)
    {
        return $this->DbQuery->get_meta($meta_key);
    }

    /* = AFFICHAGE */
    /** == Affichage des messages de notifications == **/
    public function notifications()
    {
        $output = "";
        if ($notifications = $this->current_notification()) {
            foreach ($notifications as $i => $n) {
                $output .= "<div id=\"{$n['type']}-{$i}\" class=\"notice notice-{$n['type']}" . ($n['dismissible'] ? ' is-dismissible' : '') . "\"><p>{$n['message']}</p></div>";
            }
        }

        echo $output;
    }

    /** == Champs cachés == **/
    public function hidden_fields()
    {

    }

    /** == Notification == **/
    public function notices()
    {
        do_action('alert_notices');
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
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $this->label('datas_item'); ?></h3>
            </div>
            <div class="panel-body">
                <table class="form-table" width="100%">
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
                echo call_user_func([$this, 'field_' . $field_name],
                    $this->item);
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

        if ($field_name === $this->db()->getPrimary()) {
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
        <div id="submitdiv" class="panel panel-default">
            <?php wp_nonce_field($this->get_item_nonce_action('update',
                $this->item->{$this->db()->getPrimary()})); ?>
            <input type="hidden" id="hiddenaction" name="action"
                   value="update"/>
            <input type="hidden" id="user-id" name="user_ID"
                   value="<?php echo get_current_user_id(); ?>"/>
            <input type="hidden" id="referredby" name="referredby"
                   value="<?php echo esc_url(wp_get_referer()); ?>"/>
            <input type="hidden" id="<?php echo $this->db()->getPrimary(); ?>"
                   name="<?php echo $this->db()->getPrimary(); ?>"
                   value="<?php echo $this->item->{$this->db()
                                                        ->getPrimary()}; ?>"/>

            <div class="panel-heading">
                <h3 class="panel-title"><?php _e('Enregistrement',
                        'tify'); ?></h3>
            </div>

            <div class="panel-body">
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
        <div class="updating">
            <input type="submit" class="btn btn-primary"
                   value="<?php _e('Enregistrer', 'tify'); ?>"/>
        </div>
        <?php
    }

    /** == Rendu == **/
    public function render()
    {
        ?>
        <div class="wrap">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="page-header">
                            <?php echo $this->label('edit_item'); ?>
                            <?php if ($this->NewItem) : ?>
                                &nbsp;<a class="btn btn-default"
                                         href="<?php echo $this->BaseUri; ?>"><?php echo $this->label('new_item'); ?></a>
                            <?php endif; ?>
                        </h2>
                    </div>
                </div>

                <?php $this->notices(); ?>

                <form method="post">
                    <?php $this->hidden_fields(); ?>
                    <div class="row">
                        <div class="col-lg-9">
                            <?php $this->form(); ?>
                        </div>

                        <div class="col-lg-3">
                            <?php $this->submitdiv(); ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}