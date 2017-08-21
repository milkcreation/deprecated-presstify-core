jQuery(document).ready(function($){
	$(document).on('click', '.tiFyVideo-inlineToggle', function(e) {
		e.preventDefault();
		var $this = $(this),
			$target = $($this.data('target')),
			video = $this.data('video');
		if(!$target.length) {
			return false;
		}
		if($this.hasClass('tiFyVideo-inlineToggle--loading')){
			return false;
		}
		$this.addClass('tiFyVideo-inlineToggle--loading');
		$target.addClass('tiFyVideo-inlineViewer--loading');
		$this.trigger('tify_video_inline_toggle.click', [$target, video]);
		$.post( 
			tify_ajaxurl, 
			{ action : 'tiFyVideoGetEmbed', 'attr' : video }, 
			function( resp ){
				$this.removeClass('tiFyVideo-inlineToggle--loading');
				$target.removeClass('tiFyVideo-inlineViewer--loading');
				$target.html(resp);
				$this.trigger('tify_video_inline_toggle.done', $target);
			}
		);
	});
});