<?php 
namespace tiFy\Core\Templates\Traits\Table;

trait Actions
{
    /** == Véfification d'existance d'un action == **/
    public function hasRowAction( $action )
    {
        return isset( $this->RowActions[$action] );
    }
    
    /** == Récupération des actions sur un élément == **/
    public function get_row_actions( $item, $actions, $always_visible = false )
    {
        return $this->row_actions( $this->item_row_actions( $item, $actions ), $always_visible );
    }
    
    /** == Récupération des actions sur un élément == **/
    public function item_row_actions( $item, $actions = array() )
    {        
        $row_actions = array();
        foreach ((array)$actions as $action => $attrs) :
            if (is_int($action))
                $action = $attrs;
            if (! isset($this->RowActions[$action]))
                continue;
            if (is_string($this->RowActions[$action])) :
                $row_actions[$action] = $this->RowActions[$action];
            else :
                $args = $this->row_action_item_parse_args( $item, $action, $attrs);
                $row_actions[$action] = $this->row_action_link( $action, $args );
            endif;
        endforeach;        

        return $row_actions;        
    } 
    
    /** == Traitement des attributs d'un lien d'action sur un élément == **/
    public function row_action_item_parse_args( $item, $action, $args = array() )
    {        
        // Récupération des attributs des actions par défaut
        $defaults = $this->defaults_row_actions( $item );
        if( isset( $defaults[$action] ) ) :
            $args = wp_parse_args( $args, $defaults[$action] );
        endif;
        
        if( ! isset( $args['base_uri'] ) ) :
            $args['base_uri'] = $this->BaseUri;
        endif;

        if( ( $index = $this->getParam( 'ItemIndex' ) ) && ! isset( $args['query_args'][$index] ) && isset( $item->{$index} ) ) :
            $args['query_args'][$index] = $item->{$index};
        endif;
        
        if( isset( $args['nonce'] ) && is_bool( $args['nonce'] ) && ( $args['nonce'] === true ) ) :
            $args['nonce'] = $this->get_item_nonce_action( $action, true, $item );
        endif;
        
        return $args;
    }
    
    /** == Lien de déclenchement d'une action sur un éléments == **/
    public function row_action_link( $action, $args = array() )
    {
        $defaults = array(
            'label'                     => $action,
            'title'                     => '',
            'class'                     => '',
            'link_attrs'                => array(),
            'base_uri'                  => set_url_scheme( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ),
            'query_args'                => array(),
            'nonce'                     => true,
            'referer'                   => true,
            'href'                      => ''
        );
        $args = wp_parse_args( $args, $defaults );
        
        extract( $args );
    
        // Traitement des arguments
        /// Url de destination
        if (!$href)
            $href = add_query_arg( array_merge( $query_args, array( 'action' => $action ) ), $base_uri );
        
        if( $referer ) :
            if( is_bool( $referer ) )
                $referer = $base_uri;
            $href = add_query_arg( array( '_wp_http_referer' => urlencode( wp_unslash( $referer ) ) ), $href ); 
        endif;
        
        if( $nonce )
            $href = wp_nonce_url( $href, $nonce );
        $href = esc_url( $href );
        
        $output  = "";
        $output .= "<a href=\"{$href}\"";
        if( $class )
            $output .= " class=\"{$class}\"";
        if( ! empty( $link_attrs ) ) :
            foreach( $link_attrs as $i => $j ) :
                $output .= " {$i}=\"{$j}\"";
            endforeach;
        endif;
        if( $title )
            $output .= " title=\"{$title}\"";
        $output .= ">{$label}</a>";    
        
        return $output;
    }
    
    /** == == **/
    public function defaults_row_actions( $item )
    {
        $index = $this->getParam( 'ItemIndex', 0 );
        
        return array(
            'activate'          => array(
                'label'             => __( 'Activer', 'tify' ),
                'title'             => __( 'Activation de l\'élément', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'activate', true, $item ),
                'link_attrs'        => array( 'style' => 'color:#006505;' ),
            ),
            'deactivate'        => array(
                'label'             => __( 'Désactiver', 'tify' ),
                'title'             => __( 'Désactivation de l\'élément', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'deactivate', true, $item ),
                'link_attrs'        => array( 'style' => 'color:#D98500;' ),
            ),
            'delete'            => array(
                'label'             => __( 'Supprimer définitivement', 'tify' ),
                'title'             => __( 'Suppression définitive de l\'élément', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'delete', true, $item ),
                'link_attrs'        => array( 'style' => 'color:#a00;' ),
            ),
            'duplicate'         => array(
                'label'             => __( 'Dupliquer', 'tify' ),
                'title'             => __( 'Dupliquer l\'élément', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'duplicate', true, $item ),
            ),            
            'trash'             => array(
                'label'             => __( 'Corbeille', 'tify' ),
                'title'             => __( 'Mise à la corbeille de l\'élément', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'trash', true, $item ),
            ),
            'untrash'           => array(
                'label'             => __( 'Restaurer', 'tify' ),
                'title'             => __( 'Rétablissement de l\'élément', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'untrash', true, $item ),
            ),
            'previewinline'     => array( 
                'label'             => __( 'Afficher le détail', 'Theme' ),
                'title'             => __( 'Voir le détail de la commande', 'tify' ),
                'nonce'             => $this->get_item_nonce_action( 'previewinline', true, $item ),
                'link_attrs'        => array( 'data-index' => ( isset( $item->{$index} ) ? $item->{$index} : 0 ) )
            ),
            'edit'              => $this->row_action_edit_item_parse_args( $item )
        );
    }
    
