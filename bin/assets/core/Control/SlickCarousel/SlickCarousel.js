jQuery(document).ready(function ($) {
    $('[data-tify_control="slick_carousel"]').on('init', function (event, slick) {
        $(this).addClass('is-ready');
    });
    $('[data-tify_control="slick_carousel"]').each(function () {
        $(this).slick();
    });
});