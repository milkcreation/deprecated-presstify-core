<?php
namespace tiFy\Inherits\Importer;

class User extends \tiFy\Inherits\Importer\Importer
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
    protected $DataType         = array( 'data', 'metadata', 'option' );

    /**
     * CONSTRUCTEUR
     */
    public function __construct( $inputdata = array(), $attrs = array() )
    {
        parent::__construct( $inputdata, $attrs );
        
        // Désactivation de l'expédition de mail aux utilisateurs
        add_filter( 'send_password_change_email', '__return_false', 99, 3 );
        add_filter( 'send_email_change_email', '__return_false', 99, 3 );
    }
       
    /**
     * Insertion des données principales
     */
    final public function insert_datas( $userdata )
    {
        return wp_insert_user( $userdata );
    }
    
    /**
     * Insertion d'une métadonnée
     */
    final public function insert_meta( $user_id, $meta_key, $meta_value )
    {
        return update_user_meta( $user_id, $meta_key, $meta_value );      
    }
    
    /**
     * Insertion d'une option
     */
    final public function insert_option( $user_id, $option_name, $newvalue )
    {
        return update_user_option( $user_id, $option_name, $newvalue );      
    }
    
    /**
     * Filtrage de la valeur du mot de passe
     */
    public function filter_data_user_pass( $value )
    {
        if( $this->getData( 'ID' ) && $value )
            return wp_hash_password( $value ); 
    }
}