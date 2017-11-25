jQuery(document).ready(function ($) {
    $('.tiFyCoreFields-Spinner').each(function() {
        var options = JSON.parse(
            decodeURIComponent($(this).data('options'))
        );

        $(this).spinner(options);
    });
});