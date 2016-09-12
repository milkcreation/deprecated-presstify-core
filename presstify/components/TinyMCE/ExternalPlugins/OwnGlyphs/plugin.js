tinymce.PluginManager.add( 'ownglyphs', function( editor, url ) {
	function showDialog() {
		var gridHtml, win;

		gridHtml = '<table role="list" class="mce-grid"><tbody>';

		tinymce.each( glyphs, function(row) {
			gridHtml += '<tr>';

			tinymce.each( row, function( glyph, name ) {
				gridHtml += '<td style="text-align:center;"><a href="#" data-mce-glyphname="' + name + '" data-mce-glyphcontent="' + glyph + '" title="'+name+'" class="ownglyphs">'+glyph+'</a></td>';
			});

			gridHtml += '</tr>';
		});

		gridHtml += '</tbody></table>';
		
		var glyphsPanel = {
			type: 'container',
			html: gridHtml,
			onclick: function(e) {
				e.preventDefault();
				var linkElm = editor.dom.getParent(e.target, 'a');				
				if (linkElm) {
					editor.insertContent(
						'<span class="ownglyphs">' + linkElm.getAttribute('data-mce-glyphcontent') + '</span> '
					);
					win.close();
				}
			}
		};
		
		win = editor.windowManager.open({
			title: tinymceOwnGlyphsl10n.title,
			spacing: 10,
			padding: 10,
			items: [glyphsPanel],
			buttons: [
				{text: "Close", onclick: function() {
					win.close();
				}}
			]
		});
	}
	
	editor.addButton( 'ownglyphs', {
		onclick: showDialog,
		tooltip: tinymceOwnGlyphsl10n.title
	});
});