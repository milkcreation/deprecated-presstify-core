/**
 * @see https://learn.jquery.com/plugins/stateful-plugins-with-widget-factory/
 * @see https://api.jqueryui.com/jquery.widget
 * 
 */
!(function($){
    $.widget('tify.tiFyControlAccordionMenu', {
        options: {

        },
        _create:            function() {
            this.el = this.element;
            $('.tiFyControlAccordionMenu-item--hasChildren > .tiFyControlAccordionMenu-itemLink', this.el).click( function(e){
                e.preventDefault();
                
                $closest = $(this).closest('.tiFyControlAccordionMenu-item');
                $parents = $(this).parents('.tiFyControlAccordionMenu-items');
                
                if($closest.hasClass('active')){
                    $('> .tiFyControlAccordionMenu-items', $closest).css('max-height', 0);
                    $closest.removeClass('active'); 
                } else {            
                    var height = $('> .tiFyControlAccordionMenu-items', $closest).prop('scrollHeight'); 
                    $('> .tiFyControlAccordionMenu-items', $closest).css('max-height', height);            
                    $closest.addClass( 'active' );
                    
                    $parents.each( function(){
                        var pheight = $(this).prop('scrollHeight');
                        $(this).css('max-height', pheight+height);
                    });
                }
            });            
        }
    });
    
    $( '[data-tify_control="accordion_menu"]' ).tiFyControlAccordionMenu();
})(jQuery);