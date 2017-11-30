jQuery(document).ready(function($){
    $(document).on('change', '.tiFyCoreFields-SwitcherRadio', function(e) {
        $(this)
            .closest('.tiFyCoreFields-Switcher')
            .trigger('tify_fields.switcher.change', $(this).val());
    });
});