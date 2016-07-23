<?php
namespace tiFy\Plugins\WebService\Server;

class Posts extends \WP_REST_Posts_Controller
{
	/* = CONSTRUCTEUR = */
	public function __construct( $post_type )
	{
		add_action( 'rest_api_init', array( $this, '_rest_api_init' ) );
		
		$this->post_type = $post_type;		
	}
	
	/** == == **/
	final public function _rest_api_init()
	{
		register_rest_route( 
			\tiFy\Plugins\WebService\Server\Server::getConfig( 'namespace' ), 
			"/{$this->post_type}/", 
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_items' )
			) 
		);
		
		register_rest_field( 
			$this->post_type,
	        'post_status',
	        array(
	            'get_callback'    => array( $this, 'getFieldPostStatus' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
		
		register_rest_field( 
			$this->post_type,
	        'author',
	        array(
	            'get_callback'    => array( $this, 'getFieldAuthor' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}
	
	/* = CHAMPS PERSONNALISES PREDEFINIS = */
	/** == Référence == **/
	public function getFieldPostMeta( $object, $field_name, $request )
	{
		return get_post_meta( $object['id'], $field_name, true );
	}
	
	/** == Attachment == **/
	public function getFieldPostMetaAttachmentID( $object, $field_name, $request )
	{
		if( ! $attachment_id = (int) get_post_meta( $object['id'], $field_name, true ) )
			return false;
		
		return array(
			'id'	=> $attachment_id,
			'url'	=> wp_get_attachment_url( $attachment_id ),
			'icon'	=> wp_get_attachment_image( $attachment_id, array( 80, 60 ), true )
		);
	}
		
	/** == Extrait == **/
	public function getFieldExcerpt( $object, $field_name, $request )
	{
		return get_the_excerpt( $object['id'] );
	}
	
	/** == Status == **/
	public function getFieldPostStatus( $object, $field_name, $request )
	{
		return get_post_status_object( get_post_status( $object['id'] ) )->label;
	}
	
	/** == Auteur == **/
	public function getFieldAuthor( $object, $field_name, $request )
	{
		$author_id =  get_post_field ( 'post_author', $object['id'] );
		
		return array(
			'id'	=> 	$author_id,
			'name'	=>	get_author_name( $author_id )
		);
	}
}