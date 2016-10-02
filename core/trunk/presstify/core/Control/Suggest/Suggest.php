<?php
namespace tiFy\Core\Control\Suggest;

use tiFy\Core\Control\Factory;

class Suggest extends Factory
{
	/* = ARGUMENTS = */
	// Identifiant de la classe
	protected $ID = 'suggest';
		
	/* = INITIALISATION DE WORDPRESS = */
	final public function init()
	{
		wp_register_style( 'tify_control-suggest', $this->Url .'/suggest.css', array( ), '160222' );
		wp_register_script( 'tify_control-suggest', $this->Url .'/suggest.js', array( 'jquery-ui-autocomplete' ), '160222', true );
		
		add_action( 'wp_ajax_tify_control_suggest_ajax', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_control_suggest_ajax', array( $this, 'wp_ajax' ) );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	final public function enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-suggest' );
		wp_enqueue_script( 'tify_control-suggest' );
	}
	
	/* = AFFICHAGE = */
	public static function display( $args = array() )
	{
		static $instance = 0;
		$instance++;
		
		$defaults = array(
			'id' 			=> 'tify_control_suggest-'. $instance,
			'class'			=> '',
			'name'			=> 'tify_control_suggest_term-'. $instance,
			'value'			=> '',
			'attrs'			=> array(),
			'placeholder'	=> __( 'Votre recherche', 'tify' ),
			'button_text'	=> '',
			
			// Options Autocomplete
			/** @see http://api.jqueryui.com/autocomplete/ **/
			'options'		=> array(
				'appendTo'	=> ( isset( $args['id'] ) ) ? '#'.$args['id'] .'_response' : '#tify_control_suggest-'. $instance .'_response',
				'minLength'	=> 2
			),
			// Classe de la liste de selection	
			'picker'		=> ( isset( $args['id'] ) ) ? ''.$args['id'] .'_picker' : '#tify_control_suggest-'. $instance .'_picker',				
				
			// Arguments passés par la requête
			'ajax_action'	=> 'tify_control_suggest_ajax',
			'elements'		=> array( 'title', 'permalink' /*'id', 'thumbnail', 'ico', 'type', 'status'*/ ),
			'query_args'	=> array(),
			'extras'		=> array(),
	
			'echo'			=> true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		$elements	= htmlentities( json_encode( $elements ) );
		$query_args	= htmlentities( json_encode( $query_args ) );
		$extras		= htmlentities( json_encode( $extras ) );
		$options	= htmlentities( json_encode( $options ) );
		
		$output  = "";
		$output .= "<div id=\"{$id}\" class=\"tify_control_suggest". ( $class ? ' '. $class : '' ) ."\"";
		$output .= "data-tify_control_suggest=\"{$ajax_action}\" data-elements=\"{$elements}\" data-query_args=\"{$query_args}\" data-extras=\"{$extras}\" data-options=\"{$options}\" data-picker=\"{$picker}\"";
		foreach( (array) $attrs as $k => $v )
			$output .= " {$k}=\"{$v}\"";
		$output .= ">\n";
		$output .= "\t<input type=\"text\" name=\"{$name}\" placeholder=\"{$placeholder}\" autocomplete=\"off\" value=\"{$value}\">\n";
		$output .= "\t<button type=\"button\">{$button_text}</button>\n";
		$output .= "\t<div class=\"tify_spinner\"><span></span></div>\n";
		$output .= "\t<div id=\"{$id}_response\" class=\"tify_control_suggest_response\"></div>\n";
		$output .= "</div>\n";
		
		if( $echo )
			echo $output;
		else
			return $output;		
	}
	
	/** == Rendu de l'autocomplete == **/
	private function item_render( $args = array() )
	{
		$output  = "";
		$output .= "<a href=\"". ( ! empty( $args['permalink'] ) ? $args['permalink'] : '#' )."\" class=\"". ( isset( $args['ico'] ) ? 'has_ico' : '' )."\">\n";
		unset( $args['permalink'] );
		foreach( $args as $key => $value )
			$output .= "\t<span class=\"{$key}\">{$value}</span>\n";
		$output .= "</a>\n";
	
		return $output;
	}
	
	/** == Récupération de la reponse via Ajax == **/
	final public function wp_ajax()
	{
		// Arguments par defaut à passer en $_POST
		$args = array(
			'term'				=> '',
			'elements'			=> array(),
			'query_args'		=> array(),
			'extras'			=> array()
		);
		extract( $args );
	
		// Valeur de retour par défaut
		$response = array();
			
		// Traitement des arguments de requête
		///
		if( isset( $_POST['term'] ) )
			$term = $_POST['term'];
		
		///
		if( ! empty( $_POST['elements'] ) && is_array( $_POST['elements'] ) )
			$elements = $_POST['elements'];
		
		/// Arguments de requête WP_QUERY
		$query_args['posts_per_page'] =	-1;
		if( isset( $_POST['query_args'] ) && is_array( $_POST['query_args'] ) )
			$query_args = $_POST['query_args'];
		if( ! isset( $query_args['post_type'] ) )
			$query_args['post_type'] = 'any';
		$query_args['s'] = $term;

			// Récupération des posts
		$query_post = new \WP_Query( $query_args );

		while( $query_post->have_posts() ) : $query_post->the_post();
			// Données requises
			$label 			= get_the_title();
			$value 			= get_the_ID();
				
			// Données de rendu
			if( in_array( 'id', $elements ) )
				$id 		= get_the_ID();
			if( in_array( 'title', $elements ) )
				$title		= get_the_title();
			if( in_array( 'permalink', $elements ) )
				$permalink	= get_the_permalink();
			if( in_array( 'thumbnail', $elements ) )
				$thumbnail 	= get_the_post_thumbnail( null, 'thumbnail', false );
			if( in_array( 'ico', $elements ) )
				$ico 		= get_the_post_thumbnail( null, array(50,50), false );
			if( in_array( 'type', $elements ) )
				$type 		= get_post_type_object( get_post_type() )->label;
			if( in_array( 'status', $elements ) )
				$status 	= get_post_status_object( get_post_status() )->label;
				
			// Génération du rendu
			$render = $this->item_render( compact( $elements ) );
				
			// Valeur de retour
			$response[] = compact( 'label', 'value', 'render', $elements );
		endwhile; 
		wp_reset_query();
			
		wp_send_json( $response );
	}
}