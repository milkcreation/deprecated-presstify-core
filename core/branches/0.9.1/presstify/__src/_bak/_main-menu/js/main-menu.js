var file_frame;
jQuery(document).ready(function($){
	var $container = $('#mainmenu-editor');
	var editoroptions_normal = {
	    //inline: true,
	    toolbar: "bold italic",
	    language : 'fr_FR',
	    resize: false,
		menubar:false,
		statusbar :false
	};
	// Ajout d'une entrée de menu
	$( "#add-mainmenu-entry", $container ).click( function(e){
		e.preventDefault();
		$.post( ajaxurl, {action :'add_mainmenu_entry'}, function( data ){
			$( "#mainmenu-entries", $container ).append( data );
			init_panel( $( "#mainmenu-entries > li:last", $container ) );	
		}, 'html');
	});
	// Trie des entrées de menu
	$( '#mainmenu-entries', $container).sortable({
		axis:'y',
		handle: ".move",
		containment: "#mainmenu-entries",
		start: function(e, ui){
		    $('.panel-text', $(this) ).each(function(){
		       $(this).tinymce().remove();
		    });
		},
		stop: function(e,ui) {
		     $('.panel-text', $(this) ).each(function(){
		        $(this).tinymce(editoroptions_normal);
		    });
		}		
	});	
	// Affichage du panneau d'édition 
	$(document).on('click', '#mainmenu-editor #mainmenu-entries > li .deploy', function(){
		$(this).parent('li').toggleClass('active').siblings().removeClass('active');
	});
	// Initialisation des fonctionnalités des panneaux	
	function init_panel( $target ){
		// Editeur de description		
		$('.panel-text', $target ).tinymce(editoroptions_normal);
		// Recherche d'un lien secondaire par autocompletion	
		$( '.search-content', $target ).autocomplete({
			source:		ajaxurl + '?action=adtc_autocomplete_mainmenu',
			minLength:	2,
			select: function( event, ui ) {
				$(this).val( ui.item.label );
				$(this).next().data( 'item', ui.item ).removeAttr('disabled');
			}
		});
		$( '.search-content', $target ).each( function(){
				$(this).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
					return $( "<li>" )
					.append( "<a>" + item.label + "<br><em style=\"font-size:0.8em;\"><strong>" + item.type + "</strong></em></a>" )
					.appendTo( ul );
			};
		});
		$('.customlink-panel-links', $target).sortable();		
	}
	init_panel($container);
	// Ajout d'un sous lien de menu
	$(document).on('click', '#mainmenu-editor .add-custom-sublink', function(e){
		var index = $(this).data('index');
		var item =  $(this).data('item');
		var subindex = getUniqueID();
		var html = '';
		if(  index && item && subindex ) {
			html += '<li>';
			html += '<textarea rows="1" name="adtc_mainmenu_sublinks['+index+']['+subindex+'][txt]" class="title">'+item.label+'</textarea>';
			html += '<input type="hidden" name="adtc_mainmenu_sublinks['+index+']['+subindex+'][id]" value="'+item.id+'" />';
			html += '<a href="#remove" class="mktzr_remove"></a>';
			html += '</li>';	
		}
		$(this).next().append( html );
		$(this).data( 'item', '' ).attr('disabled', 'disabled');
		$(this).prev().val('');
	});
	// Selecteur d'image
	$(document).on('click', '.add-submenu-image', function( e ){
	 	e.preventDefault();
		
		if( $(this).hasClass('remove') ){
			$(this).empty().removeClass('remove');
			return;
		}		
		
		var index = $( this ).data( 'index' );
		var $target = $( this )
		file_frame = wp.media.frames.file_frame = wp.media({
			title: $( this ).data( 'uploader_title' ),
			editing: true,
			multiple: false
		});
		 
		file_frame.on( 'select', function() {
			attachment = file_frame.state().get('selection').first().toJSON();
			if( attachment.sizes['single'] == undefined )
				var image = attachment.sizes['full'].url;
			else	
				var image = attachment.sizes['single'].url;
			
			var html  = '<img src="'+image+'" width="180" height="auto" />';
				html += '<input type="hidden" name="adtc_mainmenu[custom_link]['+index+'][pthumb]" value="'+attachment.id+'" />'; 
			
			$target.html( html ).addClass('remove');
		});	
		file_frame.open();
	});
	// Contrôleur de génération d'identifiant unique
	function getUniqueID() { 
		var uniqueID = new Date();
		return uniqueID.getTime();
	}
});