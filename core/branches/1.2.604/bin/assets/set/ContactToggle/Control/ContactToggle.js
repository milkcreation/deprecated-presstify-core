jQuery(document).ready(function($) {
    $('.tiFySetContatToggleControl-Modal').each(function(){
        var $modal = $(this);
        var o = JSON.parse(decodeURIComponent($modal.data('options')));

        $.post(
            tify_ajaxurl,
            {
                action: o.ajax_action,
                _ajax_nonce : o.ajax_nonce,
                attrs : o.attrs
            }
        ).
            done(function(data, textStatus, jqXHR) {
                $('.modal-dialog', $modal).replaceWith(data);
            });
    });
});