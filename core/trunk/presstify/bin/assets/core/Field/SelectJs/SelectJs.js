jQuery(document).ready(function($){
    $('.tiFyCoreField-selectJs').tifyselect();

    $(document).on('mouseenter.tify_field.ajax_select', '.tiFyCoreField-selectJs', function (e) {
        $(this).each(function () {
            $(this).tifyselect();
        });
    });
});