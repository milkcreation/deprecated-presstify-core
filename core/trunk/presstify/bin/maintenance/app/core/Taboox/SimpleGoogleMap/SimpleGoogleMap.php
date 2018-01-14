<?php
namespace tiFy\Core\Taboox\Admin;

use tiFy\Core\Taboox\Form;

class SimpleGoogleMap extends Admin{
	public function __construct( ){	
		parent::__construct( );
		add_action( 'admin_footer', array( $this, 'wp_admin_footer' ) );	
	}
	
	public function current_screen( $screen ){
		wp_enqueue_script( 'tify_simple_google-map', 'https://maps.googleapis.com/maps/api/js?key=&sensor=false&extension=.js', array(), 'v3', false );
	}
	
	public function form(){	
		$mapOptions = array(		
			'center' 					=> array( 50.362273, 3.102635 ),
			'disableDefaultUI'			=> true,
			'disableDoubleClickZoom'	=> '',
			//'draggable'				=> true,
			//'draggableCursor'			=> 'cursor',
			'zoom'						=> 13,			
			'zoomControl'				=> true,			
			'zoomControlOptions'		=> array(
				// DEFAULT | SMALL | LARGE
				'style'		=> 'DEFAULT',
				// BOTTOM_CENTER | BOTTOM_LEFT | BOTTOM_RIGHT | LEFT_BOTTOM | LEFT_CENTER | LEFT_TOP | RIGHT_BOTTOM | RIGHT_CENTER | RIGHT_TOP | TOP_CENTER | TOP_LEFT | TOP_RIGHT
				'position'	=> ''
			)				
			
		);
		
		$locations = array(
			array(
				// Titre
				'Milkcreation - Agence Douai',
				// Description
		        'undefined',
		        // Téléphone
		        '06.52.96.66.21', 
		        // Mail
				'contact@milkcreation.fr',
				// Site Web
				'http://milkcreation.fr',
				// Latitude
		        50.3739983,
		        // Longitude
		        3.07663720000005,
		        // Icone
		        'https://mapbuildr.com/assets/img/markers/ellipse-red.png'
			)
		);
	?>
		<div id="tify_google-map" data-map-options='<?php echo json_encode( $mapOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );?>' data-locations='<?php echo json_encode( $locations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );?>'></div>
		<ul class="location">			
		</ul>
	<?php
	}
	
	public function wp_admin_footer(){
	?>
	<script type="text/javascript">/* <![CDATA[ */
		jQuery( document ).ready( function($){
			google.maps.event.addDomListener( window, 'load', init );
	    	var map;
		    function init() {
		    	var opt =  $( '#tify_google-map' ).data( 'map-options' );
		    	var loc =  $( '#tify_google-map' ).data( 'locations' );
		        var mapOptions = {
		            center						: new google.maps.LatLng( opt.center[0], opt.center[1] ),
		            zoom						: opt.zoom,
		            zoomControl					: opt.zoomControl,
		            zoomControlOptions			: {
		                style	: google.maps.ZoomControlStyle.SMALL,
		            },
		            disableDoubleClickZoom		: false,
		            mapTypeControl				: false,
		            scaleControl				: false,
		            scrollwheel					: false,
		            panControl					: false,
		            streetViewControl			: false,
		            draggable 					: true,
		            overviewMapControl			: true,
		            overviewMapControlOptions	: {
		                opened: false,
		            },
		            mapTypeId					: google.maps.MapTypeId.ROADMAP,
		            styles						: [{	
		            	"featureType":"all",
		            	"elementType":"all",
		            	"stylers":[
		            		{"invert_lightness":true},
		            		{"saturation":10},
		            		{"lightness":30},
		            		{"gamma":0.5},
		            		{"hue":"#435158"}
		            	]
		            }]
		        }
		        var mapElement 	= document.getElementById('tify_google-map');
		        var map 		= new google.maps.Map(mapElement, mapOptions);
		        var locations 	= loc;
		        for (i = 0; i < locations.length; i++) {
					if (locations[i][1] =='undefined'){ description ='';} else { description = locations[i][1];}
					if (locations[i][2] =='undefined'){ telephone ='';} else { telephone = locations[i][2];}
					if (locations[i][3] =='undefined'){ email ='';} else { email = locations[i][3];}
					if (locations[i][4] =='undefined'){ web ='';} else { web = locations[i][4];}
					if (locations[i][7] =='undefined'){ markericon ='';} else { markericon = locations[i][7];}
		            marker = new google.maps.Marker({
		                //icon		: markericon,
		                position	: new google.maps.LatLng(locations[i][5], locations[i][6]),
		                map			: map,
		                title		: locations[i][0],
		                desc		: description,
		                tel			: telephone,
		                email		: email,
		                web			: web
		            });
					if (web.substring(0, 7) != "http://") {
						link = "http://" + web;
					} else {
						link = web;
					}
		            //bindInfoWindow(marker, map, locations[i][0], description, telephone, email, web, link);
				}
			 	function bindInfoWindow(marker, map, title, desc, telephone, email, web, link) {
					var infoWindowVisible = (function () {
						var currentlyVisible = false;
						return function (visible) {
							if (visible !== undefined) {
								currentlyVisible = visible;
							}
							return currentlyVisible;
						};
					}());
					iw = new google.maps.InfoWindow();
					google.maps.event.addListener(marker, 'click', function() {
						if (infoWindowVisible()) {
							iw.close();
							infoWindowVisible(false);
						} else {
							var html= "<div style='color:#000;background-color:#fff;padding:5px;width:150px;'><h4>"+title+"</h4><p>"+telephone+"<p><a href='mailto:"+email+"' >"+email+"<a><a href='"+link+"'' >"+web+"<a></div>";
							iw = new google.maps.InfoWindow({content:html});
							iw.open(map,marker);
							infoWindowVisible(true);
						}
			        });
			        google.maps.event.addListener(iw, 'closeclick', function () {
			            infoWindowVisible(false);
			        });
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