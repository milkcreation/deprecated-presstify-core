;(function($) {
	var re = /([^&=]+)=?([^&]*)/g;
	var decodeRE = /\+/g; // Regex for replacing addition symbol with a space
	var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );};
	$.parseParams = function(query) {
	    var params = {}, e;
	    while ( e = re.exec(query) ) {
	        var k = decode( e[1] ), v = decode( e[2] );
	        if (k.substring(k.length - 2) === '[]') {
	            k = k.substring(0, k.length - 2);
	            (params[k] || (params[k] = [])).push(v);
	        }
	        else params[k] = v;
	    }
	    return params;
	};
})(jQuery);

jQuery(document).ready( function($){
	var url_params = $.parseParams( window.location.search );
	var tabActive = (url_params['tab-active'])? url_params['tab-active'] : 0;
	$( '.mknav-tabs .nav-tab-wrapper a').click(function(e) {
		e.preventDefault();
		// Transformation du wp_referer
		$.extend( url_params, { 'tab-active': $(this).index() } );
		$( '.mknav-tabs input[name="_wp_http_referer"]').val( window.location.pathname+"?"+$.param( url_params ) );
		console.log( $( '.mknav-tabs input[name="_wp_http_referer"]').size() );
		$(this)	
			.addClass('nav-tab-active')
			.siblings().removeClass('nav-tab-active');
		$( 'input[name="tab-active"]').val($(this).index());
		var id = $(this).prop('hash');
		$('.wrap '+id+'.tabpanel' )
			.addClass('active')
			.siblings().removeClass('active');

		return false;
	}).eq(tabActive).trigger('click');
});