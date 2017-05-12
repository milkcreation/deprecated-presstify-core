jQuery( document ).ready( function($){
    // Ajout d'un élément
    $( document ).on( 'click.tify.control.repeater.add', '[data-tify_control="repeater"] .tiFyControlRepeater-Add', function(e){        
        e.stopPropagation();
        e.preventDefault();
        
        var $closest        = $( this ).closest( '[data-tify_control="repeater"]' ),
            $list           = $( '> ul', $closest );
        var count           = $( '> li', $list ).length,
            name            = $( this ).data( 'name' );
            order_name      = $( this ).data( 'order_name' );
            max             = $( this ).data( 'max' );

        if( ( max > 0 ) && ( count >= max ) ) {
            alert( tiFyControlRepeater.maxAttempt );
            return false;
        }
        
        $new     = $( $(this).prev() ).clone();
        index = getUniqIndex( $list );
        var new_html = $new.html().replace( /%%index%%/g, index ).replace( /%%name%%/g, name );
        var $el = $( '<li data-index="'+ index +'">'+ new_html +'<input type="hidden" name="'+ order_name +'[]" value="'+ index +'"/><a href="#'+ $closest.attr( 'id' ) +'" class="tify_button_remove"></a></li>' );
        $list.append( $el );
        
        $el.trigger( 'tify_repeater_added' );
    });
    
    // Ordonnacement des images de la galerie
    $( '.tiFyControlRepeater-Items--sortable' )
        .sortable({ placeholder: 'tiFyControlRepeater-ItemPlaceholder', axis: 'y' })
        .disableSelection();
    
    // Suppression d'un élément
    $( document ).on( 'click.tify.control.repeater.remove', '[data-tify_control="repeater"] > ul > li > .tify_button_remove', function(e){
        e.preventDefault();
        $( this ).closest( 'li' ).fadeOut( function(){
            $( this ).remove();
        });
    });
    
    // Obtention d'un index unique
    function getUniqIndex( $list ) {
        index = $( '> li', $list ).length;
        $( '> li', $list ).each( function() {
            if( $( this ).data( 'index' ) === index )
                index++;
        });
        return index;
    }
});