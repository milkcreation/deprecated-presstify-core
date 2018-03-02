<?php
namespace tiFy\Core\User\Login;

use tiFy\Core\Field\Field;
use tiFy\Core\Control\Notices\Notices;

class Factory extends \tiFy\App\FactoryConstructor
{
    use \tiFy\Lib\Notices\Traits\Notices;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        parent::__construct($id, $attrs);

        // Déclaration des événement
        $this->appAddAction('wp_loaded');
    }

    /**
     *
     */
    public function wp_loaded()
    {
        // Bypass
        if (!$tify_login = self::tFyAppGetRequestVar('tiFyLogin', false)) :
            return;
        endif;

        if ($tify_login !== $this->getId()) :
            return;
        endif;

        $action = self::tFyAppGetRequestVar('action', 'login');
        switch ($action) :
            default :
                break;
            case 'login' :
                $this->appAddFilter('authenticate', 'authenticate', 50, 3);
                $this->appAddAction('wp_login', 'on_login_success', 10, 2);
                $this->_login();
                break;
            case 'logout' :
                $this->appAddAction('wp_logout', 'on_logout_success');
                $this->_logout();
                break;
        endswitch;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Procédure de connection
     *
     * @return void
     */
    private function _login()
    {
        check_admin_referer('tiFyLogin-in-' . $this->getId());

        $secure_cookie = '';

        if (!empty($_POST['log']) && !force_ssl_admin()) :
            $user_name = \sanitize_user($_POST['log']);
            if ($user = get_user_by('login', $user_name)) :
                if (get_user_option('use_ssl', $user->ID)) :
                    $secure_cookie = true;
                    force_ssl_admin(true);
                endif;
            endif;
        endif;

        $reauth = empty($_REQUEST['reauth']) ? false : true;
        $user = \wp_signon([], $secure_cookie);

        if (empty($_COOKIE[LOGGED_IN_COOKIE])) :
            if (headers_sent()) :
                $user = new \WP_Error(
                    'test_cookie',
                    sprintf(
                        __('<strong>ERROR</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.'),
                        __('https://codex.wordpress.org/Cookies' ),
                        __('https://wordpress.org/support/')
                    )
                );
            elseif (isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE])) :
                $user = new \WP_Error(
                    'test_cookie',
                    sprintf(
                        __('<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.'),
                        __('https://codex.wordpress.org/Cookies')
                    )
                );
            endif;
        endif;

        if (!is_wp_error($user) && !$reauth) :
            $redirect_url = '';
            if (!empty($_REQUEST['redirect_to'])) :
                $redirect_url = $_REQUEST['redirect_to'];
            endif;

            if ($redirect_url = $this->get_login_form_redirect($redirect_url, $user)) :
                if ($secure_cookie && false !== strpos($redirect_url, 'wp-admin')) :
                    $redirect_url = preg_replace('|^http://|', 'https://', $redirect_url);
                endif;
            else :
                $redirect_url = admin_url();
            endif;

            wp_safe_redirect($redirect_url);
            exit;
        else :
            $this->_setErrors($user);
        endif;
    }

    /**
     * Procédure de déconnection
     *
     * @return void
     */
    private function _logout()
    {
        check_admin_referer('tiFyLogin-out-' . $this->getId());

        $user = wp_get_current_user();

        wp_logout();

        $redirect_url = '';
        if (!empty($_REQUEST['redirect_to'])) :
            $redirect_url = $_REQUEST['redirect_to'];
        endif;

        if ($redirect_url = $this->get_logout_redirect($redirect_url, $user)) :
        else :
            $redirect_url = remove_query_arg(['action', '_wpnonce', 'tiFyLogin'], set_url_scheme('//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
        endif;

        $redirect_url = add_query_arg('loggedout', true, $redirect_url);

        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Définition des erreurs de soumission aux formulaires
     *
     * @param string|array|\WP_Error $errors Liste des erreurs
     *
     * @return void
     */
    private function _setErrors($errors)
    {
        $errors_map = $this->getAttr('errors_map', []);

        if (is_wp_error($errors)) :
            if ($errors->get_error_codes()) :
                foreach ($errors->get_error_codes() as $code) :
                    if (isset($errors_map[$code])) :
                        $this->addNotice('error', $code, $errors_map[$code]);
                        continue;
                    endif;

                    if(!$messages = $errors->get_error_messages()) :
                        $this->addNotice('error', $code);
                    endif;
                    foreach($messages as $message) :
                        $this->addNotice('error', $code, $message);
                    endforeach;
                endforeach;
            else :
                $this->addNotice('error');
            endif;
        elseif(is_array($errors)) :
            foreach($errors as $code => $message) :
                if (isset($errors_map[$code])) :
                    $this->addNotice('error', $code, $errors_map[$code]);
                else :
                    $this->addNotice('error', $code, $message);
                endif;
            endforeach;
        else :
            $this->addNotice('error', '', (string)$errors);
        endif;
    }

    /**
     * Traitement des attributs de configuration
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return array
     */
    final public function parseAttrs($attrs = [])
    {
        // Définition des attributs de configuration par défaut
        $defaults = [
            'login_form'    => [
                'id'       => 'tiFyLogin-Form--' . $this->getId(),
                'fields'   => ['username', 'password', 'remember', 'submit']
            ],
            'logout_link'   => [],
            'lost_password_link' => [],
            'roles'         => ['subscriber'],
            'redirect_url'  => site_url('/'),
            'attempt'       => -1,
            'errors_map'    => []
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        if (!empty($attrs['login_form'])) :
            if (!empty($attrs['login_form']['fields'])) :
                // Traitement des attributs de configuration des champs de formulaire
                $fields = [];
                foreach ($attrs['login_form']['fields'] as $field_name => $field_attrs) :
                    if (is_int($field_name )) :
                        $field_name = (string)$field_attrs;
                        $field_attrs = [];
                    endif;
                    if (!in_array($field_name, ['username', 'password', 'remember', 'submit'])) :
                        continue;
                    endif;
                    $fields[$field_name] = call_user_func([$this, "parse_login_form_field_{$field_name}_attrs"], $field_attrs);
                endforeach;
                $attrs['login_form']['fields'] = $fields;
            endif;
        endif;

        // Traitement des attributs de configuration du lien de deconnexion
        $attrs['logout_link'] = $this->parse_logout_link_attrs($attrs['logout_link']);

        // Traitement des attributs de configuration de l'interface de mot de passe oublié
        $attrs['lost_password_link'] = $this->parse_lostpassword_link_attrs($attrs['lost_password_link']);

        // Traitement des attributs de la cartographie des messages d'erreurs
        $attrs['errors_map'] = $this->parse_errors_map($attrs['errors_map']);

        return $attrs;
    }

    /**
     * Récupération de la liste des attributs de configuration d'un champ du formulaire d'authentification
     *
     * @param string $field_name Identifiant de quaalification du champ
     *
     * @return array
     */
    final public function getFieldAttrs($field_name)
    {
        if (!in_array($field_name, ['username', 'password', 'remember', 'submit'])) :
            return;
        endif;

        if (!$login_form = $this->getAttr('login_form')) :
            return;
        endif;

        if (!isset($login_form['fields'])) :
            return;
        endif;

        $form_fields = $login_form['fields'];

        if (isset($form_fields[$field_name])) :
            return $form_fields[$field_name];
        endif;
    }

    /**
     * Récupération de la liste des erreurs de soumission aux formulaires
     *
     * @return array
     */
    final public function getErrors()
    {
        $errors = [];

        if (!$messages = $this->getNoticeMessages('error')) :
            return $errors;
        endif;

        foreach ($messages as $message) :
            if ($message) :
                $errors[] = $message;
            else :
                $errors = [];
                $errors[] = __('Erreur lors de la tentative d\'authentification', 'tify');
                break;
            endif;
        endforeach;

        return $errors;
    }

    /**
     * Récupération de la liste des rôles autorisés à se connecter depuis l'interface de login
     *
     * @return array
     */
    final public function getRoles()
    {
        return $this->getAttr('roles');
    }

    /**
     * Vérifie si un gabarit est valide
     *
     * @param string $template Identifiant de qualification (méthode) d'affichage de gabarit
     *
     * @return bool
     */
    final public function isTemplate($template)
    {
        return in_array($template, ['login_form', 'lostpassword_link', 'logout_link']);
    }

    /**
     * Récupération de l'url de déconnection
     *
     * @param string $redirect_url Url de redirection personnalisée
     *
     * @return string
     */
    final public function get_logout_url($redirect_url = '')
    {
        $args = [];
        if($redirect_url) :
            $args['redirect_to'] = $redirect_url;
        endif;
        $args['action'] = 'logout';
        $args['tiFyLogin'] = $this->getId();

        $logout_url = add_query_arg($args, $this->getAttr('redirect_url', site_url('wp-login.php', 'login')));
        $logout_url = wp_nonce_url($logout_url, 'tiFyLogin-out-' . $this->getId());

        return $logout_url;
    }

    /**
     * Affichage d'un gabarit
     *
     * @param string $template login_form|
     * @param array $attrs
     * @param bool $echo
     *
     * @return string
     */
    final public function display($template = 'login_form', $attrs = [], $echo = true)
    {
        if (!$this->isTemplate($template)) :
            return;
        endif;

        $output = call_user_func([$this, $template], $attrs);

        if ($echo) :
            echo $output;
        else :
            return $output;
        endif;
    }

    /**
     * SURCHAGE
     */
    /**
     * Vérification des droits d'authentification d'un utilisateur
     *
     * @param \WP_User $user
     * @param string $username Identifiant de l'utilisateur passé en argument de la requête d'authentification
     * @param string $password Mot de passe en clair passé en argument de la requête d'authentification
     *
     * @return \WP_Error|\WP_User
     */
    public function authenticate($user, $username, $password)
    {
        if (!is_wp_error($user) && ($roles = $this->getRoles()) && !array_intersect($user->roles, $this->getRoles())) :
            $user = new \WP_Error('role_not_allowed');
        endif;

        return $user;
    }

    /**
     * Action lancée en cas de succès de connection
     *
     * @param string  $user_login Identifiant de connection
     * @param \WP_User $user Object WP_User de l'utilisateur connecté
     *
     * @return void
     */
    public function on_login_success($user_login, $user)
    {
        return;
    }

    /**
     * Action lancée en cas de succès de deconnection
     *
     * @return void
     */
    public function on_logout_success()
    {
        return;
    }

    /**
     * Traitement des attributs de configuration du champ Identifiant
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ Identifiant
     *      @see \tiFy\Core\Field\Text\Text
     * }
     *
     * @return array
     */
    public function parse_login_form_field_username_attrs($attrs = [])
    {
        $defaults = [
            'label'         => __('Identifiant', 'tify'),
            'attrs'    => [
                'id'            => 'tiFyLogin-FormFieldUsername--' . $this->getId(),
                'placeholder'   => __('Renseignez votre identifiant', 'tify'),
                'size'          => 20
            ]
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $attrs['name'] = 'log';

        return $attrs;
    }

    /**
     * Traitement des attributs de configuration du champ Mot de passe
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ Mot de passe
     *      @see \tiFy\Core\Field\Password\Password
     * }
     *
     * @return array
     */
    public function parse_login_form_field_password_attrs($attrs = [])
    {
        $defaults = [
            'label'             => __('Mot de passe', 'tify'),
            'attrs'    => [
                'id'            => 'tiFyLogin-FormFieldPassword--' . $this->getId(),
                'placeholder'   => __('Renseignez votre mot de passe', 'tify')
            ]
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $attrs['name'] = 'pwd';

        return $attrs;
    }

    /**
     * Traitement des attributs de configuration du champ Se souvenir de moi
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ Se souvenir de moi
     *      @see \tiFy\Core\Field\Checkbox\Checkbox
     * }
     *
     * @return array
     */
    public function parse_login_form_field_remember_attrs($attrs = [])
    {
        $defaults = [
            'label'         => __('Se souvenir de moi', 'tify'),
            'attrs'         => [
                'id' => 'tiFyLogin-FormFieldRemember--' . $this->getId(),
                'value'         => 0
            ]
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $attrs['name'] = 'remember';

        return $attrs;
    }

    /**
     * Traitement des attributs de configuration du champ bouton de soumission
     *
     * @param array $attrs {
     *      Liste des attributs de configuration du champ bouton de soumission
     *      @see \tiFy\Core\Field\Submit\Submit
     * }
     *
     * @return array
     */
    public function parse_login_form_field_submit_attrs($attrs = [])
    {
        $defaults = [
            'attrs' => [
                'id'    => 'tiFyLogin-FormFieldSubmit--' . $this->getId(),
                'value' => __('Se connecter', 'tify')
            ]
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $attrs['name'] = 'submit';

        return $attrs;
    }

    /**
     * Traitement des attributs de configuration du lien de déconnection
     *
     * @param array $attrs {
     *      Liste des attributs de cartographie des messages d'erreurs
     * }
     *
     * @return array
     */
    public function parse_logout_link_attrs($attrs = [])
    {
        $defaults = [
            'redirect'   => '',
            'text'       => __('Déconnection', 'tify'),
            'class'      => 'tiFyLogin-LogoutLink',
            'title'      => __('se déconnecter', 'tify'),
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Traitement des attributs de mot de passe oublié
     *
     * @param array $attrs {
     *      Liste des attributs de cartographie des messages d'erreurs
     * }
     *
     * @return array
     */
    public function parse_lostpassword_link_attrs($attrs = [])
    {
        $defaults = [
            'redirect' => '',
            'text'     => __('Mot de passe oublié', 'tify')
        ];

        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Traitement des attributs de cartographie des messages d'erreurs
     *
     * @param array $attrs {
     *      Liste des attributs de cartographie des messages d'erreurs
     * }
     *
     * @return array
     */
    public function parse_errors_map($attrs = [])
    {
        $defaults = [
            'empty_username'        => __('le champ de l’identifiant est vide.', 'tify'),
            'empty_password'        => __('Veuillez renseigner le mot de passe.', 'tify'),
            'invalid_username'      => __('Nom d’utilisateur non valide.', 'tify'),
            'incorrect_password'    => __('Ce mot de passe ne correspond pas à l’identifiant fourni.', 'tify'),
            'authentication_failed' => __('Les idenfiants de connexion fournis sont invalides.', 'tify'),
            'role_not_allowed'      => __('Votre utilisateur n\'est pas autorisé à se connecter depuis cette interface.', 'tify')
        ];
        return \wp_parse_args($attrs, $defaults);
    }

    /**
     * Affichage du formulaire d'authentification
     *
     * @param array $attrs Attributs de configuration personnalisés
     *
     * @return string
     */
    public function login_form()
    {
        $attrs = $this->getAttr('login_form');
        $form_name = isset($attrs['name']) ? $attrs['name'] : "tiFyLogin-Form--" . $this->getId();
        $form_id = isset($attrs['id']) ? $attrs['id'] : "tiFyLogin-Form--" . $this->getId();
        $form_class = isset($attrs['class']) ? $attrs['class'] : "tiFyLogin-Form";

        $output  = "";

        // Pré-affichage du formulaire
        $output .= $this->login_form_before();

        // Ouverture du formulaire
        $output .= "<form name=\"{$form_name}\" id=\"{$form_id}\" class=\"{$form_class}\" action=\"\" method=\"post\">";

        // Champs cachés requis
        $output .= Field::Hidden(
            [
                'name' => 'tiFyLogin',
                'value' => $this->getId()
            ]
        );
        $output .= Field::Hidden(
            [
                'name' => '_wpnonce',
                'value' => \wp_create_nonce('tiFyLogin-in-' . $this->getId())]
        );

        // Champs cachés
        $output .= $this->hidden_fields();
        
        // Entête du formulaire
        $output .= $this->login_form_header();
        
        // Corps du formulaire (champs de saisie)
        $output .= $this->login_form_fields();
        
        // Champs de formulaire additionnels
        $output .= $this->login_form_additionnal_fields();
        
        // Mémorisation des informations de connection
        $output .= $this->login_form_field_remember();
        
        // Bouton de soumission filtré
        $output .= $this->login_form_field_submit();
        
        // Pied du formulaire
        $output .= $this->login_form_footer();
        
        // Fermeture du formulaire
        $output .= "</form>";

        // post-affichage du formulaire
        $output .= $this->login_form_after();

        return $output;
    }

    /**
     * Pré-affichage du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_before()
    {
        return  '';
    }

    /**
     * Post-affichage du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_after()
    {
        return  '';
    }

    /**
     * Affichage des champs cachés (requis)
     *
     * @return string
     */
    public function hidden_fields()
    {
        return '';
    }

    /**
     * Affichage de l'entête du formulaire
     *
     * @return string
     */
    public function login_form_header()
    {
        return $this->login_form_errors();
    }

    /**
     * Affichage des informations
     *
     * @return string
     */
    public function login_form_infos()
    {
        if(!$infos = $this->getNoticeMessages('info')) :
            return '';
        endif;

        if (count($infos)>1) :
            $text = "<ol>";
            foreach ($infos as $message) :
                $text .= "<li>{$message}</li>";
            endforeach;
            $text .= "</ol>";
        else :
            $text = reset($infos);
        endif;

        return Notices::display(
            [
                'class' => "tiFyLogin-FormPart tiFyLogin-FormInfos",
                'text'  => $text,
                'type'  => 'info'
            ],
            false
        );
    }

    /**
     * Affichage des notification d'erreurs de soumission au formulaire d'authentification
     *
     * @return string
     */
    public function login_form_errors()
    {
        if(!$errors = $this->getErrors()) :
            return '';
        endif;

        if (count($errors)>1) :
            $text = "<ol>";
            foreach ($errors as $message) :
                $text .= "<li>{$message}</li>";
            endforeach;
            $text .= "</ol>";
        else :
            $text = reset($errors);
        endif;

        return Notices::display(
            [
                'class' => "tiFyLogin-FormPart tiFyLogin-FormErrors",
                'text'  => $text,
                'type'  => 'error'
            ],
            false
        );
    }

    /**
     * Affichage du corps du formulaire
     *
     * @return string
     */
    public function login_form_fields()
    {
        $output = "";

        foreach (['username', 'password'] as $field_name) :
            $output .= call_user_func([$this, "login_form_field_{$field_name}"]);
        endforeach;

        return $output;
    }

    /**
     * Affichage du champ "Identifiant" du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_field_username()
    {
        if(!$attrs = $this->getFieldAttrs('username', false)) :
            return '';
        endif;

        $output  = "";
        $output .= "<p class=\"tiFyLogin-Part tiFyLogin-FormFieldContainer tiFyLogin-FormFieldContainer--username\">";

        if ($attrs['label']) :
            $label = [];
            $label['content'] = $attrs['label'];
            $label['attrs'] = [];
            if (isset($attrs['attrs']['id'])) :
                $label['attrs']['for'] = $attrs['attrs']['id'];
            endif;
            $label['attrs']['class'] = 'tiFyLogin-FormFieldLabel tiFyLogin-FormFieldLabel--username';
            $output .= Field::Label($label);
        endif;

        $output .= Field::Text($attrs);
        $output .= "</p>";

        return $output;
    }

    /**
     * Affichage du champ "Mot de passe" du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_field_password()
    {
        if(!$attrs = $this->getFieldAttrs('password', false)) :
            return '';
        endif;

        $output  = "";
        $output .= "<p class=\"tiFyLogin-Part tiFyLogin-FormFieldContainer tiFyLogin-FormFieldContainer--password\">";

        if ($attrs['label']) :
            $label = [];
            $label['content'] = $attrs['label'];
            $label['attrs'] = [];
            if (isset($attrs['attrs']['id'])) :
                $label['attrs']['for'] = $attrs['attrs']['id'];
            endif;
            $label['attrs']['class'] = 'tiFyLogin-FormFieldLabel tiFyLogin-FormFieldLabel--password';
            $output .= Field::Label($label);
        endif;

        $output .= Field::Password($attrs);
        $output .= "</p>";

        return $output;
    }

    /**
     * Affichage du champ "Se souvenir de moi" du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_field_remember()
    {
        if(!$attrs = $this->getFieldAttrs('remember', false)) :
            return '';
        endif;

        $output  = "";
        $output .= "<p class=\"tiFyLogin-Part tiFyLogin-FormFieldContainer tiFyLogin-FormFieldContainer--remember\">";

        $output .= Field::Checkbox($attrs);

        if ($attrs['label']) :
            $label = [];
            $label['content'] = $attrs['label'];
            $label['attrs'] = [];
            if (isset($attrs['attrs']['id'])) :
                $label['attrs']['for'] = $attrs['attrs']['id'];
            endif;
            $label['attrs']['class'] = 'tiFyLogin-FormFieldLabel tiFyLogin-FormFieldLabel--remember';
            $output .= Field::Label($label, false);
        endif;

        $output .= "</p>";

        return $output;
    }

    /**
     * Affichage du champ "Bouton de soumission" du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_field_submit()
    {
        if(!$attrs = $this->getFieldAttrs('submit', false)) :
            return '';
        endif;

        $output  = "";
        $output .= "<p class=\"tiFyLogin-Part tiFyLogin-FormFieldContainer tiFyLogin-FormFieldContainer--submit\">";
        $output .= Field::Submit($attrs);
        $output .= "</p>";

        return $output;
    }

    /**
     * Affichage des champs additionnels du formulaire d'authentification
     *
     * @return string
     */
    public function login_form_additionnal_fields()
    {
        return '';
    }

    /**
     * Affichage du pied de formulaire
     *
     * @return string
     */
    public function login_form_footer()
    {
        return $this->lostpassword_link();
    }

    /**
     * Affichage du lien vers l'interface de récupération de mot de passe oublié
     *
     * @return string
     */
    public function lostpassword_link()
    {
        $attrs = $this->getAttr('lost_password_link');

        $output =   "<a href=\"" . \wp_lostpassword_url($attrs['redirect']) ."\"" .
                    " title=\"" . __( 'Récupération de mot de passe perdu', 'tify' ) . "\"" .
                    " class=\"tiFyLogin-LostPasswordLink\">" .
                        $attrs['text'] .
                    "</a>";

        return $output;
    }

    /**
     * Affichage du lien de déconnection
     *
     * @param array $custom_attrs Liste des attributs de personnalisation
     *
     * @return string
     */
    public function logout_link($custom_attrs = [])
    {
        $attrs = \wp_parse_args($custom_attrs, $this->getAttr('logout_link', []));
        $url = $this->get_logout_url($attrs['redirect']);

        return "<a href=\"{$url}\" title=\"{$attrs['title']}\" class=\"{$attrs['class']}\">{$attrs['text']}</a>";
    }

    /**
     * Récupération de l'url de redirection du formulaire d'authentification
     *
     * @param string $redirect_url Url de redirection personnalisée
     * @param \WP_User $user Utilisateur courant
     *
     * @return string
     */
    public function get_login_form_redirect($redirect_url = '', $user)
    {
        if (!$redirect_url) :
            $redirect_url = $this->getAttr('redirect_url', admin_url());
        endif;

        return $redirect_url;
    }

    /**
     * Récupération de l'url de redirection du formulaire d'authentification
     *
     * @param string $redirect_url Url de redirection personnalisée
     * @param \WP_User $user Utilisateur courant
     *
     * @return string
     */
    public function get_logout_redirect($redirect_url = '', $user)
    {
        return $redirect_url;
    }
}