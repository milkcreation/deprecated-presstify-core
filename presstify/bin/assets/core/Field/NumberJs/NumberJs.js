jQuery(document).ready(function ($) {
    $('.tiFyCoreField-numberJs').each(function() {
        var options = JSON.parse(
            decodeURIComponent($(this).data('options'))
        );
        $(this).spinner(options);
    });
});