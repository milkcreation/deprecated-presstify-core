/**
 * @see https://learn.jquery.com/plugins/stateful-plugins-with-widget-factory/
 * @see https://api.jqueryui.com/jquery.widget
 * @see https://blog.rodneyrehm.de/archives/11-jQuery-Hooks.html
 *
 * @
 *
 */
!(function ($, doc, win) {

    // Attribution de la valeur à l'élément
    var _hook = $.valHooks.div;
    $.valHooks.div = {
        get: function (elem) {
            if (typeof $(elem).tifyselect('instance') === 'undefined') {
                return _hook && _hook.get && _hook.get(elem) || undefined;
            }
            return $(elem).data('value');
        },
        set: function (elem, value) {
            if (typeof $(elem).tifyselect('instance') === 'undefined') {
                return _hook && _hook.set && _hook.set(elem, value) ||
                    undefined;
            }
            $(elem).data('value', value);
        }
    };

    $.widget(
        'tify.tifyselect', {
            // Définition des options par défaut
            options: {
                disabled: false,
                multiple: false,
                duplicate: false,
                max: -1,
                trigger: {},
                picker: {},
                sortable: {},
                source: false,
                autocomplete: false
            },

            // Liste des agents de controle
            controllers: {
                handler: 'tiFy-selectHandler',
                trigger: 'tiFy-selectTrigger',
                autocompleteInput: 'tiFy-selectAutocomplete',
                selectedList: 'tiFy-selectSelectedItems',
                picker: 'tiFy-selectPicker',
                pickerList: 'tiFy-selectPickerItems',
                pickerFilter: 'tiFy-selectPickerFilter',
                pickerLoader: 'tiFy-selectPickerLoader'
            },

            // Instanciation de l'élément
            _create: function () {
                // Définition de l'instance
                this.instance = this;

                // Définition de l'alias court du controleur d'affichage
                this.el = this.element;

                // Initialisation des attributs de configuration du controleur
                this._initOptions();

                // Définition des agents de controle
                this._initControllers();

                // Initialisation des indicateurs d'état
                this._initStatusFlags();

                // Initialisation des événements globaux
                this._initEvents();
            },

            // Privée - Initialisation des attributs de configuration du controleur
            _initOptions: function () {
                // Traitement des options passées dans la balise du controleur
                if (this.el.data('options')) {
                    $.extend(this.options,
                        $.parseJSON(decodeURIComponent(this.el.data('options'))));
                }

                // Traitement des attributs de configuration de la source de récupération de données
                if (this.options.source) {
                    if (typeof this.options.source !== 'object') {
                        this.options.source = {};
                    }
                    this.dataSource = this.options.source;
                } else {
                    this.dataSource = undefined;
                }
            },

            // Privée - Initialisation des indicateurs d'état
            _initStatusFlags: function () {
                // Liste des indicateurs d'état par défaut
                this.flags = {
                    hasSelection: false,
                    isDisabled: true,
                    isMultiple: false,
                    isDuplicable: false,
                    isRemovable: true,
                    isSortable: false,
                    isOpen: false,
                    isComplete: true,
                    page: 1,
                    hasArrow: true,
                    hasFilter: false,
                    hasSource: false,
                    hasAutocomplete: false
                };
                this.flags.hasSelection = this._count() ? true : false;
                this.flags.Disabled = this.options.disabled;
                this.flags.isMultiple = this.options.multiple;
                this.flags.isDuplicable = (this.options.duplicate && this.flags.isMultiple);
                this.flags.isRemovable = !this.flags.Disabled;
                this.flags.isSortable = ((this.options.sortable !== false) && this.flags.isMultiple && !this.flags.Disabled);
                this.flags.isOpen = false;
                this.flags.isComplete = false;
                this.flags.page = 1;
                this.flags.hasSource = (this.dataSource !== undefined);
            },

            // Privée - Définition des agents de controle
            _initControllers: function () {
                var self = this;

                $.each(this.controllers, function (u, v) {
                    self[u] = $('.' + v, self.el);
                });
            },

            // Privée - Initialisation des événements globaux
            _initEvents: function () {
                var self = this;

                // Initialisation du déclencheur de selection
                self._initTrigger();

                // Initialisation de la liste de sélection
                self._initPicker();

                // Initialisation de la liste des éléments selectionnés
                self._initSelected();

                // Initialisation des agents de controle selon les indicateurs de status
                if (this.flags.hasSelection) {
                    this.el.attr('aria-selection', true);
                } else {
                    this.el.attr('aria-selection', false);
                }
                if (this.flags.isMultiple) {
                    this.el.attr('aria-multiple', true);
                } else {
                    this.el.attr('aria-multiple', false);
                }
                if (this.flags.isSortable) {
                    this.el.attr('aria-sortable', true);
                } else {
                    this.el.attr('aria-sortable', false);
                }
                if (this.flags.isDuplicable) {
                    this.el.attr('aria-duplicable', true);
                    this.picker.attr('aria-duplicable', true);
                } else {
                    this.el.attr('aria-duplicable', false);
                    this.picker.attr('aria-duplicable', false);
                }
                this.flags.Disabled ? this._disable() : this._enable();
            },

            /**
             * Initialisation du déclencheur de selection
             *
             * @private
             */
            _initTrigger: function () {
                var self = this;

                // Traitement des options
                var o = {
                    class : '',
                    arrow: true
                };
                self.options.trigger = $.extend(o, self.options.trigger);

                self.picker.addClass(self.options.trigger.class);

                if (self.options.trigger.arrow) {
                    self.flags.hasArrow = true;
                    self.trigger.attr('aria-arrow', true);
                } else {
                    self.flags.hasArrow = false;
                    self.trigger.attr('aria-arrow', false);
                }

                // Activation de l'autocomplétion
                if (self.options.autocomplete) {
                    if (typeof self.options.autocomplete !== 'object') {
                        self.options.autocomplete = {};
                    }

                    // Définition de l'indicateur d'état
                    self.flags.hasAutocomplete = true;
                    self.el.attr('aria-autocomplete', true);

                    // Positionnement de la liste de selection
                    if (self.flags.isMultiple) {
                        self.selectedList.insertAfter(self.trigger);
                    }

                    // Récupération d'élément dans la liste de selection
                    self.autocompleteInput = $('<input class="tiFy-selectAutocomplete" autocomplete="off"/>')
                        .prependTo(self.trigger);
                }
            },

            /**
             * Initialisation de la liste de selection
             *
             * @private
             */
            _initPicker: function () {
                var self = this;

                // Traitement des options
                var o = {
                    class : '',
                    placement: 'clever',
                    appendTo: 'body',
                    delta: {
                        top: 0,
                        left: 0,
                        width: 0
                    },
                    adminbar: true,
                    filter: false,
                    loader: '',
                    more: '+'
                };
                self.options.picker = $.extend(o, self.options.picker);

                // Positionnement de la liste de sélection dans le DOM
                var $appendTo = $(this.options.picker.appendTo);
                if (!$appendTo.length) {
                    $appendTo = $('body');
                }
                self.picker
                    .appendTo($appendTo)
                    .addClass(self.options.picker.class);

                // Traitement du filtre de liste de selection
                if (self.options.picker.filter) {
                    self.flags.hasFilter = true;
                    self.picker.attr('aria-filter', true);

                    self.pickerFilter = $(
                        '<input class="tiFy-selectPickerFilter" autocomplete="off"/>').prependTo(self.picker);
                }

                // Définition de l'indicateur de chargement
                self.pickerLoader = $('<div class="tiFy-selectPickerLoader" />')
                    .html(o.loader)
                    .prependTo(self.picker);

                // Définition de l'indicateur de chargement
                if (this.flags.hasSource) {
                    self.pickerMore = $('<a href="#" class="tiFy-selectPickerMore" />')
                        .html(o.more)
                        .prependTo(self.picker);

                    self.picker.attr('aria-complete', false);
                }
            },

            /**
             * Initialisation de la liste des éléments selectionnés
             *
             * @private
             */
            _initSelected: function () {
                var self = this;

                if (self.flags.isSortable) {
                    // Définition des options du widget
                    self.options.sortable = $.extend(
                        {
                            handle: '[aria-handle="sort"]',
                            containment: 'parent',
                            update: function () {
                                self._reset();
                            }
                        },
                        self.options.sortable
                    );

                    self.sortable = self.selectedList.sortable(
                        self.options.sortable);
                    self.selectedList.disableSelection();
                } else {
                    self.sortable = undefined;
                }

                self._reset();
            },

            /**
             * Ouverture de la liste de selection
             *
             * @private
             */
            _open: function () {
                var self = this;

                if (self.flags.isOpen) {
                    return;
                }

                // Redéfinition des indicateurs d'état
                self.flags.isOpen = true;
                self.el.attr('aria-open', true);
                self.picker.attr('aria-open', true);

                // Positionnement de la liste de selection dans le DOM
                var offset = self._getPickerOffset();
                self.picker.css(offset);

                // Récupération des éléments dans la liste de selection
                self._getPickerItems();

                // Définition de la liste des événements à l'ouverture de la liste de selection
                self._onOpen();
            },

            /**
             * Fermeture de la liste de selection
             *
             * @private
             */
            _close: function () {
                var self = this;

                if (!self.flags.isOpen) {
                    return;
                }

                // Redéfinition des indicateurs d'état
                self.flags.isOpen = false;
                self.el.attr('aria-open', false);
                self.picker.attr('aria-open', false);

                // Définition de la liste des événements à la fermeture de la liste de selection
                self._onClose();
            },

            /**
             * Activation du controleur
             *
             * @private
             */
            _enable: function () {
                var self = this;

                // Redéfinition des indicateurs d'état
                self.flags.Disabled = false;
                self.el.attr('aria-disabled', false);
                self.handler.prop('disabled', false);

                // Définition de la liste des événements à l'activation du controleur
                self._onEnable();
            },

            /**
             * Désactivation du controleur
             *
             * @private
             */
            _disable: function () {
                var self = this;

                // Mise à jour de l'indicateur
                self.flags.Disabled = true;
                self.el.attr('aria-disabled', true);
                self.handler.prop('disabled', true);

                // Définition de la liste des événements à la désactivation du controleur
                self._onDisable();
            },

            /**
             * Ajout d'un élément dans la liste des éléments sélectionnés
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _add: function (item) {
                var self = this;

                // Bypass - L'élément doit être unique et existe déja
                if (!this.flags.isDuplicable &&
                    $('> [data-value="' + item.value + '"]',
                        self.selectedList).length) {
                    item.new = false;

                    // Création du nouvel élément dans la liste de selection
                } else {
                    item.new = true;

                    // Vérification d'habititation d'ajout multiple
                    if (!self.flags.isMultiple) {
                        self._removeAll();
                    }

                    // Définition de l'index de l'élément
                    item['index'] = self._count();

                    // Ajout de l'élément dans la liste des éléments sélectionnés
                    $(
                        '<li ' +
                        'data-label="' + item.label + '" ' +
                        'data-value="' + item.value + '" ' +
                        'data-index="' + item.index + '"' +
                        '>' +
                        ((typeof item.select !== 'undefined')
                            ? item.select
                            : item.label) +
                        '</li>'
                    ).appendTo(self.selectedList);
                }

                // Définition de la liste des événements suite à l'ajout d'un élément dans la liste de selection
                self._onAdd(item);
            },

            /**
             * Suppression d'un élément de la liste des éléments selectionnés
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _remove: function (item) {
                var self = this;

                // Mise à jour de la liste d'élement sélectionné
                self._get(item.index).remove();

                // Mise à jour du selecteur de gestion de traitement
                $('> [data-index="' + item.index + '"]', self.handler).remove();

                // Mise à jour de l'élément dans la liste de selection
                if (!$('> [data-value="' + item.value + '"]', self.selectedList).length) {
                    $('> [data-value="' + item.value + '"]', self.pickerList).attr('aria-selected', false);
                }

                // Définition de la liste des événements suite à la suppression d'un élément dans la liste de selection
                self._onRemove(item);
            },

            /**
             * Récupération d'un élément de la liste de selection à partir de son indexe
             *
             * @param index
             *
             * @returns {*|HTMLElement}
             * @private
             */
            _get: function (index) {
                return $('> li[data-index="' + index + '"]', this.selectedList);
            },

            /**
             * Suppression de tous les éléments de la liste des éléments selectionnés
             *
             * @private
             */
            _removeAll: function () {
                var self = this;

                self._all().each(function () {
                    var item = self._getAttrs($(this));

                    // Mise à jour de la liste d'élement sélectionné
                    self._get(item.index).remove();

                    // Mise à jour du selecteur de gestion de traitement
                    $('> [data-index="' + item.index + '"]', self.handler).remove();

                    // Mise à jour de l'élément dans la liste de selection
                    if (!$('> [data-value="' + item.value + '"]',
                            self.selectedList).length) {
                        $('> [data-value="' + item.value + '"]', self.pickerList).attr('aria-selected', false);
                    }
                });

                // Mise à jour de la valeur du selecteur de gestion de traitement
                self.el.val(self.handler.val());
            },

            /**
             * Réinitialisation de la liste des éléments sélectionnés
             *
             * @private
             */
            _reset: function () {
                var self = this;

                // Nettoyage des entrées du selecteur de gestion de traitement
                $('option', self.handler).remove();

                // Traitement des éléments selectionnés
                self._all().each(function () {
                    var item = self._getAttrs($(this));

                    self._setRemovable(item);
                    self._setSortable(item);
                    self._addHandler(item);
                    self._setPicker(item);
                });

                // Mise à jour de la valeur du controleur d'affichage
                self.el.val(self.handler.val());
            },

            /**
             * Récupération des éléments courants dans la liste des éléments selectionnés
             *
             * @private
             */
            _all: function () {
                return $('> li', this.selectedList);
            },

            /**
             * Calcul du nombre d'éléments courants dans la liste des éléments selectionnés
             *
             * @private
             */
            _count: function () {
                return this._all().length;
            },

            /**
             * Indication de mise en avant d'un élément dans la liste des éléments sélectionnés
             *
             * @param item Attributs de l'élément
             *
             * @private
             */
            _highlight: function (item) {
                var self = this;

                $('> [data-value="' + item.value + '"]', self.selectedList).attr('aria-highlight', true).one(
                    'webkitAnimationEnd oanimationend msAnimationEnd animationend',
                    function () {
                        $(this).attr('aria-highlight', false);
                    }
                );
            },

            /**
             * Activation du controle de suppression à un élément de la liste des éléments selectionnés
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _setRemovable: function (item) {
                var self = this;

                if (self.flags.isRemovable) {
                    $('<span aria-handle="remove">×</span>')
                        .prependTo(self._get(item.index))
                        .on(
                            'click.tify_select.selected_remove.' + self.instance.uuid,
                            function () {
                                var item = self._getAttrs($(this).closest('li'));
                                self._remove(item);
                            });
                }
            },

            /**
             * Activation du controle d'ordonnancement à un élément de la liste des éléments selectionnés
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _setSortable: function (item) {
                if (!this.flags.isSortable) {
                } else if (this.options.sortable.handle) {
                    this._get(item.index).prepend('<span aria-handle="sort">...</span>');
                } else {
                    this._get(item.index).attr('aria-handle', 'sort');
                }
            },

            /**
             * Définition de la liste des événements à l'ouverture de la liste de selection
             *
             * @private
             */
            _onOpen: function () {
                var self = this;

                // Activation du clic en dehors du declencheurs
                $(doc).on('click.tify_select.outside' + self.instance.uuid,
                    function (e) {
                        var close = true;

                        $.each(self.controllers, function (u, v) {
                            if ($(e.target).is($('.' + v, self.el))) {
                                close = false;
                                return true;
                            }
                        });

                        if (close) {
                            self._close();
                        }
                    });

                // Activation du positionnement de la liste de selection au scroll dans la fenêtre du navigateur
                $(win).on('scroll.tify_select.picker_position.' + self.instance.uuid,
                    function () {
                        var offset = self._getPickerOffset();

                        self.picker.css(offset);
                    });

                // Activation de la récupération de la liste des éléments au scroll dans la liste de selection
                self.pickerList.on(
                    'scroll.tify_select.picker_list.' + self.instance.uuid,
                    function () {
                        if (self.xhr !== undefined) {
                            return;
                        }
                        else if (($(this).prop('scrollHeight') - $(this).innerHeight()) -
                            $(this).scrollTop() < 20) {
                            self._getPickerItems();
                        }
                    });

                // Activation de la récupération de la liste des éléments supplémentaire au clique sur le lien d'ajout de la liste de selection
                if (self.pickerMore !== undefined) {
                    self.pickerMore.on(
                        'click.tify_select.picker_list.' + self.instance.uuid,
                        function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            if (self.xhr !== undefined) {
                                return;
                            }
                            self._getPickerItems();
                        });
                }

                // Activation du champs de saisie de filtrage de la liste de selection
                if (self.flags.hasFilter) {
                    self.pickerFilter.focus();

                    self.pickerFilter
                        .on('keyup.tify_select.picker_filter.' + self.instance.uuid,
                            function () {
                                var term = $(this).val();

                                $('> li', self.pickerList).each(function () {
                                    var regex = new RegExp(term, 'i');

                                    if ($(this).data('label').match(regex)) {
                                        $(this).show();
                                    } else {
                                        $(this).hide();
                                    }
                                });
                            }
                        )
                        .on('click.tify_select.picker_filter.' + self.instance.uuid,
                            function(e) {
                                e.stopPropagation();
                            }
                        )
                }
            },

            /**
             * Définition de la liste des événements à la fermeture de la liste de selection
             *
             * @private
             */
            _onClose: function () {
                var self = this;

                // Désactivation du clic en dehors du declencheurs
                $(doc).off('click.tify_select.outside' + self.instance.uuid);

                // Désactivation du positionnement de la liste de selection au scroll dans la fenêtre du navigateur
                $(win).off('scroll.tify_select.picker_position.' + self.instance.uuid);

                // Désactivation de la récupération de la liste des éléments au scroll dans la liste de selection
                self.pickerList.off('scroll.tify_select.picker_list.' +
                    self.instance.uuid);

                // Désactivation du champs de saisie de filtrage de la liste de selection
                if (self.flags.hasFilter) {
                    self.pickerFilter.off('keyup.tify_select.picker_filter.' +
                        self.instance.uuid);
                }
            },

            /**
             * Définition de la liste des événements à l'activation du controleur
             *
             * @private
             */
            _onEnable: function () {
                var self = this;

                // Action au clic sur le déclencheur de selection
                self.trigger.on('click.tify_select.open.' + self.instance.uuid,
                    function () {
                        if (self.flags.isOpen) {
                            self._close();
                        } else {
                            self._open();
                        }
                    });

                // Activation de la selection d'un élément dans la liste de selection
                $('> li:not([aria-disabled="true"])', self.pickerList).on('click.tify_select.picker_item_select.' + self.instance.uuid,
                    function () {
                        var item = self._getAttrs($(this));

                        self._add(item);
                    });

                // Activation de la suppression d'élément dans la liste des éléments selectionnés
                if (self.flags.isRemovable) {
                    $('> li > [aria-handle="remove"]', self.selectedList).on('click.tify_select.selected_remove.' +
                        self.instance.uuid,
                        function () {
                            var item = self._getAttrs($(this).closest('li'));
                            self._remove(item);
                        });
                }

                // Masque la liste des éléments selectionnés si elle est vide
                if (!this._count()) {
                    this.flags.hasSelection = false;
                    this.el.attr('aria-selection', false);
                    if (!self.flags.isMultiple) {
                        this.autocompleteInput.show();
                    }
                    this.selectedList.hide();
                }

                // Activation du champs de saisie par autocomplétion
                if (self.flags.hasAutocomplete) {
                    self.autocompleteInput.on('keyup.tify_select.autocomplete.' +
                        self.instance.uuid, function () {
                        // Fermeture de la liste de selection
                        self._close();

                        // Interruption si la condition de valeur n'est pas remplie
                        if (!$(this).val()) {
                            return;
                        }

                        // Suppression des éléments de la liste de selection
                        self.pickerList.empty();

                        // Initialisation des indicateur d'état
                        self.flags.isComplete = false;
                        self.flags.page = 1;

                        // Réinitialisation de la requête de récupération des éléments de la liste de selection
                        if (self.timeout !== undefined) {
                            clearTimeout(self.timeout);
                        }
                        if (self.xhr !== undefined) {
                            self.xhr.abort();
                        }

                        // Lancement de la requête de récupération des éléments de la liste de selection
                        self.timeout = setTimeout(function () {
                            self._getPickerItems();
                            self.xhr.done(function (data) {
                                if (data.length) {
                                    self._open();
                                }
                            });
                        }, 1000);
                    });
                }
            },

            /**
             * Définition de la liste des événements à la désactivation du controleur
             *
             * @private
             */
            _onDisable: function () {
                var self = this;

                // Désactivation de l'action au clic sur le controleur de selection
                self.trigger.off('click.tify_select.open.' + self.instance.uuid);

                // Désactivation de la selection d'un élément dans la liste de selection
                $('> li', self.pickerList).on('click.tify_select.picker_item_select.' +
                    self.instance.uuid);

                // Activation de la suppression d'élément dans la liste des éléments selectionnés
                if (self.flags.isRemovable) {
                    $('> li > [aria-handle="remove"]', self.selectedList).off('click.tify_select.selected_remove.' +
                        self.instance.uuid);
                }

                // Désactivation du champs de saisie par autocomplétion
                if (self.flags.hasAutocomplete) {
                    self.autocompleteInput.off('keyup.tify_select.autocomplete.' +
                        self.instance.uuid);
                }
            },

            /**
             * Définition de la liste des événements à l'ajout d'un élément dans la liste de selection
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _onAdd: function (item) {
                var self = this;

                if (item.new) {
                    // Activation du controle de suppression de l'élément
                    self._setRemovable(item);

                    // Activation du controle d'ordonnancement de l'élément
                    self._setSortable(item);

                    // Mise à jour du statut de selection de l'élément dans la liste de selection
                    self._setPicker(item);

                    // Mise à jour de l'éléments dans le controleur de traitement
                    self._addHandler(item);
                }

                // Modification du statut de selection
                self.flags.hasSelection = true;
                self.el.attr('aria-selection', true);

                // Masque le champ de saisie d'autocompletion
                if (!self.flags.isMultiple) {
                    self.autocompleteInput.hide();
                }

                // Affiche la liste des éléments selectionnés
                self.selectedList.show();

                // Fermeture de la liste de selection
                self._close();

                // Indication de mise en avant de l'élément dans la liste des éléments sélectionnés
                self._highlight(item);

                // Mise à jour de la valeur du controleur
                self.el.val(self.handler.val());

                // Déclenchement des événements
                self._trigger('add');
            },

            /**
             * Définition de la liste des événements à l'ajout d'un élément dans la liste de selection
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _onRemove: function (item) {
                // Masque la liste des éléments selectionnés si elle est vide
                if (!this._count()) {
                    this.flags.hasSelection = false;
                    this.el.attr('aria-selection', false);
                    if (!this.flags.isMultiple) {
                        this.autocompleteInput.show();
                    }
                    this.selectedList.hide();
                }

                // Mise à jour de la valeur du controleur
                this.el.val(this.handler.val());

                // Déclenchement des événements
                this._trigger('remove');
            },

            /**
             * Récupération des attributs d'un élément de la liste de selection ou de la liste des éléments sélectionnés
             *
             * @param $item Element dans le DOM
             *
             * @private
             */
            _getAttrs: function ($item) {
                return {
                    label: $item.data('label'),
                    value: $item.data('value'),
                    index: $item.data('index'),
                    picker: $item.data('picker')
                        ? $item.data('picker')
                        : $item.data('label'),
                    select: $item.data('select')
                        ? $item.data('select')
                        : $item.data('label')
                };
            },

            /**
             * Ajout d'un élément dans la liste de selection
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _addPicker: function (item) {
                var self = this;

                // Bypass - l'élément est déjà présent
                if ($('> [data-value="' + item.value + '"]',
                        self.pickerList).length) {
                    return;
                }

                // Ajout de l'élément dans la liste des éléments sélectionnés
                $(
                    '<li ' +
                    'data-label="' + item.label + '" ' +
                    'data-value="' + item.value + '" ' +
                    'data-index="' + item.value + '" ' +
                    '>' +
                    ((typeof item.picker !== 'undefined')
                        ? item.picker
                        : item.label) +
                    '</li>'
                ).appendTo(self.pickerList).on('click.tify_select.picker_item_select.' + self.instance.uuid,
                    function () {
                        self._add(item);
                    });

                // Mise à jour du statut de selection de l'élément dans la liste de selection
                self._setPicker(item);
            },

            /**
             * Définition des attribut d'un l'élément dans la liste de selection
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _setPicker: function (item) {
                var self = this;

                if ($('> [data-value="' + item.value + '"]',
                        self.selectedList).length) {
                    $('> [data-value="' + item.value + '"]', self.pickerList).attr('aria-selected', true);
                } else {
                    $('> [data-value="' + item.value + '"]', self.pickerList).attr('aria-selected', false);
                }
            },

            /**
             * Arguments de requête de récupération des éléments
             *
             * @private
             */
            _queryPickerArgs: function () {
                var args = {
                    page: this.flags.page
                };

                if (this.flags.hasAutocomplete) {
                    args.term = this.autocompleteInput.val();
                }

                return args;
            },

            /**
             * Récupération d'élément dans la liste de selection
             *
             * @private
             */
            _getPickerItems: function () {
                var self = this;

                if (self.flags.isComplete) {
                    return;
                }

                if (this.flags.hasSource) {
                    // Lancement de la requête
                    self.pickerLoader.show();

                    self.xhr = $.ajax({
                        url: tify_ajaxurl,
                        data: $.extend(self.dataSource, self._queryPickerArgs()),
                        method: 'POST'
                    }).done(function (data) {
                        if (data.length) {
                            $.each(data, function (u, item) {
                                self._addPicker(item);
                            });
                            self.flags.page++;
                        } else {
                            self.flags.isComplete = true;
                            self.picker.attr('aria-complete', true);
                            self.flags.page = 1;
                        }
                    }).always(function () {
                        self.pickerLoader.hide();
                        self.xhr = undefined;
                    });
                }
            },

            /**
             * Récupération du positionnement de la liste de selection
             *
             * @private
             */
            _getPickerOffset: function () {
                var self = this;

                var offset = $.extend({}, self.trigger.offset(),
                    {width: self.trigger.outerWidth()}),
                    placement = self.options.picker.placement;

                // Prise en compte du pas d'ajustement
                if (self.options.picker.delta.top) {
                    offset.top += self.options.picker.delta.top;
                }
                if (self.options.picker.delta.left) {
                    offset.left += self.options.picker.delta.left;
                }
                if (self.options.picker.delta.width) {
                    offset.width += self.options.picker.delta.width;
                }

                // Prise en compte de la barre d'admin Wordpress
                if (self.options.picker.adminbar) {
                    offset.top += $('body').hasClass('admin-bar')
                        ? -$('#wpadminbar').outerHeight()
                        : 0;
                }

                // Placement intelligent
                if (placement === 'clever') {
                    placement = ($(win).outerHeight() + $(win).scrollTop() < offset.top + self.picker.outerHeight())
                        ? 'top'
                        : 'bottom';
                }

                switch (placement) {
                    case 'top' :
                        offset.top -= self.picker.outerHeight() - 1;
                        break;
                    case 'bottom' :
                        offset.top += self.trigger.outerHeight() - 1;
                        break;
                }

                return offset;
            },

            /**
             * Ajout d'un élément dans le selecteur de gestion de traitement
             *
             * @param item Attributs de l'élément {
             *      @var mixed value Valeur de retour
             *      @var string label Intitulé de qualification
             *      @var string select Rendu dans la liste des éléments selectionnés
             *      @var string picker Rendu dans la liste de selection
             * }
             *
             * @private
             */
            _addHandler: function (item) {
                if (!$('[data-index="' + item.index + '"]', this.handler).length) {
                    $('<option value="' + item.value + '" data-index="' +
                        item.index + '" selected>' + item.label + '</option>').appendTo(this.handler);
                }
            },

            // Méthodes publiques
            // Récupération de l'instance du widget d'ordonnancement des éléments
            // @uses $(selector).tifyselect('sortable');
            sortable: function () {
                if (this.flags.isSortable) {
                    return this.sortable;
                }
            },

            // Ouverture de la liste de selection
            // @uses $(selector).tifyselect('open');
            open: function () {
                if (!this.flags.Disabled) {
                    this._open();
                }
            },

            // Fermeture de la liste de selection
            // @uses $(selector).tifyselect('close');
            close: function () {
                if (!this.flags.Disabled) {
                    this._close();
                }
            },

            // Activation du controleur
            // @uses $(selector).tifyselect('enable');
            enable: function () {
                if (this.flags.Disabled) {
                    this._enable();
                }
            },

            // Désactivation du controleur
            // @uses $(selector).tifyselect('disable');
            disable: function () {
                if (!this.flags.Disabled) {
                    this._disable();
                }
            },

            // Destruction du controleur
            // @uses $(selector).tifyselect('destroy');
            destroy: function () {
                this.el.remove();
                this.picker.remove();
            }
        });
})(jQuery, document, window);