/**
 * @see https://learn.jquery.com/plugins/stateful-plugins-with-widget-factory/
 * @see https://api.jqueryui.com/jquery.widget
 * @see https://blog.rodneyrehm.de/archives/11-jQuery-Hooks.html
 *
 */
!(function ($, doc, win) {

    // Attribution de la valeur à l'élément
    var _hook = $.valHooks.div;
    $.valHooks.div = {
        get: function(elem) {
            if (!$(elem).tiFyFieldsAjaxSelect('instance')) {
                return _hook && _hook.get && _hook.get(elem) || undefined;
            }
            return $(elem).data('value');
        },
        set: function(elem, value) {
            if (!$(elem).tiFyFieldsAjaxSelect('instance')) {
                return _hook && _hook.set && _hook.set(elem, value) || undefined;
            }
            $(elem).data('value', value);
        }
    };

    $.widget('tify.tiFyFieldsAjaxSelect', {
        options: {
            'disable': false,
            'placement': 'clever',
            'viewport': {
                'selector': 'body',
                'delta': {
                    'top': 0,
                    'left': 0
                },
                'adminbar' : true
            }
        },
        _create: function () {
            this.instance = this;
            this.el = this.element;

            $.extend(this.options, $.parseJSON(decodeURIComponent(this.el.data('options'))));

            // Définition du selecteur de gestion de traitement
            this.handler = $('.tiFyCoreFields-ajaxSelectHandler', this.el);

            // Définition
            this.controller = $('.tiFyCoreFields-ajaxSelectController', this.el);
            this.options.disabled ? this._disable() : this._enable();

            // Traitement de la liste de selection
            var $picker = $('.tiFyCoreFields-ajaxSelectPicker', this.el);
            this.picker = $picker.clone();
            $picker.remove();
            this.items = $('.tiFyCoreFields-ajaxSelectOption', this.picker);

            // Traitement de l'accroche
            this.append = $(this.options.viewport.selector);
            $(this.append).append(this.picker);

            // Initialisation des événements globaux
            this._initEvents();
        },

        // Privée - Initialisation des événements globaux
        _initEvents: function () {
            var self = this;

            /**
             * Clic en dehors du selecteur
             */
            $('html, body').click(function (e) {
                self.controller.removeClass('tiFyCoreFields-ajaxSelectController--open');
                self._hidePicker();
            });

            /**
             * Choix d'un élément du controleur de liste de sélection
             */
            self.items.each(function () {
                $(this).on('click', function (e) {
                    e.stopPropagation();

                    self.controller.html($(this).html());
                    $('option:eq(' + $(this).index() + ')', self.handler).prop('selected', true).change();

                    self.controller.removeClass('tiFyCoreFields-ajaxSelectController--open');
                    self._hidePicker();
                });
            });

            /**
             * Action au changement de valeur dans le gestionnaire de traitement
             */
            self.handler.on('change', function (e) {
                self.el.val($(this).val());
                self.el.trigger('change.tify_fields.ajax_select');
            });
        },

        // Privée - Désactivation de la selection
        _disable: function () {
            var self = this;

            // Désactivation du selecteur de gestion de traitement
            self.handler.prop('disabled', true);

            // Suppression de l'action au clic sur le controleur de selection
            self.controller
                .off('click.tify_fields.ajax_select.' + self.instance.uuid)
                .addClass('tiFyCoreFields-ajaxSelectController--disabled');
        },

        // Privée - Activation de la selection
        _enable: function () {
            var self = this;

            // Activation du selecteur de gestion de traitement
            self.handler.prop('disabled', false);

            // Action au clic sur le controleur de selection
            self.controller
                .on('click.tify_fields.ajax_select.' + self.instance.uuid, function (e) {
                    e.stopPropagation();

                    if ($(this).hasClass('tiFyCoreFields-ajaxSelectController--open')) {
                        $(this).removeClass('tiFyCoreFields-ajaxSelectController--open');
                        self._hidePicker();
                    } else {
                        $(this).addClass('tiFyCoreFields-ajaxSelectController--open');
                        self._showPicker();
                    }
                })
                .removeClass('tiFyCoreFields-ajaxSelectController--disabled');
        },

        // Privée - Ouverture de la liste de selection
        _showPicker: function () {
            var self = this;

            var offset = self._getPickerOffset();

            self.picker
                .css(offset)
                .addClass('tiFyCoreFields-ajaxSelectPicker--open');

            $(win).on('scroll.tify_fields.ajax_select.' + self.instance.uuid, function(){
                var offset = self._getPickerOffset();

                self.picker.css(offset);
            });
        },

        // Privée - Fermeture de la liste de selection
        _hidePicker: function () {
            var self = this;

            $(win).off('scroll.tify_fields.ajax_select.' + self.instance.uuid);

            self.picker.removeClass('tiFyCoreFields-ajaxSelectPicker--open');
        },

        // Privée - Récupération du positionnement du selecteur
        _getPickerOffset : function () {
            var self = this;

            var offset = $.extend({}, self.controller.offset(), {width: self.controller.outerWidth(true)}),
                placement = self.options.placement;

            // Prise en compte du pas d'ajustement
            if (self.options.viewport.delta.top) {
                offset.top += self.options.viewport.delta.top;
            }
            if (self.options.viewport.delta.left) {
                offset.left += self.options.viewport.delta.left;
            }
            // Prise en compte de la barre d'admin Wordpress
            if (this.options.viewport.adminbar) {
                offset.top += $('body').hasClass('admin-bar') ? -$('#wpadminbar').outerHeight() : 0
            }

            // Placement intelligent
            if (placement === 'clever'){
                placement = ($(win).outerHeight() + $(win).scrollTop() < offset.top + self.picker.outerHeight()) ? 'top' : 'bottom'
            }

            self.picker.addClass('tiFyCoreFields-ajaxSelectPicker--' + placement);

            switch(placement){
                case 'top' :
                    offset.top -= self.picker.outerHeight(true);
                    break;
                case 'bottom' :
                    offset.top += self.controller.outerHeight(true);
                    break;
            }

            return offset;
        },

        // Méthodes publiques
        // Activation
        // @uses $(selector).tFyFieldsAjaxSelect('enable');
        enable: function (){
            var self = this;

            if (self.controller.hasClass('tiFyCoreFields-ajaxSelectController--disabled')) {
                self._enable();
            }
        },

        // Désactivation
        // @uses $(selector).tFyFieldsAjaxSelect('disable');
        disable: function (){
            var self = this;

            if (!self.controller.hasClass('tiFyCoreFields-ajaxSelectController--disabled')) {
                self._disable();
            }
        },

        // Suppression
        // @uses $(selector).tFyFieldsAjaxSelect('destroy');
        destroy: function (){
            var self = this;

            self.el.remove();
            self.picker.remove();
        },

        // Récupération de l'instance
        // @uses $(selector).tFyFieldsAjaxSelect('instance');
        instance: function (){
            var self = this;

            if (self.instance !== undefined) {
                return true;
            } else {
                return false;
            }

        }
    });

    $('.tiFyCoreFields-ajaxSelect').tiFyFieldsAjaxSelect();

})(jQuery, document, window);