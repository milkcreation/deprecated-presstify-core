<?php
/*
Plugin Name: Infinite Scroll
Plugin URI: http://presstify.com/plugins/infinite-scroll
Description: Pagination Ajax au Scroll
Version: 1.150618
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

class tiFy_InfiniteScroll{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri,
			// Configuration
			$instance,
			$config = array();
			
	/* = CONTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Actions et Filtres Wordpress
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_ajax_tify_infinite_scroll', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_infinite_scroll', array( $this, 'wp_ajax' ) );	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_enqueue_scripts(){
		wp_enqueue_script( 'jquery' );
	}
	
	/** == Chargement des post == **/
	function wp_ajax(){
		// Récupération des arguments
		$query_args 	= $_POST['query_args'];	
		$before			= stripslashes( html_entity_decode( $_POST['before'] ) );
		$after			= stripslashes( html_entity_decode( $_POST['after'] ) );
		$posts_per_page = $_POST['per_page'];
		$paged 			= ceil( $_POST['from'] / $posts_per_page )+1;
		$template 		= $_POST['template'];

		// Traitement des arguments		
		parse_str($_POST['query_args'], $query_args );	
		$query_args['posts_per_page'] = $posts_per_page;
		$query_args['paged'] = $paged;
		
		$query_post = new WP_Query;
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

	/** == Mise en file des scripts == **/
	function wp_footer(){
	?><script type="text/javascript">/* <![CDATA[ */
		var tify_infinite_scroll_xhr;
		jQuery( document ).ready( function($){
			var handler = '#<?php echo $this->config[$this->instance]['id'];?>',
				target	= '<?php echo $this->config[$this->instance]['target'];?>';
				$target = ( ! target ) ? $( handler ).prev() : $( target );
				
			$( window ).scroll( function( e ) {
				if( ( tify_infinite_scroll_xhr === undefined ) && ! $(this).hasClass( 'ty_iscroll_complete' ) && isScrolledIntoView( $( handler ) ) )
					 $( handler ).trigger( 'click' );
			});
		
			$( handler ).click( function(e){
				if( $(this).hasClass( 'ty_iscroll_complete' ) )
					return false;
				
				$target.addClass( 'ty_iscroll_load' );
				$( handler ).addClass( 'ty_iscroll_load' );
					
				var action		= $(this).data( 'action' ),
					query_args 	= $(this).data( 'query_args' ),
					before		= $(this).data( 'before' ),
					after		= $(this).data( 'after' ),
					per_page 	= $(this).data( 'per_page' ),
					template 	= $(this).data( 'template' ),
					from 		= $( '> *', $target ).size();				
					
				tify_infinite_scroll_xhr = $.post( 
					tify_ajaxurl,
					{ action: action, query_args : query_args, before : before, after : after , per_page : per_page, template: template, from : from },
					function( resp ){
						$target.removeClass( 'ty_iscroll_load' );
						$( handler ).removeClass( 'ty_iscroll_load' );	
							
						$target.append( resp );
						var complete = resp.match(/<!-- tiFy_Infinite_Scroll_End -->/);
						if( complete ){
							$target.addClass( 'ty_iscroll_complete' );
							$( handler ).addClass( 'ty_iscroll_complete' );
						}
						
						tify_infinite_scroll_xhr.abort();
						tify_infinite_scroll_xhr = undefined;
					}
				);
			});
		
			function isScrolledIntoView( elem ) {
				var docViewTop = $(window).scrollTop();
				var docViewBottom = docViewTop + $(window).height();
				var elemOffset = 0;
				  
				if( elem.data('offset') != undefined )
					elemOffset = elem.data( 'offset' );
			
				var elemTop = $(elem).offset().top;
				var elemBottom = elemTop + $(elem).outerHeight(true);
			  
				if( elemOffset != 0 )
					if(docViewTop - elemTop >= 0)
						elemTop = $(elem).offset().top + elemOffset;
					else
						elemBottom = elemTop + $(elem).outerHeight(true) - elemOffset;
			  
				if( ( elemBottom <= docViewBottom ) && ( elemTop >= docViewTop ) )
					return true;
			}
		});
		/* ]]> */</script><?php	
	}

	/* = GENERAL TEMPLATE = */
	function display( $args = array(), $echo = true ){
		global $wp_query;
		
		// Incrémentation de l'intance
		$this->instance++;		

		// Traitement des arguments
		$defaults = array(
			'id'			=> 'tify_infinite_scroll-'. $this->instance,
			'label'			=> __( 'Voir plus', 'tify' ),
			'action'		=> 'tify_infinite_scroll',
			'query_args' 	=> $wp_query->query,
			'target'		=> '',
			'before'		=> '<li>',
			'after'			=> '</li>',
			'per_page'		=> get_query_var( 'posts_per_page', get_option( 'posts_per_page', 10 ) ),
			'template'		=> 'content-archive'
		);
		$this->config[$this->instance] = wp_parse_args( $args, $defaults );
		extract( $this->config[$this->instance] );	
		
		$query_args = isset( $query_args ) ? _http_build_query( $query_args ) : '';
		
		// Caractères spéciaux
		$before = htmlentities( $before );
		$after  = htmlentities( $after );
		
		// Mise en file des scripts
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		
		$output  = "";
		$output .= "<a id=\"{$id}\" 
					   class=\"ty_iscroll\" 
					   href=\"#tify_infinite_scroll-{$this->instance}\" 
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
global $tify_infinite_scroll;
$tify_infinite_scroll = New tiFy_InfiniteScroll;

/* = GENERAL TEMPLATE = */
/** == Affichage de l'interface == **/
function tify_infinite_scroll( $args = array(), $echo = true ){
	global $tify_infinite_scroll;
	
	$tify_infinite_scroll->display( $args, $echo );
}