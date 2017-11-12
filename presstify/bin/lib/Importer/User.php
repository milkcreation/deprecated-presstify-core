<?php
namespace tiFy\Lib\Importer;

class User extends \tiFy\Lib\Importer\Importer
{        
    /**
     * Cartographie des données principales permises
     * @var array
     */
    protected $AllowedDataMap = array(
        'ID',
        'user_pass',
        'user_login',
        'user_nicename',
        'user_url',
        'user_email',
        'display_name',
        'nickname',
        'first_name',
        'last_name',
        'description',
        'rich_editing',
        'comment_shortcuts',
        'admin_color',
        'use_ssl',
        'user_registered',
        'show_admin_bar_front',
        'role',
        'locale'
    );
    
    /**
     * Type de données prises en charge
     */
    protected $Types         = [
        'data',
        'meta',
        'opt'
    ];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // Désactivation de l'expédition de mail aux utilisateurs
        add_filter('send_password_change_email', '__return_false', 99, 3);
        add_filter('send_email_change_email', '__return_false', 99, 3);
    }

    /**
     * Filtrage de la valeur du mot de passe
     */
    public function filter_data_user_pass($value)
    {
        if($this->getSet('ID', 0, 'data') && $value) :
            return \wp_hash_password($value);
        endif;

        return $value;
    }

    /**
     * Filtrage du role utilisateur
     *
     * @return void|string
     */
    public function filter_data_role($value)
    {
        global $wp_roles;

        if (empty($value) || !$wp_roles->is_role($value)) :
            $this->Notices->addError(__('Le rôle utilisateur d\'affectation n\'existe pas', 'tify'), 'tiFyLibImporterUser-RoleUnavailable');
            return $this->setStop();
        endif;

        return $value;
    }

    /**
     * Insertion des données principales de l'utilisateur
     *
     * @param $userdata Données utilisateur à enregistrer
     * @param $userdata Identifiant de qualification de l'utilisateur existant en base de données
     *
     * @return int
     */
    public function insert_datas($userdata, $user_id)
    {
        $user_id = \wp_insert_user($userdata);
        if(\is_wp_error($user_id)) :
            $this->Notices->addError($user_id->get_error_message(), $user_id->get_error_code(), $user_id->get_error_data());
            $this->setSuccess(false);
            $user_id = 0;
        else :
            $this->Notices->addSuccess(__('L\'utilisateur a été importé avec succès', 'tify'), 'tFyLibImportInsertDatasSuccess');
            $this->setInsertId($user_id);
            $this->setSuccess(true);
        endif;

        return $user_id;
    }
    
    /**
     * Insertion d'une métadonnée
     */
    public function insert_meta($meta_key, $meta_value,  $user_id)
    {
        return \update_user_meta($user_id, $meta_key, $meta_value);
    }
    
    /**
     * Insertion d'une option
     */
    public function insert_option($option_name, $newvalue, $user_id)
    {
        return \update_user_option($user_id, $option_name, $newvalue);
    }
}