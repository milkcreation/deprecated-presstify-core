jQuery(document).ready( function($){
    $( '.tiFyTabooxRelatedPosts-suggest' ).each( function(){
        $( this ).on( 'autocompleteselect', function( event, ui ) {
            event.preventDefault();
                        
            var $this        = $(this),
                $closest     = $(this).closest( '.tiFyTabooxRelatedPosts' );
    
            if( ! ui.item.value ) {
                $( 'input[type="text"]', $this ).val( '' );
                return false;
            }            
            
            var post_id         = ui.item.value,
                action          = $( '.tiFyTabooxRelatedPosts-action', $closest ).val(),
                name            = $( '.tiFyTabooxRelatedPosts-item_name', $closest ).val(),
                max             = $( '.tiFyTabooxRelatedPosts-item_max', $closest ).val(),
                count           = $( '.tiFyTabooxRelatedPosts-listItem', $closest ).length;
                
            if( ( max > 0 ) && ( count >= max ) ){
                alert( tiFyTabooxRelatedPostsAdmin.maxAttempt );
                $( 'input[type="text"]', $this ).val( '' );
                return false;
            }
            
            $.ajax({
                url         : tify_ajaxurl,
                data        : { action: action, post_id: post_id, name: name, order: count },
                success        : function( resp ){
                    $( '.tiFyTabooxRelatedPosts-list', $closest ).append( resp );
                    $( 'input[type="text"]', $this ).val( '' );
                },
                type        : 'post',
                dataType    : 'html'
            });
        });
        
        $( this ).on( 'autocompletesearch', function( event, ui ) {    
            var $closest = $(this).closest('.tiFyTabooxRelatedPosts');
            var attrs = $(this).data('attrs');

            // Gestion des doublons
            if( attrs['query_args']['post__not_in'] === undefined ){
                attrs['query_args']['post__not_in'] = [];
            }
            
            /// Empêche la récupération de l'élément courant
            var post_id = $( "#post_ID" ).val();
            if( $.inArray(post_id, attrs['query_args']['post__not_in']) < 0 ){
                attrs['query_args']['post__not_in'].push( post_id );
            }
            
            //// Empêche la récupération des élements déjà selectionnés
            $( '.tiFyTabooxRelatedPosts-listItemPostID', $closest ).each( function(){
                var post_id = $( this ).val();
                if( $.inArray( post_id, attrs['query_args']['post__not_in'] ) < 0 )
                    attrs['query_args']['post__not_in'].push( post_id );
            });
            attrs = $.extend({'query_args': attrs['query_args']}, attrs);
            $(this).data('attrs', attrs);
        });
    });    
    
    // Ordonnacement des éléments
    $( '.tiFyTabooxRelatedPosts-list' ).sortable({
        placeholder: 'tiFyTaboox-TotemListItem--sortablePlaceholder',
        update : function( event, ui ){            
            $('input.tiFyTabooxRelatedPosts-listItemOrder', $(this) ).each( function( u, v ){
                $(this).val( $(this).closest( 'li' ).index()+1 );
            });
        }
    });
    $( '.tiFyTabooxRelatedPosts-list' ).disableSelection();
    
    // Suppression d'un élément
    $(document).on('click', '.tiFyTabooxRelatedPosts-listItemRemove', function(e){
        e.preventDefault();    
        
        var $suggest     = $( '.tiFyTabooxRelatedPosts-suggest', $( this ).closest( '.tiFyTabooxRelatedPosts' ) ),
            $item         = $( this ).closest( '.tiFyTabooxRelatedPosts-listItem' );
        
        // Traitement des doublons
        var attrs     = $suggest.data('attrs');
        if( attrs['query_args']['post__not_in'] !== undefined ) {
            var post_id    = $( '.tiFyTabooxRelatedPosts-listItemPostID', $item ).val();     
            var index = $.inArray( post_id, attrs['query_args']['post__not_in'] );
            if( index > -1 ){
                attrs['query_args']['post__not_in'].splice(index, 1);
            }
            attrs = $.extend({'query_args': attrs['query_args']}, attrs);
            $suggest.data('attrs', attrs);
        }
        
        $item.fadeOut( function(){    
            var $closest = $(this).closest( '.tiFyTabooxRelatedPosts-list' );
            $(this).remove();
            $( 'input.tiFyTabooxRelatedPosts-listItemOrder', $closest ).each( function( u, v ){
                $(this).val( $(this).closest( 'li' ).index()+1 );
            });
        });
    });
    
    $(document).on( 'mouseenter mouseleave click', '.tiFyTabooxRelatedPosts-listItemMetaToggle', function(e){
        e.preventDefault();
        $( this ).next().toggleClass( 'active' );
    });
});