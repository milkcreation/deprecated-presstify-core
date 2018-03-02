jQuery( document ).ready( function($) {
	/* = VARIABLES = */
	var $currentField,
		$fieldsContainer = $( '.is-fields-container' ),
		$fieldsList = $( '.is-fields-list' ),
		$fieldEditor = $( '.is-field-editor' ),
		$fieldEditorContent = $( '.is-field-editor-content' );
	
	/* = AJOUT D'UN CHAMP = */
	$( document ).on( 'click', '.js-choose-field', function() {
		var $this = $( this ),
			fieldType = $( this ).data( 'type' ),
			fieldGroup = $( this ).data( 'group' );

		$.ajax({
			url 		: tify_ajaxurl,
			data		: { action : 'theme_taboox_surveyform_item', type : fieldType, group : fieldGroup },
			type 		: 'POST',
			dataType	: 'html',
			beforeSend 	: function() {
				$this.closest( '.is-dropdown-list-picker.active' ).removeClass( 'active' );
				$( '.is-overlay' ).fadeIn(400);
			},
			success 	: function( resp ) {
				if( $fieldsContainer.hasClass( 'is-empty' ) )
					$fieldsContainer.removeClass( 'is-empty' );
				$fieldsList.append( resp );
			},
			complete 	: function() {
				$( '.is-overlay' ).fadeOut(400);
			}
		});
	});
	
	/* = SUPPRESSION D'UN CHAMP = */
	$( document ).on( 'click', '.js-remove-field', function(e) {
		e.preventDefault();
		var $_currentField = $( this ).closest( '.is-field' ),
			$others = $_currentField.siblings();
		$_currentField.fadeOut( 400, function() {
			if(  $_currentField.data( 'index' ) == $fieldEditor.data( 'field' ) )
				$( '.is-field-editor-items', $fieldEditorContent ).remove();
			$_currentField.remove();
			if( ! $others.length )
				$fieldsContainer.addClass( 'is-empty' );
		});
	});
	
	/* = PERSONNALISATION D'UN CHAMP = */
	$( document ).on( 'click', '.js-customize-field', function() {
		var $_currentField = $( this ).closest( '.is-field' ),
			$currentFieldEditor = $( '.is-field-editor-items', $_currentField ),
			$fieldEditorItems = $( '.is-field-editor-items', $fieldEditorContent ),
			$activeEditorItem;
		
		$( '.is-editor-title', $fieldEditor ).html( $( '.is-field-title', $_currentField ).clone() );
		$fieldEditor.attr( 'data-field', $_currentField.data( 'index' ) );
		
		if( ! $_currentField.is( $currentField ) ) {
			$( '.is-field-item-editor', $currentField ).append( $fieldEditorItems );
			$fieldEditorContent.append( $currentFieldEditor );
			if( $fieldEditor.hasClass( 'has-opened-panel' ) ) {
				if( $fieldEditor.attr( 'data-active_item' ) && $( '.is-editor-item:eq('+$fieldEditor.attr( 'data-active_item' )+')', $currentFieldEditor ).length ) {
					$( '.is-editor-item:eq('+$fieldEditor.attr( 'data-active_item' )+')', $currentFieldEditor ).addClass( 'is-active' ).siblings().removeClass( 'is-active' );
				} else {
					$fieldEditor.removeClass( 'has-opened-panel' );
				}
			}
		}
		
		$_currentField.addClass( 'is-active' ).siblings().removeClass( 'is-active' );
		
		if( ! $fieldEditor.hasClass( 'is-active' ) )
			$fieldEditor.addClass( 'is-active' );
		
		$currentField = $_currentField;
	});
	
	/* = TRI DES CHAMPS = */
	$fieldsList.sortable({
		axis: 'y',
		handle: '.js-sort-field'
	});
	
	/* = AJOUT D'UN CHOIX = */
	$( document ).on( 'click', '.js-add-choice', function() {
		var $this = $( this ),
			$fieldEditorChoices = $( '.is-choices-list', $this.closest( '.is-field-editor-items' ) ),
			fieldEditorChoicesCount = $fieldEditorChoices.attr( 'data-count' ),
			index = $( this ).closest( '.is-field-editor-items' ).data( 'index' );
		
		$.ajax({
			url 		: tify_ajaxurl,
			data		: { action : 'theme_taboox_surveyform_item_choices', index : index, n : fieldEditorChoicesCount  },
			type 		: 'POST',
			dataType	: 'html',
			beforeSend 	: function() {
				$this.prop( 'disabled', true );
				$this.addClass( 'disabled' );
				$( '.is-choices-spinner' ).addClass( 'is-processing' );
				$( '.is-choices-overlay', $fieldEditor ).fadeIn(400);
			},
			success 	: function( resp ) {
				if( $fieldEditorChoices.hasClass( 'is-empty' ) )
					$fieldEditorChoices.removeClass( 'is-empty' );
				$fieldEditorChoices.append( resp );
				$fieldEditorChoices.attr( 'data-count', ++fieldEditorChoicesCount );
				$( '.is-choices-list', $fieldEditor ).sortable({
					axis	: 'y'
				});
			},
			complete 	: function() {
				$this.prop( 'disabled', false );
				$this.removeClass( 'disabled' );
				$( '.is-choices-spinner' ).removeClass( 'is-processing' );
				$( '.is-choices-overlay', $fieldEditor ).fadeOut(400);
			}
		});
	});
	
	/* = MODIFICATION D'UN CHOIX = */
	$( document ).on( 'click', '.js-deploy-choice', function() {
		$( this ).closest( '.is-choice' ).toggleClass( 'is-active' ).siblings().removeClass( 'is-active' );
	});
	
	$( document ).on( 'keyup', '.js-type-label', function (){
		$( '.is-choice-label', $( this ).closest( '.is-choice' ) ).html( $( this ).val() );
	});
	/* = SUPPRESSION D'UN CHOIX = */
	$( document ).on( 'click', '.js-delete-choice', function() {
		var $fieldEditorChoices = $( '.is-choices-list', $( this ).closest( '.is-field-editor-items' ) ),
			$currentChoice = $( this ).closest( '.is-choice' ),
			$others = $currentChoice.siblings();
		$currentChoice.fadeOut( 400, function() {
			$currentChoice.remove();
			if( ! $others.length )
				$fieldEditorChoices.addClass( 'is-empty' );
		});
	});
	
	/* = TRI DES CHOIX = */
	if( $( '.is-choices-list' ).length ) {
		$( '.is-choices-list' ).sortable({
			axis	: 'y'
		});
	}
	
	/* = SAUVEGARDE D'UN CHAMP = */
	$( document ).on( 'click', '.js-save-field', function() {
		$( '[data-save]', $fieldEditor ).each( function() {
			var selector = $( this ).data( 'save' );
			if( $( this ).data( 'type' ) === 'switch' ) {
				$( selector, $fieldEditor ).val( $( '.tify_control_switch-radio:checked', $( this ) ).val() );
			} else if(  $( this ).data( 'type' ) === 'dropdown' ) {
				$( selector, $fieldEditor ).val( $( 'option:selected', $( this ) ).val() );
			} else {
				$( selector, $fieldEditor ).val( $( this ).val() );
			}
		});
		$( '.is-field-label', $currentField ).html( $( '[data-show="label"]', $fieldEditor ).val() );
		$( '.is-field-label', $fieldEditor ).html( $( '[data-show="label"]', $fieldEditor ).val() );
	});
	
	/* = FERMETURE DU PANNEAU D'ÉDITION = */
	$( document ).on( 'click', '.js-cancel', function() {
		$fieldEditor.removeClass( 'is-active' );
	});
	
	/* = CLIC EN DEHORS DU PANNEAU D'ÉDITION = */
	$( document ).on( 'click', function(e) {
		if( ! $( e.target ).closest( '.is-field-editor' ).length && $fieldEditor.hasClass( 'is-active' ) && ! $( e.target ).is( '.js-customize-field' ) && ! $( e.target ).closest( '.js-customize-field' ).length )
			$fieldEditor.removeClass( 'is-active' );		
	});
	
	/* = DÉPLOIEMENT DES SOUS NIVEAUX DU PANNEAU D'ÉDITION = */
	$( document ).on( 'click', '.js-slide-panel', function() {
		$fieldEditor.attr( 'data-active_item', $( this ).closest( '.is-editor-item' ).index() );
		$fieldEditor.addClass( 'has-opened-panel' );
		$( this ).closest( '.is-editor-item' ).addClass( 'is-active' );
	});
	
	/* = FERMETURE DES SOUS NIVEAUX DU PANNEAU D'ÉDITION = */
	$( document ).on( 'click', '.js-collapse-panel', function() {
		$fieldEditor.attr( 'data-active_item', null );
		$fieldEditor.removeClass( 'has-opened-panel' );
		$( this ).closest( '.is-editor-item' ).removeClass( 'is-active' );
	});
});