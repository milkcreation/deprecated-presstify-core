jQuery(document).ready(function ($) {
    var $folder = $('.BrowserFolder');

    var previewImages = function() {
        $('.BrowserFolder-FileLink:has(.BrowserFolder-FileIcon--image)').each(function () {
            var filename = $(this).data('target');
            var $item = $('.BrowserFolder-FileIcon--image', this);

            $item.addClass('load');

            $.ajax({
                url: tify_ajaxurl,
                async: true,
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

            $.post(
                tify_ajaxurl,
                {
                    action: 'tiFyCoreUiAdminTemplatesBrowser-getFolderContent',
                    folder: folder
                }
            )
            .done(function(resp) {
                $folder.html(resp);
                previewImages();
            })
            .then(function(){
                $folder.removeClass('load');
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