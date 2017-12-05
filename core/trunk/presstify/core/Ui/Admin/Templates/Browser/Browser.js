jQuery(document).ready(function ($) {
    var $folder = $('.BrowserFolder');

    var previewImages = function() {
        $('.BrowserFolder-FileLink:has(.BrowserFolder-FileIcon--image:not(:has(img)))').each(function () {
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
    },
        getFolderContent = function(folder) {
            $folder.addClass('load');

            $.ajax({
                url: tify_ajaxurl,
                data :{
                    action: 'tiFyCoreUiAdminTemplatesBrowser-getFolderContent',
                    folder: folder
                },
                type: 'POST',
                xhrFields:{
                    id: 'getFolderContent'
                }
            })
            .done(function(resp) {
                $folder.html(resp);
            })
            .always(function(){
                $folder.removeClass('load');
                previewImages();
            });
    };

    // Navigation du fil d'ariane
    $(document).on('click', '.BrowserFolder-BreadcrumbPartLink', function (e) {
        e.preventDefault();

        getFolderContent($(this).data('target'));
    });

    // Navigation
    $(document).on('dblclick', '.BrowserFolder-FileLink', function (e) {
        e.preventDefault();

        if ($(this).hasClass('BrowserFolder-FileLink--dir')) {
            getFolderContent($(this).data('target'));
        }
    });

    // Selection
    $(document).on('click', '.BrowserFolder-File:not(:has(.selected)) .BrowserFolder-FileLink', function (e) {
        e.preventDefault();

        $(this).closest('.BrowserFolder-File').addClass('selected').siblings().removeClass('selected');
    });
});