<?php
/*
Plugin Name: Google map
Plugin URI: http://presstify.com/theme-manager/addons/google-map
Description: Interface de création de carte gmap
Version: 1.0.1
Author: Milkcreation
Author URI: http://milkcreation.fr
*/

/**
 * USAGE :
		Depuis l'éditeur :
			[tify_google-map]
		Directement dans un template :
			<?php echo do_shortcode( '[tify_google-map]' ); ?>
		Personnalisation du style de la Google Map :
		1 - Enregistrer le code du style dans un fichier json
		2 - Retourner le chemin vers le fichier json par l'intermédiaire du filtre ci-dessous
	 		// Exemple
			add_filter( 'tify_google_map_styles', 'my_style_tify_google_map_styles' );
			function my_style_tify_google_map_styles(){
				return get_stylesheet_directory().'/assets/gmap/style.json';
			}
		Personnalisation de l'icône du marqueur principal
		1 - Retourner le chemin vers le fichier image (svg,jpg,png) par l'intermédiaire du filtre ci-dessous 
 * 			( attention si le marker est un svg, il doit IMPÉRATIVEMENT avoir un attribut width et un attribut height )
			// Exemple
			add_filter( 'tify_google_map_main_marker_icon', 'my_icon_tify_google_map_main_marker_icon' );
			function my_icon_tify_google_map_main_marker_icon(){
				return get_stylesheet_directory().'/assets/gmap/icon.svg';
			}
 */

namespace tiFy\Plugins\GoogleMap; 

use tiFy\Environment\Plugin;

class GoogleMap extends Plugin
{
	/* = ARGUMENTS = */
	// Liste des actions à déclencher
	protected $CallActions				= array(
		'init'
	);
	
	private $styles;
	private $main_marker_icon;
		
	/**
	 * Initialisation
	 */
	public function __construct()
	{
		parent::__construct();
		
		add_shortcode( 'tify_google-map', array( $this, 'add_shortcode' ) );
	}
	
	/* = ACTIONS ET FILTRES WORPDRESS = */
	/** == Initialisation globale == */
	final public function init()
	{
		// Style de la Google Map
		$style = apply_filters( 'tify_google_map_styles', false );
		$this->set_style( $style );
		// Personnalisation de l'icône du marker principal
		$main_marker_icon = apply_filters( 'tify_google_map_main_marker_icon', false );
		$this->set_main_marker_icon( $main_marker_icon );
		wp_register_script( 'tify_google-map', '//maps.googleapis.com/maps/api/js?key=&sensor=false&extension=.js', array(), 'v3', false );
	}
	
	/** == Déclaration du shortcode == **/
	final public function add_shortcode( $atts = array() )
	{
		return $this->display( $atts );		
	}
	
	/* = CONTROLEUR = */
	/** == Paramètrage du style de la Google Map == **/
	private function set_style( $style )
	{
		// Bypass		
		if( ! $style )
			return;
		if( ! file_exists( $style ) )
			return;
		if( ( $style_infos = pathinfo( $style ) ) && ( $style_infos['extension'] != 'json' ) )
			return;
		if( ! $_style = file_get_contents( $style ) )
			return;
		
		$this->styles = $_style;
	}
	
