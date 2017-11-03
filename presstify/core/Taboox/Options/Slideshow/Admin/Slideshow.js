jQuery(document).ready(function ($) {
    //
    var editoroptions_normal = {
        inline: true,
        toolbar: "bold italic",
        menubar: false
    };

    //
    var initItem = function ($item) {
        $('.tinymce-editor', $item).tinymce(editoroptions_normal);

        $('[data-hide_unchecked]', $item).not(':checked').each(function () {
            var target = $(this).data('hide_unchecked');
            $(this).closest('.tiFyTabooxSlideshowList-inputFields').find(target).each(function () {
                $(this).hide();
            });
        });
    };

    //
    var getItem = function (target, post_id) {
        var $target = $(target),
            $container = $target.closest('.tify_taboox_slideshow'),
            action = $container.data('action'),
            max = $container.data('max');

        var count = $('.tiFyTabooxSlideshowList-item', $container).length;

        if (( max > 0 ) && ( count == max )) {
            alert(tiFyTabooxOptionSlideshowAdmin.l10nMax);
            return false;
        }

        $.ajax({
            url: tify_ajaxurl,
            data: {action: action, post_id: post_id, order: parseInt(count + 1)},
            dataType: 'html',
            type: 'post',
            beforeSend: function () {
                $('.tiFyTabooxSlideshowList-overlay', $container).show();
            },
            complete: function (resp) {
                $('.tiFyTabooxSlideshowList-overlay', $container).hide();
            },
            success: function (resp) {
                $('.items > ul', $container).prepend(resp);
                var $item = $('.tiFyTabooxSlideshowList-item:eq(0)', $container);
                initItem($item);
                orderItem($container);
                $(document).trigger('tify_taboox_slideshow_item_load', $item);
            }
        });
        return false;
    };

    // Mise à jour de l'ordre des items
    var orderItem = function ($container) {
        $('.tiFyTabooxSlideshowList-item', $container).each(function () {
            $(this).find('.tiFyTabooxSlideshowItemHelper-orderInput').val(parseInt($(this).index() + 1));
        });
    };

    $('.tiFyTabooxSlideshowList-item').each(function () {
        initItem($(this));
    });

    //
    $(document).on('change', '.tiFyTabooxSlideshowList-inputFields [data-hide_unchecked]', function (e) {
        var target = $(this).data('hide_unchecked');
        if ($(this).is(':checked')) {
            $(this).closest('.tiFyTabooxSlideshowList-inputFields').find(target).each(function () {
                $(this).show();
            });
        } else {
            $(this).closest('.tiFyTabooxSlideshowList-inputFields').find(target).each(function () {
                $(this).hide();
            });
        }
    });

    // Autocomplete
    /// Modification de l'autocomplete pour éviter les doublons
    $('.tiFyTabooxSlideshowSelector-suggest > .tify_taboox_slideshow-suggest[data-duplicate=""] > input[type="text"]').on("autocompletesearch", function (e, ui) {
        var $input = $(e.target),
            $container = $input.closest('.tify_taboox_slideshow'),
            $suggest = $input.closest('[data-tify_control="suggest"]');

        var attrs = $suggest.data('attrs'),
            post__not_in = [];

        $('.tiFyTabooxSlideshowInputField-postIdInput', $container).each(function () {
            post__not_in.push($(this).val());
        });

        if (post__not_in)
            attrs['query_args'] = $.extend(attrs['query_args'], {post__not_in: post__not_in});

        $(e.target).autocomplete('option', 'source', function (req, response) {
            $.post(
                tify_ajaxurl,
                {
                    action: attrs['ajax_action'],
                    _ajax_nonce: attrs['ajax_nonce'],
                    term: req['term'],
                    query_args: attrs['query_args'],
                    elements: attrs['elements'],
                    extras: attrs['extras']
                },
                function (data) {
                    if (data.length) {
                        response(data);
                    } else {
                        response([{value: '', label: '', render: tiFyControlSuggest.noResultsFound}]);
                    }
                    return;
                },
                'json'
            );
        });

    });

    /// Modification de la selection de l'autocomplete
    $('.tiFyTabooxSlideshowSelector-suggest').on("autocompleteselect", function (e, ui) {
        e.preventDefault();

        ui.item.value = '';
        getItem(e.target, ui.item.id);
    });

    // Bouton d'ajout d'un contenu du site à la liste
    $('.tiFyTabooxSlideshowSelector-customAdd').click(function (e) {
        e.preventDefault();

        getItem(e.target, 0);
    });

    // Suppression d'un élément de la liste
    $(document).on('click', '.tiFyTabooxSlideshowItemHelper-remove', function (e) {
        e.preventDefault();
        var $container = $(this).closest('li');

        $container.fadeOut(function () {
            $container.remove();
            orderItem();
        });
    });

    // Trie
    $('.tiFyTabooxSlideshowList-items').sortable({
        axis: "y",
        update: function (event, ui) {
            var container = $(this).closest('.tify_taboox_slideshow');
            orderItem(container);
        },
        handle: ".tify_handle_sort"
    });
});