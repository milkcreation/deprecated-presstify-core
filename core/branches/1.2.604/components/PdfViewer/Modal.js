jQuery(document).ready(function($) {
	$(document).on('click', '.tiFyPdfViewer-modalToggle', function(e) {
		e.preventDefault();
		var $this = $(this),
			$target = $($(this).data('target')),
			$targetBody = $('.modal-body', $target),
			footerButtons = $this.data('footer_buttons'),
			fileUrl = $this.data('file_url'),
			pdf = $this.data('pdf');
		if (!$targetBody.is(':empty')) {
			$target.modal('show');
		} else {
			$.ajax({
				url: 		tify_ajaxurl,
			    dataType: 	'json',
			    method:		'POST',
			    data:		{ action: 'tify_components_pdfviewer_modal', file_url : fileUrl, pdf : pdf },
			    beforeSend: function() {
			    	$this.trigger('tify_components_pdfviewer_modal.before');
			    	$this.addClass('tiFyPdfViewer-modalToggle--loading');
			    },
			    success:	function(resp) {
			    	if (resp.success) {
			    		$targetBody.html(resp.data);
			    		if (footerButtons) {
			    			$('.tiFyPdfViewer', $target).tiFyPdfViewer({
				    			prevTrigger: 		$('.tiFyPdfViewer-nav--prev', $target),
					    		nextTrigger: 		$('.tiFyPdfViewer-nav--next', $target),
					    		downloadTrigger: 	$('.tiFyPdfViewer-download', $target),
					    		load:				function(event, PdfViewer) {
					    			$target.modal('show');
					    			if (PdfViewer.ui.pdfDoc.numPages === 1) {
					    				$('.tiFyPdfViewer-nav', $target).hide();
					    			}
					    		}
				    		});
			    		} else {
			    			$('.tiFyPdfViewer', $target).tiFyPdfViewer({
			    				load:				function(event, PdfViewer) {
			    					$target.modal('show');
			    				}
			    			});
			    		}
			    	}
			    },
			    complete:	function() {
			    	$this.trigger('tify_components_pdfviewer_modal.complete');
			    	$this.removeClass('tiFyPdfViewer-modalToggle--loading');
			    }
			});
		}
	});
});