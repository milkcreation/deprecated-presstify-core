<?php
namespace tiFy\Components\InfiniteScroll;

use tiFy\Environment\App;

/** @Autoload */
class InfiniteScroll extends App
{
	/* = ARGUMENTS = */
	// Actions
	/// Liste des actions à déclencher
	protected $CallActions				= array( 
		'wp_enqueue_scripts',
		'wp_ajax_tify_infinite_scroll',
		'wp_ajax_nopriv_tify_infinite_scroll'
	);
	/// Fonctions de rappel des actions
	protected $CallActionsFunctionsMap	= array(
		'wp_ajax_tify_infinite_scroll' 			=> 'wp_ajax',
		'wp_ajax_nopriv_tify_infinite_scroll'	=> 'wp_ajax'
	);
	
	// Identifiant des fonctions d'aide au développement
	protected $ID 			= 'infinite_scroll';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers		= array( 'display' );
	
	// Instances
	static $Instance		= 0;
	
	// Configuration
	static $Config 			= array();
				
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	public function wp_enqueue_scripts()
	{
		wp_enqueue_script( 'tify-infinite-scroll' );
	}
	
	/** == Chargement des post == **/
	public function wp_ajax()
	{
		// Récupération des arguments
		$query_args 	= $_POST['query_args'];	
		$before			= stripslashes( html_entity_decode( $_POST['before'] ) );
		$after			= stripslashes( html_entity_decode( $_POST['after'] ) );
		$posts_per_page = $_POST['per_page'];
		$paged 			= ceil( $_POST['from'] / $posts_per_page )+1;
		$template 		= $_POST['template'];

		// Traitement des arguments		
		parse_str( $_POST['query_args'], $query_args );	
		$query_args['posts_per_page'] = $posts_per_page;
		$query_args['paged'] = $paged;
		if( ! isset( $query_args['post_status'] ) )
			$query_args['post_status'] = 'publish';
		
		$query_post = new \WP_Query;
		$posts = $query_post->query( $query_args );
		
		$output = "";		
		if( $query_post->found_posts ) :
			while( $query_post->have_posts() ) : $query_post->the_post();
				ob_start();
				get_template_part( $template );				
				$output .= $before. ob_get_contents() .$after;
				ob_end_clean();
			endwhile;
			if( $query_post->max_num_pages == $paged ) :
				$output .= "<!-- tiFy_Infinite_Scroll_End -->";
			endif;
		else :
			$output .= "<!-- tiFy_Infinite_Scroll_End -->";
		endif;
		
		echo $output;
		exit;
	}

	/* = GENERAL TEMPLATE = */
	static function display( $args = array(), $echo = true ){
		global $wp_query;
		
		// Incrémentation de l'intance
		self::$Instance++;		

		// Traitement des arguments
		$defaults = array(
			'id'			=> 'tify_infinite_scroll-'. self::$Instance,
			'label'			=> __( 'Voir plus', 'tify' ),
			'action'		=> 'tify_infinite_scroll',
			'query_args' 	=> $wp_query->query,
			'target'		=> '',
			'before'		=> '<li>',
			'after'			=> '</li>',
			'per_page'		=> get_query_var( 'posts_per_page', get_option( 'posts_per_page', 10 ) ),
			'template'		=> 'content-archive'
		);
		self::$Config[self::$Instance] = wp_parse_args( $args, $defaults );
		extract( self::$Config[self::$Instance] );	
		
		$query_args = isset( $query_args ) ? _http_build_query( $query_args ) : '';
		
		// Caractères spéciaux
		$before = htmlentities( $before );
		$after  = htmlentities( $after );
		
		$config = self::$Config[self::$Instance];
		$wp_footer = function() use ( $config ){
			?><script type="text/javascript">/* <![CDATA[ */
			var tify_infinite_scroll_xhr;
			jQuery( document ).ready( function($){
				var handler = '#<?php echo $config['id'];?>',
					target	= '<?php echo $config['target'];?>';
	
				tify_infinite_scroll( handler, target );
			});
			/* ]]> */</script><?php	
		};
				
		// Mise en file des scripts
		add_action( 'wp_footer', $wp_footer, 99 );
		
		$output  = "";
		$output .= "<a id=\"{$id}\" 
					   class=\"ty_iscroll\" 
					   href=\"#tify_infinite_scroll-". self::$Instance. "\" 
					   data-action=\"{$action}\" 
					   data-query_args=\"{$query_args}\" 
					   data-target=\"{$target}\" 
					   data-before=\"{$before}\"
					   data-after=\"{$after}\"
					   data-per_page=\"{$per_page}\" 
					   data-template=\"{$template}\">{$label}</a>";
					   		
		if( $echo )
			echo $output;
		else	
			return $output;	
	}
}