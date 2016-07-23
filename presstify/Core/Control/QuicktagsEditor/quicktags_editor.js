jQuery( document ).ready( function($){
	function _escape( text ) {
		text = text || '';
		text = text.replace( /&([^#])(?![a-z1-4]{1,8};)/gi, '&#038;$1' );
		return text.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' ).replace( /'/g, '&#039;' );
	}
	QTags.Button.prototype.html = function(idPrefix) {
		var active, on, wp,
			title = this.title ? ' title="' + _escape( this.title ) + '"' : '',
			ariaLabel = this.attr && this.attr.ariaLabel ? ' aria-label="' + _escape( this.attr.ariaLabel ) + '"' : '',
			val = this.display ? ' value="' + _escape( this.display ) + '"' : '',
			id = this.id ? ' id="' + _escape( idPrefix + this.id ) + '"' : '',
			dfw = ( wp = window.wp ) && wp.editor && wp.editor.dfw;
	
		if ( this.id === 'fullscreen' ) {
			return '<button type="button"' + id + ' class="ed_button qt-dfw qt-fullscreen"' + title + ariaLabel + '></button>';
		} else if ( this.id === 'dfw' ) {
			active = dfw && dfw.isActive() ? '' : ' disabled="disabled"';
			on = dfw && dfw.isOn() ? ' active' : '';
	
			return '<button type="button"' + id + ' class="ed_button qt-dfw' + on + '"' + title + ariaLabel + active + '></button>';
		}
	
		return '<button type="button"' + id + ' class="ed_button button button-small"' + title + ariaLabel +' />'+ this.display +'</button>';
	};
});
