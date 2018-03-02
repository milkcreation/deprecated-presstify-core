jQuery(document).ready(function($){
    $('.tiFyField-selectJs').tifyselect();

    $(document).on('mouseenter.tify_field.ajax_select', '.tiFyField-selectJs', function (e) {
        $(this).each(function () {
            $(this).tifyselect();
        });
    });
});