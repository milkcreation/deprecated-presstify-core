!(function($, doc, win){
    $.widget( 'tify.tiFyAdminPanel', {
    	/**
    	 * Création du widget tiFyAdminPanel
    	 */
        _create: function () {
        	this.toggleId = this.element.data('toggle');
        	this.toggle = $(this.toggleId);
        	this._listenEvents();
        },
        /**
         * Écoute d'évènements
         */
        _listenEvents: function() {
        	this._on(this.document, {
        		click: function(event){
        			// Ouverture - Fermeture du panneau
        			if ($(event.target).closest(this.toggle).length || $(event.target).is(this.toggle)) {
        				this.open();
        			}
        			if ($(event.target).closest($('.tiFyControl-adminPanelControlsItem--cancel', this.element)).length || $(event.target).is($('.tiFyControl-adminPanelControlsItem--cancel', this.element))) {
        				this.close();
        			}
        			// Glissement des sous panneaux
        			if($(event.target).is($('.tiFyControl-adminPanelItemSlide', this.element))) {
        				this.openItem(event);
        			}
        			if($(event.target).is($('.tiFyControl-adminPanelItemPanelBack', this.element))) {
        				this.closeItem(event);
        			}
        		}
        	});
        },
        /**
         * Vérification de l'ouverture du panneau
         */
        isOpened: function() {
        	return this.element.hasClass('tiFyControl-adminPanel--opened');
        },
        /**
         * Ouverture du panneau
         */
        open: function() {
        	this.element.toggleClass('tiFyControl-adminPanel--opened');
        },
        /**
         * Fermeture du panneau
         */
        close: function() {
        	this.element.removeClass('tiFyControl-adminPanel--opened');
        },
        /**
         * Ouverture d'un élément du panneau
         */
        openItem: function(event) {
        	this.element.addClass('tiFyControl-adminPanel--hasOpenedPanel');
			$(event.target).closest('.tiFyControl-adminPanelItem').addClass('tiFyControl-adminPanelItem--active');
        },
        /**
         * Fermeture d'un élément du panneau
         */
        closeItem: function(event) {
        	this.element.removeClass('tiFyControl-adminPanel--hasOpenedPanel');
			$(event.target).closest('.tiFyControl-adminPanelItem').removeClass('tiFyControl-adminPanelItem--active');
        }
    });
    $(document).ready(function() {
    	$('.tiFyControl-adminPanel').tiFyAdminPanel();
    });
})(jQuery, document, window);