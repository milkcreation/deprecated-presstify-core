/**
 * @see https://learn.jquery.com/plugins/stateful-plugins-with-widget-factory/
 * @see https://api.jqueryui.com/jquery.widget
 * 
 * @see https://tympanus.net/Development/MultiLevelPushMenu/
 */
!(function($){
    $.widget('tify.tiFyControlCurtainMenu', {
        options: {

        },
        _create:            function() {
            this.el = this.element;
            this.level = 0;
            this.levels = $('.tiFyControlCurtainMenu-panel', this.el);
            this.levelBacks = $('[data-toggle="curtain_menu-back"]', this.el);
            
            this.levels.each(function() {
                var level = $(this).parents('.tiFyControlCurtainMenu-panel').length+1;
                $(this).attr('data-level', level);
            });
            
            this.menuItems = $( 'li', this.el );
            this._initEvents();
        },
        _initEvents :       function() {
            self = this;
            
            this.menuItems.each(function(i,j) {
                var subLevel = $('> .tiFyControlCurtainMenu-panel', j);
                if(subLevel.length) {
                    $('> a', j).on('click', function(e) {
                        e.preventDefault();
                        var level = $(this).closest('.tiFyControlCurtainMenu-panel').attr('data-level');
                        if(self.level <= level) {
                            e.stopPropagation();
                            self._openMenu(subLevel);
                        }
                    });
                }
            });
            
            this.levelBacks.each(function(i,j) {
                var curLevel = $(j).closest('.tiFyControlCurtainMenu-panel');
                if(curLevel.length) {
                    $(j).on('click', function(e) {
                        e.preventDefault();
                        
                        var level = curLevel.attr('data-level');
                        if( self.level <= level ) {
                            e.stopPropagation();
                            self._closeMenu(curLevel);
                        }
                    });
                }
            }); 
        },
        _openMenu :         function(subLevel) {
            ++this.level;
            subLevel.addClass('tiFyControlCurtainMenu-panel--open');
            var parentLevel = subLevel.closest('li').closest('.tiFyControlCurtainMenu-panel');
            if(parentLevel.length){
                parentLevel.addClass('tiFyControlCurtainMenu-panel--pushed').scrollTop(0);
            }

        },
        _closeMenu : function(curLevel) {
            --this.level;            
            curLevel.removeClass('tiFyControlCurtainMenu-panel--open');
            var parentLevel = curLevel.closest('li').closest('.tiFyControlCurtainMenu-panel');
            if(parentLevel.length){
                parentLevel.removeClass('tiFyControlCurtainMenu-panel--pushed')
            }
        }
    });
    
    $( '[data-tify_control="curtain_menu"]' ).tiFyControlCurtainMenu();
})(jQuery);