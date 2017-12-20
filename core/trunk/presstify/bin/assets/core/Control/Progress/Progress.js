/**
 * @see https://learn.jquery.com/plugins/stateful-plugins-with-widget-factory/
 * @see https://api.jqueryui.com/jquery.widget
 */
!(function ($) {
    $.widget('tify.tiFyProgress', {
        options: {
            value: 0,
            max: 100,
            step: 1,
            info: '',
            show: false,
            close: function (event, ui) {
                ui.close();
            }
        },

        // Création du widget
        _create: function () {
            this.el = this.element;
            this.bar = $('[data-role="bar"]', this.el);
            this.indicator = $('[data-role="indicator"]', this.el);
            this.closeButton = $('[data-role="close"]', this.el);
            this.info = $('[data-role="info"]', this.el);
            this.options.max = this.bar.data('max');

            // Evénements
            this._on(this.closeButton, {
                click: function (event) {
                    this._trigger('close', event, this);
                }
            });

            this._update();
        },

        // Mise à jour
        _update: function () {
            // Définition de la valeur
            this.value(this.options.value);

            // Affichage de l'interface
            if (this.options.info)
                this.infos(this.options.info);

            // Affichage de l'interface
            if (this.options.show)
                this.open();
        },

        // Définition des options
        _setOptions: function (key, value) {
            this.options[key] = value;
        },

        // Change la valeur de la barre de progression
        _changeValue: function (value) {
            var max = this.options.max;
            if (value > max)
                value = max;

            var percent = ((value / max) * 100).toFixed(2);
            this.bar.css('background-position', '-' + percent + '% 0');
            this.indicator.text(percent + '%');

            return value;
        },

        // Modification de la valeur d'une option
        option: function (key, value) {
            if (typeof key === 'object') {
                this.options = $.extend(this.options, key);
            } else {
                this._setOptions(key, value);
            }
            this._update();
        },

        // Traitement de la valeur (récupération/définition)
        value: function (value) {
            if (value === undefined) {
                return this.options.value;
            } else {
                this.options.value = this._changeValue(value);
            }
        },

        // Augmente la valeur d'un pas
        increase: function () {
            value = this.options.value + this.options.step;
            if (value > this.options.max)
                value = this.options.max;

            this.value(value);
        },

        // Diminue la valeur d'un pas
        decrease: function () {
            value = this.options.value - this.options.step;
            if (value < 0)
                value = 0;

            this.value(value);
        },

        // Ouverture de l'interface
        open: function () {
            this.el.show();
        },

        // Fermeture de l'interface
        close: function () {
            this.el.hide();
        },

        // Réinitialisation
        reset: function () {
            this.value(0);
        },

        infos: function (html) {
            this.info.html(html).hide().fadeIn();
        }
    });

    $('[data-tify_control="progress"]').tiFyProgress();
})(jQuery);