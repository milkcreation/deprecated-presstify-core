!( function( $, doc, win, undefined ){
	"use strict";
	var name = 'milk-dynamic-tabs';

	var methods = {
		init: function(opts){
			return this.each(function (i, el) {
				var instance = new MilkDynamicTabs( this, opts );
			});
		}
	};
		
	$.fn.milkDynamicTabs = function( method ){
		if (methods[method]) {
			return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('La methode ' + method + ' n\'existe pas pour jQuery.milkDynamicTabs');
		}
 	};	
		
	function MilkDynamicTabs( el, opts ) {
		this.$el = $(el);
				
		// Mise en cache d'une référence à l'objet 
		this.$el.data(name, this);
	
		// Traitement des options
		var meta  = this.$el.data( name+'-opts' );
		this.defaults = {  
			container 	: this.$el.data('container'),
			group 		: this.$el.data('group'),
			name 		: this.$el.data('name'),
			callback 	: function( uniqid ){
	    		return false;
	    	}			
		};
	    this.o = $.extend(true, this.defaults, opts );
		// Initialisation	
	    this.init();	   
	}
	
	// Prototype	
	MilkDynamicTabs.prototype = 
	{
		// Initialisation du plugin	    
	 	init : function( ){
			var self = this;
			// Ecoute des actions sur la galerie
			self._listen();			
		},
		// Ecoute des actions
		_listen : function(){
			var self = this;
			this.$el.click(function(e) {
		        e.preventDefault();
				
		        var id 			= $(this).parents( '.nav-tabs' ).children().length;
				var container 	= $(this).data('container');
				var group 		= $(this).data('group');
				var name 		= $(this).data('name');		
				var sample_html = $( $(this).data('sample') ).html().replace( /%%index%%/g, 1 ).replace( /%%name%%/g, name );
						
		        $(this).closest('li').before('<li><a data-toggle="tab" data-current="'+container+',dynamic_tab-content-'+id+'" data-group="'+group+'" href="#dynamic_tab-content-'+id+'"><input type="text" name="'+name+'[1][tab-title]" value="Zone de texte #1"/></a></li>');         
		        $('.tab-content',  $(this).parents(".tab-pane") ).append('<div class="tab-pane" id="dynamic_tab-content-'+id+'">'+sample_html+'</div>').show();        
			});		
		}
	};	
})( jQuery, document, window, undefined );

jQuery( document ).ready( function($){
	$( '#add-tab').milkDynamicTabs({
		callback : function( name, uniqid ){
			textarea = $( '#ajax_wp_editor'+uniqid );
			$.ajax({
				url : tify_ajaxurl,
				data : { action : 'wp_editor_box_editor_html', content:"", id:'ajax_wp_editor'+uniqid, textarea_name:name+'['+uniqid+'][txt]'},
				type : 'post',
				// the ajax respnose code
				success : function(response){
				    textarea.replaceWith(response);
				    tinyMCE.init(tinyMCEPreInit.mceInit[textarea.attr('id')]);
				    try { quicktags( tinyMCEPreInit.qtInit[textarea.attr('id')] ); } catch(e){}
				}
			});
			$('#colorpicker'+uniqid).spectrum( spectrum_args ); 
		}
	});	
});