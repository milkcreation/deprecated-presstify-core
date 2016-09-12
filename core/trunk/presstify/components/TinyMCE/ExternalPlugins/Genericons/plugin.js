tinymce.PluginManager.add( 'genericons', function( editor, url ) {
	function showDialog() {
		var gridHtml, win;

		gridHtml = '<table role="list" class="mce-grid"><tbody>';

		tinymce.each( GenericonsChars, function(row) {
			gridHtml += '<tr>';

			tinymce.each( row, function( glyph, name ) {
				gridHtml += '<td style="text-align:center;"><a href="#" data-mce-glyphname="' + name + '" data-mce-glyphcontent="' + glyph + '" title="'+name+'" class="genericons">'+glyph+'</a></td>';
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
						'<span class="genericons">' + linkElm.getAttribute('data-mce-glyphcontent') + '</span> '
					);
					win.close();
				}
			}
		};
		
		win = editor.windowManager.open({
			title: tinymceGenericonsl10n.title,
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
	
	editor.addButton( 'genericons', {
		onclick: showDialog,
		tooltip: tinymceGenericonsl10n.title
	});
});