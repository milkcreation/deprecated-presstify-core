jQuery(document).ready(function ($) {
    var $content = $('.Browser-content'),
        $infos = $('.Browser-itemInfos');

    var getContent = function(folder) {
            $content.addClass('load');
            console.log(folder);
            $.ajax({
                url: tify_ajaxurl,
                data :{
                    action: 'tiFyCoreUiAdminTemplatesBrowser-getContent',
                    folder: folder
                },
                type: 'POST'
            })
            .done(function(resp) {
                $content.html(resp);
                $content.trigger('tify_ui.browser.content.loaded');
            })
            .always(function(){
                $content.removeClass('load');
            });
        },
        getItemInfos = function(item) {
            $infos.addClass('load');

            $.ajax({
                url: tify_ajaxurl,
                data :{
                    action: 'tiFyCoreUiAdminTemplatesBrowser-getItemInfos',
                    item: item
                },
                type: 'POST'
            })
                .done(function(resp) {
                    $infos.html(resp);
                })
                .always(function(){
                    $infos.removeClass('load');
                });
        },
        previewImages = function() {
            $('.Browser-contentFileLink:has(.BrowserFolder-FileIcon--image:not(:has(img)))').each(function () {
                var filename = $(this).data('target');
                var $item = $('.BrowserFolder-FileIcon--image', this);

                $item.addClass('load');

                $.ajax({
                    url: tify_ajaxurl,
                    async: false,
                    cache: false,
                    data: {
                        action: 'tiFyCoreUiAdminTemplatesBrowser-getImagePreview',
                        filename: filename
                    },
                    type: 'POST'
                })
                    .done(function (resp) {
                        $item.html('<img src="' + resp.src + '"/>');
                    })
                    .then(function(){
                        $item.removeClass('load');
                    });
            });
        };

    // Evènements
    // Navigation du fil d'ariane
    $(document).on('click', '.Browser-contentBreadcrumbPartLink', function (e) {
        e.preventDefault();

        getContent($(this).data('target'));
    });

    // Navigation dans un repértoire
    $(document).on('dblclick', '.Browser-contentFileLink', function (e) {
        e.preventDefault();

        if ($(this).hasClass('Browser-contentFileLink--dir')) {
            getContent($(this).data('target'));
        }
    });

    // Selection d'un élément
    $(document).on('click', '.Browser-contentFile:not(:has(.selected)) .Browser-contentFileLink', function (e) {
        e.preventDefault();

        $(this).closest('.Browser-contentFile').addClass('selected').siblings().removeClass('selected');
        getItemInfos($(this).data('target'));
    });

    // Pagination
    $(document).on('tify_ui.browser.content.loaded', function(e){
        tify_scroll_paginate('.Browser-contentPaginate', '.Browser-contentFiles');
    });

    $(document).on('tify_control.scroll_paginate.loading', function(e){
        $folder.addClass('load');
    });

    $(document).on('tify_control.scroll_paginate.loaded', function(e){
        $folder.removeClass('load');
    });
});