jQuery(document).ready(function($){

    // Récupération de la liste des utilisateurs liés
    $(document).on('tify_select.add', '.tiFyTakeOverSwitcherForm-selectField--role', function(e){
        // Bypass
        if(!$(this).val()) {
            return;
        }

        var $roles = $(this),
            $form = $(this).closest('form');
        var $users =  $('.tiFyTakeOverSwitcherForm-selectField--user', $form),
            role = $(this).val(),
            o = $.parseJSON(decodeURIComponent($form.data('options')));

        // Désactivation du champs de selection des utilisateurs durant la requête de récupération des éléments
        $users.tiFyFieldSelectJs('disable');

        $.post(
            tify_ajaxurl,
            {
                action:         o.ajax_action,
                _ajax_nonce:    o.ajax_nonce,
                role:           role
            }
        )
            .done(function(resp){
                console.log(resp);
                $users.before(resp).tiFyFieldSelectJs('destroy');
                $('.tiFyTakeOverSwitcherForm-selectField--user').tiFyFieldSelectJs();
            });
    });

    // Soumission automatique du formulaire à l'issue de la selction d'un utilisateur
    $(document).on('tify_select.add', '.tiFyTakeOverSwitcherForm-selectField--user', function(){
        if($(this).val() > 0) {
            $(this).closest('form').submit();
        }
    });
});