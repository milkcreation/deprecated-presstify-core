!(function($, doc, win){
    $.widget( 'tify.tiFyPdfViewer', {
    	/**
    	 * Options par défaut
    	 */
    	options: {
    		prevTrigger: 		'.tiFyPdfViewer-nav--prev',
    		nextTrigger: 		'.tiFyPdfViewer-nav--next',
    		downloadTrigger: 	'.tiFyPdfViewer-download',
    		load:				function(event, ui) {},
    		beforeRenderPage:	function(event, ui) {},
    		afterRenderPage:	function(event, ui) {}
    	},
    	/**
    	 * Création du widget PdfViewer
    	 */
        _create: function () {
        	this.PdfViewerContainer = this.element;
			this.canvas = $('.tiFyPdfViewer-canvas', this.PdfViewerContainer).get(0);
			this.fileUrl = this.PdfViewerContainer.data('file_url');
			this.fileName = this.PdfViewerContainer.data('filename');
			this.pdfDoc = null;
			this.reachedEdge = false;
			this.touchStart = null;
			this.touchDown = false;
			this.lastTouchTime = 0;
			this.PdfViewer = {
				navigation:		Boolean(this.PdfViewerContainer.data('navigation')),
				pageNum:		1,
				pageRendering:	false,
				pageNumPending:	null,
				scale:			this.PdfViewerContainer.data('scale'),
				canvas:			this.canvas,
				width:			this.PdfViewerContainer.data('width'),
				fullWidth:		Boolean(this.PdfViewerContainer.data('full_width'))
			};
			this._load();
			this.navPrev();
			this.navNext();
			this.download();
			this.touch();
        },
    	/**
		 * Initialisation de la visionneuse
		 */
    	_load: function() {
    		this.PdfViewer.ctx = this.PdfViewer.canvas.getContext('2d');
			PDFJS.workerSrc = tiFyComponentsPdfViewer.workerSrc;
			var self = this;
			PDFJS.getDocument(self.fileUrl).then(function(_pdfDoc) {
				self.pdfDoc = _pdfDoc;
				self.PdfViewerContainer.find('.tiFyPdfViewer-pageCount').text(self.pdfDoc.numPages);
				if (self.pdfDoc.numPages === 1) {
					self.PdfViewerContainer.addClass('tiFyPdfViewer--onePage');
					self.PdfViewerContainer.find('.tiFyPdfViewer-nav').hide();
				}
				self._renderPage(self.PdfViewer.pageNum);
				self._trigger('load', event, {ui: self});
			});
    	},
        /**
		 * Définition des dimensions du document
		 * @param page Page demandée
		 * @param width Largeur voulue
		 * @returns viewport Vue
		 */
		_setPageViewport: function(page, width) {
			var viewport = page.getViewport(1),
				scale = width / viewport.width;
			return page.getViewport(scale);
		},
        /**
		 * Affichage de la page demandée
		 * @param num Numéro de la page
		 */
		_renderPage: function(num) {
			this.PdfViewer.pageRendering = true;
			var self = this;
			this._trigger('beforeRenderPage', event, {ui: this});
			self.pdfDoc.getPage(num).then(function(page) {
				var viewport;
				if (self.PdfViewer.width) {
					viewport = self._setPageViewport(page, self.PdfViewer.width);
				} else if (self.PdfViewer.fullWidth) {
					viewport = self._setPageViewport(page, self.PdfViewerContainer.innerWidth());
				} else {
					viewport = page.getViewport(self.PdfViewer.scale);
				}
				self.PdfViewer.canvas.height = viewport.height;
			    self.PdfViewer.canvas.width = viewport.width;
				
				var renderContext = {
				    canvasContext: self.PdfViewer.ctx,
				    viewport: viewport
				};
				var renderTask = page.render(renderContext);
				
				renderTask.promise.then(function() {
					if (!self.PdfViewerContainer.hasClass('tiFyPdfViewer--loaded')) {
						self.PdfViewerContainer.addClass('tiFyPdfViewer--loaded');
					}
					self.PdfViewer.pageRendering = false;
					if (self.PdfViewer.pageNumPending !== null) {
						self._renderPage(self.PdfViewer.pageNumPending);
						self.PdfViewer.pageNumPending = null;
					}
				});
			});
			this._trigger('afterRenderPage', event, {ui: this});
			this.PdfViewerContainer.find('.tiFyPdfViewer-pageNum').text(self.PdfViewer.pageNum);
		},
        /**
		 * Définition de la page
		 * @param num Numéro de la page
		 */
		_queueRenderPage: function(num) {
			if (this.PdfViewer.pageRendering) {
	            this.PdfViewer.pageNumPending = num;
	        } else {
	            this._renderPage(num);
	        }
		},
        /**
		 * Page précédente
		 */
		_onPrevPage: function() {
			if (this.PdfViewer.pageNum <= 1) {
		        return;
		    }
		    this.PdfViewer.pageNum--;
		    this._queueRenderPage(this.PdfViewer.pageNum);
		},
		/**
		 * Page suivante
		 */
		_onNextPage: function() {
			if (this.PdfViewer.pageNum >= this.pdfDoc.numPages) {
		        return;
		    }
		    this.PdfViewer.pageNum++;
		    this._queueRenderPage(this.PdfViewer.pageNum);
		},
		/**
		 * Téléchargement
		 */
		_download: function() {
			var a = document.createElement('a');
			if (a.click) {
				a.href = this.fileUrl;
				a.target = '_parent';
				if ('download' in a) {
				  a.download = this.fileName;
				}
			    (document.body || document.documentElement).appendChild(a);
			    a.click();
			    a.parentNode.removeChild(a);
			} else {
			    if (window.top === window &&
			        this.fileUrl.split('#')[0] === window.location.href.split('#')[0]) {
			    	var padCharacter = this.fileUrl.indexOf('?') === -1 ? '?' : '&';
			    	this.fileUrl = this.fileUrl.replace(/#|$/, padCharacter + '$&');
			    }
			    window.open(this.fileUrl, '_parent');
			}
		},
		/**
		 * Traitement d'un élément de navigation
		 */
		_parseNavTrigger: function(element) {
			trigger = {};
			if (!(element instanceof $)) {
				element = $(element);
			}
			trigger['selector'] = element;
			if (element.closest(this.PdfViewerContainer).length) {
				trigger['handler'] = this.PdfViewerContainer;
			} else {
				trigger['handler'] = $(document);
			}
			return trigger;
		},
		/**
		 * Navigation page précédente
		 */
		navPrev: function() {
			var self = this,
				trigger = this._parseNavTrigger(this.options.prevTrigger);
			trigger.handler.on('click.tiFyPdfViewer:prev', function(event) {
				if ($(event.target).is(trigger.selector)) {
					event.preventDefault();
					self._onPrevPage();
				}
			});
		},
		/**
		 * Navigation page suivante
		 */
		navNext: function() {
			var self = this,
				trigger = this._parseNavTrigger(this.options.nextTrigger);
			trigger.handler.on('click.tiFyPdfViewer:next', function(event) {
				if ($(event.target).is(trigger.selector)) {
					event.preventDefault();
					self._onNextPage();
				}
			});
		},
		/**
		 * Navigation téléchargement de fichier
		 */
		download: function() {
			var self = this,
				trigger = this._parseNavTrigger(this.options.downloadTrigger);
			trigger.handler.on('click.tiFyPdfViewer:download', function(event) {
				if ($(event.target).is(trigger.selector)) {
					event.preventDefault();
					self._download();
				}
			});
		},
		/**
		 * Navigation mobile
		 */
		touch: function() {
			var self = this;
			$(self.canvas).on('touchstart.tiFyPdfViewer', function(e) {
				self.touchDown = true;
			    if (e.timeStamp - self.lastTouchTime < 500) {
			        self.lastTouchTime = 0;
			    } else {
			        self.lastTouchTime = e.timeStamp;
			    }
			});
			$(self.canvas).on('touchmove.tiFyPdfViewer', function(e) {
				var _PdfViewerContainer = self.PdfViewerContainer.get(0);
				if (_PdfViewerContainer.scrollLeft === 0 ||
			        _PdfViewerContainer.scrollLeft === _PdfViewerContainer.scrollWidth - page.clientWidth) {
			        self.reachedEdge = true;
			        if (self.touchStart === null) {
			            self.touchStart = e.originalEvent.changedTouches[0].clientX;
			        }
			    } else {
			        self.reachedEdge = false;
			        self.touchStart = null;
			    }
			    if (self.reachedEdge && self.touchStart) {
			        var distance = e.originalEvent.changedTouches[0].clientX - self.touchStart;
			        if (distance < -100) {
			            self.touchStart = null;
			            self.reachedEdge = false;
			            self._onNextPage();
			        } else if (distance > 100) {
			            self.touchStart = null;
			            self.reachedEdge = false;
			            self._onPrevPage();
			        }
			    }
			});
			$(self.canvas).on('touchend.tiFyPdfViewer', function(e) {
				self.touchStart = null;
				self.touchDown = false;
			});
		}
    });
    $('.tiFyPdfViewer').tiFyPdfViewer();
})(jQuery, document, window);