	/** == Personnalisation de l'icône du marker principal == **/
	private function set_main_marker_icon( $icon )
	{
		// Bypass
		if( ! $icon )
			return;
		if( ! file_exists( $icon ) )
			return;
		if( ! $icon_infos = pathinfo( $icon ) )
			return;
		
		switch( $icon_infos['extension'] ) :
			case 'svg' :
				// Récupération du contenu du fichier SVG
				$dom = new \DOMDocument;
				$dom->loadXML( file_get_contents( $icon ) );
				$svgs = $dom->getElementsByTagName('path');
				$svg_containers = $dom->getElementsByTagName('svg');
				
				// Bypass
				if( $svg_containers->length > 1 && $svgs->length > 1 )
					return;
				
				$svg_icon = new stdClass;
				
				// Traitement de la balise <svg>
				foreach( $svg_containers as $n => $svg_container )
					$svg_containers->item($n)->C14N();
				// Taille du SVG
				/// Largeur
				if( $svg_container->getAttribute('width') )
					$svg_icon->width = $svg_container->getAttribute('width');
				/// Hauteur
				if( $svg_container->getAttribute('height') )
					$svg_icon->height = $svg_container->getAttribute('height');
				
				// Traitement du chemin <path>
				foreach( $svgs as $n => $svg )
					$svgs->item($n)->C14N();				
				
				// Chemin SVG
				if( $svg->getAttribute('d') )
					$svg_icon->path = $svg->getAttribute('d');
				// Couleur de remplissage
				if( $svg->getAttribute('fill') )
					$svg_icon->fillColor = $svg->getAttribute('fill');
				// Opacity de la couleur de remplissage
				if( $svg->getAttribute('fill-opacity') )
					$svg_icon->fillOpacity = $svg->getAttribute('fill-opacity');
				else
					$svg_icon->fillOpacity = 1;
				
				// Mise à l'échelle
				// Épaisseur du contour
				if( $svg->getAttribute('scale') )
					$svg_icon->scale = $svg->getAttribute('scale');
				else
					$svg_icon->scale = 1;
				
				// Couleur du contour
				if( $svg->getAttribute('stroke') )
					$svg_icon->strokeColor = $svg->getAttribute('stroke');
				
				// Épaisseur du contour
				if( $svg->getAttribute('stroke-width') )
					$svg_icon->strokeWeight = $svg->getAttribute('stroke-width');
				else
					$svg_icon->strokeWeight = 0;
				
				$this->main_marker_icon = json_encode( $svg_icon );
				
				break;
			case 'png' :
			case 'jpg' :
				$root = $_SERVER['DOCUMENT_ROOT'];
				$uri = preg_replace( "#^".preg_quote($root)."#", '', $icon );
				$res = 'http';
				if ( isset( $_SERVER["HTTPS"] ) && ( $_SERVER["HTTPS"] == "on" ) ) 
					$res .= "s";
				$res .= "://";
				$res .= ( $_SERVER["SERVER_PORT"] != "80" ) ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$uri : $_SERVER["SERVER_NAME"].$uri;
				
				$this->main_marker_icon = $res;
				break;
		endswitch;
	}
	
	/** == Affichage de la Google Map == **/
	private function display( $args )
	{
		$defaults = array(
			'address' 		=> '',
			'zoom'			=> 10
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		// Options d'affichage de la carte
		$map_options 	= json_encode( array(
				'zoom'						=> (int) $zoom,
				'zoomControl'				=> (bool) true,
				'disableDoubleClickZoom'	=> (bool) false,
	            'mapTypeControl'			=> (bool) false,
	            'scaleControl'				=> (bool) false,
	            'scrollwheel'				=> (bool) false,
	            'panControl'				=> (bool) false,
	            'streetViewControl'			=> (bool) false,
	            'draggable' 				=> (bool) true,
	            'overviewMapControl'		=> (bool) true
			), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );
		
		$locations		= '';

		// Mise en file des scripts
		wp_enqueue_script( 'tify_google-map' );
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 99 );
		
		return apply_filters( 'tify_google_map_display', "<div id=\"tify_google-map_wrapper\"><div id=\"tify_google-map\" data-address=\"$address\" data-main_marker_icon='{$this->main_marker_icon}' data-locations='$locations' data-map_options='$map_options' data-map_styles='{$this->styles}'></div></div>", $args, $map_options, $this->styles, $this->main_marker_icon );
	}
	
