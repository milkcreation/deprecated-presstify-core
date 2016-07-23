jQuery( document ).ready( function($){
	$('#wistify_campaign_content_html-preview').click( function(){
		console.log( tinyMCE.get('wistify_campaign_content_html').getContent() );
	});
});