jQuery(document).ready(function($){
	//Sauvegarde de la tabulation courante 
	$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    	if( $(this).data('current') && $(this).data('group') )
    		$.post( ajaxurl, { action: 'mkcrm_update_current_tab', current: $(this).data('current'), tabindex : $(this).data('group') } );
	});
	$( '.mkcrm-metabox-topnav' ).each( function(){
		if( ! $('li.active', $(this) ).length ){
			$('li:first', $(this) ).addClass('active');
			$('.tab-pane:first', $(this).next() ).addClass('active');
		}
	});
	
	// Suppression des éléments
	$('body').on( 'click', '.mkcrm-metabox-inside .remove', function(e){
		e.preventDefault();
		$(this).parent( ).fadeOut( function(){ $(this).remove(); });
	});
	// Masquage d'éléments
	$('body').on( 'click', '.mkcrm-metabox-inside *[data-target-hide]', function(e){
		$( $(this).data('target-hide') ).fadeOut();
	});
	$('.mkcrm-metabox-inside *[data-target-hide]:checked').each( function(){
		$( $(this).data('target-hide') ).hide();
	});
	$('body').on( 'click', '.mkcrm-metabox-inside *[data-target-show]', function(e){
		$( $(this).data('target-show') ).fadeIn();
	});
	//Clonage d'élément
	$('body').on( 'click', '.mkcrm-metabox-inside .clone', function(e){
		e.preventDefault();
		$clone = $( $(this).parent() ).clone();
		$('input', $clone ).val('');
		$clone.insertAfter( $(this).parent() );
	});
	// Déplacement d'élément
	$('body').on( 'click', '.mkcrm-metabox-inside .movesample', function(e){
		e.preventDefault();
		if( !$(this).prev().hasClass('sample') )
			return;
		$this = $(this);
		$container = $this.parent();
		var target = $(this).data('target');
		var action = $(this).data('action');
		$( '.spinner', $container ).show();
		$.post( ajaxurl, {action : action }, function( sample ){ 
			$this.prev().fadeOut( function(){
				$(this).appendTo( target ).removeClass('sample').fadeIn();
				$( sample ).insertBefore( $this );
				$( '.spinner', $container ).hide();
			});
		}, 'html');		
	});
});