<?php
namespace tiFy\Core\Ui\Admin\Templates\UserEditForm;

use tiFy\Core\Field\Field;

class UserEditForm extends \tiFy\Core\Ui\Admin\Templates\EditForm\EditForm
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
            'fields',
            [
                'user_login' => __('Identifiant  (obligatoire)', 'tify'),
                'role'       => __('Rôle', 'tify'),
                'first_name' => __('Prénom', 'tify'),
                'last_name'  => __('Nom', 'tify'),
                'nickname'   => __('Pseudonyme (obligatoire)', 'tify'),
                'email'      => __('Adresse de messagerie (obligatoire)', 'tify'),
                'url'        => __('Site web', 'tify'),
                'password'   => __('Nouveau mot de passe', 'tify'),
                'confirm'    => __('Répétez le nouveau mot de passe', 'tify')
            ]
        );
        $this->setDefaultParam('capability', 'edit_users');
        $this->setDefaultParam('create_new_item', false);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Vérification des habilitations d'accès de l'utilisateur à l'interface
     *
     * @return void
     */
    public function check_user_can()
    {
        parent::check_user_can();

        $editable_user = false;
        if ($current_item_index = $this->current_item_index()) :
            $user = get_userdata($current_item_index);
            if(!$roles = $this->getParam('roles')) :
            elseif (\is_wp_error($user)) :
            elseif(!array_intersect($user->roles, $roles)) :
            elseif ($roles) :
                foreach ($roles as $role) :
                    if (user_can($current_item_index, $role)) :
                        $editable_user = true;
                        break;
                    endif;
                endforeach;
            endif;
        else :
            $editable_user = true;
        endif;

        if (!$editable_user) :
            if ($current_item_index) :
                $edit_link = \esc_url(
                    \add_query_arg(
                        'wp_http_referer',
                        \urlencode(
                            \wp_unslash($_SERVER['REQUEST_URI'])),
                            \get_edit_user_link($current_item_index)
                        )
                );
            else :
                $edit_link = admin_url('/user-new.php');
            endif;

            \wp_die(
                '<h1>' . __('Habilitations d\'accès insuffisantes') . '</h1>' .
                '<p><b>' . __('Désolé, mais vous n\'êtes pas autorisé a éditer l\'utilisateur depuis cette interface.', 'tify') . '</b></p>' .
                '<p>' . __('Vous devriez plutôt essayer directement depuis', 'tify') . '&nbsp;' .
                    '<a href="' . $edit_link . '" title="' . __('Éditer l\'utilisateur depuis l\'interface de Wordpress', 'tify') . '">' .
                        __(' l\'interface utilisateurs Wordpress.', 'tify') .
                    '</a>' .
                '</p>'
            );
        endif;
    }

    /**
     * Préparation de l'élément à afficher
     *
     * @return void
     */
    public function prepare_item()
    {
        if ($this->current_item_index()) :
            $this->item = \get_userdata($this->current_item_index());
        else :
            $this->item = (object)$this->get_item_defaults();
        endif;
    }

    /**
     * Récupération des attributs par défaut d'un élément
     *
     * @return object
     */
    public function get_item_defaults()
    {
        $item_defaults = [];
        $item_defaults['ID'] = 0;
        $item_defaults['user_login'] = isset($_POST['user_login']) ? wp_unslash($_POST['user_login']) : '';
        $roles = $this->getParam('roles');
        $item_defaults['role'] = isset($_POST['role']) ? wp_unslash($_POST['role']) : reset($roles);
        $item_defaults['user_email'] = isset($_POST['email']) ? wp_unslash($_POST['email']) : '';
        $item_defaults['firstname'] = isset($_POST['firstname']) ? wp_unslash($_POST['firstname']) : '';
        $item_defaults['lastname'] = isset($_POST['lastname']) ? wp_unslash($_POST['lastname']) : '';
        $item_defaults['nickname'] = isset($_POST['nickname']) ? wp_unslash($_POST['nickname']) : '';
        $item_defaults['user_url'] = isset($_POST['user_url']) ? wp_unslash($_POST['user_url']) : '';

        $item_defaults = \wp_parse_args($this->getParam('item_defaults'), $item_defaults);

        return (object)$item_defaults;
    }

    /**
     * Création d'un nouvel élément
     *
     * @return object
     */
    protected function create_new_item()
    {
        return 0;
    }

    /**
     * Contenu du champ - Login
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_user_login($item)
    {
        $args = [
            'name'  => 'user_login',
            'value' => $item->user_login,
            'attrs' => [
                'id' => 'user_login',
            ],
        ];

        if (!$this->item->ID) :
            $args['attrs']['disabled'] = 'disabled';
        endif;

        return Field::Text($args);
    }

    /**
     * Contenu du champ - Rôle
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_role($item)
    {
        global $wp_roles;

        $args = [
            'name'  => 'role',
            'value' => $item->role,
            'attrs' => [
                'id' => 'role',
            ]
        ];

        if ($roles = $this->getParam('roles')) :
            foreach ($roles as $role) :
                $name = isset($wp_roles->role_names[$role]) ? \translate_user_role($wp_roles->role_names[$role]) : $role;
                $args['options'][esc_attr($role)] = $name;
            endforeach;
        endif;

        return Field::Select($args);
    }

    /**
     * Contenu du champ - Email
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_email($item)
    {
        return Field::Text(
            [
                'name'  => 'email',
                'value' => $item->user_email,
                'attrs' => [
                    'id' => 'email',
                ],
            ]
        );
    }

    /**
     * Contenu du champ - Prénom
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_firstname($item)
    {
        return Field::Text(
            [
                'name' => 'firstname',
                'value' => $item->firstname,
                'attrs' => [
                        'id' => 'firstname'
                ]
            ]
        );
    }

    /**
     * Contenu du champ - Nom
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_lastname($item)
    {
        return Field::Text(
            [
                'name' => 'lastname',
                'value' => $item->lastname,
                'attrs' => [
                    'id' => 'lastname'
                ]
            ]
        );
    }

    /**
     * Contenu du champ - Pseudonyme
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_nickname($item)
    {
        return Field::Text(
            [
                'name'  => 'nickname',
                'value' => $item->nickname,
                'attrs' => [
                    'id' => 'nickname',
                ],
            ]
        );
    }

    /**
     * Contenu du champ - Url
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_url($item)
    {
        return Field::Text(
            [
                'name'  => 'user_url',
                'value' => $item->user_url,
                'attrs' => [
                    'id' => 'user_url',
                ],
            ]
        );
    }

    /**
     * Contenu du champ - Mot de passe
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_password($item)
    {
    ?><input type="password" name="pass1" id="pass1" value="" class="regular-text" autocomplete="off"><?php
    }

    /**
     * Contenu du champ - Confirmation de mot de passe
     *
     * @param object $item Attributs de l'élément courant
     *
     * @return string
     */
    public function field_confirm($item)
    {
    ?><input type="password" name="pass2" id="pass2" value="" class="regular-text" autocomplete="off"><?php
    }
}