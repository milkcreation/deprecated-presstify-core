jQuery(document).ready(function($){
    // Récupération de la liste des utilisateurs liés
    $('.tiFyTakeOverSwitcherForm-selectField--role').on('change.tify_fields.ajax_select', function(e){
        // Bypass
        if($(this).val() <= 0) {
            return;
        }

        var $form = $(this).closest('form'),
            role = $(this).val(),
            o = $.parseJSON(decodeURIComponent($form.data('options')));

        // Désactivation du champs de selection des utilisateurs durant la requête de récupération des éléments
        $('.tiFyTakeOverSwitcherForm-selectField--user', $form).tiFyFieldsAjaxSelect('disable');

        $.post(
            tify_ajaxurl,
            {
                action:         o.ajax_action,
                _ajax_nonce:    o.ajax_nonce,
                role:           role
            }
        )
            .done(function(resp){
                $('.tiFyTakeOverSwitcherForm-selectField--user', $form)
                    .before(resp)
                    .tiFyFieldsAjaxSelect('destroy');
            })
            .always(function(){
                $('.tiFyTakeOverSwitcherForm-selectField--user', $form).tiFyFieldsAjaxSelect();
            });
    });

    // Soumission automatique du formulaire à l'issue de la selction d'un utilisateur
    $(document).on('change.tify_fields.ajax_select', '.tiFyTakeOverSwitcherForm-selectField--user', function(){
        if($(this).val() > 0) {
            $(this).closest('form').submit();
        }
    });
});