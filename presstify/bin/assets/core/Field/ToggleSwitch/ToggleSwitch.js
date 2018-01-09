jQuery(document).ready(function($){
    $(document).on('change', '.tiFyCoreField-toggleSwitchRadio', function(e) {
        $(this)
            .closest('.tiFyCoreField-toggleSwitch')
            .trigger('tify_field.toggleSwitch.change', $(this).val());
    });
});