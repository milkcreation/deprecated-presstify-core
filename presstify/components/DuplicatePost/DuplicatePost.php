<?php
namespace tiFy\Components\DuplicatePost;

use tiFy\tiFy;
use \tiFy\Lib\File;

final class DuplicatePost extends \tiFy\Environment\Component
{
    /* = ARGUMENTS = */
    // ACTIONS
    protected $CallActions                = array(
        'wp_ajax_tiFyDuplicatePost',
        'post_action_tiFyDuplicatePost',
        'admin_notices'
    );
    
    // FILTRES
    /// Liste des actions à déclencher
    protected $CallFilters                = array(
        'admin_enqueue_scripts',
        'page_row_actions',
        'post_row_actions'
    );

    // Fonctions de rappel des filtres
    protected $CallFiltersFunctionsMap    = array(
        'page_row_actions'    => 'row_actions',
        'post_row_actions'    => 'row_actions'    
    );

    // Ordres de priorité d'exécution des filtres
    protected $CallFiltersPriorityMap    = array(
        'page_row_actions' => 99,    
        'post_row_actions' => 99
    );

    // Nombre d'arguments autorisés
    protected $CallFiltersArgsMap        = array(
        'page_row_actions' => 2,
        'post_row_actions' => 2
    );

    // Configuration
    /// Type de post
    private static $PostType        = array();

    /// Configuration
    private static $PostTypeAttrs   = array();

    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();
        
        if( $post_types = self::getConfig( 'post_type' ) ) :
        
            self::$PostType = array_keys( $post_types );
        
            foreach( $post_types as $pt => $attrs ) :
                self::$PostTypeAttrs[$pt] = $this->parseAttrs( $attrs );
            endforeach;
        endif;       
    }

    /* = DECLENCHEURS = */
    /** == Action AJAX de duplication == **/
    final public function wp_ajax_tiFyDuplicatePost()
    {
        // Bypass
        if( empty( $_REQUEST['post'] ) )
            wp_send_json_error( __( 'Le contenu original est indisponible', 'tify' ), 'tiFyDuplicatePost-UnavailableSource' );
        
        $post_id =  (int) $_REQUEST['post'];
        
        // Vérification de sécurité
        check_ajax_referer( 'tify_duplicate_post:'. $post_id );
        
        // Duplication de l'élément
        $results = $this->duplicatePost( $post_id );
        
        // Retour
        wp_send_json( $Ctrl->duplicate( self::$PostTypeAttrs[$post_type] ) );        
    }
    
    /** == Action PHP de duplication == **/
    final public function post_action_tiFyDuplicatePost( $post_id )
    {
        // Bypass
        if( empty( $post_id ) )
            return new \WP_Error( __( 'Le contenu original est indisponible', 'tify' ), 'tiFyDuplicatePost-UnavailableSource' );
                
        check_admin_referer( 'tify_duplicate_post:'. $post_id );
                
        // Duplication de l'élément
        $results = $this->duplicatePost( $post_id );
        
        if( is_wp_error( $results ) )
            wp_die( $results->get_error_message() );
        
        if( ! $sendback = wp_get_referer() ) :
            $sendback = admin_url( 'edit.php' );
    		if( $post_type = get_post_type( $post_id ) ) :
    			$sendback = add_query_arg( 'post_type', $post_type, $sendback );
    		endif;
        endif;
        
        wp_redirect( add_query_arg( array( 'tiFyDuplicatedPost' => 1 ), $sendback ) );
	    exit();
    }
        
    /** == Mise en file des scripts de l'interface d'administration == **/
    final public function admin_enqueue_scripts()
    {
        if( ( get_current_screen()->base !== 'edit' ) || ! in_array( get_current_screen()->post_type, self::$PostType ) )
            return;
        
        wp_enqueue_script( 'tiFyComponentsDuplicatePost', self::getUrl( get_class() ) .'/DuplicatePost.js', array( 'jquery' ), 170216, true );          
    }

    /** == Notification de l'interface d'adminitration == **/
    final public function admin_notices()
    {
        if( empty( $_REQUEST['tiFyDuplicatedPost'] ) )
            return;
    ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Le contenu a été dupliqué avec succès', 'tify' ); ?></p>
        </div>
    <?php   
    }
    
    /** == Actions de page liste == **/
    final public function row_actions( $actions, $post )
    {       
        if( ! in_array( $post->post_type, self::$PostType ) )
            return $actions;
        if( empty( self::$PostTypeAttrs[$post->post_type]['row_actions'] ) )
            return $actions;             
        
        $post_type_object = get_post_type_object( $post->post_type );
        
        $actions['tiFyDuplicatePost'] = "<a href=\"". wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'tiFyDuplicatePost' ), admin_url( sprintf( $post_type_object->_edit_link, $post->ID ) ) ), 'tify_duplicate_post:'. $post->ID ) ."\" title=\"". __( 'Dupliquer le contenu', 'tify' ) ."\" class=\"tiFyDuplicatePost-rowAction\">". __( 'Dupliquer', 'tify' ) ."</a>";

        return $actions;
    }

    /* = CONTROLEURS = */
    /** == Traitement des arguments de duplication == **/
    private function parseAttrs( $attrs = array() )
    {
        $attrs = wp_parse_args(
            $attrs,
            array(
                'cb'            => false,
                'row_actions'   => true,
                'blog'          => false,
                'meta'          => false
            )
        );
        
        if( empty( $attrs['blog'] ) ) :
            $attrs['blog'] = array( get_current_blog_id() );
        else :
            $attrs['blog'] = (array) $attrs['blog'];
            // Force le type int des IDs
            $attrs['blog'] = array_map( 'absint', $attrs['blog'] );
            // Dédoublonnage des valeurs
            $attrs['blog'] = array_unique( $attrs['blog'] );
            // Suppression des données vide
            $attrs['blog'] = array_filter( $attrs['blog'] );
        endif;        
        
        return $attrs;
    }
    
    /** == Duplication de post == **/
    private function duplicatePost( $post_id )
    {                    
        // Définition du type de post
        $post_type = get_post_type( $post_id );
        
        // Bypass
        if( ! isset( self::$PostTypeAttrs[$post_type] ) )
            return new \WP_Error( __( 'Le type du contenu original n\'est pas autorisé à être dupliqué', 'tify' ), 'tiFyDuplicatePost-SourceTypeNotAllowed' );
        
        
        // Instanciation du contrôleur
        $className = '\tiFy\Components\DuplicatePost\Factory';        
        if( self::$PostTypeAttrs[$post_type]['cb'] ) :
            $overridePath[] = self::$PostTypeAttrs[$post_type]['cb'];
        endif;
        $overridePath[] = "\\". tiFy::getConfig( 'namespace' ) ."\\tiFy\\Components\\DuplicatePost\\PostType\\". ucfirst( $post_type );
        
        $Cloner = self::loadOverride( $className, $overridePath );
        $Cloner->setSource( $post_id, self::$PostTypeAttrs[$post_type]['meta'] );            
        
        return $Cloner->duplicate( self::$PostTypeAttrs[$post_type] ); 
    }
}