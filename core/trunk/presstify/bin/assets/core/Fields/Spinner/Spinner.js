jQuery(document).ready(function () {
    $('.tiFyCoreFieldsSpinner-input').each(function() {
        var $spinner = $(this).spinner({
            icons: {
                down: 'dashicons dashicons-arrow-down-alt2',
                up: 'dashicons dashicons-arrow-up-alt2'
            }
        });
        if ($(this).is('[readonly]')) {
            $spinner.spinner('disable');
        }
    });
});