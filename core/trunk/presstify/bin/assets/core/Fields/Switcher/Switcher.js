jQuery(document).ready(function($){
    $(document).on('change', '.tiFyCoreFieldsSwitcher input[type="radio"]', function(e) {
        $(this)
            .closest('.tiFyCoreFieldsSwitcher')
            .trigger('tify_control.switcher.change', $(this).val());
    });
});