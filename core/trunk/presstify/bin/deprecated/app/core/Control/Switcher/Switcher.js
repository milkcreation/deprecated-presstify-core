jQuery(document).ready(function($){
    $(document).on('change', '[data-tify_control="switcher"] input[type="radio"]', function(e){
        $(this).closest('[data-tify_control="switcher"]').trigger( 'tify_control.switcher.change', $(this).val() );
    });
});