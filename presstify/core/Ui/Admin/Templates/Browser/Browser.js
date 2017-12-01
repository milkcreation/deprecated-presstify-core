jQuery(document).ready(function ($) {
    $(document).on('dblclick', '.BrowserFolderContent-itemLink', function (e) {
        e.preventDefault();

        //style=\"background-image:url(data:image/{$mime_type};base64,{});\"

        var $closest = $(this).closest('.BrowserFolderContent');

        if ($(this).hasClass('BrowserFolderContent-itemLink--dir')) {
            var folder = $(this).data('target');
            $closest.addClass('load');

            $.post(
                tify_ajaxurl,
                {
                    action: 'tiFyCoreUiAdminTemplatesBrowser-getFolderContent',
                    folder: folder
                }
            )
                .done(function(resp) {
                    $closest.html(resp);
                })
                .then(function(){
                    $closest.removeClass('load');
                });
        }
    });

    $('.BrowserFolderContent-itemLink:has(.BrowserFolderContent-itemIcon--image)').each(function(){
        var filename = $(this).data('target');
        var $item = $('.BrowserFolderContent-itemIcon--image', this);

        $.ajax({
            url: tify_ajaxurl,
            async: true,
            data: {
                action: 'tiFyCoreUiAdminTemplatesBrowser-getImagePreview',
                filename: filename
            },
            type: 'POST'
        })
            .done(function(resp) {
                $item.css('background-image', 'url(data:image/'+resp.mime_type+';base64,'+resp.data+')');
            });
    });
});