/**
 * @see https://learn.jquery.com/plugins/stateful-plugins-with-widget-factory/
 * @see https://api.jqueryui.com/jquery.widget
 */
!(function($){
    $.widget( 'tify.tiFyProgress', {
        options: {
            value:      0,
            max:        100,
            step:       1,
            close:     function( event, el ){
                el.hide();
            }
        },
        
        // Création du widget
        _create:        function () {
            this.bar = $( '[data-role="bar"]', this.element );
            this.indicator = $( '[data-role="indicator"]', this.element );
            this.close = $( '[data-role="close"]', this.element );
            this.options.max = this.bar.data( 'max' );
            
            this.value( this.options.value );
            
            this._on( this.close, {
                click: function( event ) {
                    this._trigger( 'close', event, this.element );
                }
            });
                        
        },
        
        // Définition des options
        _setOptions:    function ( key, value ){
            this.options[ key ] = value;
        },
        
        // Modification de la valeur d'une option
        option:         function ( key, value ){
            if( typeof key === 'object' ){
                this.options = $.extend( this.options, key );
            } else {
                this._setOptions( key, value );
            }            
            
        },
                
        // Traitement de la valeur (récupération/définition)
        value:          function (value) {
            if ( value === undefined ) { 
                return this.options.value;
            } else {
                this.options.value = this._changeValue(value);
            }
        },
        
        // Augmente la valeur d'un pas
        increase:       function () {
            value = this.options.value+this.options.step;
            if(value > this.options.max)
                value = this.options.max;
            
            this.value(value);
        }, 
        
        // Diminue la valeur d'un pas
        decrease:       function() {
            value = this.options.value-this.options.step;
            if( value < 0 )
                value = 0;
            
            this.value(value);
        }, 
        
        // Change la valeur de la barre de progression
        _changeValue:   function (value) {            
            var max = this.options.max;
            if(value > max)
                value = max;
            
            var percent = ((value/max)*100).toFixed(2);
            this.bar.css('background-position', '-'+ percent +'% 0');
            this.indicator.text(percent+'%');
            
            return value;
        }
    });
    
    $( '[data-tify_control="progress"]' ).tiFyProgress();
})(jQuery);