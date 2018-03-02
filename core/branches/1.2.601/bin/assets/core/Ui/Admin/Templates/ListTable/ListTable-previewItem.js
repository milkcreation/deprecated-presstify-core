jQuery(document).ready(function($){
    var i18n = tiFyUiAdminListTablePreviewItem;

    $(document).on( 'click', '#the-list .row-actions .preview_item a', function(e){
        e.preventDefault();

        var item_index = url('?' + i18n.item_index_name, $(this).attr('href')),
            nonce = url('?' + i18n.nonce_action, $(this).attr('href')),
            $closest = $(this).closest('tr');

        if (!item_index) {
            return;
        }

        if ($closest.next().attr('id') != 'Item-preview--' + item_index) {
            // Création de la zone de prévisualisation
            $preview = $('#Item-previewContainer').clone(true);
            var id      = 'Item-preview--' + item_index,
                data    = $.extend(
                    {
                        'action' : i18n.action,
                        '_ajax_nonce' : nonce
                    },
                    JSON.parse(
                        decodeURIComponent($('#PreviewItemAjaxData').val())
                    )
                );
                data[i18n.item_index_name] = item_index;

            $preview
                .attr('id', id)
                .hide();

            $closest.after($preview);

            if (i18n.mode === 'dialog') {
                $('#' + id).dialog({
                    autoOpen: false,
                    draggable: false,
                    width: 'auto',
                    modal: true,
                    resizable: false,
                    closeOnEscape: true,
                    position:
                        {
                            my: "center",
                            at: "center",
                            of: window
                        },
                    open: function () {
                        $('.ui-widget-overlay').bind('click', function () {
                            $('#' + id).dialog('close');
                        });
                    },
                    create: function () {
                        $('.ui-dialog-titlebar-close').addClass('ui-button');
                    }
                });
            }

            // Récupération et affichage de la prévisualisation de l'élément
            $.post(
                tify_ajaxurl,
                data,
                function(resp){
                    $('.Item-previewContent', $preview).html(resp);

                    if(i18n.mode === 'dialog') {
                        $('#'+ id).dialog('open');
                    }
                }
            );
        } else {
            $preview = $closest.next();
        }

        $preview.toggle();

        return false;
    });
});