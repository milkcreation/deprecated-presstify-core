<?php
/*
Plugin Name: Timeline
Plugin URI: http://presstify.com/addons/premium/timeline
Description: Gestion d'une ligne des temps
Version: 1.150617
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * http://knightlab.northwestern.edu/
 * 
 * CDN
 * @see http://cdn.knightlab.com/
 * 
 * V2
 * @see http://timeline.knightlab.com
 * @see https://github.com/NUKnightLab/TimelineJS 
 * 
 * V3
 * @see http://timeline3.knightlab.com/
 * @see https://github.com/NUKnightLab/TimelineJS3
 */

class tiFy_Timeline{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri,
			
			// Configuration
			$post_types,
			$query_args = array(),			
			$config = array();
			
	/* = CONSTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp_ajax_tify_timeline_events', array( $this, 'wp_ajax_get_events' ) );
		add_action( 'wp_ajax_nopriv_tify_timeline_events', array( $this,  'wp_ajax_get_events' ) );
		
		add_shortcode( 'tify_timeline', array( $this, 'timeline_shortcode' ) );
		add_shortcode( 'tjs-video', array( $this, 'video_shortcode' ) );
		
		// Actions et Filtres TiFy Timeline
		add_filter( 'tify_timeline_dates', array( $this, 'tify_timeline_dates' ), 9, 2 );
		add_action( 'tify_taboox_register_form', array( $this, 'tify_taboox_register_form' ) );
	}
	
	/* = CONFIGURATION = */
	/** == Définition des types de post == **/
	function set_post_types(){
		$defaults = array(
			'show_text' 		=> true,
			'show_media' 		=> true,
			'show_thumbnail' 	=> true,
			'show_credit' 		=> false,
			'show_caption' 		=> false
		);
		$post_types = apply_filters( 'tify_timeline_post_types', array() );
		
		foreach( $post_types as $k => $v )
			if( is_string( $v ) )
				$this->post_types[$v] = $defaults;
			elseif( is_array( $v ) )
				$this->post_types[$k] = wp_parse_args( $v, $defaults );
	}
	
	/* = CONTROLEURS = */
	/** == Récupération des types de posts == **/
	function get_post_types(){
		// Bypass
		if( ! is_array( $this->post_types ) )
			return array();
		
		return array_keys( $this->post_types );		
	}
	
	/** == Vérifie si le type de post est valide  == **/
	function is_post_type( $post_type ){
		return in_array( $post_type, $this->get_post_types() );
	}
	
	/** == Récupération d'une option de type de post  == **/
	function get_post_type_option( $post_type, $option ){
		// Bypass
		if( ! $this->is_post_type( $post_type ) )
			return;
		
		if( isset( $this->post_types[$post_type][$option] ) )
			return $this->post_types[$post_type][$option];
	}
	
	/* = CONTROLEUR = */
	/** == Traitement de la configuration == */
	function parse_config( $config = array() ){
		$defaults 			= array(
			'width' 			=> '100%',
			'height' 			=> '600',
			'source'			=> add_query_arg( array_merge( array( 'action' => 'tify_timeline_events' ), $this->query_args ), admin_url( 'admin-ajax.php' ) ),
			'embed_id'          => 'tify_timeline',
			'start_at_end' 		=> false,
			'start_at_slide'	=> '0',
			'start_zoom_adjust' => '0',
			'hash_bookmark' 	=> false,
			'font' 				=> '//cdn.knightlab.com/libs/timeline/latest/css/themes/font/NewsCycle-Merriweather.css',
			'debug' 			=> false,
			'lang' 				=> '//cdn.knightlab.com/libs/timeline/latest/js/locale/'.( array_shift( @preg_split( '/_/', get_locale() ) ) ).'.js',
			'maptype' 			=> false,					
			'css' 				=> '//cdn.knightlab.com/libs/timeline/latest/css/timeline.css',
			'js' 				=> '//cdn.knightlab.com/libs/timeline/latest/js/timeline-min.js',
			// Personnalisation
			'init'				=> true,					// Initialisation de la timeline au démarrage
			'start_at_next'		=> current_time( 'mysql', false ),  // Démarre au slide suivant la date indiquée (au format sql ) | false pour désactiver la fonction 
			'date_format'		=> 'Y,m,d,H,i'
		);
		
		$config =  wp_parse_args( apply_filters( 'tify_timeline_config', $config ), $defaults );
		foreach( $config as $k => &$v )
			if( in_array( $k, array( 'start_at_end', 'hash_bookmark', 'debug', 'maptype', 'init'  ) ) )
				$v = filter_var( $v, FILTER_VALIDATE_BOOLEAN );
			
		return $config;
	}
	
