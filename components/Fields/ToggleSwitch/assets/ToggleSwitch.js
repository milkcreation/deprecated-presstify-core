jQuery(document).ready(function($){
    $(document).on('change', '.tiFyField-toggleSwitchRadio', function(e) {
        $(this)
            .closest('.tiFyField-toggleSwitch')
            .trigger('tify_field.toggleSwitch.change', $(this).val());
    });
});