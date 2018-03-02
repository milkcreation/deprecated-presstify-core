jQuery( document ).ready( function($){    
    $(document).on( 'click', '[data-tify_control="calendar"] a[data-toggle]', function(event){
        event.preventDefault();
        var $closest = $(this).closest( '[data-tify_control="calendar"]' )
            id = $closest.data('id'),
            selected = $(this).data( 'toggle' );

        $closest.addClass( 'load' );
        $.post( tify_ajaxurl, { action: 'tiFyControlCalendar', id: id, selected: selected }, function( resp ){
            var $new = $( resp ).replaceAll( $closest );
            $new.trigger( 'tify_control.calendar.loaded', { id: id, selected: selected });
        });
    });
});