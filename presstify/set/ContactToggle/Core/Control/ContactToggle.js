jQuery(document).ready(function($) {
    $(document ).on('click', '[data-tify_control="contact_toggle"]', function(e) {
        e.preventDefault();
                
        var attrs = $(this).data('attrs'),
            target = $(this).data('target');
        
        if( $('[data-role="tiFyModal"][data-id="'+ target +'"]').hasClass('loaded') ){
            $('[data-role="tiFyModal"][data-id="'+ target +'"]').modal('show');
        } else {
            $.post(
                tify_ajaxurl,
                {action: attrs['ajax_action'], _ajax_nonce : attrs['ajax_nonce'], query_args : attrs['query_args']},
                function(resp) {
                    if (resp.success) {
                        $('[data-role="tiFyModal"][data-id="'+ target +'"]')
                            .find('.modal-body').html(resp.data).end()
                            .modal('show')
                            .addClass('loaded');
                    }               
                }
            );
        }
    });
});