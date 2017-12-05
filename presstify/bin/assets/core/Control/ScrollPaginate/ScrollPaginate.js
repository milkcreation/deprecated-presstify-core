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

        /*
        $(window).scroll(function (e) {
            if ((tify_infinite_scroll_xhr === undefined) && !$(this).hasClass('ty_iscroll_complete') && isScrolledIntoView($(handler)))
                $(handler).trigger('click');
        });
        */

        $(document).on('click', handler, function (e) {
            // Bypass plus d'élément à charger
            if ($(this).hasClass('tiFyCoreControl-ScrollPaginate--complete')) {
                return false;
            }

            // Définition des arguments
            var $handler = $(handler),
                o = JSON.parse(decodeURIComponent($(this).data('options'))),
                from = $('> *', $target).length;

            $target.addClass('tiFyCoreControl-ScrollPaginateTarget--load');
            $(handler).addClass('tiFyCoreControl-ScrollPaginateHandler--load');

            tify_scroll_paginate_xhr = $.post(
                tify_ajaxurl,
                {
                    action: o.ajax_action,
                    _ajax_nonce: o.ajax_nonce,
                    query_args: o.query_args,
                    before: o.before,
                    after: o.after,
                    per_page: o.per_page,
                    item_cb: o.item_cb,
                    from: from
                }
            )
                .done(function(resp){
                    console.log(resp);
                });

                /*
                    $target.removeClass('ty_iscroll_load');
                    $(handler).removeClass('ty_iscroll_load');

                    $target.append(resp);
                    var complete = resp.match(/<!-- tiFy_Infinite_Scroll_End -->/);
                    if (complete) {
                        $target.addClass('ty_iscroll_complete');
                        $(handler).addClass('ty_iscroll_complete');
                    }
                    $target.trigger('ty_iscroll_loaded', $(handler));
                    tify_infinite_scroll_xhr.abort();
                    tify_infinite_scroll_xhr = undefined;
                }*/
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