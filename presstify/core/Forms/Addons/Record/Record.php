<?php
/**
 * Configuration des champs :
    Standard
    ----------------------------------------
    'fields'    => array(
        [...]
        array(
            [...]
             'addons'        => array(
                'record'        => array(
                    // Active l'affichage de la colonne pour ce champ, le label du champ de formulaire est utilisé comme intitulé de colonne
                    'column'         => true,
                    // Active l'affichage de l'aperçu en ligne pour ce champ, le label du champ de formulaire est utilisé comme intitulé    
                    'preview'        => true
                )
            )
        )
        [...]
    )
    Avancée
    ----------------------------------------
    'fields'    => array(
        [...]
        array(
            [...]
             'addons'        => array(
                'record'        => array(
                    // Active l'affichage de la colonne pour ce champ
                    'column'         => 'intitulé personnalisé',
                    // Active l'affichage de l'aperçu en ligne pour ce champ    
                    'preview'        => 'intitulé personnalisé'
                )
            )
        )
        [...]
    )
 */

/**
 * @Overridable 
 * \Theme\Core\Forms\Addons\Record\Record
 */
namespace tiFy\Core\Forms\Addons\Record;

use tiFy\Core\Db\Db;
use tiFy\Core\Templates\Templates;

class Record extends \tiFy\Core\Forms\Addons\Factory
{
    /* = ARGUMENTS = */
    // Liste des actions à déclencher
    protected $tFyAppActions                = array(
        'admin_init',
        'tify_templates_register',
        'tify_db_register',
        'tify_upload_register'
    ); 
    
    // Définition de l'identifiant
    public $ID = 'record';
    
    // Options de formulaire par défaut
    public $default_form_options     = array(
        'cb'        => 'tiFy\Core\Forms\Addons\Record\ListTable',
        'export'     => false 
    );
    
    // Options de champs par défaut
    public $default_field_options     = array(
        'record'            => true,
        'export'            => false,
        'column'            => false,
        'preview'           => false,
        'editable'          => false
    );
    
    // Argument de base de données
    protected static $DbAttrs = array(
        'install'       => true,
        'name'          => 'tify_forms_record',
        'col_prefix'    => 'record_',
        'columns'       => array(
            'ID'            => array(
                'type'          => 'BIGINT',
                'size'          => 20,
                'unsigned'      => true,
                'auto_increment'=> true,
                'prefix'        => false
            ),
            'form_id'        => array(
                'type'          => 'VARCHAR',
                'size'          => 255,
                'prefix'        => false
            ),
            'session'        => array(
                'type'          => 'VARCHAR',
                'size'          => 32
            ),
            'status'         => array(
                'type'          => 'VARCHAR',
                'size'          => 32,
                'default'       => 'publish'
            ),
            'date'           => array(
                'type'          => 'DATETIME',
                'default'       => '0000-00-00 00:00:00'
            )                                
        ),
        'keys'            => array( 'form_id' => 'form_id' ),
        'meta'            => true
    );
    
    // 
    private static $ExportMenu = false;
        
    /* = CONSTRUCTEUR = */                
    public function __construct() 
    {    
        parent::__construct();

        // Définition des fonctions de callback
        $this->callbacks = array(
            'handle_successfully'    => array( $this, 'cb_handle_successfully' )
        );
    }

    /* = DECLENCHEURS = */
    /** == == **/
    public function admin_init()
    {        
        new Upgrade( 'tify_core_forms_addon_record' );
    }
    
    /** == Définition de l'interface d'administration == **/
    public function tify_templates_register()
    {
        Templates::register(
            'tiFyCoreFormsAddonsRecordMenu',
            array(
                'admin_menu'    => array(
                    'menu_title'    => __( 'Formulaires', 'tify' ),
                    'menu_slug'     => 'tify_forms_record',
                    'icon_url'      => 'dashicons-clipboard'
                ),
                'cb'            => '\tiFy\Core\Forms\Addons\Record\ListTable',
                'db'            => 'tify_forms_record'
            ),
            'admin'
        );
        
        Templates::register(
            'tiFyCoreFormsAddonsRecordListTable',
            array(
                'admin_menu' => array(
                    'parent_slug'   => 'tify_forms_record',
                    'menu_slug'     => 'tify_forms_record',
                    'menu_title'    => __( 'Enregistrements', 'tify' ),
                    'position'      => 1
                )
            ),
            'admin'
        );
        
        if( ! self::$ExportMenu && $this->getFormAttr( 'export', false ) ) :
            self::$ExportMenu = true;
        
            Templates::register(
                'tiFyCoreFormsAddonsRecordExport',
                array(
                    'admin_menu' => array(
                        'parent_slug'    => 'tify_forms_record',
                        'menu_slug'        => 'tify_forms_record_export',
                        'menu_title'    => __( 'Exporter', 'tify' ),
                        'position'      => 2
                    ),
                    'cb'            => '\tiFy\Core\Forms\Addons\Record\Export',
                    'db'            => 'tify_forms_record'
                ),
                'admin'
            );
        endif;
    }
    
    /** == Définition de la base de données (admin uniquement) == **/
    public function tify_db_register()
    {
        Db::register( 
            'tify_forms_record', 
            self::$DbAttrs
        );
    }
    
    /** == Autorisation de téléchargement des fichier d'export == **/
    public function tify_upload_register( $abspath )
    {
        if( isset( $_REQUEST['authorize'] ) && get_transient( $_REQUEST['authorize'] ) )
            tify_upload_register( $abspath );
    }
    
        
    /* = COURT-CIRCUITAGE = */
    /** == Enregistrement des données de formulaire en base == **/
    public function cb_handle_successfully( $handle )
    {            
        $datas = array(
            'form_id'               => $this->form()->getID(),
            'record_session'        => $this->form()->getSession(),
            'record_status'         => 'publish',
            'record_date'           => current_time( 'mysql' ),
            'item_meta'             => $this->form()->getFieldsValues()
        );
        
        // Définition de la base de données (front)
        if( ! Db::has( 'tify_forms_record' ) )
            Db::register( 'tify_forms_record', self::$DbAttrs );
        
        Db::get( 'tify_forms_record' )->handle()->create( $datas );
    } 
}