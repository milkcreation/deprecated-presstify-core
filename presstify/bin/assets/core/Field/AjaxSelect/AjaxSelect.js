!(function ($, doc, win) {
    $.widget('tify.tiFyFieldAjaxSelect', $.tify.tiFySelect, {
        // Définition des options par défaut
        options: {
            disabled: false,
            multiple: false,
            duplicate: false,
            max: -1,
            picker: {},
            sortable: {},
            autocomplete: false
        },

        controllers : {
            handler : 'tiFyCoreField-ajaxSelectHandler',
            trigger : 'tiFyCoreField-ajaxSelectTrigger',
            selectedList : 'tiFyCoreField-ajaxSelectSelectedItems',
            picker : 'tiFyCoreField-ajaxSelectPicker',
            pickerList : 'tiFyCoreField-ajaxSelectPickerItems',
            pickerLoader : 'tiFyCoreField-ajaxSelectPickerLoader'
        }
    });

    // Initialisation des éléments présent au chargement du DOM
    $('.tiFyCoreField-ajaxSelect').tiFyFieldAjaxSelect();

    // Auto-initialisation des éléments chargés à posteriori dans le DOM
    $(doc).on('mouseenter.tify_field.ajax_select', '.tiFyCoreField-ajaxSelect', function(e) {
        $(this).each(function(){
            $(this).tiFyFieldAjaxSelect();
        });
    });
})(jQuery, document, window);