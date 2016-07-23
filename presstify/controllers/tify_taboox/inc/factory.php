<?php
class tiFy_Taboox_Factory{
	/* = ARGUMENTS = */
	public  // Paramètres
			$current_screen_id,
			$nodes_tree,
			$current_tab,
			$box_render_args = array(),
			
			// Référence
			$master;
		
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Taboox_Master $master ){
		// Instanciation de la classe de référence		
		$this->master 	= $master;

		// Actions et Filtres Wordpress
		add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
		add_action( 'current_screen', array( $this, 'wp_current_screen' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'wp_add_meta_boxes' ) );
		add_action( 'wp_ajax_tify_taboox_current_tab', array( $this, 'wp_ajax_current_tab' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation de l'interface d'administration == **/
	public function wp_admin_init(){
		// Pré-Création des boîtes à onglets des pages d'édition des post
		foreach( get_post_types() as $post_type ) :
			if( isset( $this->master->nodes[$post_type] ) && ! isset( $this->master->boxes[$post_type] ) ) :
				$this->master->register_box( $post_type );
				foreach( $this->master->nodes[$post_type] as $node )			
					$this->master->init_call_form( $node, $post_type );
			endif;
		endforeach;
	}
	
	/** == Initialisation de l'administration == **/
	public function wp_current_screen( $current_screen ){
		// Bypass
		if( ! in_array( $current_screen->id, array_keys( $this->master->screens ) ) )
			return;
		
		// Définition de l'écran courant
		$this->current_screen_id = $current_screen->id;
				
		// Bypass
		if( ! isset( $this->master->boxes[$this->current_screen_id] ) )
			return;
	
		if( $this->master->boxes[$this->current_screen_id]['env'] === 'option' )
			add_settings_section( $this->current_screen_id, null, array( $this, 'box_render' ), $this->master->boxes[$this->current_screen_id]['page'] );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Mise en file des scripts de l'interface d'administration == **/
	public function wp_admin_enqueue_scripts(){
		// Bypass
		if( ! $this->current_screen_id )
			return;
				
		// Chargement des scripts
		wp_enqueue_style( 'tify_taboox' );
		wp_enqueue_script( 'tify_taboox' );
	}
	
	/** == == **/
	public function wp_add_meta_boxes( $post_type ){
		// Bypass
		if( $this->current_screen_id !==  $post_type )
			return;		
		if( ! isset( $this->master->nodes[$post_type] ) )
			return;
		
		if( $post_type == 'page' ) 
			add_action( 'edit_page_form', array( $this, 'box_render' ) );
		else 
			add_action( 'edit_form_advanced', array( $this, 'box_render' ) );					
	}
	
	/** == Action Ajax - Sauvegarde de l'onglet courant == **/
	public function wp_ajax_current_tab(){
		if( empty( $_POST['current'] ) )
			wp_die(0);
		
		list( $screen_id, $node_id ) = explode( ':', $_POST['current'] );
		
		update_user_meta( get_current_user_id(), 'tify_taboox_'. $screen_id, ! empty( $node_id ) ? $node_id : 0 );
		
		wp_send_json_success( $node_id );
	} 
	
	
	/* = CONTROLEURS = */		
	/** == Récupération d'une section de boîte à onglet == **/
	private function get_node( $id ){
		if( isset( $this->master->nodes[ $this->current_screen_id ][ $id ] ) )
			return $this->master->nodes[ $this->current_screen_id ][ $id ];
	}
	
	/** == == **/
	function create_nodes_tree( ){
		// Bypass
		if( ! isset( $this->master->nodes[ $this->current_screen_id ] ) )
			return;
		// Réinitialisation de l'arbordescence
		$this->nodes_tree = array();
		
		// Récupération de l'ensemble des noeuds
		$nodes = $this->master->nodes[ $this->current_screen_id ];
		
		// Trie global de l'ensemble des noeuds
		foreach( $nodes as $node_id => $args )
			$order[$node_id] = $args['order'];
		@array_multisort( $order, $nodes );
		
		// Arborescence des noeuds de niveau 1
		foreach( $nodes as $id => $node ) :
			if( $node['parent'] )
				continue;
			if( ! isset( $this->nodes_tree[1] ) ) $this->nodes_tree[1] = array();
			$order = $this->node_tree_get_uniq_order( $this->nodes_tree[1], $node['order'] );
			$this->nodes_tree[1][$order] = $id;
			unset( $nodes[$id] );
		endforeach;
		/// Trie des noeud
		if( ! empty( $this->nodes_tree[1] ) )
			ksort( $this->nodes_tree[1], SORT_NUMERIC );
					
		// Arborescence des noeuds de niveau 2
		$order = array();
		foreach( $nodes as $id => $node ) :
			if( ! in_array( $node['parent'], $this->nodes_tree[1] ) )
				continue;
			if( ! isset( $this->nodes_tree[2][$node['parent']] ) ) $this->nodes_tree[2][$node['parent']] = array();
			$order = $this->node_tree_get_uniq_order( $this->nodes_tree[2][$node['parent']], $node['order'] );
			
			$node_tree_2[] = $this->nodes_tree[2][$node['parent']][$order] = $id;
			unset( $nodes[$id] );
		endforeach;
		/// Trie des noeuds
		if( ! empty( $this->nodes_tree[2] ) )
			foreach( (array) array_keys( $this->nodes_tree[2] ) as $parent )		
				ksort( $this->nodes_tree[2][$parent], SORT_NUMERIC );
		
		// Arborescence des noeuds de niveau 3
		$order = array();
		foreach( $nodes as $id => $node ) :
			if( empty( $node_tree_2 ) )
				break;
			if( ! in_array( $node['parent'], $node_tree_2 ) )
				continue;
			if( ! isset( $this->nodes_tree[3][$node['parent']] ) ) $this->nodes_tree[3][$node['parent']] = array();
			$order = $this->node_tree_get_uniq_order( $this->nodes_tree[3][$node['parent']], $node['order'] );
			$this->nodes_tree[3][$node['parent']][$order] = $id;
			unset( $nodes[$id] );
		endforeach;
		/// Trie des noeuds	
		if( ! empty( $this->nodes_tree[3] ) )
			foreach( (array) array_keys( $this->nodes_tree[3] ) as $parent )		
				ksort( $this->nodes_tree[3][$parent], SORT_NUMERIC );
		
		// Récupération de la tabulation active
		$this->current_tab = get_user_meta( get_current_user_id(), 'tify_taboox_'. $this->current_screen_id, true );

		return $this->nodes_tree;
	}
	
	/** == == **/
	private function node_tree_get_uniq_order( $node_tree, $order ){		
		if( isset( $node_tree[$order] ) )
			return $this->node_tree_get_uniq_order( $node_tree, $order+1 );
		else
			return $order;
	}
	
	/** == Rendu de l'interface d'administration == **/
	public function box_render( ){
		// Récupération des arguments
		$this->box_render_args = ( func_num_args() ) ? func_get_args() : array();
		
		// Création de l'arborescence des onglets	
		$this->create_nodes_tree( $this->current_screen_id );	
		$output  = "";
		$output .= 	"<div id=\"taboox-container-{$this->current_screen_id}\" class=\"taboox-container\">".
						"<h3 class=\"hndle\">".
							"<span>". ( ! empty( $this->master->boxes[$this->current_screen_id]['title'] ) ? $this->master->boxes[$this->current_screen_id]['title'] : __( 'Réglages généraux', 'tify' ) ) ."</span>".
						"</h3>";
		$output .= 		"<div class=\"wrapper\">".					
							"<div class=\"back\"></div>".					
								"<div class=\"wrap\">".
									"<div class=\"tabbable tabs-left\">".							
										$this->nodes_tab_render( 1 ).	
										$this->nodes_content_render( 1 ).						
									"</div>".
								"</div>".
						"</div>";
		$output .= 	"</div>";
		
		echo $output;
	}
		
	/** == Rendu des onglets == **/
	public function nodes_tab_render( $depth = 0, $parent = null ){		
		// Bypass	
		if( ! $parent && ! isset( $this->nodes_tree[$depth] ) )
			return;
		if( $parent && ! isset( $this->nodes_tree[$depth][$parent] ) )
			return;
		
		// Récupération des noeuds		
		$nodes = $parent ? $this->nodes_tree[$depth][$parent] : $this->nodes_tree[$depth];

		// Définition de la classe
		if( $depth === 2 )
			$class = 'nav nav-tabs';
		elseif( $depth === 3 )
			$class = 'nav nav-pills';
		else
			$class = 'nav nav-tabs';			
		
		$output  =	"<ul class=\"{$class}\">";
		foreach( $nodes as $id ) :
			if( ! $node = $this->get_node( $id ) )
				continue;				
			$output .=	"<li>".
							"<a class=\"". ( $id === $this->current_tab ? 'current_tab' : '' ) ."\" href=\"#tify_taboox-node-{$id}\" aria-controls=\"tify_taboox-node-{$id}\" role=\"tab\" data-toggle=\"tab\" data-current=\"{$this->current_screen_id}:{$id}\" >{$node['title']}</a>".
						"</li>";
		endforeach;			
		$output  .=	"</ul>";
		
		return $output;
	}
	
	/** == == **/
	public function nodes_content_render( $depth = 0, $parent = null ){		
		// Bypass	
		if( ! $parent && ! isset( $this->nodes_tree[$depth] ) )
			return;
		elseif( $parent && ! isset( $this->nodes_tree[$depth][$parent] ) )
			return;

		// Récupération des noeuds		
		$nodes = $parent ? $this->nodes_tree[$depth][$parent] : $this->nodes_tree[$depth];
		
		$output = "";
		$output .=	"<div class=\"tab-content\">";
		foreach( $nodes as $id ) :	
			$node = $this->get_node( $id );
			
			$output .= "<div role=\"tabpanel\" class=\"tab-pane\" id=\"tify_taboox-node-{$id}\">";
			if( empty( $node['cb'] ) ) :
				$output .= 	"<div class=\"tabbable tabs-top\">";							
				$output .= 		$this->nodes_tab_render( $depth+1, $id );	
				$output .= 		$this->nodes_content_render( $depth+1, $id );						
				$output .= 	"</div>";
			else : 
				$output .= 	"<div class=\"tab-content\">";
				$output .= 		"<div class=\"node-content \">";
				if( ! current_user_can( $node['capability'], $node['id'] ) ) :
					$output .= 		"<h3 class=\"current_user_cannot\">". __( 'Vous n\'êtes pas habilité à administrer cette section', 'tify' ) ."</h3>";
				elseif( is_callable( array( $this->master->_{$node['cb']}, '_form' ) ) ) :					
					ob_start();
					call_user_func_array( array( $this->master->_{$node['cb']}, '_form' ), $this->box_render_args );
					$output .= ob_get_clean();	 
				endif;
				$output .= 		"</div>";
				$output .= 	"</div>";
			endif;
			$output .= "</div>";
		endforeach;
		$output .=	"</div>";
		
		return $output;
	}
}