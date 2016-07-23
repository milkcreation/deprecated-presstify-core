<?php
final class tiFy_QueryMain{
	/* = ARGUMENTS = */
	public	// Paramètres
			$registred = array(),
			$current,
			
			// Référence
			$master;
			
	/* = CONTRUCTEUR = */
	public function __construct( tiFy $master ){
		$this->master = $master;
		
		require_once( __DIR__ .'/inc/helpers.php' );
	} 
}

class tiFy_Query{
	/* = ARGUMENTS = */
	public	// Configuration
			
			// Paramètres
			$query,						// Query vars set by the user
			$query_vars = array(),		// Query vars, after parsing
			$queried_object,			// Holds the data for a single object that is queried.
			$queried_object_id,			// The ID of the queried object.
			$request,					// Get post database query
			
			$items,						// Liste des éléments
			$item_count 	= 0,		// Quantité d'éléments trouvé
			$current_item 	= -1,		// Index de l'élément courant dans la boucle
			$in_the_loop 	= false,	// Chaque fois que la boucle est commencée et que le demandeur est dans cette boucle.
			$item,						// Elément courant.
			$found_items,				// The amount of found posts for the current query
			
			// Références
			$tiFy,
			$db;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Db $db ){
		$this->db = $db;
		
		global $tiFy;
		$this->tiFy = $tiFy;		
		
		if( ! $this->tiFy->Query instanceof tiFy_QueryMain )
			$this->tiFy->Query = new tiFy_QueryMain;
		
		// Déclaration
		if( ! isset( $this->tiFy->Query->registred[$this->db->table] ) )
			$this->tiFy->Query->registred[$this->db->table] = $this;
	}	
	
	/* = PARAMETRAGE = */
	/** == Initiates object properties and sets default values. == **/
	public function init() {
		unset( $this->items );
		unset( $this->query );
		$this->query_vars = array();
		unset( $this->queried_object );
		unset( $this->queried_object_id );
		$this->item_count = 0;
		$this->current_item = -1;
		$this->in_the_loop = false;
		unset( $this->request );
		unset( $this->item );
		$this->found_items = 0;
	}
	
	/** == Sets up the WordPress query by parsing query string. == **/
	public function query( $query = '' ) {
		$this->init();
		$this->query = $this->query_vars = wp_parse_args( $query );
		return $this->get_items();
	}
	
	/** == Retrieve the posts based on query variables. == **/
	public function get_items(){
		if( $this->items = $this->db->get_items( $this->query_vars ) ) :
			$this->item_count = count( $this->items );
			$this->item = reset( $this->items );
		else :			
			$this->item_count = 0;
			$this->items = array();
		endif;
		
		$this->found_items = $this->db->count_items( $this->query_vars );
		
		return $this->items;
	}
		
	/* = CONTRÔLEUR = */
	/** == == **/
	public function get_field( $name ){
		$_name = $this->db->col_prefix.$name;
		if( isset( $this->item->{$_name} ) )
			return $this->item->{$_name};
	}
	
	/** == == **/
	public function get_meta( $meta_key, $single = true ){
		if( ! $this->db->has_meta )
			return;
		
		return $this->db->get_item_meta( $this->item->{$this->db->primary_key}, $meta_key, $single );
	}
	
	/* = BOUCLE = */
	/** == Set up the next post and iterate current post index. == **/
	public function next_item() {
		$this->current_item++;

		$this->item = $this->items[$this->current_item];
		return $this->item;
	}

	/** == Sets up the current item. == **/
	public function the_item() {
		$this->in_the_loop = true;

		if ( $this->current_item == -1 ) // loop has just started
			do_action_ref_array( 'tify_query_loop_start', array( &$this ) );

		$item = $this->next_item();
		//$this->setup_itemdata( $item );
	}

	/** == Whether there are more posts available in the loop. == **/
	public function have_items() {
		if ( $this->current_item + 1 < $this->item_count ) :
			$this->tiFy->Query->current = $this->db->table;
			return true;
		elseif ( $this->current_item + 1 == $this->item_count && $this->item_count > 0 ) :
			do_action_ref_array( 'tify_query_loop_end', array( &$this ) );
			$this->rewind_items();
		endif;

		$this->in_the_loop = false;
		return false;
	}

	/** == Rewind the posts and reset post index. == **/
	public function rewind_items() {
		$this->current_item = -1;
		if ( $this->item_count > 0 )
			$this->item = $this->items[0];
	}
	
	/** == 	== **/
	public function get_adjacent( $previous = true, $args = array() ){
		$args = wp_parse_args( $args, $this->query );
		return $this->db->get_adjacent_item_by_id( $this->item->{$this->db->primary_key}, $previous, $args );		
	}	
}