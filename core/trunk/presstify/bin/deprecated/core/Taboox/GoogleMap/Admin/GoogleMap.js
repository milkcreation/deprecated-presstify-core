jQuery(document).ready( function($){
	$( 'a[data-toggle="tab"]', '.taboox-container' ).on( 'shown.bs.tab', function(e){
		$( '#map-reload' ).trigger( 'click' );	
	});
	
    /* = CARTE INTERACTIVE = */
	/** == Déclaration des variables globales == **/
	var container 	= "#gmap-postbox";
	var $map 		= $( "#googleMap", container );
	/** == Traitement des types de marqueurs == **/
	var ico			= {};
	var mainMarker 	= TabooxPostAdminGMap.main_marker;
	var markerTypes = TabooxPostAdminGMap.marker_types;
	
	// Définition du marqueur par défaut
	if(  mainMarker ){
		var defaultType = "main_marker";
	} else if(  markerTypes ) {
		for( var defaultType in markerTypes ) break;
	}
	// Génération de la liste des icones
	if(  mainMarker )
		ico.main_marker = mainMarker.ico;
	if(  markerTypes ) {
		 $.each( markerTypes, function( i, j ){
		 	ico[i] = j.ico; 
		 });
	}
	/** == Actions == **/
    /*** === Chargement de la carte === ***/
   	loadMap();
	/*** === Barre d'options de la carte === ***/
	/**** ==== Bouton d'ajout de nouveau géocode ==== ****/
	$( '#marker-add > a', container ).click( function(e){
		e.preventDefault();
		showMarkerEdit();				
	});	
	/**** ==== Bouton de rechargement de la carte ==== ****/
	$( '#map-reload' ).click(function(e){
		e.preventDefault();
		$map.gmap3( 'destroy' );
		loadMap();
	});	
	/*** === Panneau d'édition des marqueurs === ***/	    
    /**** ==== Recherche de position de marqueur par autocomplétion ==== ****/
	var input = document.getElementById('search-input'), place;
	// Options d'autocompletion
	if( TabooxPostAdminGMap.autocomplete ){
		var autocmp_opts = {};
		var br = TabooxPostAdminGMap.autocomplete.bounds;
		if( br.north && br.east && br.south && br.west )
			autocmp_opts.bounds = new google.maps.LatLngBounds( new google.maps.LatLng(br.south,br.west), new google.maps.LatLng(br.north,br.east) );
		if( TabooxPostAdminGMap.autocomplete.country )
			autocmp_opts.componentRestrictions = { country : TabooxPostAdminGMap.autocomplete.country };
	}
    var autocomplete = new google.maps.places.Autocomplete( input, autocmp_opts );
    
    google.maps.event.addListener( autocomplete, 'place_changed', function () {
        if( place = autocomplete.getPlace() ){       
        	$( '#marker-edit .lat', container ).val( place.geometry.location.lat() );
	        $( '#marker-edit .lng', container ).val( place.geometry.location.lng() );
		}
    });
	/**** ==== Sauvegarde d'un géocode ==== ****/
    $( '#marker-edit .save', container ).click( function(e){
    	e.preventDefault();
    	// Récupération des données de marqueur
    	var attrs = {};
    	if( $( '#marker-edit .id', container ).val() )
    		attrs.id = $( '#marker-edit .id').val();
    	if( $( '#marker-edit .title', container ).val() )
    		attrs.title = $( '#marker-edit .title').val();
    	if( $( '#marker-edit .type', container ).val() )
    		attrs.type = $( '#marker-edit .type').val();
    	if( $( '#marker-edit .tooltip', container ).val() )
    		attrs.tooltip = $( '#marker-edit .tooltip', container ).val();
    	if( $( '#marker-edit .lat').val() && $( '#marker-edit .lng').val() )
    		attrs.latLng = [ $( '#marker-edit .lat', container ).val(), $( '#marker-edit .lng', container ).val() ];
		// Action de sauvegarde
    	saveMarker( attrs );
    });
	/**** ==== Annulation de création / mise à jour d'un géocode ==== ****/
	$( '#marker-edit .reset', container ).click( function(e){
		e.preventDefault();
		hideMarkerEdit();				
	});
	/**** ==== Suppression d'un géocode depuis le panneau d'édition ==== ****/
	$( '#marker-edit .delete', container ).click( function(e){
		e.preventDefault();
		removeMarker( $( '#marker-edit .id', container ).val() );	
	});
	/**** ==== Affichage du panneau d'édition d'un géocode ==== ****/
	function showMarkerEdit( target ){
		// Masquage du bouton d'ajout de la barre d'options de la carte
		$( '#marker-add a', container  ).addClass( 'hide' );
		// Renseignement des valeurs des attributs si un géocode est spécifié
		$( '#marker-edit', container ).slideDown( 200, function(){
			if( target ){
				var attrs = getMarkerAttrs( target );
				$( '#marker-edit .id', container ).val( attrs.id );
				$( '#marker-edit .title', container ).val( attrs.title );
				$( '#marker-edit .type', container ).val( attrs.type );
				$( '#marker-edit .search', container ).val('');
				$( '#marker-edit .lat', container ).val( attrs.latLng[0] );
				$( '#marker-edit .lng', container ).val( attrs.latLng[1] );
				$( '#marker-edit .tooltip', container ).val( attrs.tooltip );
				// Intitulés et boutons
				$( '#marker-edit .save' ).html( TabooxPostAdminGMap.buttonUpdate );
				$( '#marker-edit .delete' ).show();
			} else {
				// Intitulés et boutons
				$( '#marker-edit .save' ).html( TabooxPostAdminGMap.buttonAdd );
				$( '#marker-edit .delete' ).hide();
			}
		});			
	}
	/**** ==== Masquage du panneau d'édition d'un géocode ==== ****/ 
	function hideMarkerEdit(){
		// Masquage du bouton d'ajout de la barre d'options de la carte
		$( '#marker-add a' ).removeClass( 'hide' );
		$( '#geocodes li', container ).removeClass('active');
		$('#marker-edit').slideUp( 200, function(){
			$( '#marker-edit .marker-data' ).val('');
			$( '#marker-edit .type' ).val( defaultType );
		});		
	}
	/*** === Liste des géocodes === ***/
	/**** ==== Animation du géocode de la carte au survol d'un élément correspondant dans la liste ==== ****/
	$( 'body' ).on({
		mouseenter : function( e ){
			e.preventDefault();
			var target = $( this ).data( target );
			var marker = $map.gmap3({ get : { name : "marker", id : target } });
		    marker.setAnimation( google.maps.Animation.BOUNCE );
		},
		mouseleave : function( e ){
			e.preventDefault();
			var target = $( this ).data( target );
			var marker = $map.gmap3({ get : { name : "marker", id : target } });
			marker.setAnimation( null );
		}
	}, container +' #geocodes li a' );
	/**** ==== Affichage du panneau d'édition d'un géocode au clic d'un élément de la liste ==== ****/
	$( 'body' ).on( 'click', container +' #geocodes li a', function(e){
		e.preventDefault();
		var $li = $(this).parent();
		$li.siblings().removeClass( 'active' );
		if( $li.hasClass( 'active' ) ){
			hideMarkerEdit( );
		} else {
			$li.addClass( 'active' );	
			showMarkerEdit( $( this ).data( 'target' ) );
		}		
	});


   	/** == Initialisation du menu contextuel de la carte == **/
	var menu = new Gmap3Menu( $map ), cursor;	
	/*** === Création du menu de choix de type de marqueur === ***/
	if( mainMarker )
		menu.add( mainMarker, "main_marker", 
			function(){
				menu.close();
	            saveMarker( { type : "main_marker", latLng : cursor.latLng } );
			}
		);
	/*** === Ajout des points d'intérêts au menu contextuel === ***/
	if( markerTypes )
		$.each( markerTypes, function( type, attrs ){
			menu.add( attrs, type, 
				function(){
					menu.close();
		            saveMarker( { type : type, latLng : cursor.latLng } );
				}
			);
		});
	/** == Fermeture de l'overlay d'affichage d'alerte == **/
    $( 'body' ).on( 'click', container +' .overlay .close', function(e){ 
    		e.preventDefault(); 
    		alertClose(); 
    });
    
	/** == Méthode == **/
    /*** === Chargement de la carte === ***/
    function loadMap(){
    	var attrs = {};
    	attrs.map = getMapOptions();
    	attrs.marker = getMarkersOptions();
    	// Panneau d'aide à la saisie
    	if( TabooxPostAdminGMap.showPanelHelper ){
			attrs.panel = {
				options : {
					content : 	'<table id="map-infos" class="table" style="background-color:rgba(255,255,255,0.7);font-size:11px;">' +
									'<thead><tr><th colspan="2">'+TabooxPostAdminGMap.mapInfos+'</tr></thead><tbody>' +
									'<tr id="lat-north"><td class="name">'+TabooxPostAdminGMap.north+'</td><td class="value"></td></tr>' +
                    				'<tr id="lng-east"><td class="name">'+TabooxPostAdminGMap.east+'</td><td class="value"></td></tr>' +
                    				'<tr id="lat-south"><td class="name">'+TabooxPostAdminGMap.south+'</td><td class="value"></td></tr>' +
                    				'<tr id="lng-west"><td class="name">'+TabooxPostAdminGMap.west+'</td><td class="value"></td></tr>' +
                  				'</tbody></table>',
	        		bottom: true	        		
	      		}
	    	};
	    }
	    $map.gmap3( attrs );			    
	}		
	/*** ===  Options de la carte === ***/
	function getMapOptions(){
		// Déclaration des variable
		var zoom, cx, cy, attrs	= { options: {}, events : {} };		
		// Options
		if( zoom = $( '#map_zoom', container ).val() )
			attrs.options.zoom 	= parseInt( zoom );
		if( ( cx = $( '#map_center_x', container ).val() ) && ( cy = $( '#map_center_y', container ).val() ) )
			attrs.options.center = [ cx, cy ];
		else
			attrs.address = TabooxPostAdminGMap.gmap3.map.address;			
		attrs.options = $.extend( true, TabooxPostAdminGMap.gmap3.map.options, attrs.options );		
		// Evénements
		attrs.events = {
	    	tilesloaded 	: function(){
	    		$( '#map_center_x' ).val( $(this).gmap3("get").getCenter().lat() );
				$( '#map_center_y' ).val( $(this).gmap3("get").getCenter().lng() );
				$( '#map_zoom' ).val( $(this).gmap3("get").getZoom() );
	    	},  	
			rightclick 		: function( map, event ){
				if( event.latLng )
					event.latLng = getGeometryLatLng( event.latLng );
				cursor = event;
				menu.open( cursor );
			},
			click 			: function(){ menu.close(); },
			dragstart		: function(){ menu.close(); },
			dragend			: function(){
				// Mise à jour des options de la carte
				$( '#map_center_x', container ).val( $(this).gmap3("get").getCenter().lat() );
				$( '#map_center_y', container ).val( $(this).gmap3("get").getCenter().lng() );
			},					
			zoom_changed 	: function(){
				// Mise à jour des options de la carte
				$( '#map_zoom' ).val( $(this).gmap3("get").getZoom() );
				menu.close();
			},
			bounds_changed	: function( map ){
				if( TabooxPostAdminGMap.showPanelHelper ){
					var bounds = map.getBounds(), ne = bounds.getNorthEast(), sw = bounds.getSouthWest();
					$( "#lat-north .value", '#map-infos' ).html( ne.lat() );
					$( "#lng-east .value", '#map-infos' ).html( ne.lng() );
					$( "#lat-south .value", '#map-infos' ).html( sw.lat() );
					$( "#lng-west .value", '#map-infos' ).html( sw.lng() );
				}
			}
		};	
		return attrs;
	}
	/*** ===  Options des marqueurs === ***/
	function getMarkersOptions(){
		var	attrs = {
			values : [],
			options:{ draggable :true },
			events : getMarkersEvents()
		};		
		$( '#geocodes ul > li > a', container ).each( function(){
    		var type, lat, lng, id;
    		if( ( type =  $(this).find('.type').val() ) && ( lat =  $(this).find('.lat').val() ) && ( lng =  $(this).find('.lng').val() ) && ( id =  $(this).find('.id').val() ) ){
    			var tooltip = "";
    			var title = $(this).find('.title').val(), text = $(this).find('.tooltip').val();
    			if( title || text ) tooltip += '<div style="line-height:1.35;overflow:hidden;white-space:nowrap;">';
    			if( title ) tooltip += '<h4 style="margin:5px 0;">'+ title +'</h4>';
    			if( text ) tooltip += '<div style="margin:0; margin-bottom:10px;">'+ text +'</div>';
    			if( title || text ) tooltip += '</div>';
    				
    			attrs.values.push({ 
    				latLng 	: [ lat, lng ], 
    				data 	: tooltip, 
    				id 		: id,
    				options : { icon : ico[type] }
    			});
    		}  			
    	});    	
    	return attrs;
	}
	/*** ===  Evénéments des marqueurs === ***/
	function getMarkersEvents(){
		return {
			dragstart : function(){
				hideMarkerEdit();
			},
			dragend : function( marker, event, context ){
				// Mise à jour des coordonnées du marker
				var attrs = getMarkerAttrs( context.id );
				attrs.latLng = [ marker.getPosition().lat(), marker.getPosition().lng() ];		
				saveMarker( attrs );
			},
			click : function( marker, event, context ){
				if( ! context.data )
					return;
		        var map = $map.gmap3("get"), infowindow = $map.gmap3({ get : { name: "infowindow" } });
		        if( infowindow ){
					infowindow.open( map, marker );
					infowindow.setContent( context.data );
		        } else {
					$map.gmap3({
						infowindow:{
							anchor:marker,
							options: { content : context.data }
						}
					});
		        }							
			},
			rightclick : function( marker, event, context ){				
				var menu = new Gmap3MarkerMenu( $map );	
				menu.add( TabooxPostAdminGMap.buttonEdit, 'edit', 
					function(){
			            showMarkerEdit( context.id );
					}
				);
				menu.add( TabooxPostAdminGMap.buttonDelete, 'delete', 
					function(){
			            removeMarker( context.id );
					}
				);
				menu.open( event );
			}			
		};
	}
	/*** === Récupération des coordonnées depuis la geometry GoogleMap === ***/// 
	function getGeometryLatLng( geometry_location ){
		var lat, lng;
		if( ( lat = geometry_location.lat() ) && ( lng = geometry_location.lng() ) )
			return [lat, lng ];
	}
	/*** === Récupération des attributs d'un marqueur === ***/
	function getMarkerAttrs( target ){		
		var attrs = {};
		attrs.id 		= $( '#geocodes #geocode-'+ target +' .id', container ).val();
		attrs.title 	= $( '#geocodes #geocode-'+ target +' .title', container ).val();
		attrs.tooltip 	= $( '#geocodes #geocode-'+ target +' .tooltip', container ).val();
		attrs.type 		= $( '#geocodes #geocode-'+ target +' .type', container ).val();
		attrs.latLng 	= [ $( '#geocodes #geocode-'+ target +' .lat', container ).val(), $( '#geocodes #geocode-'+ target +' .lng', container ).val() ];
		
		return attrs;
	}
	/*** === Sauvegarde d'un marqueur === ***/
	function saveMarker( attrs ){
		// Déclaration des attributs des marqueurs
		if( ! attrs )
			attrs = {};
		if( ! attrs.type ) attrs.type = defaultType;
		if( ! attrs.id ) attrs.id = 0;
		if( ! attrs.title ) attrs.title = '';
		if( ! attrs.latLng ) attrs.latLng = [ $( '#map_center_x', container ).val(), $( '#map_center_y', container ).val() ];
		if( ! attrs.tooltip ) attrs.tooltip = '';
		// Nombre maximum de géocode par type
		var max = ( attrs.type == 'main_marker' ) ? 1 : TabooxPostAdminGMap.marker_types[attrs.type].max;
		// Vérifie si le nombre maximum de géocode pour ce type de marqueur est atteint
		if( ! attrs.id && max > 0 && ( $( '#geocodes > ul > li .type[value="'+attrs.type+'"]', container ).length >= parseInt( max ) ) ){
			alertDisplay( TabooxPostAdminGMap.maxTypeAttempt, { duration : 0 } );
			hideMarkerEdit( );
			return;
		} else if(  max > 0 && ( $( '#geocodes > ul > li .type[value="'+attrs.type+'"]', container ).length >= parseInt( max ) ) && ( $( '#'+ attrs.id ).find('.type').val() != attrs.type ) ){
			alertDisplay( TabooxPostAdminGMap.maxTypeAttempt, { duration : 0 } );
			hideMarkerEdit( );
			return;
		}

		// Mise à jour du marqueur
		$.post( tify_ajaxurl, { action : 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap_saveMarker', post_id : $( '#post_ID' ).val(), data : attrs }, function( resp ){
			console.log( resp );
			if( resp.error ){
			
			} else {				
				var html = "";
				if( resp.geocode_type == 'main_marker' ){
					var name = "mkpbx_postbox[single]";
					html +=	'<a href="#" id="'+ resp.geocode_id +'" class="button-secondary" data-target="'+ resp.geocode_id +'">';
					html +=		'<img src="'+ico[resp.geocode_type]+'" class="ico" width="24" height="auto" style="vertical-align:middle;"/>';
					html +=		'<span class="label">'+ resp.geocode_title +'</span>';
					html +=		'<input type="hidden" class="id" value="'+ resp.geocode_id +'"/>';
					html +=		'<input type="hidden" class="title" name="'+ name +'[gmap_marker_title]" value="'+ resp.geocode_title +'"/>';
					html += 	'<input type="hidden" class="tooltip" name="'+ name +'[gmap_marker_tooltip]" value="'+ resp.geocode_content +'"/>';
					html +=		'<input type="hidden" class="type" value="main_marker"/>';
					html +=		'<input type="hidden" class="lat" name="'+ name +'[gmap_marker_lat]" value="'+ resp.geocode_lat +'"/>';
					html +=		'<input type="hidden" class="lng" name="'+ name +'[gmap_marker_lng]" value="'+ resp.geocode_lng +'"/>';
					html +=	'</a>';
				} else {
					var name = "mkpbx_postbox[multi][geocode]";
					html +=	'<a href="#" id="'+ resp.geocode_id +'" class="button-secondary" data-target="'+ resp.geocode_id +'">',
					html +=		'<img src="'+ico[resp.geocode_type]+'" class="ico" width="24" height="auto" style="vertical-align:middle;"/>';
					html += 	'<span class="label">'+ resp.geocode_title +'</span>';
					html += 	'<input type="hidden" class="id" value="'+ resp.geocode_id +'"/>';
					html += 	'<input type="hidden" class="title" name="'+ name +'['+ resp.geocode_id +'][geocode_title]" value="'+ resp.geocode_title +'" />';
					html += 	'<input type="hidden" class="tooltip" name="'+ name +'['+ resp.geocode_id +'][geocode_content]" value="'+ resp.geocode_content +'" />';
					html += 	'<input type="hidden" class="type" name="'+ name +'['+ resp.geocode_id +'][geocode_type]" value="'+ resp.geocode_type +'" />';
					html += 	'<input type="hidden" class="lat" name="'+ name +'['+ resp.geocode_id +'][geocode_lat]" value="'+ resp.geocode_lat +'" />';
					html += 	'<input type="hidden" class="lng" name="'+ name +'['+ resp.geocode_id +'][geocode_lng]" value="'+ resp.geocode_lng +'" />';				
					html += '</a>';
				}
				// Mise à jour du marqueur
				if( resp.geocode_id == attrs.id ){
					// Maj de la Liste des géocodes
					$( '#geocodes > ul li#geocode-'+ resp.geocode_id, container ).html( html );
					// Maj du marqueur sur la carte			
					$map.gmap3({ 
						get : { 
							id 			: resp.geocode_id,
							full 		: true,
							callback	: function( marker ){
								marker.object.setIcon( ico[resp.geocode_type] );
								marker.object.setPosition( new google.maps.LatLng( resp.geocode_lat, resp.geocode_lng ) );
							}
						} 
					});					
					// Affichage du message d'alerte de mise à jour
					alertDisplay( TabooxPostAdminGMap.markerUpdated );
				// Création du marqueur
				} else {
					// Ajout à la liste des géocodes
					if( $( '#geocodes ul.'+resp.geocode_type ).length )
						var $closest = $( '#geocodes ul.'+resp.geocode_type, container  );
					else
						var $closest = $( '#geocodes > ul', container );
						
					$closest.append( '<li id="geocode-'+ resp.geocode_id +'">'+ html +'</li>' );
					// Ajout du marqueur à la carte
					var marker = { 
							latLng	: [ resp.geocode_lat, resp.geocode_lng],
							options	: {
								draggable :true,
								icon : { url : ico[resp.geocode_type] }
							},
							data 	: resp.geocode_content,
							id 		: resp.geocode_id,
							events 	: getMarkersEvents()			
					};		
					$map.gmap3( { marker : marker } );
					// Affichage du message d'alerte
					alertDisplay( TabooxPostAdminGMap.markerSaved );
				}
			}
			hideMarkerEdit( );
		}, 'json' );	
	}
	/*** === Suppression d'un géocode === ***/
	function removeMarker( target ){
		$.post( tify_ajaxurl, { action : 'tiFyCoreTabooxAdminPostGoogleMapAdminGoogleMap_deleteMarker', geocode_id : target }, function( resp ){
			// Suppression du géocode sur la carte
			$map.gmap3({ clear : { name	: "marker", id : target	} });
			// Suppression du géocode dans la liste
			$( '#geocodes #geocode-'+target, container ).remove();
			hideMarkerEdit( );		
			alertDisplay( TabooxPostAdminGMap.markerDeleted );
		});
	}	
	// Affichage des alertes
	var overlayTime;
	function alertDisplay( message, attrs ){
		// Déclaration des variables
		var def_attrs = { duration : TabooxPostAdminGMap.errorDuration, cl : '' }, attrs = $.extend( def_attrs, attrs ), $overlay = $( '.overlay', container );
		
		clearTimeout( overlayTime );	
		$overlay.empty().removeClass( 'full' );
		
		var html = '';
		html += '<div class="alert '+attrs.cl+'">';
		html += 	'<div class="message">'+message+'</div>';
		if( attrs.duration ){
			html += '<i class="spinner" style="display:inherit;"></i>';
		} else {
			$overlay.addClass( 'full' );
			html += '<a href="#" class="close dashicons dashicons-no"></i>';
		}
		html += '</div>';					
		
		$overlay.append( html ).show();		
		if( attrs.duration )
			overlayTime = setTimeout( function(){ alertClose(); }, attrs.duration );		
	}	
	// Fermeture de la fenêtre d'alerte
	function alertClose(){
		$( '.overlay', container ).hide().find( '.alert' ).remove();
	}	
	/*** === Contrôleur de menu contextuel d'ajout de marqueur à la carte === ***/
	function Gmap3Menu($div){
		var that = this, items = [], ts = null, namespace = "gmap3-menu";	    
		function create( item ){
			var $item = $( "<div class='item "+item.cl+"'><img src='"+item.ico+"' width='24' height='auto' style='vertical-align:middle;'/> "+item.label+"</div>" );
			$item
				.click( function(){
					if ( typeof item.fnc === "function" ) item.fnc.apply( $(this), [] );
				})
				.hover(
					function(){ $(this).addClass( "hover" ); },
					function(){ $(this).removeClass( "hover" ); }
				);
			return $item;
		};	  
		function clearTs(){
			if(ts){ clearTimeout(ts); ts = null; }
		};	  
		function initTs(t){
			ts = setTimeout(function(){ that.close(); }, t);
		};	  
		this.add = function( item, cl, fnc){
			items.push({ label : item.label, ico : item.ico, fnc : fnc, cl : cl });
		};	
		this.open = function(event){
			this.close();
			var offset = {x:0, y:0},
			$menu = $( "<div id='"+namespace+"'></div>" );	        
			$.each( items, function(i, item){ $menu.append( create( item ) ); });	    
			$menu.hover( function(){ clearTs(); }, function(){ initTs(3000); } );	    
			if ( event.pixel.y + $menu.height() > $div.height() ) offset.y = -$menu.height();
			if ( event.pixel.x + $menu.width() > $div.width() ) offset.x = -$menu.width();    
			$div.gmap3({ overlay : { latLng : event.latLng, options : { content: $menu, offset: offset }, tag: [ namespace, 'gmapOverlayMenu' ] } });	    
			initTs(5000);
		};	  
	  	this.close = function(){
			clearTs();
			$div.gmap3({ clear:{ name : "overlay", tag : 'gmapOverlayMenu' } });
		};
	}
	/*** === Contrôleur de menu contextuel d'action sur un marqueur === ***/
	function Gmap3MarkerMenu( $div ){
		var that = this, items = [], ts = null, namespace = "marker-contextual-menu";	    
		function create( item ){
			var $item = $( '<li><a class="'+item.cl+'" href="#">'+item.label+'</a></li>' );
			$item.click( function(e){
				e.preventDefault();
				if( typeof item.fnc === "function" ) item.fnc.apply( $(this), [] );
				that.close();
			});
			return $item;
		};	  
		this.add = function( label, cl, fnc ){
			items.push({ label : label,	cl : cl, fnc : fnc });
		};	
		this.open = function(event){
			this.close();
			var offset = { x: 20, y: -40 },
			$menu = $( '<ul class="'+ namespace +'"></ul>' );	        
			$.each( items, function( i, item ){ $menu.append( create( item ) ); });	    	    	    
			$div.gmap3({ overlay : { latLng : event.latLng, options : { content: $menu, offset: offset }, tag: [ namespace, 'gmapOverlayMenu' ] } });
		};	  
	  	this.close = function(){
			$div.gmap3({ clear:{ name : "overlay", tag : 'gmapOverlayMenu' } });
		};
	}	
});