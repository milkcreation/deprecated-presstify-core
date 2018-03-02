<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\User;

class User extends \tiFy\Core\Forms\Addons\Factory
{
    /**
     * Identifiant unique de qualification de l'addon
     * @var string
     */
    public $ID = 'user';

    /**
     * Liste des options (paramètres) par défaut du formulaire
     * @var array
     */
    public $default_form_options = [
        'roles' => []
    ];

    /**
     * Liste des options (paramètres) par défaut des champs de formulaire
     * Liste des champs natifs : user_login (requis)|role|first_name|last_name|nickname|display_name|user_email (requis)|user_url|description|user_pass
     * @var array
     */
    public $default_field_options = [
        'userdata' => false,
    ];

    /**
     * Liste des rôles concernés
     * @var array
     */
    private $Roles = [];

    /// Identifiant de l'utilisateur
    protected $UserID             = 0;
    
    /// Page d'édition du profil utilisateur
    private $isProfile            = false;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Définition des fonctions de callback
        $this->callbacks = [
            'form_set_current'      => [$this, 'cb_form_set_current'],
            'handle_check_field'    => [$this, 'cb_handle_check_field'],
            'handle_submit_request' => [$this, 'cb_handle_submit_request']
        ];

        parent::__construct();

        // Définition de l'id de l'utilisateur à éditer
        $this->UserID = get_current_user_id();
        $this->isProfile = $this->getUserID() ? true : false;
    }

    /**
     * COURT-CIRCUITAGE
     */
    /**
     * Initialisation du formulaire
     *
     * @param \tiFy\Core\Forms\Form\Form $Form
     *
     * @return void
     */
    public function cb_form_set_current($Form)
    {
        //
        /**
         * Mise à jour des rôles
         * @deprecated Le rôle doit être crée par le biais de \tiFy\Core\User\Role
         foreach ($this->getRoles() as $name => $attrs) :
            // Création du rôle
            if (!$role = get_role($name)) :
                $role = add_role($name, $attrs['display_name']);
            endif;

            // Mise à jour des habilitations
            if (isset($attrs['capabilities'])) :
                foreach ((array)$attrs['capabilities'] as $cap => $grant) :
                    if (!isset($role->capabilities[$cap]) || ($role->capabilities[$cap] != $grant)) :
                        $role->add_cap($cap, $grant);
                    endif;
                endforeach;
            endif;
        endforeach;
        */
        // Modification des attributs de champs
        foreach ($this->fields() as $field) :
            // Bypass
            if (!$userdata = $this->getFieldAttr($field, 'userdata', false)) {
                continue;
            }
            if ($userdata === 'user_pass') :
                $field->setAttr('onpaste', 'off');
                $field->setAttr('autocomplete', 'off');
            endif;
        endforeach;
    }

    /**
     * Vérification d'intégrité d'un champ
     *
     * @param array $errors
     * @param \tiFy\Core\Forms\Form\Field $field
     */
    public function cb_handle_check_field(&$errors, $field)
    {
        // Bypass
        if (!$userdata = $this->getFieldAttr($field, 'userdata', false)) :
            return;
        endif;

        if (!$this->isNativeUserData($userdata)) :
            return;
        endif;

        if (!in_array($userdata, ['user_login', 'user_email', 'role'])) :
            return;
        endif;

        switch ($userdata) :
            /// Identifiant de connexion
            case 'user_login' :
                if (!$this->isProfile() && get_user_by('login', $field->getValue())) :
                    $errors[] = __('Cet identifiant est déjà utilisé par un autre utilisateur', 'tify');
                endif;

                if (is_multisite()) :
                    // Lettres et/ou chiffres uniquement
                    $user_name = $field->getValue();
                    $orig_username = $user_name;
                    $user_name = preg_replace('/\s+/', '', sanitize_user($user_name, true));
                    if ($user_name != $orig_username || preg_match('/[^a-z0-9]/', $user_name)) :
                        $_errors[] = __('L\'identifiant de connexion ne devrait contenir que des lettres minuscules (a-z) et des chiffres',
                            'tify');
                    endif;

                    // Identifiant réservés
                    $illegal_names = get_site_option('illegal_names');
                    if (!is_array($illegal_names)) :
                        $illegal_names = ['www', 'web', 'root', 'admin', 'main', 'invite', 'administrator'];
                        add_site_option('illegal_names', $illegal_names);
                    endif;
                    if (in_array($user_name, $illegal_names)) :
                        $_errors[] = __('Désolé, cet identifiant de connexion n\'est pas permis', 'tify');
                    endif;

                    // Identifiant réservés personnalisés
                    $illegal_logins = (array)apply_filters('illegal_user_logins', []);
                    if (in_array(strtolower($user_name), array_map('strtolower', $illegal_logins))) :
                        $_errors[] = __('Désolé, cet identifiant de connexion n\'est pas permis', 'tify');
                    endif;

                    // Longueur minimale
                    if (strlen($user_name) < 4)
                        $_errors[] = __('L\'identifiant de connexion doit contenir au moins 4 caractères', 'tify');

                    // Longueur maximale
                    if (strlen($user_name) > 60)
                        $_errors[] = __('L\'identifiant de connexion ne doit pas contenir plus de 60 caractères',
                            'tify');

                    // Lettres obligatoire
                    if (preg_match('/^[0-9]*$/', $user_name))
                        $_errors[] = __('L\'identifiant de connexion doit aussi contenir des lettres', 'tify');
                endif;

                break;
            /// Email
            case 'user_email' :
                if (!$this->isProfile() && get_user_by('email', $field->getValue())) :
                    $errors[] = __('Cet email est déjà utilisé par un autre utilisateur', 'tify');
                endif;

                break;
            /// Role    
            case 'role' :
                if (!$this->hasRole($field->getValue())) :
                    $_errors[] = __('L\'attribution de ce rôle n\'est pas autorisée.', 'tify');
                endif;

                break;
        endswitch;
    }

    /** == Traitement du formulaire == **/
    public function cb_handle_submit_request( $handle )
    {
        $request_data = array( 
            'user_login'                => '', 
            'role'                      => '', 
            'first_name'                => '', 
            'last_name'                 => '', 
            'nickname'                  => '', 
            'display_name'              => '', 
            'user_email'                => '', 
            'user_url'                  => '',  
            'description'               => '',  
            'user_pass'                 => '',
            'show_admin_bar_front'      => false
        );
        
        // Récupération des données utilisateurs dans les variables de requête
        foreach( $this->form()->getFieldsValues( true ) as $slug => $value ) :
            if(  ! $userdata = $this->getFieldAttr( $slug, 'userdata' ) )
                continue;
            if( ! isset( $request_data[ $userdata ] ) )
                continue;
            
            $request_data[ $userdata ] = $value;
        endforeach;    
        
        // Traitement de l'identifiant et récupération des données utilisateur existante
        if( ! $request_data[ 'user_login' ] && ( $user = get_userdata( $this->UserID ) ) ) :
            foreach( $request_data as $data => $value ) :
                if( in_array( $data, array( 'user_pass' ) ) ) :
                    continue;
                endif;
                
                if( empty( $value ) ) :
                    $request_data[$data] = $user->{$data};
                endif;
            endforeach;
        endif;

        // Traitement du rôle
        if (!$request_data['role']) :
            if (is_user_logged_in()) :
                $request_data['role'] = current(wp_get_current_user()->roles);
            elseif ($names = $this->getRoleNames()) :
                $request_data['role'] = current($names);
            else :
                $request_data['role'] = get_option('default_role', 'subscriber');
            endif;
        endif;

        // Traitement de l'affichage de la barre d'administration
        if( $this->hasRole( $request_data[ 'role' ] ) ) :
            $show_admin_bar_front =  ! $this->getRoleAttr( $request_data[ 'role' ], 'show_admin_bar_front', false ) ? 'false' : '';
        endif;
        
        // Traitement de l'enregistrement de l'utilisateur
        /// Mise à jour
        if( $current_user = get_userdata( $this->getUserID() ) ) :
            if( empty( $request_data['user_pass'] ) )
                unset( $request_data['user_pass'] );
            if( empty( $request_data['role'] ) )
                unset( $request_data['role'] );

            $exits_data = (array) get_userdata( $current_user->ID )->data;
            unset( $exits_data['user_pass'] );            
            $request_data = wp_parse_args( $request_data, $exits_data );
            $user_id     = wp_update_user( $request_data );
            
        /// Création    
        else :
            if( is_multisite() ) :
                $user_details = wpmu_validate_user_signup( $request_data['user_login'], $request_data['user_email'] );
                if ( is_wp_error( $user_details[ 'errors' ] ) && ! empty( $user_details[ 'errors' ]->errors ) ) :
                    return $handle->addError( $user_details[ 'errors' ]->get_error_message() );    
                endif;
            endif;

            $user_id = wp_insert_user( $request_data );
        endif;
        
        // Traitement des metadonnées et options utilisateur
        if( ! is_wp_error( $user_id ) ) :
            $this->setUserID( $user_id );

            // Création ou modification des informations personnelles
            foreach( $this->form()->getFieldsValues( true ) as $slug => $value ) :
                if(  ! $userdata = $this->getFieldAttr( $slug, 'userdata' ) )
                    continue;
                if(  $userdata === 'meta' ) :
                    update_user_meta( $this->getUserID(), $slug, $value );
                elseif( $userdata === 'option' ) :
                    update_user_option( $this->getUserID(), $slug, $value );
                endif;
            endforeach;            
        else :
            return $handle->addError( $user_id->get_error_message() );
        endif;
    }
        
    /* = PARAMETRES = */
    /** == Initialisation de l'ID Utilisateur == **/
    final protected function setUserID( $user_id )
    {
        $this->UserID = $user_id;
    }
    
    /** == Récupération de l'ID Utilisateur == **/
    final public function getUserID()
    {
        return $this->UserID;
    }
    
    /** == Récupération de l'ID Utilisateur == **/
    final protected function isProfile()
    {
        return $this->isProfile;
    }

    /**
     * Récupération de la liste des attributs de configuration d'un rôle
     *
     * @return array
     */
    final protected function getRoles()
    {
        if ($this->Roles) :
            return $this->Roles;
        endif;

        $_roles = [];
        if ($roles = (array)$this->getFormAttr('roles', [])) :
            foreach ($roles as $name => $attrs) :
                if (is_int($name) && is_string($attrs)) :
                    $name = $attrs; $attrs= [];
                endif;
                $_roles[$name] = array_merge(
                    [
                        'capabilities'              => [],
                        'show_admin_bar_front'      => false
                    ],
                    $attrs
                );
            endforeach;
        endif;

        return $this->Roles = $_roles;
    }
    
    /** == Récupération des rôles == **/
    final protected function getRoleNames()
    {
        if( $roles = $this->getRoles() )
            return array_keys( $roles );
    }    

    /** == Vérifie l'existance d'un rôle == **/
    final protected function hasRole( $name )
    {
        return array_key_exists( $name, $this->getRoles() );
    }
    
    /** == Récupére l'attribut d'un rôle == **/
    final protected function getRoleAttr( $name, $attr, $default = '' )
    {
        $roles = $this->getRoles();    
            
        if( isset( $roles[$name][$attr] ) )
            return $roles[$name][$attr];
        
        return $default;
    }
    
    /** == Vérifie si une donnée utilisateur native == **/
    final protected function isNativeUserData( $userdata )
    {
        return in_array(
            $userdata,
            [
                'user_login',
                'role',
                'first_name',
                'last_name',
                'nickname',
                'display_name',
                'user_email',
                'user_url',
                'description',
                'user_pass'
            ]
        );
    }
}