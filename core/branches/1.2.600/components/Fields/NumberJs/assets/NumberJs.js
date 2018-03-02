jQuery(document).ready(function ($) {
    $('.tiFyField-numberJs').each(function() {
        var options = JSON.parse(
            decodeURIComponent($(this).data('options'))
        );
        $(this).spinner(options);
    });
});