tinymce.PluginManager.add( 'jumpline', function(editor) {
	editor.addCommand( 'InsertJumpLine', function() {
		editor.execCommand( 'mceInsertContent', false, '<hr class="jumpline"/>' );
	});

	editor.addButton( 'jumpline', {
		tooltip: tiFyTinyMCEJumpLinel10n.title,
		cmd: 'InsertJumpLine'
	});
});
