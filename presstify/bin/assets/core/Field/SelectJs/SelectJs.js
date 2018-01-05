!(function ($, doc, win) {
    $.widget('tify.tiFyFieldSelectJs', $.tify.tiFySelect, {
        // Définition des options par défaut
        options: {
            disabled: false,
            multiple: false,
            duplicate: false,
            max: -1,
            picker: {filter: true},
            sortable: {},
            autocomplete: false
        },

        controllers : {
            handler : 'tiFyCoreField-selectJsHandler',
            trigger : 'tiFyCoreField-selectJsTrigger',
            selectedList : 'tiFyCoreField-selectJsSelectedItems',
            picker : 'tiFyCoreField-selectJsPicker',
            pickerList : 'tiFyCoreField-selectJsPickerItems'
        }
    });

    // Initialisation des éléments présent au chargement du DOM
    $('.tiFyCoreField-selectJs').tiFyFieldSelectJs();

    // Auto-initialisation des éléments chargés à posteriori dans le DOM
    $(doc).on('mouseenter.tify_field.select_js', '.tiFyCoreField-selectJs', function(e) {
        $(this).each(function(){
            $(this).tiFyFieldSelectJs();
        });
    });
})(jQuery, document, window);