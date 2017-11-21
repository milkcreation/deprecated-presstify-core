jQuery(document).ready(function($) {
    var jqxhr;
    
    // Ajout d'un élément
    $( document ).on('click.tify.control.repeater.add', '[data-tify_control="repeater"] .tiFyControlRepeater-Add', function(e) {        
        e.stopPropagation();
        e.preventDefault();
        
        if( jqxhr !== undefined )
            return;
        
        // Eléments du DOM
        var $this           = $(this);
            $closest        = $(this).closest('[data-tify_control="repeater"]'),
            $list           = $('.tiFyControlRepeater-Items', $closest);
        
        // Variables
        var index           = $('.tiFyControlRepeater-Item', $list).length,
            attrs           = $(this).data('attrs');
   
        if((attrs['max'] > 0) && (index >= attrs['max'])) {
            alert( tiFyControlRepeater.maxAttempt );
            return false;
        }
       
        jqxhr = $.post(
            tify_ajaxurl,
            {action: attrs['ajax_action'], _ajax_nonce: attrs['ajax_nonce'], index: index, attrs: attrs},
            function( resp ){
                $el = $(resp);
                $list.append($el);
            }            
        )
        .done(function() {
            $el.trigger('tify_control_repeater_item_added');
        })
        .always(function() {
            jqxhr = undefined;
        });
    });
    
    // Ordonnacement des images de la galerie
    $('.tiFyControlRepeater-Items--sortable')
        .sortable({placeholder: 'tiFyControlRepeater-ItemPlaceholder', axis: 'y'})
        .disableSelection();
    
    // Suppression d'un élément
    $(document).on('click.tify.control.repeater.remove', '[data-tify_control="repeater"] .tiFyControlRepeater-Item > .tify_button_remove', function(e) {
        e.preventDefault();
        $(this).closest('.tiFyControlRepeater-Item').fadeOut( function(){
            $(this).remove();            
            $(document).trigger('tify_control_repeater_item_removed');
        });
    });
});