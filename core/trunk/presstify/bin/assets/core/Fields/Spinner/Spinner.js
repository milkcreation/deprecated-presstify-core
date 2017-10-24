jQuery(document).ready(function ($) {
    $('.tiFyCoreFieldsSpinner-input').each(function() {
        $(this).spinner({
            icons: {
                down: 'dashicons dashicons-arrow-down-alt2',
                up: 'dashicons dashicons-arrow-up-alt2'
            }
        });
    });
});