    /** == Récupération de l'attribut de sécurisation d'une action == **/
    public function get_item_nonce_action( $action, $suffix = null, $item = null )
    {
        $nonce_action = $this->getParam( 'Singular' ) . $action;
        
        if( is_null( $suffix ) )
            return $nonce_action;
        
        if( is_bool( $suffix ) && ( $suffix === true ) ) :
            $index = $this->getParam( 'ItemIndex', 0 );
            if( $item && isset( $item->{$index} ) ) :
                $nonce_action .= $item->{$index};
            endif;
        else :
            $nonce_action .= $suffix;
        endif;           
        
        return $nonce_action;
    }
    
    /** == Lien d'édition d'un élément == **/
    public function get_item_edit_link( $item, $args = array(), $label, $class = '' ) 
    {
        if( $args = $this->row_action_edit_item_parse_args( $item, $args, $label, $class ) ) :
            return $this->row_action_link( 'edit', $args );
        endif;
    }
    
    /** == Traitement des attributs du lien d'édition sur un élément == **/
    public function row_action_edit_item_parse_args( $item, $query_args = array(), $label = '', $class = '' ) 
    {
        if( ( $base_uri = $this->getParam( 'EditBaseUri' ) ) && ( $index = $this->getParam( 'ItemIndex' ) ) && isset( $item->{$index} )  ) :
            return array(
                'label'             => $label ? $label : ( is_null( $label )? null : __( 'Modifier' ) ),
                'class'             => $class,
                'base_uri'          => $base_uri,
                'query_args'        => array_merge( $query_args, array( $index => $item->{$index} ) ),
                'nonce'             => false,
                'referer'           => false
            );
        endif;
    }
                
    /** == Éxecution de l'action - activation == **/
    protected function process_bulk_action_activate()
    {
        $item_ids = $this->current_item();
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'activate', reset( $item_ids ) ) );
        endif;
        
        // Bypass
        if( ! $this->db()->isCol( 'active' ) )
            return;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :                
            /// Modification du statut
            $this->db()->handle()->update( $item_id, array( 'active' => 1 ) );
        endforeach;
        
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'activated', $sendback );    
        
        wp_redirect( $sendback );
        exit;
    }
        
    /** == Éxecution de l'action - désactivation == **/
    protected function process_bulk_action_deactivate()
    {
        $item_ids = $this->current_item();
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'deactivate', reset( $item_ids ) ) );
        endif;
        
        // Bypass
        if( ! $this->db()->isCol( 'active' ) )
            return;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :                
            /// Modification du statut
            $this->db()->handle()->update( $item_id, array( 'active' => 0 ) );
        endforeach;
        
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'deactivated', $sendback );    
        
        wp_redirect( $sendback );
        exit;
    }
    
    /** == Éxecution de l'action - suppression == **/
    protected function process_bulk_action_delete()
    {
        $item_ids = $this->current_item();
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'delete', reset( $item_ids ) ) );
        endif;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :
            $this->db()->handle()->delete_by_id( $item_id );
            if( $this->db()->hasMeta() )
                $this->db()->meta()->delete_all( $item_id );
        endforeach;
        
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'deleted', $sendback );    
        
        wp_redirect( $sendback );
        exit;
    }
    
    /** == Éxecution de l'action - mise à la corbeille == **/
    protected function process_bulk_action_trash()
    {
        $item_ids = $this->current_item();
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'trash', reset( $item_ids ) ) );
        endif;
        
        // Bypass
        if( ! $this->db()->isCol( 'status' ) )
            return;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :
            /// Conservation du statut original
            if( $this->db()->meta() && ( $original_status = $this->db()->select()->cell_by_id( $item_id, 'status' ) ) )
                $this->db()->meta()->update( $item_id, '_trash_meta_status', $original_status );                    
            /// Modification du statut
            $this->db()->handle()->update( $item_id, array( 'status' => 'trash' ) );
        endforeach;
            
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'trashed', $sendback );
                                            
        wp_redirect( $sendback );
        exit;
    }
    
    /** == Éxecution de l'action - restauration d'élément à la corbeille == **/
    protected function process_bulk_action_untrash()
    {
        $item_ids = $this->current_item();    
        
        // Vérification des permissions d'accès
        if( ! wp_verify_nonce( @$_REQUEST['_wpnonce'], 'bulk-'. $this->Plural ) ) :
            check_admin_referer( $this->get_item_nonce_action( 'untrash', reset( $item_ids ) ) );
        endif;
        
        // Bypass
        if( ! $this->db()->isCol( 'status' ) )
            return;
        
        // Traitement de l'élément
        foreach( (array) $item_ids as $item_id ) :
            /// Récupération du statut original
            $original_status = ( $this->db()->meta() && ( $_original_status = $this->db()->meta()->get( $item_id, '_trash_meta_status', true ) ) ) ? $_original_status : $this->db()->getColAttr( 'status', 'default' );                
            if( $this->db()->meta() ) $this->db()->meta()->delete( $item_id, '_trash_meta_status' );
            /// Mise à jour du statut
            $this->db()->handle()->update( $item_id, array( 'status' => $original_status ) );
        endforeach;
            
        // Traitement de la redirection
        $sendback = remove_query_arg( array( 'action', 'action2' ), wp_get_referer() );
        $sendback = add_query_arg( 'message', 'untrashed', $sendback );
            
        wp_redirect( $sendback );
        exit;
    }
}