!(function ($, doc, win) {
    $.widget('tify.tiFyFieldAjaxAutocomplete', $.tify.tiFySelect, {
        // Définition des options par défaut
        options: {
            disabled: false,
            multiple: false,
            duplicate: false,
            max: -1,
            picker: {},
            sortable: {},
            autocomplete: {}
        },

        controllers : {
            handler : 'tiFyCoreField-ajaxAutocompleteHandler',
            trigger : 'tiFyCoreField-ajaxAutocompleteTrigger',
            selectedList : 'tiFyCoreField-ajaxAutocompleteSelectedItems',
            picker : 'tiFyCoreField-ajaxAutocompletePicker',
            pickerList : 'tiFyCoreField-ajaxAutocompletePickerItems',
            pickerLoader : 'tiFyCoreField-ajaxAutocompletePickerLoader'
        }
    });

    // Initialisation des éléments présent au chargement du DOM
    $('.tiFyCoreField-ajaxAutocomplete').tiFyFieldAjaxAutocomplete();

    // Auto-initialisation des éléments chargés à posteriori dans le DOM
    $(doc).on('keydown.tify_field.ajax_autocomplete', '.tiFyCoreField-ajaxAutocomplete', function (e) {
        $(this).each(function () {
            $(this).tiFyFieldAjaxAutocomplete();
        });
    });
})(jQuery, document, window);