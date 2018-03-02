jQuery(document).ready(function ($) {
    $(document).
        on(
            'change',
            '[data-tify_control="checkbox"] > label > input[type="checkbox"]',
            function (e) {
                e.preventDefault();
                e.stopPropagation();

                $parent = $(this).closest('[data-tify_control="checkbox"]');
                if ($parent.hasClass('checked')) {
                    $parent.removeClass('checked');
                } else {
                    $parent.addClass('checked');
                }
            }
        );
});