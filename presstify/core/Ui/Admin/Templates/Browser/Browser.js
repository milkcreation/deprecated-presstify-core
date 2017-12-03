jQuery(document).ready(function ($) {
    var $folderContent = $('.BrowserFolderContent');

    var previewImages = function() {
        $('.BrowserFolderContent-itemLink:has(.BrowserFolderContent-itemIcon--image)').each(function () {
            var filename = $(this).data('target');
            var $item = $('.BrowserFolderContent-itemIcon--image', this);

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
                    $item.html('<img src="data:image/' + resp.mime_type + ';base64,' + resp.data + '"/>');
                })
                .then(function(){
                    $item.removeClass('load');
                });
        });
    },
        getFolderContent = function(folder) {
            $folderContent.addClass('load');

            $.post(
                tify_ajaxurl,
                {
                    action: 'tiFyCoreUiAdminTemplatesBrowser-getFolderContent',
                    folder: folder
                }
            )
            .done(function(resp) {
                $folderContent.html(resp);
                previewImages();
            })
            .then(function(){
                $folderContent.removeClass('load');
            });
    };

    // Navigation du fil d'ariane
    $(document).on('click', '.BrowserBreadcrumb-itemLink', function (e) {
        e.preventDefault();

        getFolderContent($(this).data('target'));
    });

    // Navigation
    $(document).on('dblclick', '.BrowserFolderContent-itemLink', function (e) {
        e.preventDefault();

        if ($(this).hasClass('BrowserFolderContent-itemLink--dir')) {
            getFolderContent($(this).data('target'));
        }
    });
});