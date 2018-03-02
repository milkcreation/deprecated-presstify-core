!(function($, doc, win){
    $.widget('tify.tiFyGoogleMapEditor', {
    	/**
    	 * Options à auto-saisir en sortie de l'autocomplete
    	 */
    	componentForm : {
    	    street_number: 'short_name',
	        route: 'long_name',
	        locality: 'long_name',
	        country: 'long_name',
	        postal_code: 'short_name'
    	},
    	/**
    	 * Création du widget tiFyGoogleMapEditor
    	 */
        _create: function () {
        	this.googleMap = this.element.data('map');
        	this.geolocationText = this.element.data('geoloc_text');
        	this.mapOptions = this.element.data('map_options');
        	this.mapOptions = this._parseMapOptions(this.mapOptions);
        	this.mapCenter = {lat:48.85844,lng:2.294555};
        	this.newMarkerToggle = this.element.find('.tiFyGoogleMap-editorNewMarker');
        	this.panel = this.element.find('.tiFyGoogleMap-editorPanel');
        	this.autocompleteForm = this.element.data('autocomplete_form');
        	this.markersList = this.element.find('.tiFyGoogleMap-markers');
        	this.markers = {};
        	this.markersTypes = {};
        	this._loadMap();
        	this._setMarkersTypes(this.element.data('markers_types'));
        	this._setMarkers(this.element.data('markers'));
        	this._setPanelControls();
        	this._loadAutocomplete();
        	this._listenEvents();
        },
        /**
         * Traitement des options de la carte
         */
        _parseMapOptions: function(mapOptions) {
        	var self = this;
        	$.each(mapOptions, function(index, value) {
        		if (index === 'position') {
        			mapOptions[index] = eval("google.maps.ControlPosition."+value);
        		}
        		if (index === 'styles') {
                    mapOptions[index] = JSON.parse(value);
                }
        		if (typeof value === 'object') {
        			return self._parseMapOptions(value);
        		}
			});
        	return mapOptions;
        },
        /**
         * Initialisation de la carte
         */
        _initMap: function() {
        	this.map = new google.maps.Map(document.getElementById(this.googleMap), this.mapOptions);
        	this._initCenter();
        	this._responsive();
        	this._tabShown();
        },
        /**
         * Définition du centre de la carte
         */
        _setCenter: function() {
        	var center = this.map.getCenter();
		    google.maps.event.trigger(this.map, "resize");
		    this.map.setCenter(center); 
        },
        /**
         * Gestion de l'affichage de la carte dans les tabs Bootstrap 
         */       
        _tabShown: function() {
        	var self = this;
        	$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				var $toggle = $(e.target),
					$tab = $($toggle.attr('href'));
				if($tab.find('.tiFyGoogleMap-editorMap').length) {
					self._setCenter();
				}
			});
        },
        /**
         * Redéfinition du centre au redimensionnement de la carte
         */
        _responsive: function() {
        	var self = this;
        	google.maps.event.addDomListener(window, "resize", function() {
			    self._setCenter();
			});
        },
        /**Nouveau marqueur
         * Initialisation du centrage de la carte en fonction des coordonnées
         * Si pas de coordonnées, centrage en fonction de la géolocalisation de l'utilisateur
         * Sinon centrage sur la Tour Eiffel
         */
        _initCenter: function() {
        	var self = this;
        	if(typeof this.map.getCenter() === 'undefined') {
        		/**
        		 * @see https://developers.google.com/maps/documentation/javascript/examples/map-geolocation?hl=fr
        		 */
        		if(navigator.geolocation) {
        			var infoWindow = new google.maps.InfoWindow({map: this.map});
        			navigator.geolocation.getCurrentPosition(function(position) {
			            var pos = {
			              lat: position.coords.latitude,
			              lng: position.coords.longitude
			            };
			            self.mapCenter = pos;
			            infoWindow.setPosition(pos);
			            infoWindow.setContent(self.geolocationText);
			            self.map.setCenter(pos);
			            }, function() {
			            	self._handleLocationError(true, infoWindow, self.map.getCenter());
			        });
        		} else {
        			this.map.setCenter(self.mapCenter);
        		}
        	} else {
        		this.mapCenter = {
        			lat: this.map.getCenter().lat(),
        			lng: this.map.getCenter().lng()
        		};
        	}
        },
        /**
         * Affichage des erreurs concernant la géolocalisation
         */
        _handleLocationError: function(browserHasGeolocation, infoWindow, pos) {
        	infoWindow.setPosition(pos);
        	infoWindow.setContent(browserHasGeolocation ?
                              'Error: The Geolocation service failed.' :
                              'Error: Your browser doesn\'t support geolocation.');
        },
        /**
         * Chargement de la carte
         */
        _loadMap: function() {
        	google.maps.event.addDomListener(window, 'load', this._initMap());
        },
        /**
         * Définition des contrôleurs du panneau
         */
        _setPanelControls: function() {
        	this.saveMarkerToggle = this.panel.find('.tiFyControl-adminPanelControlsItemButton--save');
        	this.removeMarkerToggle = this.panel.find('.tiFyControl-adminPanelControlsItemButton--remove');
        },
        /**
         * Chargement du champ d'autocomplétion
         */
        _loadAutocomplete: function() {
        	var self = this;
        	if ($(document.getElementById(this.autocompleteForm)).length) {
        		this.autocomplete = new google.maps.places.Autocomplete((document.getElementById(this.autocompleteForm)), this.element.data('autocomplete_options'));
	        	this.autocomplete.addListener('place_changed', function() {
	        		self._fillInAddress();
	        	});
	        	$(document).on('focus', '#'+this.autocompleteForm, function(event) {
	        		self._autocompleteGeolocate();
	        	});
        	}
        },
        /**
         * Définition des types de marqueurs
         */
        _setMarkersTypes: function(markersTypes) {
        	for(id in markersTypes) {
        		markersTypes[id]['icon'] = this._setMarkerTypeIcon(markersTypes[id]['icon']);
        		this.markersTypes[id] = markersTypes[id];
        	}
        },
        /**
         * Définition de l'icône d'un type de marqueur
         */
        _setMarkerTypeIcon: function(icon) {
        	var self = this;
        	switch(typeof icon.src) {
	        	case 'string':
	        		var tmpImage = new Image();
					tmpImage.src = icon.src;
					icon.src = {};
					icon.src.url = tmpImage.src;
					tmpImage.onload = function(){
						var imageWidth = tmpImage.width,
							imageHeight = tmpImage.height;
						icon.src.size = new google.maps.Size(imageWidth,imageHeight);
				        icon.src.origin = new google.maps.Point(0,0);
				        icon.src.anchor = self._setMarkerTypeIconAnchor(icon);
					};
	        		break;
	        	case 'object':
	        		if (typeof icon.width === 'undefined') {
	        			icon.src.width = 32;
	        		}
	        		if (typeof icon.width === 'undefined') {
	        			icon.src.height = 32;
	        		}
	        		icon.src.size = new google.maps.Size(icon.src.width,icon.src.height);
	        		icon.src.origin = new google.maps.Point(0,0);
	        		icon.src.anchor = this._setMarkerTypeIconAnchor(icon);
	        		break;
        	}
        	return icon;
        },
        /**
         * Définition du point d'ancrage d'une icône d'un type de marqueur
         */
        _setMarkerTypeIconAnchor: function(icon) {
        	var _anchor;
        	switch(icon.anchor) {
	        	case 'TOP_LEFT':
	        		_anchor = new google.maps.Point(0,0);
	        		break;
	        	case 'TOP_CENTER':
	        		_anchor = new google.maps.Point((icon.src.width/2),0);
	        		break;
	        	case 'TOP_RIGHT':
	        		_anchor = new google.maps.Point(icon.src.width,0);
	        		break;
	        	case 'CENTER_LEFT':
	        		_anchor = new google.maps.Point(0,(icon.src.height/2));
	        		break;
	        	case 'CENTER':
	        		_anchor = new google.maps.Point((icon.src.width/2),(icon.src.height/2));
	        		break;
	        	case 'CENTER_RIGHT':
	        		_anchor = new google.maps.Point(icon.src.width,(icon.src.height/2));
	        		break;
	        	case 'BOTTOM_LEFT':
	        		_anchor = new google.maps.Point(0,icon.src.height);
	        		break;
	        	case 'BOTTOM_CENTER':
	        		_anchor = new google.maps.Point((icon.src.width/2),icon.src.height);
	        		break;
	        	case 'BOTTOM_RIGHT':
	        		_anchor = new google.maps.Point(icon.src.width,icon.src.height);
	        		break;
	        	default:
	        		_anchor = new google.maps.Point(0,0);
	        		break;
        	}
        	return _anchor;
        },
        /**
         * Géolocalisation à l'initialisation du champ d'autocomplétion
         */
        _autocompleteGeolocate: function() {
        	var self = this;
        	if (navigator.geolocation) {
	            navigator.geolocation.getCurrentPosition(function(position) {
	                var geolocation = {
	                    lat: position.coords.latitude,
	                    lng: position.coords.longitude
	                };
		            var circle = new google.maps.Circle({
		                center: geolocation,
		                radius: position.coords.accuracy
		            });
		            self.autocomplete.setBounds(circle.getBounds());
	            });
	        }
        },
        /**
         * Autoremplissage des champs à la sélection d'une adresse
         */
        _fillInAddress: function() {
        	var place = this.autocomplete.getPlace();
        	this.element.find('[data-autocomplete]').each(function() {
        		$(this).val('');
        	});
        	if($('[data-autocomplete="lat"]', this.element).length) {
        		$('[data-autocomplete="lat"]', this.element).val(place.geometry.location.lat());
        	}
        	if($('[data-autocomplete="lng"]', this.element).length) {
        		$('[data-autocomplete="lng"]', this.element).val(place.geometry.location.lng());
        	}
        	if($('[data-autocomplete="formatted_address"]', this.element).length) {
        		$('[data-autocomplete="formatted_address"]', this.element).val(place.formatted_address);
        	}
        	for (var i = 0; i < place.address_components.length; i++) {
        		var addressType = place.address_components[i].types[0];
        		if (this.componentForm[addressType] && $('[data-autocomplete="'+addressType+'"]').length) {
        			var val = place.address_components[i][this.componentForm[addressType]];
        			$('[data-autocomplete="'+addressType+'"]', this.element).val(val);
        		}
	        }
        },
        /**
         * Écoute d'évènements
         */
        _listenEvents: function() {
        	this._on(this.document, {
        		click: function(event) {
        			if ($(event.target).closest(this.newMarkerToggle).length || $(event.target).is(this.newMarkerToggle)) {
        				/**
	    				 * Ajout d'un nouveau marqueur
	    				 */
        				this._addNewMarker();
        			} else if ($(event.target).closest(this.saveMarkerToggle).length || $(event.target).is(this.saveMarkerToggle)) {
        				/**
	        			 * Sauvegarde d'un marqueur
	        			 */
        				this._saveMarker();
        			} else if ($(event.target).is($('.tiFyGoogleMap-markerRemove', this.markersList)) || $(event.target).is(this.removeMarkerToggle)) {
        				/**
	        			 * Suppression d'un marqueur
	        			 */
        				this._removeMarker(event);
        			} else if ($(event.target).is($('.tiFyGoogleMap-markerEdit', this.markersList))) {
        				/**
	        			 * Édition d'un marqueur
	        			 */
        				this._editMarker(event);
        			} else if (!$(event.target).closest(this.panel).length && !$(event.target).closest('.tiFyGoogleMap-editorMap').length) {
        				/**
        				 * Clic en dehors des éléments d'édition de marqueurs
        				 */
        				this._clean();
        			}
        		},
        		/**
        		 * Activation de l'animation au survol d'un marqueur
        		 */
        		mouseover: function(event) {
        			if ($(event.target).is($('.tiFyGoogleMap-markerEdit', this.markersList)) && !$(event.target).closest('.tiFyGoogleMap-marker').hasClass('tiFyGoogleMap-marker--editing')) {
        				this._toggleBounceMarker($(event.target).closest('.tiFyGoogleMap-marker').data('id'));
        			}
        		},
        		/**
        		 * Désactivation de l'animation au survol d'un marqueur
        		 */
        		mouseout: function(event) {
        			if ($(event.target).is($('.tiFyGoogleMap-markerEdit', this.markersList)) && !$(event.target).closest('.tiFyGoogleMap-marker').hasClass('tiFyGoogleMap-marker--editing')) {
        				this._toggleBounceMarker($(event.target).closest('.tiFyGoogleMap-marker').data('id'));
        			}
        		}
        	});
        },
        /**
         * Fermeture et suppression du panneau
         */
        _closePanel: function() {
        	var self = this;
        	$('.tiFyControl-adminPanel', this.panel).tiFyAdminPanel('close');
    		setTimeout(function(){
    			self._emptyPanel();
    		},300);
        },
        /**
         * Nettoyage du panneau
         */
        _emptyPanel: function() {
        	if (!this.panel.is(':empty')) {
	    		this.panel.empty();
	    	}
        },
        /**
         * Mise à jour du panneau
         */
        _updatePanel: function(panel) {
        	var self = this;
        	this.panel.html(panel);
    		$('.tiFyControl-adminPanel', this.panel).tiFyAdminPanel();
    		this._setPanelControls();
    		this._loadAutocomplete();
    		setTimeout(function(){
    			$('.tiFyControl-adminPanel', self.panel).tiFyAdminPanel('open');
    		},1);
        },
        /**
         * Ajout d'un nouveau marqueur
         */
        _addNewMarker: function() {
        	var self = this;
        	if (this.panel.is(':empty') || (this.panel.find('.tiFyControl-adminPanel').data('id') !== 0)) {
				$.ajax({
					url: tify_ajaxurl,
				    dataType: 'json',
				    method:	'POST',
				    data: { 
				    	action: this.element.data('new_action'),
				    	_ajax_nonce: this.element.data('ajax_nonce'),
				    	autocomplete_id: this.element.data('autocomplete_form')
				    },
				    beforeSend: function() {
				    	self.newMarkerToggle.addClass('tiFyGoogleMap-editorNewMarker--loading');
				    	self._emptyPanel();
				    },
				    success: function(resp) {
				    	if (resp.success) {
				    		self._updatePanel(resp.data);
				    	}
				    },
				    complete: function() {
				    	self.newMarkerToggle.removeClass('tiFyGoogleMap-editorNewMarker--loading');
				    }
				});
			}
        },
        /**
         * Sauvegarde d'un marqueur
         */
        _saveMarker: function() {
        	var self = this;
        		formDatas = {};
				fields = this.element.data('fields');
			for (var component in fields) {
				if ($('[data-save="'+fields[component]+'"]', this.element).length) {
					formDatas[fields[component]] = $('[data-save="'+fields[component]+'"]', this.element).val();
				}
			}
			$.ajax({
				url: tify_ajaxurl,
			    dataType: 'json',
			    method:	'POST',
			    data: {
			    	post_id: $('#post_ID').val(),
			    	action: this.element.data('save_action'),
			    	_ajax_nonce: this.element.data('ajax_nonce'),
			    	marker: formDatas,
			    	meta_id: $('.tiFyControl-adminPanel', this.panel).data('id')
			    },
			    beforeSend: function() {
			    	self.panel.find('.tiFyControl-adminPanelControlsItem--save').addClass('tiFyControl-adminPanelControlsItem--save--loading');
			    	self.saveMarkerToggle.addClass('disabled');
			    },
			    success: function(resp) {
			    	if (resp.success) {
			    		if (!$('[data-id="'+resp.data.id+'"]', self.markersList).length) {
			    			self.markersList.append(resp.data.render);
			    		} else {
			    			$('[data-id="'+resp.data.id+'"]', self.markersList).replaceWith(resp.data.render);
			    		}
		    			self._updateMapMarker(resp.data.id, resp.data.marker, true);
			    	} else {
			    		alert(resp.data);
			    	}
			    	// Fermeture du panneau et nettoyage de celui-ci
			    	if (!self.panel.is(':empty')) {
			    		self._closePanel();
			    	}
			    },
			    complete: function() {
			    	self.panel.find('.tiFyControl-adminPanelControlsItem--save').removeClass('tiFyControl-adminPanelControlsItem--save--loading');
			    	self.saveMarkerToggle.removeClass('disabled');
			    }
			});
        },
        /**
         * Suppression d'un marqueur
         */
        _removeMarker: function(event) {
        	event.preventDefault();
        	var self = this,
        		meta_id = $(event.target).is(this.removeMarkerToggle) ? $('.tiFyControl-adminPanel', this.panel).data('id') : $(event.target).closest('.tiFyGoogleMap-marker').data('id'),
        		$marker = $('[data-id="'+meta_id+'"]', this.markersList);
        	$.ajax({
				url: tify_ajaxurl,
			    dataType: 'json',
			    method:	'POST',
			    data: {
			    	action: this.element.data('remove_action'),
			    	_ajax_nonce: this.element.data('ajax_nonce'),
			    	meta_id: meta_id
			    },
			    beforeSend: function() {
			    	if ($(event.target).is(self.removeMarkerToggle)) {
			    		self.panel.find('.tiFyControl-adminPanelControlsItem--save').addClass('tiFyControl-adminPanelControlsItem--save--loading');
			    	}
			    	$marker.addClass('tiFyGoogleMap-marker--loading');
			    },
			    success: function(resp) {
			    	if (resp.success) {
			    		$marker.fadeOut(400, function() {
			    			$(this).remove();
			    		});
			    		self._removeMapMarker(resp.data);
			    		self._centerMap();
			    	}
			    	if (!self.panel.is(':empty') && (self.panel.find('.tiFyControl-adminPanel').data('id') === meta_id)) {
			    		self._closePanel();
			    	}
			    },
			    complete: function() {
			    	if ($(event.target).is(self.removeMarkerToggle)) {
			    		self.panel.find('.tiFyControl-adminPanelControlsItem--save').removeClass('tiFyControl-adminPanelControlsItem--save--loading');
			    	}
			    	if ($marker.length) {
			    		$marker.removeClass('tiFyGoogleMap-marker--loading');
			    	}
			    }
			});
        },
        /**
         * Édition d'un marqueur
         */
        _editMarker: function(event) {
        	event.preventDefault();
        	var self = this,
        		$marker = $(event.target).closest('.tiFyGoogleMap-marker'),
        		meta_id = $marker.data('id');
        	this._centerMap(meta_id);
        	this._toggleBounceMarker(meta_id, true);
        	this._removeMarkersAnimation(meta_id);
        	$marker.addClass('tiFyGoogleMap-marker--editing').siblings().removeClass('tiFyGoogleMap-marker--editing');
        	if (this.panel.is(':empty') || (this.panel.find('.tiFyControl-adminPanel').data('id') !== meta_id)) {
        		$.ajax({
					url: tify_ajaxurl,
				    dataType: 'json',
				    method:	'POST',
				    data: {
				    	action: this.element.data('edit_action'),
				    	_ajax_nonce: this.element.data('ajax_nonce'),
				    	autocomplete_id: this.element.data('autocomplete_form'),
				    	meta_id: meta_id
				    },
				    beforeSend: function() {
				    	$marker.addClass('tiFyGoogleMap-marker--loading');
				    	self._closePanel();
				    },
				    success: function(resp) {
				    	if (resp.success) {
				    		self._updatePanel(resp.data);
				    	}
				    },
				    complete: function() {
				    	if ($marker.length) {
				    		$marker.removeClass('tiFyGoogleMap-marker--loading');
				    	}
				    }
				});
        	} else {
        		event.stopPropagation();
				$('.tiFyControl-adminPanel', this.panel).tiFyAdminPanel('open');
        	}
        },
        /**
         * Vérification de l'existence d'un marqueur
         */
        _isMapMarker: function(markerId) {
        	var id = (typeof markerId !== 'undefined') ? markerId : 0;
        	if (typeof this.markers[id] !== 'undefined') {
        		return true;
        	} else {
        		return false;
        	}
        },
        /**
         * Définition des marqueurs
         */
        _setMarkers: function(markers) {
        	for (var id in markers) {
        		this._updateMapMarker(id, markers[id], false);
        	}
        },
        /**
         * Ajout d'un nouveau marqueur sur la carte
         * Créé le marqueur s'il n'existe pas, sinon supprime l'ancien et créé le nouveau
         */
        _updateMapMarker: function(id, marker, center) {
        	var position = new google.maps.LatLng(marker.lat, marker.lng),
        		_center = (typeof center !== 'undefined') ? center : false,
        		_marker = new google.maps.Marker({
	    			position: position,
	    			icon: this.markersTypes[marker.type].icon.src,
	    			map: this.map,
	    			animation: google.maps.Animation.DROP,
	    			title: marker.title,
	    			draggable: true
	    		});
        	if (this._isMapMarker(id)) {
        		this._removeMapMarker(id);
        	}
        	this.markers[id] = _marker;
        	this._addMapMarkerClickEvent(id, this.markers[id]);
        	this._addMapMarkerDragEvent(id, this.markers[id]);
        	if (_center) {
        		this.map.setCenter(position);
        	}
        },
        /**
         * Suppression d'un marqueur sur la carte
         */
        _removeMapMarker: function(id) {
        	this.markers[id].setMap(null);
        	delete this.markers[id];
        },
        /**
         * Centrage de la carte par rapport à un marqueur
         */
        _centerMap: function(markerId) {
        	var id = (typeof markerId !== 'undefined') ? markerId : 0;
        	if (this._isMapMarker(id)) {
        		this.map.setCenter(this.markers[id].getPosition());
        	} else {
        		this.map.setCenter(this.mapCenter);
        	}
        },
        /**
         * Gestion du clic sur un marqueur
         */
        _addMapMarkerClickEvent: function(id, marker) {
        	var self = this;
        	marker.addListener('click', function() {
        		$('[data-id="'+id+'"]', self.markersList).find('.tiFyGoogleMap-markerEdit').trigger('click');
        	});
        },
        /**
         * Gestion du déplacement manuel du marqueur
         */
        _addMapMarkerDragEvent: function(id, marker) {
        	var self = this;
        	marker.addListener('dragend', function() {
        		var $marker = $('[data-id="'+id+'"]', self.markersList);
        		$.ajax({
					url: tify_ajaxurl,
				    dataType: 'json',
				    method:	'POST',
				    data: {
				    	action: self.element.data('drag_action'),
				    	_ajax_nonce: self.element.data('ajax_nonce'),
				    	meta_id: id,
				    	lat: this.getPosition().lat(),
				    	lng: this.getPosition().lng()
				    },
				    beforeSend: function() {
				    	$marker.addClass('tiFyGoogleMap-marker--loading');
				    	self._closePanel();
				    },
				    complete: function() {
				    	if ($marker.length) {
				    		$marker.removeClass('tiFyGoogleMap-marker--loading');
				    	}
				    }
				});
        	});
        },
        /**
         * Effet de rebondissement d'un marqueur
         */
        _toggleBounceMarker: function(id, clicked) {
        	var _clicked = (typeof clicked !== 'undefined') ? clicked : false;
        	if (this._isMapMarker(id)) {
        		if ((this.markers[id].getAnimation() !== null) && !_clicked) {
        			this.markers[id].setAnimation(null);
        		} else {
        			this.markers[id].setAnimation(google.maps.Animation.BOUNCE);
        		}
        	}
        },
        /**
         * Désactivation de l'animation des marqueurs
         */
        _removeMarkersAnimation: function(exclude) {
        	var _exclude = (typeof exclude !== 'undefined') ? exclude : 0;
        	for (var id in this.markers) {
        		if (id != _exclude) {
        			if (this.markers[id].getAnimation() !== null) {
	        			this.markers[id].setAnimation(null);
	        		}
        		}
        	}
        },
        /**
         * Nettoyage de l'édition de la Google Map
         */
        _clean: function() {
        	$('.tiFyGoogleMap-marker--editing', this.markersList).removeClass('tiFyGoogleMap-marker--editing');
			if ($('.tiFyControl-adminPanel', this.panel).length && $('.tiFyControl-adminPanel', this.panel).tiFyAdminPanel('isOpened')) {
				$('.tiFyControl-adminPanel', this.panel).tiFyAdminPanel('close');
			}
			this._removeMarkersAnimation();
        }
    });
    $('.tiFyGoogleMap').tiFyGoogleMapEditor();
})(jQuery, document, window);