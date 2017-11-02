jQuery(document).ready(function ($) {
    /**
     * Sauvegarde de l'onglet courant
     */
    $(document).on('click', '[data-tify_control="tabs"] li:not(.active) > a[data-toggle="tab"]', function (e) {
        var key = $(this).data('key');

        $.ajax({
            'url':      tify_ajaxurl,
            'type' :    'POST',
            'data' :    {
                action :        'tiFyControlTabs',
                key :           key,
                _ajax_nonce :   tiFyControlTabs._ajax_nonce
            }
        });
    });

    var
    // Affichage recursif des onglets enfants
    tabShowRecursiveChild = function($tab)
    {
        var id = $tab.attr('href'),
            $tabs = ($tab.closest('[data-tify_control="tabs"]')),
            $child = $(id +'.tab-pane .nav li:first-child:not(.active) > a[data-toggle="tab"]', $tabs);

        if ($child.length) {
            $child.tab('show');
            tabShowRecursiveChild($child);
        }
    },
        // Affichage recursif des onglets parents
    tabShowRecursiveParent = function($tab)
    {
        var $tabs = ($tab.closest('[data-tify_control="tabs"]')),
            parent_id = $tab.closest('.tab-pane').attr('id'),
            $parent = $('.nav > li:not(.active) > a[data-toggle="tab"][href="#' + parent_id + '"]', $tabs);

        if ($parent.length) {
            $parent.tab('show');
            tabShowRecursiveParent($parent);
        }
    }

    /**
     * Affichage de l'onglet courant
     */
    $('[data-tify_control="tabs"] > .nav > li:not(:has(a[data-toggle="tab"].current)) > a[data-toggle="tab"]').each(function() {
        tabShowRecursiveChild($(this));
    });

    if ($('a[data-toggle="tab"].current', '[data-tify_control="tabs"]').length) {
        var $current = $('a[data-toggle="tab"].current', '[data-tify_control="tabs"]');

        $current.tab('show');
        tabShowRecursiveChild($current);
        tabShowRecursiveParent($current);
    } else {
        var $current = $('[data-tify_control="tabs"] > .nav > li:first-child > a[data-toggle="tab"]');
        $current.addClass('current');
        $current.tab('show');
        tabShowRecursiveChild($current);
    }

});