<?php 
namespace tiFy\Core\Templates\Traits\Form;

trait Actions
{
	/** == == **/
	public function defaults_row_actions( $item )
	{
		return array(
			'activate'				=> array(
				'label'			=> __( 'Activer', 'tify' ),
				'title'			=> __( 'Activation de l\'élément', 'tify' ),
				'nonce'			=> $this->get_item_nonce_action( 'activate', $item->{$this->db()->getPrimary()} ),
				'link_attrs'	=> array( 'style' => 'color:#006505;' ),
			),
			'deactivate'			=> array(
				'label'			=> __( 'Désactiver', 'tify' ),
				'title'			=> __( 'Désactivation de l\'élément', 'tify' ),
				'nonce'			=> $this->get_item_nonce_action( 'deactivate', $item->{$this->db()->getPrimary()} ),
				'link_attrs'	=> array( 'style' => 'color:#D98500;' ),
			),
			'duplicate'				=> array(
				'label'			=> __( 'Dupliquer', 'tify' ),
				'title'			=> __( 'Dupliquer l\'élément', 'tify' ),
				'nonce'			=> $this->get_item_nonce_action( 'duplicate', $item->{$this->db()->getPrimary()} )
			),
			'trash'					=> array(
				'label'			=> __( 'Déplacer dans la corbeille', 'tify' ),
				'title'			=> __( 'Déplacer l\'élément dans la corbeille', 'tify' ),
				'nonce'			=> $this->get_item_nonce_action( 'trash', $item->{$this->db()->getPrimary()} )
			),
			'untrash'				=> array(
				'label'			=> __( 'Restaurer', 'tify' ),
				'title'			=> __( 'Rétablissement de l\'élément', 'tify' ),
				'nonce'			=> $this->get_item_nonce_action( 'untrash', $item->{$this->db()->getPrimary()} )
			)			
		);
	}
	
    /** == Récupération de l'attribut de sécurisation d'une action == **/
    public function get_item_nonce_action( $action, $suffix = null )
    {
        $nonce_action = $this->getParam('Singular') . $action;
        
        if( isset( $suffix ) )
            $nonce_action .= $suffix;
        
        return $nonce_action;
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