	/**
	 * 
	 */
	function wp_footer(){
	?>
	<script type="text/javascript">/* <![CDATA[ */
		jQuery(document).ready( function($){		
			google.maps.event.addDomListener(window, 'load', init);
			var geocoder;
	    	var map;
		    function init() {
		    	var o = $( '#tify_google-map' ).data( 'map_options' );		    	
		        var mapOptions = o;
		        var mapStyles = $( '#tify_google-map' ).data( 'map_styles' );
		        var mapMainMarkerIcon = $( '#tify_google-map' ).data( 'main_marker_icon' );
		     	
		     	// Définition de l'origine du marker
		     	if( mapMainMarkerIcon ){
		     		switch( typeof mapMainMarkerIcon ){
		     			case 'string' :
		     				var tmpImage = new Image(),
		     					src = mapMainMarkerIcon;
							tmpImage.src = src;
							mapMainMarkerIcon = new Object();
							mapMainMarkerIcon.url = src;
							tmpImage.onload = function(){
								var imageWidth	 = tmpImage.width,
									imageHeight	 = tmpImage.height;
								mapMainMarkerIcon.size = new google.maps.Size( imageWidth, imageHeight );
						        mapMainMarkerIcon.origin = new google.maps.Point( 0, 0 );
						        mapMainMarkerIcon.anchor = new google.maps.Point( 0, imageHeight );
							};
		     				break;
		     			case 'object' :
		     				if( ! mapMainMarkerIcon.width && ! mapMainMarkerIcon.height ){
		     					delete mapMainMarkerIcon;
		     				} else {
		     					mapMainMarkerIcon.size = new google.maps.Size( mapMainMarkerIcon.width, mapMainMarkerIcon.height );
						        mapMainMarkerIcon.origin = new google.maps.Point( 0, 0 );
						        mapMainMarkerIcon.anchor = new google.maps.Point( 0, mapMainMarkerIcon.height );
		     				}
		     				break;
		     		}
		     	}
		           /* 
		            zoomControlOptions			: {
		                style	: google.maps.ZoomControlStyle.SMALL,
		            },
		            overviewMapControlOptions	: {
		                opened: false,
		            },
		            mapTypeId					: google.maps.MapTypeId.ROADMAP,*/
		            // @see https://snazzymaps.com
		            //styles						: [{"featureType":"water","elementType":"geometry","stylers":[{"color":"#193341"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#2c5a71"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#29768a"},{"lightness":-37}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#3e606f"},{"weight":2},{"gamma":0.84}]},{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"weight":0.6},{"color":"#1a3541"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#2c5a71"}]}]
		       
		       
		        var mapElement 	= document.getElementById('tify_google-map');
		        var map 		= new google.maps.Map(mapElement, mapOptions);
		        if( mapStyles )
		        	map.setOptions({styles : mapStyles});
		        	
		        var address		= $( '#tify_google-map' ).data( 'address' );
		        var locations	= $( '#tify_google-map' ).data( 'locations' );
		        // Chargement du Geocoder
		        if( address ){
		        	geocoder = new google.maps.Geocoder();
		      	  
			        geocoder.geocode( { 'address': address}, function(results, status) {
				    	if (status == google.maps.GeocoderStatus.OK) {
				     		map.setCenter(results[0].geometry.location);
				     		if( mapMainMarkerIcon ){
				     			var marker = new google.maps.Marker({
						         	map: map,
									position: results[0].geometry.location,
									icon: mapMainMarkerIcon
								});
				     		} else {
				     			var marker = new google.maps.Marker({
						         	map: map,
									position: results[0].geometry.location
								});
				     		}
				    	}
					});
				} else if( locations ){
					for (i = 0; i < locations.length; i++) {
						
					}
				}
			}		
		});
	/* ]]> */</script>
		<style type="text/css">
		    #tify_google-map {
		        height:350px;
		        width:100%;
		    }
		    .gm-style-iw * {
		        display: block;
		        width: 100%;
		    }
		    .gm-style-iw h4, .gm-style-iw p {
		        margin: 0;
		        padding: 0;
		    }
		    .gm-style-iw a {
		        color: #4272db;
		    }
		    /** Bootstrap Hack **/
		    #tify_google-map img {
				max-width: none;
			}
		</style>
	<?php
	}
}