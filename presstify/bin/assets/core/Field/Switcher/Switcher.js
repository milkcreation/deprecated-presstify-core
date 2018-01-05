jQuery(document).ready(function($){
    $(document).on('change', '.tiFyCoreField-switcherRadio', function(e) {
        $(this)
            .closest('.tiFyCoreField-switcher')
            .trigger('tify_field.switcher.change', $(this).val());
    });
});