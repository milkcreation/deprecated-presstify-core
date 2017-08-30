<?php
namespace tiFy\Components\Duplicate\PostType;

use tiFy\Components\Duplicate\Duplicate;
use tiFy\tiFy;

final class PostType extends \tiFy\App\Factory
{
    /* = ARGUMENTS = */
    // ACTIONS
    protected $tFyAppActions                = array(
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
    
    /* = DECLENCHEURS = */    
    /** == Action de duplication des pages liste == **/
    final public function post_action_tiFyDuplicatePost( $post_id )
    {
        // Bypass
        if( empty( $post_id ) )
            return new \WP_Error( __( 'Le contenu original est indisponible', 'tify' ), 'tiFyDuplicatePost-UnavailableInput' );
                
        check_admin_referer( 'tify_duplicate_post:'. $post_id );
                
        // Duplication de l'élément
        $results = $this->duplicate( $post_id );
        
        if( is_wp_error( $results ) )
            wp_die( $results->get_error_message() );
        
        if( ! $sendback = wp_get_referer() ) :
            $sendback = admin_url( 'edit.php' );
    		if( $post_type = get_post_type( $post_id ) ) :
    			$sendback = add_query_arg( 'post_type', $post_type, $sendback );
    		endif;
        endif;
        
        wp_redirect( add_query_arg( array( 'message' => 'tiFyDuplicatedPost' ), $sendback ) );
	    exit();
    }

    /** == Notification de l'interface d'adminitration == **/
    final public function admin_notices()
    {
        if( empty( $_REQUEST['message'] ) || ( $_REQUEST['message'] != 'tiFyDuplicatedPost' ) )
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
        if( ! $post_type_attrs = Duplicate::getPostType( $post->post_type ) )
            return $actions;
        
        if( empty( $post_type_attrs['row_actions'] ) )
            return $actions;             

        $post_type_object = get_post_type_object( $post->post_type );
        
        $actions['tiFyDuplicatePost'] = "<a href=\"". wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'tiFyDuplicatePost' ), admin_url( sprintf( $post_type_object->_edit_link, $post->ID ) ) ), 'tify_duplicate_post:'. $post->ID ) ."\" title=\"". __( 'Dupliquer le contenu', 'tify' ) ."\" class=\"tiFyDuplicatePost-rowAction\">". __( 'Dupliquer', 'tify' ) ."</a>";

        return $actions;
    }

    /* = CONTROLEURS = */    
    /** == Duplication de post == **/
    private function duplicate( $post_id )
    {                    
        // Définition du type de post
        $post_type = get_post_type( $post_id );
        
        // Bypass
        if( ! $post_type_attrs = Duplicate::getPostType( $post_type ) )
            return new \WP_Error( __( 'Le type du contenu original n\'est pas autorisé à être dupliqué', 'tify' ), 'tiFyDuplicatePost-InputTypeNotAllowed' );
                
        // Instanciation du contrôleur
        $className = '\tiFy\Components\Duplicate\PostType\Factory';
        $overridePath[] = "\\". self::getOverrideNamespace() ."\\Components\\Duplicate\\PostType\\". ucfirst( $post_type );
        
        $Cloner = self::loadOverride( $className, $overridePath );
        $Cloner->setInput( $post_id, $post_type_attrs['meta'] );            
        
        return $Cloner->duplicate( $post_type_attrs ); 
    }
}