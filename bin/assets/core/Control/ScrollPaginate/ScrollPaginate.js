var tify_scroll_paginate_xhr, tify_scroll_paginate;
!(function ($, doc, win, undefined) {
    tify_scroll_paginate = function (handler, target)
    {
        // Récupération du receveur d'éléments
        var $target = !target ? $(handler).prev() : $(target);

        // Contrôle d'existance des éléments dans le DOM
        if (!$target.length) {
            return;
        }

        $(window).scroll(function (e) {
            if ((tify_scroll_paginate_xhr === undefined) && !$target.hasClass('tiFyCoreControl-ScrollPaginateComplete') && isScrolledIntoView($(handler))) {
                $(handler).trigger('click');
            }
        });

        $(document).on('click', handler, function (e) {
            // Bypass plus d'élément à charger
            if ($(this).hasClass('tiFyCoreControl-ScrollPaginate--complete')) {
                return false;
            }

            // Définition des arguments
            var $handler = $(handler),
                o = JSON.parse(decodeURIComponent($(this).data('options'))),
                offset = $('> *', $target).length;

            $target.addClass('tiFyCoreControl-ScrollPaginateLoading tiFyCoreControl-ScrollPaginateLoading--target');
            $(handler).addClass('tiFyCoreControl-ScrollPaginateLoading tiFyCoreControl-ScrollPaginateLoading--handler');

            $target.trigger('tify_control.scroll_paginate.loading', $handler);

            tify_scroll_paginate_xhr = $.post(
                tify_ajaxurl,
                {
                    action: o.ajax_action,
                    _ajax_nonce: o.ajax_nonce,
                    options: o,
                    offset: offset
                }
            )
                .done(function(data, textStatus, jqXHR){
                    console.log(data);
                    $target.append(data.html);
                    if (data.complete) {
                        $target.addClass('tiFyCoreControl-ScrollPaginateComplete tiFyCoreControl-ScrollPaginateComplete--target');
                        $handler.addClass('tiFyCoreControl-ScrollPaginateComplete tiFyCoreControl-ScrollPaginateComplete--handler');
                    }
                })
                .then(function(data, textStatus, jqXHR ){
                    $target.removeClass('tiFyCoreControl-ScrollPaginateLoading tiFyCoreControl-ScrollPaginateLoading--target');
                    $handler.removeClass('tiFyCoreControl-ScrollPaginateLoading tiFyCoreControl-ScrollPaginateLoading--handler');

                    $target.trigger('tify_control.scroll_paginate.loaded', $handler);
                    //tify_scroll_paginate_xhr.abort();
                    tify_scroll_paginate_xhr = undefined;
                });
        });
    }

    function isScrolledIntoView($ele) {
        var offset = $ele.offset();
        if (!offset)
            return false;

        var lBound = $(window).scrollTop(),
            uBound = lBound + $(window).height(),
            top = offset.top,
            bottom = top + $ele.outerHeight(true);

        return (top > lBound && top < uBound)
            || (bottom > lBound && bottom < uBound)
            || (lBound >= top && lBound <= bottom)
            || (uBound >= top && uBound <= bottom);
    }
})(jQuery, document, window, undefined);