	/** == Traitement des arguments de requête == */
	function parse_query_args( $query_args = array() ){
		$defaults = array(
			'post_type' => $this->get_post_types(),
		);
		$query_args  = wp_parse_args( $query_args, array() );		
		$query_args = wp_parse_args( apply_filters( 'tify_timeline_query', $query_args ), $defaults );
		
		// Traitement des types de post
		if( is_string( $query_args['post_type'] ) )
			$query_args['post_type'] = array_map( 'trim', explode( ',', $query_args['post_type'] ) );
		
		foreach( $query_args['post_type'] as $k => $post_type )
			if( ! $this->is_post_type( $post_type ) )
				unset( $query_args['post_type'][$k] );
		if( empty( $query_args['post_type'] ) )
			return;		

		return $query_args;
	}
	
	/** == Traitement des événements == **/
	function get_event_datas( $post = null, $index ){
		// Bypass
		if( ! $post = get_post( $post ) )
			return;
	
		$event = array();				
		// Date
		/// Date de début
		$start_datetime = ( $start = get_post_meta( $post->ID, '_tytml_start_date', true ) ) ? $start : $post->post_date;		
		$event['startDate'] = mysql2date( $this->config['date_format'], $start_datetime );
		/// Date de fin
		$end_datetime = ( $end = get_post_meta( $post->ID, '_tytml_end_date', true ) ) ? $end : $start;
		$event['endDate'] = mysql2date( $this->config['date_format'], $end_datetime );
		/// Titre
		$event['headline'] 	= ( $headline = get_post_meta( $post->ID, '_tytml_headline', true ) ) ? $headline : $post->post_title;
		/// Text
		if( ! $show_text = get_post_meta( $post->ID, '_tytml_show_text', true ) )
			$show_text = $this->get_post_type_option( $post->post_type , 'show_text' ) ? 'y' : 'n';		
		if(  $show_text === 'y' )			
			if( $text = get_post_meta( $post->ID, '_tytml_text', true ) )
				$event['text'] = $text;
			else 		
				$event['text'] = $post->post_excerpt;
		else
			$event['text'] = '';

		// Asset
		$asset = wp_parse_args( get_post_meta( $post->ID, '_tytml_asset', true ), array(
				'media' 	=> '',
				'thumbnail' => '',
				'credit' 	=> '',
				'caption' 	=> ''
			) 
		);
		/// Media
		if( ! $show_media = get_post_meta( $post->ID, '_tytml_show_media', true ) )
			$show_media = $this->get_post_type_option( $post->post_type , 'show_media' ) ? 'y' : 'n';
		if( $show_media === 'y' ) :	
			$media = $asset['media'];
			if( ! $media && ( $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ) ) )
				$media = $image[0];
			if( $media = trim( $media ) ) :		
				//if( ! preg_match( '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $media ) ) :
					//if( has_shortcode( $media, 'tjs-video' ) || has_shortcode( $media, 'tjs-image' ) )
					$asset['media'] = $media;		
				/*else :
					$asset['media'] = sprintf( "<blockquote>%s</blockquote>", $media );
				endif;*/
			endif;	
		endif;
		/// Thumbnail
		if( ! $show_thumbnail = get_post_meta( $post->ID, '_tytml_show_thumbnail', true ) )
			$show_thumbnail = $this->get_post_type_option( $post->post_type , 'show_thumbnail' ) ? 'y' : 'n';
		if( $show_thumbnail === 'y'  )
			if( $asset['thumbnail'] && ( $image = wp_get_attachment_image_src( $asset['thumbnail'], 'thumbnail' ) ) )
				$asset['thumbnail'] = esc_url( $image[0] );
			elseif( $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' ) )
				$asset['thumbnail'] = esc_url( $image[0] );
					
		/// Crédit
		if( ! $show_credit = get_post_meta( $post->ID, '_tytml_show_credit', true ) )
			$show_credit = $this->get_post_type_option( $post->post_type , 'show_credit' ) ? 'y' : 'n';
		if( $show_credit === 'n'  )
			$asset['credit'] = '';		
		/// Légende
		if( ! $show_caption = get_post_meta( $post->ID, '_tytml_show_caption', true ) )
			$show_caption = $this->get_post_type_option( $post->post_type , 'show_caption' ) ? 'y' : 'n';
		if( $show_caption === 'n'  )
			$asset['caption'] = '';
		
		$event['asset'] = $asset; 
				
		// Misc		
		$event['post_id'] = $post->ID;

		return apply_filters_ref_array( 'tify_timeline_get_event', array( $event, $post, $index, &$this ) );
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_init(){
		// Définition des types de post
		$this->set_post_types();
		// Mise en file des scripts
		wp_register_style( 'tify_timeline', $this->uri. 'timeline.css', array( 'spinkit-rotating-plane' ), '151116' );
		wp_register_script( 'timelineJS-story', '//cdn.knightlab.com/libs/timeline/latest/js/storyjs-embed.js', array( 'jquery' ), '2.36.0', true );
	}
	
	/** == Mise en file des scripts == **/
	function wp_footer(){
		wp_enqueue_script( 'timelineJS-story' );
	?><script type="text/javascript">/* <![CDATA[ */
			var tify_timeline_xhr, tify_timeline_kill, tify_timeline_load;
			
			jQuery( document ).ready( function($){
				var init = '<?php echo $this->config['init'];?>';
				tify_timeline_kill = function(){
					if( tify_timeline_xhr !== undefined ){
						tify_timeline_xhr.abort();
						tify_timeline_xhr = undefined;
					}
					$( '#<?php echo $this->config['embed_id'];?>' ).empty();	
				}
				tify_timeline_load = function( data ){
					if( data === undefined )
						data = $.parseJSON( '<?php echo json_encode( $this->config );?>' );
						
					$( '#<?php echo $this->config['embed_id'];?>' ).append( '<div class="tify_timeline-overlay"><div class="tify_timeline-spinner sk-rotating-plane"></div></div></div>');
					tify_timeline_xhr = $.ajax({ 
						url 		: '<?php echo $this->config['source'];?>', 
						data 		: data,
						type 		: 'post',
						success 	: function( resp ){
							tify_timeline_kill();		
							if( resp.source.timeline.date.length > 0 )	{		
								createStoryJS({
									embed_id			: '<?php echo $this->config['embed_id'];?>',
									source				: resp.source,            
									// Options
									width				: resp.config.width ? resp.config.width : '<?php echo $this->config['width'];?>',
									height				: resp.config.height ? resp.config.height : '<?php echo $this->config['height'];?>',
									start_at_end		: resp.config.start_at_end ? resp.config.start_at_end : '<?php echo $this->config['start_at_end'];?>',
									start_at_slide		: resp.config.start_at_slide ? resp.config.start_at_slide : '<?php echo $this->config['start_at_slide'];?>',
									start_zoom_adjust	: resp.config.start_zoom_adjust ? resp.config.start_zoom_adjust : '<?php echo $this->config['start_zoom_adjust'];?>',           
									hash_bookmark		: resp.config.hash_bookmark ? resp.config.hash_bookmark : '<?php echo $this->config['hash_bookmark'];?>',
									maptype				: resp.config.maptype ? resp.config.maptype : '<?php echo $this->config['maptype'];?>',
									// Mode debug
									debug				: resp.config.debug ? resp.config.debug : '<?php echo $this->config['debug'];?>',
									// Url
									lang				: resp.config.lang ? resp.config.lang : '<?php echo $this->config['lang'];?>',	
									font				: resp.config.font ? resp.config.font : '<?php echo $this->config['font'];?>',
									css					: resp.config.css ? resp.config.css : '<?php echo $this->config['css'];?>',
									js					: resp.config.js ? resp.config.js : '<?php echo $this->config['js'];?>'
								});
							} else {
								$('#<?php echo $this->config['embed_id'];?>' ).html( "<span id='tify_timeline-noresults'>Aucun resultat trouvé</span>")
							}
							$( document ).trigger( 'tify_timeline_init' );
						},
						dataType 	:'json'
					});
				}
				if( init )
					tify_timeline_load();		
			});
		/* ]]> */</script><?php	
	}
	
	/** == Récupération AJAX des événements == **/
	function wp_ajax_get_events(){		
		// Traitement des arguments de requêtes		
		unset( $_GET['action'] );
		$query_args = wp_parse_args( $_GET, array() );	
		
		// Traitement des arguments de configuration
		$this->config = $this->parse_config( $_POST );

		// Lancement de la requête de récupération des événements
		$events_query = new WP_Query();
		$events = $events_query->query( $query_args );
		
		// Traitement des événements
		$dates = array();		
		foreach( $events as $k => $event )
			$dates[$k] = $this->get_event_datas( $event, $k );
				
		// Création de la timeline	
		$timeline =  array(
			'timeline' => array( 
				'type' 		=> 'default',
				/*'headline'	=> 'Présentation de la saison',	        	
				'text' 		=> 'Ceci est la présentation des possibilités de l\'outil de timeline<br/>Basé sur timelineJS il permet d\'afficher toute sorte de médias ...<br/>Sans être exhaustive, voici une liste des possibilités offerte par ce service <br/>Plus de détails sur http://timeline.verite.co',
				'startDate' => mysql2date( 'Y,m,d', current_time( 'mysql' ) ),
				'asset' 	=> array(
					'media' 	=> 'https://vimeo.com/130540103'
					
				),*/
				'date' 		=> apply_filters_ref_array( 'tify_timeline_dates', array( $dates, &$this ) )
			)
		);	

		wp_send_json( array( 'source' => $timeline, 'config' => $this->config ) );
	}
	
	/** == Shortcode Timeline == **/
	function timeline_shortcode( $atts ){
		extract(
	    	shortcode_atts(
	    		array( ),
	    		$atts
			)
		);		
	    return $this->display( $atts, false );
	}

	/** == Shortcode Vidéo == **/
	function video_shortcode( $atts ){
		extract( 
			shortcode_atts( 
				array(
					'id' => 0,
				), 
				$atts
			)
		);		
		if( $stream = get_attachment_link( $id ) )
			return "<iframe src=\"". $stream ."\" frameborder=\"0\" scrolling=\"no\" style=\"overflow:hidden;height:100%;width:100%\" height=\"100%\" width=\"100%\" allowfullscreen=\"true\" webkitallowfullscreen=\"true\" mozallowfullscreen=\"true\"></iframe>";	
	}
	
	/* = ACTIONS ET FILTRES TiFY Timeline = */
	/** == == **/
	function tify_timeline_dates( $dates, &$timeline_obj ){
		if( empty( $this->config['start_at_next'] ) )
			return $dates;
		
		$datetimezone 		= new DateTimeZone( get_option( 'timezone_string' ) );
		$cursor 			= new DateTime( $this->config['start_at_next'], $datetimezone );
		$cursor_datetime 	= $cursor->getTimestamp();
		
		// Pré-traitement et trie des slides par date de démarrage
		$order = array();
		foreach( $dates as $i => &$date ) :
			// Récupération du timestamp des dates de début et de fin
			$start = DateTime::createFromFormat( $this->config['date_format'], $date['startDate'], $datetimezone );
			if( empty( $date['endDate'] ) ) :
				$end = new DateTime( $start->format( 'Y-m-d' ), $datetimezone );
				$end->setTime( 23, 59, 59 );
			else :
				$end = DateTime::createFromFormat( $this->config['date_format'], $date['endDate'], $datetimezone );
			endif;							
			$date['_start_datetime'] 	= $start->getTimestamp();
			$date['_end_datetime']		= $end->getTimestamp();
			
			// Ordre de trie des dates de début
			$order[$i] = $date['_start_datetime'];			
		endforeach;

		// Trie des dates par leur date de début
		@array_multisort( $order, $dates );
		
		foreach( (array) $dates as $index => $d ) :		
			if( ( $d['_start_datetime'] < $cursor_datetime ) && ( $d['_end_datetime'] < $cursor_datetime ) )
				continue;
			$this->config['start_at_slide'] = $index;
			break;
		endforeach;
		
		reset( $dates );
				
		return $dates;
	}
	
	/* = = */
	function tify_taboox_register_form(){
		tify_taboox_register_form( 'tiFy_Timeline_Taboox', $this );
	}
	
	/* = VUES = */
	/** == Affichage de la timeline == **/
	function display( $args = array(), $echo = true ){
		$this->query_args = ( isset( $args['query_args'] ) ) ? $this->parse_query_args( $args['query_args'] ) : $this->parse_query();
		if( isset( $args['query_args'] ) )
			unset( $args['query_args'] );
		
		$this->config = $this->parse_config( $args );
				
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		
		$output = "<div id=\"tify_timeline\" style=\"min-height:". $this->config['height'] ."px\"></div>";

		if( $echo )
			echo $output;
		else
			return $output;
	}
}
global $tify_timeline;
$tify_timeline = new tiFy_Timeline();

/* = Formulaire de saisie = */
class tiFy_Timeline_Taboox extends tiFy\Taboox\Admin
{
	/* = Formulaire = */
	public function form( $post ){
	?>
	<table class="form-table">
		<?php /*<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Afficher dans la frise des temps', 'mktjs');?></label><br />
			</th>
			<td>
				<label><input type="radio" name="mktjs[active]" value="y" <?php checked( ( get_post_meta( $post->ID, '_mktjs_active', true )? get_post_meta( $post->ID, '_mktjs_active', true ) : 'y'  ) =='y' );?> /> <?php _e('Oui', 'mktjs');?></label>&nbsp;&nbsp;
				<label><input type="radio" name="mktjs[active]" value="n" <?php checked( ( get_post_meta( $post->ID, '_mktjs_active', true )? get_post_meta( $post->ID, '_mktjs_active', true ) : 'y'  ) =='n' );?> /> <?php _e('Non', 'mktjs');?></label>
			</td>
		</tr>*/ ?>				
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Date de début', 'mktjs');?></label><br />
				<em><?php _e('Date de publication de l\'article si vide', 'mktjs');?></em>
			</th>
			<td>
				<input type="text" class="mktjs-datepicker" value="<?php echo mysql2date( 'd/m/Y', get_post_meta( $post->ID, '_mktjs_startDate', true ));?>" />
				<input type="hidden" name="mktjs[startDate]" value="<?php echo get_post_meta( $post->ID, '_mktjs_startDate', true );?>"  />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Date de fin', 'mktjs');?></label><br />
				<em><?php _e('Date de début si vide', 'mktjs');?></em>
			</th>
			<td>
				<input type="text" class="mktjs-datepicker" value="<?php echo mysql2date( 'd/m/Y', get_post_meta( $post->ID, '_mktjs_endDate', true ));?>" />
				<input type="hidden" name="mktjs[endDate]" value="<?php echo get_post_meta( $post->ID, '_mktjs_endDate', true );?>"  />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Titre', 'mktjs');?></label><br />
				<em><?php _e( 'Titre original de l\'article si vide', 'mktjs');?></em>
			</th>
			<td>
				<input type="text" class="widefat" name="mktjs[headline]" value="<?php echo get_post_meta( $post->ID, '_mktjs_headline', true );?>" />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Masquer le texte de description', 'mktjs');?></label><br />
			</th>
			<td>
				<label><input type="radio" name="mktjs[hide_text]" value="y" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_text', true )? get_post_meta( $post->ID, '_mktjs_hide_text', true ) : 'n'  ) =='y' );?> /> <?php _e('Oui', 'mktjs');?></label>&nbsp;&nbsp;
				<label><input type="radio" name="mktjs[hide_text]" value="n" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_text', true )? get_post_meta( $post->ID, '_mktjs_hide_text', true ) : 'n'  ) =='n' );?> /> <?php _e('Non', 'mktjs');?></label>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Texte de description', 'mktjs');?></label><br />			
			</th>
			<td>
				<?php wp_editor( get_post_meta( $post->ID, '_mktjs_text', true ), 'mktjs-text', array( 'textarea_name' => 'mktjs[text]',  'wpautop' => false, 'media_buttons' => false, 'textarea_rows' =>5, 'teeny' => false ) );?>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Masquer le lien de suite de lecture vers le contenu', 'mktjs');?></label><br />
			</th>
			<td>
				<label><input type="radio" name="mktjs[hide_readmore]" value="y" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_readmore', true )? get_post_meta( $post->ID, '_mktjs_hide_readmore', true ) : 'n'  ) =='y' );?> /> <?php _e('Oui', 'mktjs');?></label>&nbsp;&nbsp;
				<label><input type="radio" name="mktjs[hide_readmore]" value="n" <?php checked( ( get_post_meta( $post->ID, '_mktjs_hide_readmore', true )? get_post_meta( $post->ID, '_mktjs_hide_readmore', true ) : 'n'  ) =='n' );?> /> <?php _e('Non', 'mktjs');?></label>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Intitulé du lien de suite de lecture vers le contenu', 'mktjs');?></label>
			</th>
			<td>
				<input type="text" class="widefat" name="mktjs[readmore]"  placeholder="<?php _e('Lire la suite', 'mktjs');?>" value="<?php echo get_post_meta( $post->ID, '_mktjs_readmore', true );?>" />
			</td>
		</tr>	
	</table>
	<?php //Twitter, YouTube, Flickr, Instagram, TwitPic, Wikipedia, Dailymotion, SoundCloud and Vimeo 
	$asset = wp_parse_args( get_post_meta( $post->ID, '_mktjs_asset', true ), array(
					'media' => '',
					'thumbnail' => '',
					'credit' => '',
					'caption' => ''
				) 
			);	
	?>
	<table class="form-table">
		<?php $types = array(
				'blockquote' => __('Citation', 'mktjs'),
				'website' =>  __('Site Web', 'mktjs'),
				'image' => __( 'Image', 'mktjs'),
				'twitter' => __('Twitter', 'mktjs'),
				'youtube' => __('YouTube', 'mktjs'),
				'dailymotion' => __('Dailymotion', 'mktjs'),
				'vimeo' => __('Vimeo', 'mktjs'),
				'flickr' => __('Flickr', 'mktjs'),
				'soundcloud' => __('Soundcloud', 'mktjs'),
				'instagram' => __('Instagram', 'mktjs'), 
				'twitpic' => __('TwitPic', 'mktjs') 
			); 
		?>				
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Média', 'mktjs');?></label><br />
				<?php /*<select>
					<option value=""><?php _e( 'Choisir un type de média', 'mktjs' );?></option>
					<?php foreach(  $types as $slug => $label ) :?>
					<option value="<?php echo $slug;?>"><?php echo $label;?></option>
					<?php endforeach;?>
				</select> */?>
			</th>
			<td>
				<div>
					<textarea id="mktjs-asset-media" name="mktjs[asset][media]" class="widefat" rows="4" style=""><?php echo $asset['media'];?></textarea>
					<p>
						<a href="#" id="mktjs-add-local-image" class="button-secondary" data-target="#mktjs-asset-media" data-uploader_title="<?php _e( 'Sélectionner une image', 'mktjs' );?>">
							<?php _e('Image de la médiathèque', 'mktjs');?>
						</a>
						<a href="#" id="mktjs-add-local-video" class="button-secondary" data-target="#mktjs-asset-media" data-uploader_title="<?php _e( 'Sélectionner une video', 'mktjs' );?>">
							<?php _e('Video de la médiathèque', 'mktjs');?>
						</a>
					</p>
					<p>
						les contenus suivants sont autorisés : Citations (texte simple), Les images (utiliser le bouton), url de site, url de service de streaming vidéo (Youtube, Dailymotion, Vimeo ...),
						url de service de streaming image (Flickr, Instagram ...), url de service de streaming audio (Soundcloud), url de Tweet texte et image ... 					
					</p>
					<?php /*<div href="#" id="mktjs-asset-thumbnail" class="add-thumb-area-50" data-name="mktjs[asset][thumbnail]" data-uploader_title="<?php _e( 'Sélectionner une jaquette', 'mktzr_postbox' );?>">
						<div class="poster">
							<?php if( $asset['thumbnail'] && ( $image = wp_get_attachment_image_src( $asset['thumbnail'], 'thumbnail' ) ) ) :?>								
								<img width="50" height="50" src="<?php echo $image[0];?>" />
								<input type="hidden" name="mktjs[asset][thumbnail]" value="<?php echo $asset['thumbnail'];?>" />
								<a href="#" class="mkpbx-sprite remove" data-action="parent-empty"></a>								
							<?php endif;?>
						</div>
						<a href="#add" class="add"><?php _e('Cliquez ici', 'mktzr_postbox');?></a>
					</div>*/?>	
				</div>
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Crédit', 'mktjs');?></label>
			</th>
			<td>
				<input type="text" class="widefat" name="mktjs[asset][credit]" value="<?php echo $asset['credit'];?>" />
			</td>
		</tr>
		<tr valign="top">	        		
			<th scope="row">
				<label><?php _e( 'Légende', 'mktjs');?></label>
			</th>
			<td>
				<textarea class="widefat" name="mktjs[asset][caption]"><?php echo $asset['caption'];?></textarea>
			</td>
		</tr>			
	</table>
	<?php
	}
}

/* = GENERAL TEMPLATE = */
/** == Affichage de la timeline == **/
function tify_timeline_display( $args = array(), $echo = true ){
		
	$query_args = isset( $args['query_args'] ) ? _http_build_query( $args['query_args'] ) : '';
	unset( $args['query_args'] );
	
	$_args = '';
	foreach( (array) $args as $k => $v )
		$_args .= "$k=\"$v\" ";
	
	if( $echo )
		echo do_shortcode( '[tify_timeline query_args="'. $query_args .'" '. $_args .']' );
	else
		return do_shortcode( '[tify_timeline query_args="'. $query_args .'" '. $_args .']' );
}