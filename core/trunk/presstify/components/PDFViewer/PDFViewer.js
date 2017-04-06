var tiFyPDFViewer;
!( function( $, doc, win, undefined ){
	var
	/**
	 * Définition des dimensions du document
	 * @param page Page demandée
	 * @param width Largeur voulue
	 * @returns viewport Vue
	 */
	setPageViewport = function(page, width) {
		var viewport = page.getViewport(1),
			scale = width / viewport.width;
		return page.getViewport(scale);
	},
	/**
	 * Affichage de la page demandée
	 * @param num Numéro de la page
	 */
	renderPage = function(num, PDFViewerContainer, pdfDoc, PDFViewer) {
		PDFViewer.pageRendering = true;
		pdfDoc.getPage(num).then(function(page) {
			var viewport;
			if(PDFViewer.width) {
				viewport = setPageViewport(page, PDFViewer.width);
			} else if(PDFViewer.fullWidth) {
				viewport = setPageViewport(page, PDFViewerContainer.innerWidth());
			} else {
				viewport = page.getViewport(PDFViewer.scale);
			}
			PDFViewer.canvas.height = viewport.height;
		    PDFViewer.canvas.width = viewport.width;
			
			var renderContext = {
			    canvasContext: PDFViewer.ctx,
			    viewport: viewport
			};
			var renderTask = page.render(renderContext);
			
			renderTask.promise.then(function() {
				if(!PDFViewerContainer.hasClass('tiFyPDFViewer--ready')) {
					PDFViewerContainer.addClass('tiFyPDFViewer--ready');
				}
				PDFViewer.pageRendering = false;
				if (PDFViewer.pageNumPending !== null) {
					renderPage(PDFViewer.pageNumPending);
					PDFViewer.pageNumPending = null;
				}
			});
		});
		PDFViewerContainer.find('.tiFyPDFViewer-pageNum').text(PDFViewer.pageNum);
	},
	/**
	 * Définition de la page
	 * @param num Numéro de la page
	 * @param PDFViewerContainer Conteneur de la visionneuse
	 * @param pdfDoc Document PDF
	 * @param PDFViewer Visionneuse
	 */
	queueRenderPage = function(num, PDFViewerContainer, pdfDoc, PDFViewer) {
		if (PDFViewer.pageRendering) {
            PDFViewer.pageNumPending = num;
        } else {
            renderPage(num, PDFViewerContainer, pdfDoc, PDFViewer);
        }
	},
	/**
	 * Page précédente
	 * @param PDFViewerContainer Conteneur de la visionneuse
	 * @param pdfDoc Document PDF
	 * @param PDFViewer Visionneuse
	 */
	onPrevPage = function(PDFViewerContainer, pdfDoc, PDFViewer) {
		if (PDFViewer.pageNum <= 1) {
	        return;
	    }
	    PDFViewer.pageNum--;
	    queueRenderPage(PDFViewer.pageNum, PDFViewerContainer, pdfDoc, PDFViewer);
	},
	/**
	 * Page suivante
	 * @param PDFViewerContainer Conteneur de la visionneuse
	 * @param pdfDoc Document PDF
	 * @param PDFViewer Visionneuse
	 */
	onNextPage = function(PDFViewerContainer, pdfDoc, PDFViewer) {
		if (PDFViewer.pageNum >= pdfDoc.numPages) {
	        return;
	    }
	    PDFViewer.pageNum++;
	    queueRenderPage(PDFViewer.pageNum, PDFViewerContainer, pdfDoc, PDFViewer);
	},	
	/**
	 * Téléchargement
	 * @param fileUrl URL du fichier
	 * @param fileName Nom du fichier
	 */
	download = function(fileUrl, fileName) {
		var a = document.createElement('a');
		if (a.click) {
			a.href = fileUrl;
			a.target = '_parent';
			if ('download' in a) {
			  a.download = fileName;
			}
		    (document.body || document.documentElement).appendChild(a);
		    a.click();
		    a.parentNode.removeChild(a);
		} else {
		    if (window.top === window &&
		        fileUrl.split('#')[0] === window.location.href.split('#')[0]) {
		    	var padCharacter = fileUrl.indexOf('?') === -1 ? '?' : '&';
		    	fileUrl = fileUrl.replace(/#|$/, padCharacter + '$&');
		    }
		    window.open(fileUrl, '_parent');
		}
	};
	/**
	 * Initialisation de la visionneuse
	 * @param $target Zone d'affichage de la visionneuse
	 */
	tiFyPDFViewer = function($target) {
		var PDFViewerContainer = $target,
			canvas = $('.tiFyPDFViewer-canvas',$target).get(0),
			fileUrl = PDFViewerContainer.data('file_url'),
			fileName = PDFViewerContainer.data('filename');
			pdfDoc = null,
			reachedEdge = false,
			touchStart = null,
			touchDown = false,
			lastTouchTime = 0;
		var PDFViewer = {
			navigation:		Boolean(PDFViewerContainer.data('navigation')),
			pageNum:		1,
			pageRendering:	false,
			pageNumPending:	null,
			scale:			PDFViewerContainer.data('scale'),
			canvas:			canvas,
			width:			PDFViewerContainer.data('width'),
			fullWidth:		Boolean(PDFViewerContainer.data('full_width'))
		};
		PDFViewer.ctx = PDFViewer.canvas.getContext('2d');
		PDFJS.workerSrc = tiFyComponentsPDFViewer.workerSrc;
		PDFJS.getDocument(fileUrl).then(function(_pdfDoc) {
			pdfDoc = _pdfDoc;
			PDFViewerContainer.find('.tiFyPDFViewer-pageCount').text(pdfDoc.numPages);
			if(pdfDoc.numPages === 1) {
				PDFViewerContainer.find('.tiFyPDFViewer-nav').hide();
			}
			renderPage(PDFViewer.pageNum, PDFViewerContainer, pdfDoc, PDFViewer);
		});
		PDFViewerContainer.on('click', '.tiFyPDFViewer-nav--prev', function() {
			onPrevPage(PDFViewerContainer, pdfDoc, PDFViewer);
		});
		PDFViewerContainer.on('click', '.tiFyPDFViewer-nav--next', function() {
			onNextPage(PDFViewerContainer, pdfDoc, PDFViewer);
		});
		PDFViewerContainer.on('click', '.tiFyPDFViewer-download', function() {
			download(fileUrl, fileName);
		});
		$(canvas).on('touchstart', function(e) {
			touchDown = true;
		    if (e.timeStamp - lastTouchTime < 500) {
		        lastTouchTime = 0;
		    } else {
		        lastTouchTime = e.timeStamp;
		    }
		});
		$(canvas).on('touchmove', function(e) {
			var _PDFViewerContainer = PDFViewerContainer.get(0);
			if (_PDFViewerContainer.scrollLeft === 0 ||
		        _PDFViewerContainer.scrollLeft === _PDFViewerContainer.scrollWidth - page.clientWidth) {
		        reachedEdge = true;
		        if (touchStart === null) {
		            touchStart = e.originalEvent.changedTouches[0].clientX;
		        }
		    } else {
		        reachedEdge = false;
		        touchStart = null;
		    }
		    if (reachedEdge && touchStart) {
		        var distance = e.originalEvent.changedTouches[0].clientX - touchStart;
		        if (distance < -100) {
		            touchStart = null;
		            reachedEdge = false;
		            onNextPage(PDFViewerContainer, pdfDoc, PDFViewer);
		        } else if (distance > 100) {
		            touchStart = null;
		            reachedEdge = false;
		            onPrevPage(PDFViewerContainer, pdfDoc, PDFViewer);
		        }
		    }
		});
		$(canvas).on('touchend', function(e) {
			touchStart = null;
			touchDown = false;
		});
	};
})( jQuery, document, window, undefined );

jQuery(document).ready(function($) {
	$('.tiFyPDFViewer').each(function() {
		tiFyPDFViewer($(this));
	});
});