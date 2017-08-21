var tiFyImageLightbox;

!( function( $, doc, win, undefined ){
    
    var
    // ACTIVITY INDICATOR
    activityIndicatorOn = function(theme)
    {
        $( '<div id="tiFyImageLightbox-loading" class=\"tiFyImageLightbox-loading tiFyImageLightbox-loading'+ theme +'\"><div></div></div>' ).appendTo( 'body' );
    },
    activityIndicatorOff = function()
    {
        $( '#tiFyImageLightbox-loading' ).remove();
    },

    // OVERLAY
    overlayOn = function(theme)
    {
        $( '<div id="tiFyImageLightbox-overlay" class="tiFyImageLightbox-overlay tiFyImageLightbox-overlay--'+ theme +'"></div>' ).appendTo( 'body' );
    },
    overlayOff = function()
    {
        $( '#tiFyImageLightbox-overlay' ).remove();
    },

    // CLOSE BUTTON
    closeButtonOn = function(instance, theme)
    {
        $( '<button type="button" id="tiFyImageLightbox-close" class="tiFyImageLightbox-close tiFyImageLightbox-close--'+ theme +'" title="Close"></button>' ).appendTo( 'body' ).on( 'click touchend', function(){ $( this ).remove(); instance.quitImageLightbox(); return false; });
    },
    closeButtonOff = function()
    {
        $( '#tiFyImageLightbox-close' ).remove();
    },

    // CAPTION
    captionOn = function( instance, selector, theme )
    {
        var current = selector.filter( '[href="' + $( '#tiFyImageLightbox' ).attr( 'src' ) + '"]' );
        var caption = '';
        if( caption = current.data( 'caption' ) ){
        } else if( caption = $( 'img', current ).attr('alt') ){
        } else if( caption = $( current ).attr( 'title' ) ){
        }

        if( caption )
            $( '<div id="tiFyImageLightbox-caption" class="tiFyImageLightbox-caption tiFyImageLightbox-caption--'+ theme +'">' + caption + '</div>' ).appendTo( 'body' );
    },
    captionOff = function()
    {
        $( '#tiFyImageLightbox-caption' ).remove();
    },

    // NAVIGATION
    navigationOn = function(instance, selector, theme)
    {        
        if( instance.length < 2 )
            return;
        
        var nav = $( '<div id="tiFyImageLightbox-nav" class="tiFyImageLightbox-nav tiFyImageLightbox-nav'+ theme +'"></div>' );
        for( var i = 0; i < instance.length; i++ )
            nav.append( '<button type="button"></button>' );

        nav.appendTo( 'body' );
        nav.on( 'click touchend', function(){ return false; });

        var navItems = nav.find( 'button' );
        navItems.on( 'click touchend', function()
        {
            var $this = $( this );
            if( selector.eq( $this.index() ).attr( 'href' ) != $( '#tiFyImageLightbox' ).attr( 'src' ) ){
                instance.switchImageLightbox( $this.index() );
            }

            navItems.removeClass( 'active' );
            navItems.eq( $this.index() ).addClass( 'active' );

            return false;
        })
        .on( 'touchend', function(){ return false; });
    },
    navigationUpdate = function( instance, selector )
    {
        var items = $( '#tiFyImageLightbox-nav button' );
        items.removeClass( 'active' );
        
        var current = selector.filter( '[href="' + $( '#tiFyImageLightbox' ).attr( 'src' ) + '"]' );
        
        items.eq( selector.index( current ) ).addClass( 'active' );
    },
    navigationOff = function()
    {
        $( '#tiFyImageLightbox-nav' ).remove();
    },

    // ARROWS
    arrowsOn = function( instance, selector, theme )
    {
        if( instance.length < 2 )
            return;
        
        var $arrows = $( '<button type="button" class="tiFyImageLightbox-arrow tiFyImageLightbox-arrow--left tiFyImageLightbox-arrow--'+ theme +'"></button><button type="button" class="tiFyImageLightbox-arrow tiFyImageLightbox-arrow--right tiFyImageLightbox-arrow--'+ theme +'"></button>' );

        $arrows.appendTo( 'body' );

        $arrows.on( 'click touchend', function( e )
        {
            e.preventDefault();

            var $this    = $( this ),
                $target    = selector.filter( '[href="' + $( '#tiFyImageLightbox' ).attr( 'src' ) + '"]' ),
                index    = selector.index( $target );

            if( $this.hasClass( 'tiFyImageLightbox-arrow--left' ) ) {
                index = index - 1;
                if( ! selector.eq( index ).length )
                    index = selector.length;
            } else {
                index = index + 1;
                if( ! selector.eq( index ).length )
                    index = 0;
            }
            
            instance.switchImageLightbox( index );
            return false;
        });
    },
    arrowsOff = function()
    {
        $( '.tiFyImageLightbox-arrow' ).remove();
    };
    
    tiFyImageLightbox = function( $selectors, o )
    {
        $selectors = $selectors.filter( function(){ return $(this).attr('href').match(/\.(jpe?g|png|gif)/i); });

        var opts = {
            selector:        'id="tiFyImageLightbox"',
            
            enableKeyboard:    o.keyboard,
            
            quitOnDocClick:    o.overlay_close,
            
            animationSpeed: parseInt( o.animation_speed ),
        
            onStart:        function() { 
                if( o.overlay )
                    overlayOn(o.theme); 
                if( o.close_button )
                    closeButtonOn(instance, o.theme);
                if( o.navigation )
                    arrowsOn(instance, $selectors, o.theme); 
                if( o.tabs )
                    navigationOn(instance, $selectors, o.theme);
            },
            
            onEnd:            function() { 
                if( o.overlay )
                    overlayOff(); 
                if( o.caption )
                    captionOff(); 
                if( o.close_button )
                    closeButtonOff();
                if( o.navigation )  
                    arrowsOff();
                if( o.spinner ) 
                    activityIndicatorOff(); 
                if( o.tabs )
                    navigationOff();
            },
            
            onLoadStart:         function() { 
                if( o.caption )
                    captionOff();
                if( o.spinner ) 
                    activityIndicatorOn(o.theme); 
            },
            
            onLoadEnd:         function() { 
                if( o.caption )
                    captionOn( instance, $selectors, o.theme );
                if( o.spinner ) 
                    activityIndicatorOff(); 
                if( o.navigation )
                    $( '.tiFyImageLightbox-arrow' ).css( 'display', 'block' ); 
                if( o.tabs )
                    navigationUpdate( instance, $selectors );
            }
        };
        var instance = $selectors.imageLightbox( opts );
    };
})( jQuery, document, window, undefined );