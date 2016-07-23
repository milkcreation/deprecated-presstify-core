<?php
namespace tiFy\Core\Db;

class Query
{
	/* = ARGUMENTS = */
	private $Db = null;
			
	public	// Paramètres
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
			$found_items;				// The amount of found posts for the current query
	
	/* = CONSTRUCTEUR = */
	public function __construct( Factory $Db, $query = '' )
	{
		$this->Db = $Db;
		
		Db::$Query = $this; 
		
		if ( ! empty( $query ) )
			$this->query( $query );
	}	
	
	/* = PARAMETRAGE = */
	/** == Initiates object properties and sets default values. == **/
	public function init() 
	{
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
	public function query( $query = '' ) 
	{
		$this->init();
		$this->query = $this->query_vars = wp_parse_args( $query );

		return $this->get_items();
	}
	
	/** == Retrieve the posts based on query variables. == **/
	public function get_items()
	{
		if( $this->items = $this->Db->select()->rows( $this->query_vars ) ) :
			$this->item_count = count( $this->items );
			$this->item = reset( $this->items );
		else :			
			$this->item_count = 0;
			$this->items = array();
		endif;
		
		$this->found_items = $this->Db->select()->count( $this->query_vars );
		
		return $this->items;
	}
		
	/* = CONTRÔLEUR = */
	/** == == **/
	public function get_field( $name )
	{
		if( isset( $this->Db->ColMap[$name] ) ) :
			$_name = $this->Db->ColMap[$name];

			return $this->item->{$_name};
		endif;
	}
	
	/** == == **/
	public function get_meta( $meta_key, $single = true )
	{
		if( ! $this->Db->meta() )
			return;
		
		return $this->Db->meta()->get( $this->item->{$this->Db->Primary}, $meta_key, $single );
	}
	
	/* = BOUCLE = */
	/** == Set up the next post and iterate current post index. == **/
	public function next_item() 
	{
		$this->current_item++;

		$this->item = $this->items[$this->current_item];
		return $this->item;
	}

	/** == Sets up the current item. == **/
	public function the_item() 
	{
		$this->in_the_loop = true;

		if ( $this->current_item == -1 ) // loop has just started
			do_action_ref_array( 'tify_query_loop_start', array( &$this ) );

		$item = $this->next_item();
		//$this->setup_itemdata( $item );
	}

	/** == Whether there are more posts available in the loop. == **/
	public function have_items() 
	{
		if ( $this->current_item + 1 < $this->item_count ) :
			return true;
		elseif ( $this->current_item + 1 == $this->item_count && $this->item_count > 0 ) :
			do_action_ref_array( 'tify_query_loop_end', array( &$this ) );
			$this->rewind_items();
		endif;

		$this->in_the_loop = false;
		return false;
	}

	/** == Rewind the posts and reset post index. == **/
	public function rewind_items() 
	{
		$this->current_item = -1;
		if ( $this->item_count > 0 )
			$this->item = $this->items[0];
	}
	
	/** == 	== **/
	public function get_adjacent( $previous = true, $args = array() )
	{
		$args = wp_parse_args( $args, $this->query );
		return $this->Db->select()->adjacent( $this->item->{$this->Db->Primary}, $previous, $args );		
	}	
}