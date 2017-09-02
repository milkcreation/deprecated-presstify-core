<?php
namespace tiFy\Core\Taboox;

class Screen extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// ID de l'écran courant
	protected $ID;
	
	// 
	protected $Hookname;
	
	// Boîte à onglets de l'écran courant
	protected $Box			= array();
	
	// Liste des sections de la boîte à onglets de l'écran courant
	protected $Nodes		= array();
	
	// 
	public $Forms			= array();
	
	// Liste des attributs récupérables
	protected $GetAttrs		= array( 'ID', 'Hookname' );
	
	// Liste des attributs définissables
	protected $SetAttrs		= array( 'ID', 'Hookname', 'Box', 'Nodes', 'Forms' );
	
	// Section de boîte à onglets active
	protected $ActiveNodeID;
				
	public  $nodes_tree,
			$box_render_args = array();
			
	/* = RECUPÉRATION DE DONNÉES = */
	/** == Récupération d'une section de boîte à onglets selon son ID == **/
	private function getNode( $id )
	{	    
	    if( isset( $this->Nodes[ $id ] ) )
			return $this->Nodes[ $id ];
	}
		
	/* = CONTROLEURS = */	
	/** == Création de l'arborescence des sections de boîtes à onglets == **/
	private function create_nodes_tree(){
		// Réinitialisation de l'arbordescence
		$this->nodes_tree = array();

		// Récupération de l'ensemble des noeuds
		$nodes = $this->Nodes;
				
		// Filtrage et Trie global de l'ensemble des noeuds
		foreach( (array) $nodes as $node_id => $args ) :
			if( is_bool( $args['show'] ) ) :
				if( ! $args['show'] ) :
					unset( $nodes[$node_id] );
					continue;
				endif;
			elseif( is_callable( $args['show'] ) && ! call_user_func_array( $args['show'], $this->box_render_args ) ) :
				unset( $nodes[$node_id] );
				continue;
			endif;
			
			$order[$node_id] = $args['order'];
		endforeach;
		
		@array_multisort( $order, $nodes );
		
		// Arborescence des noeuds de niveau 1
		foreach( (array) $nodes as $id => $node ) :
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
		foreach( (array) $nodes as $id => $node ) :
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
		foreach( (array) $nodes as $id => $node ) :
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
		$this->ActiveNodeID = get_user_meta( get_current_user_id(), 'tify_taboox_'. $this->ID, true );

		return $this->nodes_tree;
	}
	
	/** == Définition d'un ordre unique de section de boîte à onglet == **/
	private function node_tree_get_uniq_order( $node_tree, $order ){		
		if( isset( $node_tree[$order] ) )
			return $this->node_tree_get_uniq_order( $node_tree, $order+1 );
		else
			return $order;
	}
	
	/* = AFFICHAGE = */
	/** == Rendu de l'interface d'administration == **/
	public function box_render()
	{
		// Récupération des arguments
		$this->box_render_args = ( func_num_args() ) ? func_get_args() : array();
	
		// Création de l'arborescence des onglets	
		$this->create_nodes_tree( $this->ID );
			
		$output  = "";
		$output .= 	"<div id=\"taboox-container-{$this->ID}\" class=\"taboox-container\">".
						"<h3 class=\"hndle\">".
							"<span>". ( ! empty( $this->Box['title'] ) ? $this->Box['title'] : __( 'Réglages généraux', 'tify' ) ) ."</span>".
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
	public function nodes_tab_render( $depth = 0, $parent = null )
	{		
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
			if( ! $node = $this->getNode( $id ) )
				continue;				
			$output .=	"<li class=\"". ( $id === $this->ActiveNodeID ? 'active' : '' ) ."\">".
							"<a href=\"#tify_taboox-node-{$id}\" aria-controls=\"tify_taboox-node-{$id}\" role=\"tab\" data-toggle=\"tab\" data-current=\"{$this->ID}:{$id}\" >{$node['title']}</a>".
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
			$node = $this->getNode( $id );

			$output .= "<div role=\"tabpanel\" class=\"tab-pane ". ( $id === $this->ActiveNodeID ? 'active' : '' ) ."\" id=\"tify_taboox-node-{$id}\">";
			if( empty( $node['cb'] ) ) :
				$output .= 	"<div class=\"tabbable tabs-top\">";							
				$output .= 		$this->nodes_tab_render( $depth+1, $id );	
				$output .= 		$this->nodes_content_render( $depth+1, $id );						
				$output .= 	"</div>";
			else : 
				$output .= 	"<div class=\"tab-content\">";
				$output .= 		"<div class=\"node-content \">";
				if( ! current_user_can( $node['cap'], $node['id'] ) ) :
					$output .= 		"<h3 class=\"current_user_cannot\">". __( 'Vous n\'êtes pas habilité à administrer cette section', 'tify' ) ."</h3>";
				elseif( ! empty( $this->Forms[$id] ) ) :			
					ob_start();
					call_user_func_array( $this->Forms[$id], $this->box_render_args );
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