<?php
/* = HELPER = */
/** 
 wp_enqueue( 'tify_suggest' ); 
 **/
function tify_suggest( $args = array() ){
	static $instance;
	$instance ++;
	
	$defaults = array(
		'id' 			=> 'tify_suggest-'. $instance,
		'class'			=> '',
		'name'			=> 'tify_suggest_term-'. $instance,
		'placeholder'	=> __( 'Votre recherche', 'tify' ),
		'button_text'	=> '',
		
		// Arguments passés par la requête
		'ajax_action'	=> 'tify_suggest_post',
		'elements'		=> array(),		// 		
		'query_args'	=> array(),
		'extras'		=> array(),		
		
		'echo'			=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );
	
	$elements	= htmlentities( json_encode( $elements ) );
	$query_args	= htmlentities( json_encode( $query_args ) );
	$extras		= htmlentities( json_encode( $extras ) );
	
	$output  = "";
	$output .= "<div id=\"{$id}\" class=\"tify_suggest". ( $class ? $class : '' ) ."\"";
	$output .= "data-tify_suggest=\"{$ajax_action}\" data-elements=\"{$elements}\" data-query_args=\"{$query_args}\" data-extras=\"{$extras}\">\n";
	$output .= "\t<form method=\"get\" action=\"\">\n";
	$output .= "\t\t<input type=\"text\" name=\"{$name}\" placeholder=\"{$placeholder}\" autocomplete=\"off\">\n";			
	$output .= "\t\t<button type=\"button\">{$button_text}</button>\n";
	$output .= "\t\t<div class=\"tify_spinner\"><span></span></div>\n";
	$output .= "\t\t<div id=\"{$id}_response\" class=\"tify_suggest_response\"></div>\n";								
	$output .= "\t</form>\n";
	$output .= "</div>\n";
	
	if( $echo )
		echo $output;
	else
		return $output;	
}

/** == Rendu de l'autocomplete == **/
function tify_suggest_item_render( $args = array() ){
	$output  = "";
	$output .= "<a href=\"". ( ! empty( $args['permalink'] ) ? $args['permalink'] : '#' )."\" class=\"". ( ! empty( $args['ico'] ) ? 'has_ico' : '' )."\">\n";
	unset( $args['permalink'] );
	foreach( $args as $key => $value )		
		$output .= "\t<span class=\"{$key}\">{$value}</span>\n";
	$output .= "</a>\n";
	
	return $output;
}

class tiFy_Suggest{
	/* = ARGUMENTS = */			
	public	// Configuration
			$dir, $uri;
			
	/* = CONSTRUCTEUR = */
	function __construct( ){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_ajax_tify_suggest_post', array( $this, 'ajax_tify_suggest_post' ) );
		add_action( 'wp_ajax_nopriv_tify_suggest_post', array( $this, 'ajax_tify_suggest_post' ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/* = Initialisation globale = */
	public function wp_init(){
		//var_dump( preg_replace( '/\+/', ' ', urlencode( '<svg version="1.1" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 -256 1792 1792" width="100%" height="100%" fill="#000000"><g transform="matrix(1,0,0,-1,121.49153,1315.7966)"><path d="M 496,192 Q 496,132 453.5,90 411,48 352,48 q -60,0 -102,42 -42,42 -42,102 0,60 42,102 42,42 102,42 59,0 101.5,-42 Q 496,252 496,192 z M 928,0 Q 928,-53 890.5,-90.5 853,-128 800,-128 747,-128 709.5,-90.5 672,-53 672,0 672,53 709.5,90.5 747,128 800,128 853,128 890.5,90.5 928,53 928,0 z M 320,640 Q 320,574 273,527 226,480 160,480 94,480 47,527 0,574 0,640 q 0,66 47,113 47,47 113,47 66,0 113,-47 47,-47 47,-113 z M 1360,192 q 0,-46 -33,-79 -33,-33 -79,-33 -46,0 -79,33 -33,33 -33,79 0,46 33,79 33,33 79,33 46,0 79,-33 33,-33 33,-79 z M 528,1088 Q 528,1015 476.5,963.5 425,912 352,912 279,912 227.5,963.5 176,1015 176,1088 q 0,73 51.5,124.5 51.5,51.5 124.5,51.5 73,0 124.5,-51.5 Q 528,1161 528,1088 z m 464,192 q 0,-80 -56,-136 -56,-56 -136,-56 -80,0 -136,56 -56,56 -56,136 0,80 56,136 56,56 136,56 80,0 136,-56 56,-56 56,-136 z m 544,-640 q 0,-40 -28,-68 -28,-28 -68,-28 -40,0 -68,28 -28,28 -28,68 0,40 28,68 28,28 68,28 40,0 68,-28 28,-28 28,-68 z m -208,448 q 0,-33 -23.5,-56.5 -23.5,-23.5 -56.5,-23.5 -33,0 -56.5,23.5 -23.5,23.5 -23.5,56.5 0,33 23.5,56.5 23.5,23.5 56.5,23.5 33,0 56.5,-23.5 23.5,-23.5 23.5,-56.5 z"/></g></svg>' ) ) ); exit;
		// Déclaration des scripts
		wp_register_style( 'tify_suggest', $this->uri .'tify_suggest.css', array( ), '20150827' );
		wp_register_script( 'tify_suggest', $this->uri .'tify_suggest.js', array( 'jquery-ui-autocomplete' ), '20150827', true );		
	}
	
	/** == Récupération de post par autocompletion ==
	 * @param (string)	term			// Chaine de recherche
	 * @param (array) 	elements		// Elements de rendu
	 * @param (array) 	query_args		// Arguments de requete WP_QUERY complémentaires
	 * @param (array) 	extra			// paramètres complémentaires
	 **/
	public function ajax_tify_suggest_post(){
		// Arguments par defaut à passer en $_POST
		$args = array(
			'term'				=> '',
			'elements'			=> array( 'title', 'permalink' /*'id', 'thumbnail', 'ico', 'type', 'status'*/ ),			
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
			
		// Récupération des post concernés	
		$query_post = new WP_Query;				
		$posts = $query_post->query( $query_args );
		
		foreach ( (array) $posts as $post ) :
			// Données requises
			$label 			= $post->post_title; 
			$value 			= $post->post_title;
			
			// Données de rendu
			$id 		= $post->ID;
			$title		= $post->post_title;
			$permalink	= get_permalink( $post->ID );
			$thumbnail 	= wp_get_attachment_image( get_post_thumbnail_id( $post->ID ), 'thumbnail', false );
			$ico 		= wp_get_attachment_image( get_post_thumbnail_id( $post->ID ), array(50,50), false );
			$type 		= get_post_type_object( $post->post_type )->label;
			$status 	= get_post_status_object( get_post_status( $post->ID ) )->label;			
			
			// Génération du rendu
			$render = tify_suggest_item_render( compact( $elements ) );
			
			// Valeur de retour
			$response[] = compact( 'label', 'value', 'render' );
		endforeach;
		
		wp_send_json( $response );	
	}
}
new tiFy_Suggest;