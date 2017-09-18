<?php
namespace Theme\tiFy\Core\Taboox\Post\SurveyForm\Admin;

class SurveyForm extends \tiFy\Core\Taboox\Admin
{
	/* = ARGUMENTS = */
	/** == Types de champs == **/
	protected $fieldTypes = array();
	/** == Choix pour sélection de champs == **/
	protected $choices = array();
	/** == Nom == **/
	protected $name = '_survey_form_field';
	/** == Tests d'intégrité == **/
	protected $integrityTests;

	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		$this->setFieldTypes();
		$this->setIntegrityTests();
		add_action( 'save_post', array( $this, 'Save' ), 9, 2 );
	}
	/* = INITIALISATION DES TYPES DE CHAMPS = */
	protected function setFieldTypes()
	{
		$this->fieldTypes = array(
			'text' 		=> array(
				'input'	=> array(
					'icon'	=> 'input-text',
					'label'	=> __( 'Une ligne', 'theme' )
				),
				'textarea' => array(
					'icon'	=> 'textarea',
					'label'	=> __( 'Plusieurs lignes', 'theme' )
				)
			),
			'selection'	=> array(
				'radio'			=> array(
					'icon'	=> 'input-radio',
					'label'	=> __( 'Boutons radio', 'theme' )
				),
				'tify_checkbox' => array(
					'icon'	=> 'input-checkbox',
					'label'	=> __( 'Cases à cocher', 'theme' )
				)
				/*'tify_dropdown'	=> array(
					'icon'	=> 'dropdown-list',
					'label'	=> __( 'Liste déroulante', 'theme' )
				)*/
			),
			'buttons'	=> array(
				'submit'	=> array(
					'icon'	=> 'submit',
					'label'	=> __( 'Soumission', 'theme' )
				)
			)
		);
	}
	/* = INITIALISATION DES TESTS D'INTÉGRITÉ = */
	protected function setIntegrityTests()
	{
		$this->integrityTests = array(
			'isInteger'		=> __( 'Chiffres uniquement', 'theme' ),
			'isAlpha'		=> __( 'Lettres uniquement', 'theme' ),
			'isAlphaNum'	=> __( 'Lettres et chiffres uniquement', 'theme' ),
			'isEmail'		=> __( 'Doit être un email valide', 'theme' ),
			'isUrl'			=> __( 'Doit être une URL valide', 'theme' ),
			'isDate'		=> __( 'Doit être une date valide', 'theme' )
		);
	}
	/* = RÉCUPÉRATION D'UNE VALEUR POUR UN TYPE DE CHAMP = */
	protected function getFieldTypeValue( $fieldType, $needle )
	{
		foreach( $this->fieldTypes as $fieldGroup )
			foreach( $fieldGroup as $type => $field )
				if( $type === $fieldType )
					return $field[$needle];
		return false;
	}
	/* = RÉCUPÉRATION DU GROUPE D'UN TYPE DE CHAMP = */
	protected function getFieldTypeGroup( $fieldType )
	{
		foreach( $this->fieldTypes as $fieldGroup => $fields )
			if( in_array( $fieldType, array_keys( $fields ) ) )
				return $fieldGroup;
		return false;
	}
	/* = PRÉPARATION DES TYPES DE CHAMPS POUR LA SÉLECTION = */
	protected function prepareFieldTypes()
	{
		foreach( $this->fieldTypes as $fieldType => $fields ) :
			foreach( $fields as $type => $field ) :
				$this->choices[$fieldType][$type] = "<div class=\"FormFieldsManager-dropdownPickerChoice FormFieldsManager-dropdownPickerChoice--{$type} js-choose-field\" data-group=\"{$fieldType}\" data-type=\"{$type}\">";
				$this->choices[$fieldType][$type] .= "<svg class=\"tifyforms-{$field['icon']} FormFieldsManager-dropdownPickerChoiceIcon\"><use xlink:href=\"#tifyforms-{$field['icon']}\"></use></svg>";
				$this->choices[$fieldType][$type] .= $field['label'];
				$this->choices[$fieldType][$type] .= "\n</div>";
			endforeach;
		endforeach;
	}
	
	/* = RÉCUPÉRATION DES ARGUMENTS D'UN CHAMP = */
	protected function getFieldTypeArgs( $fieldType )
	{		
		if( $this->getFieldTypeGroup( $fieldType ) === 'buttons' ) :
			$args = array(
				'slug'			=> '',
				'type'			=> 'button',
				'value'			=> $fieldType,
				'options'		=> array(
					'label'	=> $this->getFieldTypeValue( $fieldType, 'label' )
				)
			);
		else :
			$args = array(
				'slug'			=> '',
				'type'			=> $fieldType,
				'label'			=> $this->getFieldTypeValue( $fieldType, 'label' ),
				'placeholder'	=> $this->getFieldTypeValue( $fieldType, 'label' )
			);
		endif;
		
		return $args;
	}
	
	/* = AFFICHAGE DES LISTES DE CHOIX = */
	protected function displayFieldTypesDropdown()
	{
		$show_option_none = array(
			'text'		=> __( 'Texte', 'theme' ),
			'selection'	=> __( 'Sélection', 'theme' ),
			'buttons'	=> __( 'Bouton', 'theme' )
		);
		$output = '';
		foreach( $this->choices as $type => $choices ) :
			$output .= "<div class=\"FormFieldsManager-dropdown FormFieldsManager-dropdown--{$type}\">";
			$output .= tify_control_dropdown_menu(
				array(
					'id'				=> "form_fields_manager_dropdown_{$type}",
					'class'				=> 'FormFieldsManager-dropdownList',
					'picker'			=> array(
						'class'	=> "FormFieldsManager-dropdownPicker FormFieldsManager-dropdownPicker--{$type} is-dropdown-list-picker"
					),
					'links' 			=> $choices,
					'show_option_none' 	=> $show_option_none[$type],
					'echo'				=> 0
				)
			);
			$output .= "<span class=\"FormFieldsManager-dropdownChevron dashicons dashicons-arrow-down-alt2\"></span>";
			$output .= "</div>";
		endforeach;
		
		echo $output;
	}
	
	/* = PANNEAU D'ÉDITION = */
	/** == NOEUDS == **/
	/*** === AFFICHAGE D'UN NOEUD === ***/
	public function fieldEditorNode( $title = '', $content = '', $echo = false )
	{		
		$output = "<li class=\"FormFieldsManager-editorItem is-editor-item\">";
		$output .= "\n\t<h3 class=\"FormFieldsManager-editorItemTitle js-slide-panel\">{$title}</h3>";
		$output .= "\n\t<div class=\"FormFieldsManager-editorItemPanel\">";
		$output .= "\n\t\t<div class=\"FormFieldsManager-editorItemPanelHeader\">";
		$output .= "\n\t\t\t<button type=\"button\" class=\"FormFieldsManager-editorItemPanelBack dashicons dashicons-arrow-left-alt2 js-collapse-panel\"></button>";
		$output .= "\n\t\t\t<span class=\"FormFieldsManager-editorItemPanelTitle\">".__( 'Personnalisation', 'theme' )."</span>";
		$output .= "\n\t\t\t<span class=\"FormFieldsManager-editorItemPanelSubtitle\">{$title}</span>";
		$output .= "\n\t\t</div>";
		$output .= "\n\t\t<div class=\"FormFieldsManager-editorItemPanelContent\">";
		$output .= $content;
		$output .= "\n\t\t</div>";
		$output .= "\n\t</div>";
		$output .= "</li>";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/*** === INFORMATIONS GÉNÉRALES === ***/
	public function fieldEditorNodeGeneral( $index, $values, $fieldGroup = false, $echo = false )
	{
		// Nom du champ
		$output = "<div class=\"FormFieldsManager-editorItemField\">";
		$output .= "\n\t<label class=\"FormFieldsManager-editorItemFieldLabel\">".__( 'Intitulé du champ', 'theme' )."</label>";
		$output .= "\n\t<input type=\"text\" data-show=\"label\" data-save=\"#{$index}-label\" class=\"FormFieldsManager-editorItemFieldInput\" value=\"{$values['label']}\">";
		$output .= "\n\t<input type=\"hidden\" id=\"{$index}-label\" name=\"tify_meta_post[{$this->name}][{$index}][label]\" value=\"{$values['label']}\">";
		$output .= "\n\t<span class=\"FormFieldsManager-editorItemFieldDesc\">";
		$output .= __( 'Texte utilisé pour l\'affichage des enregistrements et les messages d\'erreurs.', 'theme' );
		$output .= "\n\t</span>";
		$output .= "\n</div>";
		// Identifiant du champ
		$output .= "<div class=\"FormFieldsManager-editorItemField\">";
		$output .= "\n\t<label class=\"FormFieldsManager-editorItemFieldLabel\">".__( 'Identifiant du champ', 'theme' )."</label>";
		$output .= "\n\t<input type=\"text\" data-save=\"#{$index}-slug\" class=\"FormFieldsManager-editorItemFieldInput\" value=\"{$values['slug']}\">";
		$output .= "\n\t<input type=\"hidden\" id=\"{$index}-slug\" name=\"tify_meta_post[{$this->name}][{$index}][slug]\" value=\"{$values['slug']}\">";
		$output .= "\n\t<span class=\"FormFieldsManager-editorItemFieldDesc\">";
		$output .= __( 'Identifiant unique de ce champ (généré aléatoirement).', 'theme' );
		$output .= "\n\t</span>";
		$output .= "\n</div>";
		// Placeholder
		if( $fieldGroup === 'text' ) :
			$output .= "<div class=\"FormFieldsManager-editorItemField\">";
			$output .= "\n\t<label class=\"FormFieldsManager-editorItemFieldLabel\">".__( 'Texte de remplacement', 'theme' )."</label>";
			$output .= "\n\t<input type=\"text\" data-save=\"#{$index}-placeholder\" class=\"FormFieldsManager-editorItemFieldInput\" value=\"{$values['placeholder']}\">";
			$output .= "\n\t<input type=\"hidden\" id=\"{$index}-placeholder\" name=\"tify_meta_post[{$this->name}][{$index}][placeholder]\" value=\"{$values['placeholder']}\">";
			$output .= "\n\t<span class=\"FormFieldsManager-editorItemFieldDesc\">";
			$output .= __( 'Texte affiché dans le champ si aucune valeur n\'est présente.', 'theme' );
			$output .= "\n\t</span>";
			$output .= "\n</div>";
		endif;
		
		return $this->fieldEditorNode( __( 'Informations générales', 'theme' ), $output, $echo );
	}
	
	/*** === CONTROLES === ***/
	public function fieldEditorNodeControls( $index, $values, $fieldGroup = false, $echo = false )
	{
		// Champ requis
		$output = "<div class=\"FormFieldsManager-editorItemField\" data-save=\"#{$index}-required\" data-type=\"switch\">";
		$output .= "\n\t<label class=\"FormFieldsManager-editorItemFieldLabel\">".__( 'Champ requis', 'theme' )."</label>";
		$output .= tify_control_switch(
			array(
				'id'				=> 'tify_control_switch-'. $index,
				'class'				=> 'FormFieldsManager-editorItemFieldSwitch',
				'value_on'			=> '1',
				'value_off'			=> '0',
				'checked'			=> $values['required'],
				'default'			=> '0',
				'index'				=> $index,
				'echo'				=> 0
			)
		);
		$output .= "\n\t<input type=\"hidden\" id=\"{$index}-required\" name=\"tify_meta_post[{$this->name}][{$index}][required]\" value=\"{$values['required']}\">";
		$output .= "\n</div>";
		
		// Tests d'intégrité
		if( $fieldGroup === 'text' ) :
			$output .= "<div class=\"FormFieldsManager-editorItemField\">";
			$output .= "\n\t<label class=\"FormFieldsManager-editorItemFieldLabel\">".__( 'Test d\'intégrité', 'theme' )."</label>";
			$output .= "\n\t<select class=\"FormFieldsManager-editorItemFieldDropdown\" data-save=\"#{$index}-integrity\" data-type=\"dropdown\">";
				$output .= "\n\t\t<option value=\"-1\" ".selected( $values['integrity_cb'],  '-1', false ).">".__( 'Choisir un test d\'intégrité', 'theme' )."</option>";
				foreach( $this->integrityTests as $test => $label )
					$output .= "\n\t\t<option value=\"".esc_attr(  $test )."\" ".selected( $values['integrity_cb'], $test, false ).">".$label."</option>";
			$output .= "\n\t</select>";
			$output .= "\n\t<input type=\"hidden\" id=\"{$index}-integrity\" name=\"tify_meta_post[{$this->name}][{$index}][integrity_cb]\" value=\"{$values['integrity_cb']}\">";
			$output .= "\n</div>";
		endif;
		
		return $this->fieldEditorNode( __( 'Contrôles', 'theme' ), $output, $echo );
	}
	
	/*** === CHOIX === ***/
	public function fieldEditorNodeChoices( $index, $values, $echo = false )
	{
		$isEmpty = empty( $values['choices' ] ) ? 'is-empty' : null;
		$count = ( ! empty( $values['choices' ] ) ) ? count( $values['choices' ] ) : 0;
		$output = "<div class=\"FormFieldsManager-editorItemChoices\">";
		$output .= "\n\t<button type=\"button\" class=\"FormFieldsManager-editorItemChoicesButton button-secondary add-new-menu-item js-add-choice\"><span class=\"FormFieldsManager-editorItemChoicesButtonAdd is-choices-plus dashicons dashicons-plus\"></span>".__( 'Ajouter un choix', 'theme' )."</button><span class=\"FormFieldsManager-editorItemChoicesButtonSpinner is-choices-spinner spinner\"></span>";
		$output .= "\n\t<div class=\"FormFieldsManager-editorItemChoicesInner\">";
		$output .= "\n\t\t<span class=\"FormFieldsManager-editorItemChoicesInnerOverlay is-choices-overlay\"></span>";
		$output .= "\n\t\t<ul class=\"FormFieldsManager-editorItemChoicesItems is-choices-list {$isEmpty}\" data-title=\"".__( 'Aucun choix ajouté', 'theme' )."\" data-count=\"{$count}\">";
		if( ! empty( $values['choices' ] ) )
			foreach( $values['choices' ] as $n => $v )
				$output .= $this->choiceRender( $index, $n, $v, false );
		$output .= "\n\t\t</ul>";
		$output .= "\n\t</div>";
		$output .= "\n</div>";
		
		return $this->fieldEditorNode( __( 'Liste de choix', 'theme' ), $output, $echo );
	}
	
	/*** === BOUTON === ***/
	public function fieldEditorNodeButton( $index, $values, $echo = false )
	{
		// Nom du champ
		$output = "<div class=\"FormFieldsManager-editorItemField\">";
		$output .= "\n\t<label class=\"FormFieldsManager-editorItemFieldLabel\">".__( 'Intitulé du bouton', 'theme' )."</label>";
		$output .= "\n\t<input type=\"text\" data-show=\"label\" data-save=\"#{$index}-label\" class=\"FormFieldsManager-editorItemFieldInput\" value=\"{$values['options']['label']}\">";
		$output .= "\n\t<input type=\"hidden\" id=\"{$index}-label\" name=\"tify_meta_post[{$this->name}][{$index}][options][label]\" value=\"{$values['options']['label']}\">";
		$output .= "\n\t<input type=\"hidden\" name=\"tify_meta_post[{$this->name}][{$index}][value]\" value=\"{$values['value']}\">";
		$output .= "\n\t<span class=\"FormFieldsManager-editorItemFieldDesc\">";
		$output .= __( 'Texte utilisé pour l\'affichage des enregistrements et les messages d\'erreurs.', 'theme' );
		$output .= "\n\t</span>";
		$output .= "\n</div>";
		
		return $this->fieldEditorNode( __( 'Informations générales', 'theme' ), $output, $echo );
	}
	
	/** == AFFICHAGE DU PANNEAU == **/
	public function fieldEditor()
	{
		?>
		<div class="FormFieldsManager-editor is-field-editor" data-field="" data-active_item="">
			<ul class="FormFieldsManager-editorControls">
				<li class="FormFieldsManager-editorControlsItem FormFieldsManager-editorControlsItem--cancel js-cancel">
					<button type="button" class="FormFieldsManager-editorControlsItemCancel dashicons dashicons-no-alt"></button>
				</li>
				<li class="FormFieldsManager-editorControlsItem FormFieldsManager-editorControlsItem--save">
					<button type="button" class="FormFieldsManager-editorControlsItemSave button button-primary save js-save-field">
						<?php _e( 'Enregistrer', 'theme' ); ?>
					</button>
				</li>
			</ul>
			<div class="FormFieldsManager-editorWrap is-field-editor-content">
				<div class="FormFieldsManager-editorHeader">
					<span class="FormFieldsManager-editorHeaderTitle is-editor-title"><?php _e( 'Personnalisation du champ', 'theme' ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}
	
	/* = INITIALISATION DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_init()
	{
		add_action( 'wp_ajax_theme_taboox_surveyform_item', array( $this, 'wp_ajax_surveyform_item' ) );
		add_action( 'wp_ajax_theme_taboox_surveyform_item_choices', array( $this, 'wp_ajax_surveyform_item_choices' ) );
	}
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Préparation des types de champs
		$this->prepareFieldTypes();
		// Déclaration des metadonnées à enregistrer
		tify_meta_post_register( $current_screen->id, $this->name, false );
	}
	
	/* = MISE EN FILE DES SCRIPTS DE L'INTERFACE D'ADMINISTRATION = */
	public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'dropdown_menu' );
		tify_control_enqueue( 'switch' );
		wp_enqueue_style( 'ThemeTabooxSurveyForm', self::tFyAppUrl() . '/admin.css', array( 'dashicons', 'spinkit-rotating-plane' ), '161123' );
		wp_enqueue_script( 'ThemeTabooxSurveyForm', self::tFyAppUrl() . '/admin.js', array( 'jquery', 'jquery-ui-sortable' ), '161123' );
	}
		
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $post )
	{	
		$fields = \tify_meta_post_get( $post->ID, $this->name );
		?>
		<div class="FormFieldsManager">
			<?php include( $this->Dirname.'/icons.svg' ); ?>
			<div class="FormFieldsManager-tools">
				<?php $this->displayFieldTypesDropdown(); ?>
			</div>
			<div class="FormFieldsManager-fields is-fields-container <?php echo ( empty( $fields ) ) ? 'is-empty' : null; ?>" data-title="<?php _e( 'Aucun champ ajouté', 'theme' ); ?>">
				<div class="FormFieldsManager-fieldsOverlay is-overlay">
					<div class="FormFieldsManager-fieldsSpinner sk-rotating-plane"></div>
				</div>
				<div class="FormFieldsManager-fieldsWrapper">
					<ul class="FormFieldsManager-fieldsItems is-fields-list" id="form_fields_manager_fields_items">
						<?php 
						if( $fields )
							foreach( $fields as $index => $field )
								$this->itemRender( $index, $field, true );
						?>
					</ul>
				</div>
				<?php $this->fieldEditor(); ?>
			</div>
		</div>
		<?php
	}
	
	/* = RENDU D'UN CHAMP = */
	public function itemRender( $index = '', $args = array(), $echo = false )
	{
		if( ! $index )
			$index = uniqid();
		
		$defaults = array(
			'slug'				=> '',
			'type'				=> '',
			'label'				=> '',
			'placeholder'		=> '',
			'value'				=> '',
			'required'			=> '0',
			'integrity_cb'		=> '-1',
			'options'			=> array()
		);
		$values  = wp_parse_args( $args, $defaults );

		extract( $values );
		
		if( $type === 'button' ) :
			$fieldGroup = 'buttons';
			$_type = $value;
			$_label = $options['label'];
		else :
			$fieldGroup = $this->getFieldTypeGroup( $type );
			$_type = $type;
			$_label = $label;
		endif;
		
		$output = "<li class=\"FormFieldsManager-fieldsItem is-field\" data-index=\"{$index}\">";
		$output .= "\n\t<div class=\"FormFieldsManager-fieldsItemInner\">";
		$output .= "\n\t\t<div class=\"FormFieldsManager-fieldsItemInnerWrap js-customize-field\">";
		// Customisation
		$output .= "\n\t\t\t<div class=\"FormFieldsManager-fieldsItemCustomize\"><span class=\"FormFieldsManager-fieldsItemCustomizeIcon dashicons dashicons-plus\"></span></div>";
		// Tri
		$output .= "\n\t\t\t<span class=\"FormFieldsManager-fieldsItemSort dashicons dashicons-sort js-sort-field\"></span>";
		$output .= "\t\t\t\t\t<div class=\"FormFieldsManager-fieldsItemTitle is-field-title\">";
		/// Icône
		if( $icon = $this->getFieldTypeValue( $_type, 'icon' ) )
			$output .= "\n\t\t\t\t<svg class=\"FormFieldsManager-fieldsItemIcon is-field-icon tifyforms-{$icon}\"><use xlink:href=\"#tifyforms-{$icon}\"></use></svg>";
		/// Intitulé
		$output .= "\n\t\t\t\t<span class=\"FormFieldsManager-fieldsItemLabel is-field-label\">{$_label}</span>";
		$output .= "\n\t\t\t</div>";
		$output .= "\n\t\t</div>";
		// Suppression
		$output .= "\n\t<a href=\"#\" type=\"button\" class=\"FormFieldsManager-fieldsItemRemove js-remove-field dashicons dashicons-no-alt\"></a>";
		$output .= "\n\t<input type=\"hidden\" name=\"tify_meta_post[{$this->name}][{$index}][type]\" value=\"{$type}\">";
		
		// Données		
		$output .= "\n\t<div class=\"FormFieldsManager-fieldsItemEditor is-field-item-editor\">";
		$output .= "\n\t\t<ul class=\"FormFieldsManager-editorItems is-field-editor-items\" data-index=\"{$index}\">";
		if( $fieldGroup === 'buttons' ) :
			$output .= $this->fieldEditorNodeButton( $index, $values, false );
		else: 
			$output .= $this->fieldEditorNodeGeneral( $index, $values, $fieldGroup, false );
			$output .= $this->fieldEditorNodeControls( $index, $values, $fieldGroup, false );
			if( $fieldGroup === 'selection' )
				$output .= $this->fieldEditorNodeChoices( $index, $values, false );
		endif;
		$output .= "\n\t\t</ul>";
		$output .= "\n\t</div>";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
	
	/* = RENDU D'UN CHOIX = */
	public function choiceRender( $index, $n, $values = array(), $echo = false )
	{
		$defaults = array(
			'label'			=> sprintf( __( 'Choix %s', 'theme' ), ($n+1) ),
			'slug'			=> ''
		);
		$_values  = wp_parse_args( $values, $defaults );
		
		$output = "<li class=\"FormFieldsManager-editorItemChoicesItem is-choice\">";
		$output .= "\n\t<div class=\"FormFieldsManager-editorItemChoicesItemHeader js-deploy-choice\">";
		$output .= "\n\t<span class=\"is-choice-label\">{$_values['label']}</span>";
		$output .= "\n\t<span class=\"FormFieldsManager-editorItemChoicesItemDeploy dashicons dashicons-arrow-down\"></span>";
		$output .= "\n\t</div>";
		$output .= "\n\t<div class=\"FormFieldsManager-editorItemChoicesItemSettings\">";
		$output .= "\n\t\t<div class=\"FormFieldsManager-editorItemChoicesItemSettingsInner\">";
		$output .= "\n\t\t\t<label for=\"name\" class=\"FormFieldsManager-editorItemChoicesItemLabel\">".__( 'Intitulé du choix', 'theme' )."</label>";
		$output .= "\n\t\t\t<input type=\"text\" data-save=\"#{$index}-choices-{$n}-label\" class=\"FormFieldsManager-editorItemChoicesItemInput js-type-label\" value=\"{$_values['label']}\">";
		$output .= "\n\t\t\t<input type=\"hidden\" id=\"{$index}-choices-{$n}-label\" name=\"tify_meta_post[{$this->name}][{$index}][choices][{$n}][label]\" value=\"{$_values['label']}\">";
		$output .= "\n\t\t\t<span class=\"FormFieldsManager-editorItemChoicesItemDesc\">";
		$output .= __( 'Texte utilisé pour l\'affichage des enregistrements et les messages d\'erreurs.', 'theme' );
		$output .= "\n\t\t\t</span>";
		$output .= "\n\t\t\t<label for=\"name\" class=\"FormFieldsManager-editorItemChoicesItemLabel\">".__( 'Identifiant du choix', 'theme' )."</label>";
		$output .= "\n\t\t\t<input type=\"text\" data-save=\"#{$index}-choices-{$n}-slug\" class=\"FormFieldsManager-editorItemChoicesItemInput\" value=\"{$_values['slug']}\">";
		$output .= "\n\t\t\t<input type=\"hidden\" id=\"{$index}-choices-{$n}-slug\" name=\"tify_meta_post[{$this->name}][{$index}][choices][{$n}][slug]\" value=\"{$_values['slug']}\">";
		$output .= "\n\t\t\t<span class=\"FormFieldsManager-editorItemChoicesItemDesc\">";
		$output .= __( 'Identifiant unique de ce choix (généré aléatoirement).', 'theme' );
		$output .= "\n\t\t\t</span>";
		$output .= "\n\t\t\t<button type=\"button\" class=\"FormFieldsManager-editorItemChoicesItemDelete js-delete-choice\">".__( 'Supprimer', 'theme' )."</button>";
		$output .= "\n\t\t</div>";
		$output .= "\n\t</div>";
		$output .= "\n</li>";
		
		if( $echo )
			echo $output;
		else
			return $output;
	}
	/* = ACTIONS AJAX = */
	/** == AJOUT D'UN CHAMP == **/
	public function wp_ajax_surveyform_item()
	{
		$this->itemRender( null, $this->getFieldTypeArgs( $_POST['type'] ), true );
		exit;
	}
	/** == AJOUT DE CHOIX == **/
	public function wp_ajax_surveyform_item_choices()
	{
		$this->choiceRender( $_POST['index'], $_POST['n'], array(), true );
		exit;
	}
	/* = SAUVEGARDE = */
	public function Save( $post_id, $post )
	{
		// Bypass
		if( empty( $_POST['tify_meta_post'][$this->name] ) )
			return;
		
		$fields = $_POST['tify_meta_post'][$this->name];
		$_fields = $this->getFieldsWithoutButtons( $fields );
		
		foreach( $fields as $n => $field ) :
			if( $field['type'] === 'button' )
				continue;
			$fields[$n] = $this->setSlug( $n, $field, $_fields );
			if( ! empty( $fields[$n]['choices'] ) )
				foreach( $fields[$n]['choices'] as $key => $choice )
					$fields[$n]['choices'][$key] = $this->setSlug( $key, $choice, $fields[$n]['choices'] );
		endforeach;

		$_POST['tify_meta_post'][$this->name] = $fields;
		
		return $post;
	}
	
	/* = RÉCUPÉRATION DES CHAMPS OR BOUTONS = */
	protected function getFieldsWithoutButtons( $fields )
	{
		foreach( $fields as $n => $field )
			if( $field['type'] === 'button' )
				unset( $fields[$n] );
		return $fields;
	}
	
	/* = INITIALISATION D'UN SLUG = */
	protected function setSlug( $n, $field, $fields )
	{
		if( empty( $field['slug'] ) ) :
			if( ! empty( $field['label'] ) )
				$field['slug'] = sanitize_title( $field['label'] );
			else 
				$field['slug'] = uniqid();
		else :
			$field['slug'] = sanitize_title( $field['slug'] );
		endif;
		
		$_fields = $fields;
		unset( $_fields[$n] );
	
		if( ! $this->isUniqueSlug( $field['slug'], $_fields ) ) :
			$suffix = 2;
			$_slug = sanitize_title( $this->truncateSlug( $field['slug'], 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix" );
			while( ! $this->isUniqueSlug( $_slug, $_fields ) ) :
				$_slug = sanitize_title( $this->truncateSlug( $field['slug'], 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix" );
				$suffix++;
			endwhile;
			$field['slug'] = $_slug;
		endif;
		return $field;
	}
	
	/* = VÉRIFIE L'UNICITÉ D'UN SLUG = */
	protected function isUniqueSlug( $slug, $fields )
	{
		foreach( $fields as $field )
			if( $field['slug'] === $slug )
				return false;
		return true;
	}
	/* = TRONCATURE D'UN SLUG = */
	protected function truncateSlug( $slug, $length = 200 )
	{
		if ( strlen( $slug ) > $length ) :
			$decoded_slug = urldecode( $slug );
			if ( $decoded_slug === $slug )
				$slug = substr( $slug, 0, $length );
			else
				$slug = utf8_uri_encode( $decoded_slug, $length );
		endif;
	
		return rtrim( $slug, '-' );
	}
}