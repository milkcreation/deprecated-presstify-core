jQuery(document).ready(function($) {
    $(document).on('click', '.tiFyControlSuggest-button--delete', function(e) {
        e.preventDefault();
        
        var $closest    = $(this).closest('[data-tify_control="suggest"]'),
            select        = $closest.data('select');
        
        $closest.removeClass('tiFyControlSuggest--selected'); 
        $('.tiFyControlSuggest-textInput', $closest).val('').prop('readonly', false);
        $('.tiFyControlSuggest-altInput', $closest).val('');
        
        $closest.trigger('tify_control_suggest_unselected');
    });            
    
    $(document).on('keyup', '.tiFyControlSuggest-textInput', function(e){
        $(this).next('.tiFyControlSuggest-altInput').val($(this).val());
    });
    
    $(document).on('keydown.autocomplete', '[data-tify_control="suggest"]', function(e) {
        $(this).each(function() {
            // Elements du DOM 
            var $this           = $(this),
                $spinner        = $this.find('.tify_spinner');

            // Variables
            var attrs           = $this.data('attrs'),
                defaults        = {
                    source:     function(req, response) {                        
                        $spinner.addClass('active' );
                        $.post(    
                            tify_ajaxurl, 
                            {action: attrs['ajax_action'], _ajax_nonce: attrs['ajax_nonce'], term: req['term'], query_args: attrs['query_args'], elements: attrs['elements'], extras: attrs['extras']}, 
                            function( data ){ 
                                if(data.length) {
                                    response(data);
                                } else {
                                    response([{value: '', label: '', render: tiFyControlSuggest.noResultsFound}]);
                                }                                
                                return;
                            }, 
                            'json' 
                        )
                        .always(function() {
                            $spinner.removeClass('active');
                        });
                    },
                    change: function( event, ui ){
                        // $( 'autocompleteselector' ).on( "autocompletechange", function( event, ui ) {} );
                    },
                    close: function( event, ui ){
                        // $( 'autocompleteselector' ).on( "autocompleteclose", function( event, ui ) {} );
                    },
                    create: function( event, ui ){
                        // $( 'autocompleteselector' ).on( "autocompletecreate", function( event, ui ) {} );
                    },
                    focus: function( event, ui ){
                        // $( 'autocompleteselector' ).on( "autocompletefocus", function( event, ui ) {} );
                    },
                    open: function( event, ui ){
                        // $( 'autocompleteselector' ).on( "autocompleteopen", function( event, ui ) {} );
                    },
                    response: function( event, ui ) {                        
                        // $( 'autocompleteselector' ).on( "autocompletereponse", function( event, ui ) {} );
                    },
                    search: function( event, ui ) {                        
                        // $( 'autocompleteselector' ).on( "autocompletesearch", function( event, ui ) {} );
                    },
                    select: function( event, ui ){
                        // $( 'autocompleteselector' ).on( "autocompleteselect", function( event, ui ) {} ); 
                        $('.tiFyControlSuggest-altInput', $this).val(ui.item.value);

                        
                        if(attrs['select']) {
                            event.preventDefault();
                            
                            $this.addClass('tiFyControlSuggest--selected');
                            $('.tiFyControlSuggest-textInput', $this ).prop('readonly', true);                            
                        }
                        return true;
                    }
                },                
                o = $.extend(attrs['options'], defaults);

            $('.tiFyControlSuggest-textInput', $(this))
                .autocomplete(o)
                .data('ui-autocomplete')._renderItem = function(ul, item) {
                    ul.addClass('tiFyControlSuggest-picker '+ attrs['picker']);
                    
                    return $( '<li class="tiFyControlSuggest-pickerItem">' )
                        .append(item.render)
                        .appendTo(ul);
                };            
        });
    });
});