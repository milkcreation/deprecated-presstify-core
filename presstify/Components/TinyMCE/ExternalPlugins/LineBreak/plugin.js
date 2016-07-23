tinymce.PluginManager.add( 'linebreak', function(editor) {
	editor.addCommand( 'InsertLineBreak', function() {
		editor.execCommand( 'mceInsertContent', false, '<hr class="linebreak"/>' );
	});

	editor.addButton( 'linebreak', {
		tooltip: tiFyTinyMCELineBreakl10n.title,
		cmd: 'InsertLineBreak'
	});
});
