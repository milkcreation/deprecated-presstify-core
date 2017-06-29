jQuery(document).ready(function($) {
    $(document).on('click', '[data-tify_control="cookie_notice"] [data-toggle]', function(e){
        e.preventDefault();
        
        $closest = $(this).closest('[data-tify_control="cookie_notice"]');
        var attrs = $closest.data('attrs');
        
        $.post( 
            tify_ajaxurl, 
            {action: attrs['ajax_action'], _ajax_nonce: attrs['ajax_nonce'], cookie_name: attrs['cookie_name'], cookie_expire: attrs['cookie_expire']}, 
            function(resp){
                
            }
        )
        .always(function(){
            $closest.fadeOut(function(){
                $(this).remove();
            }); 
        });
         
    });
});