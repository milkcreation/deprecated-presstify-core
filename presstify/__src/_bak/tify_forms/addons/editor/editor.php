<?php
	/**
	 * EDITEUR
	 */	
	/**
	 * Outils de personnalisation des champs d'un formulaire
	 */
	function form_field_editor( $fields = array() ){
		if( $fields )
			$this->parse_fields( $fields );
						
		$output  = "";		
		$output .= "\n<div id=\"form-customizer\">";
		// Types Navbar
		$output .= "\n\t<nav id=\"type-selector\">";
		$output .= "\n\t\t<ul>";
		foreach( $this->sections as $sslug => $slabel ) :			
			$output .= "\n\t\t\t<li>";
			$output .= "\n\t\t\t\t<a href=\"#section\">{$slabel}</a>";
			$output .= "\n\t\t\t\t<ul>";
			foreach( $this->types_by_section( $sslug ) as $tslug => $tdatas ) :
				$output .= "\n\t\t\t\t\t<li>";
				$output .= "\n\t\t\t\t\t\t<a href=\"#type\" id=\"\" class=\"type-select\" data-type=\"".$tdatas['slug']."\">".$tdatas['label']."</a>";
				$output .= "\n\t\t\t\t\t<li>";				
			endforeach;	
			$output .= "\n\t\t\t\t</ul>";
			$output .= "\n\t\t\t<li>";			
		endforeach;	
		$output .= "\n\t\t</ul>";
		$output .= "\n\t\t<div class=\"clear\"></div>";
		$output .= "\n\t</nav>";
		
		// Form Editor
		$output .= "\n\t<div id=\"form-editor\">";		
		$output .= "\n\t\t<div id=\"field-editor\" class=\"stuffbox\">";
		$output .= "\n\t\t\t<h3 id=\"field-editor-header\">".__( 'Field editor', 'mk_form_class' )."<span class=\"spinner\"></span></h3>";			
		$output .= "\n\t\t\t<div class=\"overlay\"></div>";
		reset($this->fields );
		$field = $this->fields[ key( $this->fields ) ];
		$output .= "\n\t\t\t<div class=\"inside\">";
		$output .= $this->field_editor( $field );
		$output .= "\n\t\t\t</div>";	
 		$output .= "\n\t\t</div>"; // End of #fields-editor 
		
		//Previewer
		$output .= "\n\t\t<div id=\"form-preview\" class=\"stuffbox\">";
		$output .= "\n\t\t\t<h3 id=\"form-preview-header\">".__( 'Form preview', 'mk_form_class' )."<span class=\"spinner\"></span></h3>";	
		$output .= "\n\t\t\t<div class=\"inside\">";
		$output .= "\n\t\t\t<div class=\"overlay\"></div>";
		reset( $this->fields );	

		foreach( (array) $this->fields as $field ) :
			// Prévisualisation du champ			
			$output .= $this->field_preview( $field );
		endforeach;
		
		$output .= "\n\t\t\t</div>";	
		$output .= "\n\t\t</div>";// End of #form-preview		
		$output .= "\n\t</div>"; // End of #form-editor		
		$output .= "\n</div>"; // End of #form-customizer
		
		echo $output; 			
	}
	
	/**
	 * Editeur de champ personnalisé
	 */
	function field_editor( $field ){
		$disabled = ( !$field['editable'] ) ? "disabled=\"disabled\"":"";
				
		$output  = "";
		$output .= "\n<div id=\"field-edit-form\">";
		// Identifiant du champ
		$output .= "\n\t<input type=\"hidden\" name=\"_mkcfield[slug]\" id=\"{$this->args['prefix']}-{$field['slug']}-slug\" value=\"".$field['slug']."\" />";
		$output .= "\n\t<input type=\"hidden\" name=\"_mkcfield[order]\" value=\"".$field['order']."\" />";		
		$output .= "\n\t<input type=\"hidden\" name=\"_mkcfield[type]\" value=\"".$field['type']."\" />";
				
		$output .= "\n\t\t<div class=\"inner\">";
		
		$output .= "\n\t\t\t<h4>";
		$output .= $this->get_type_label( $field['type'] );
		if( $field['new'])
			$output .= " <em style=\"color:#999\">".__('(new)',  'mk_form_class' )."</em>";			
		$output .= "</h4>";
			
		// Tabs
		$output .= "\n\t\t\t<ul class=\"wp-tab-bar\">";
		$output .= "\n\t\t\t\t<li class=\"wp-tab-active\">";
		$output .= "\n\t\t\t\t\t<a href=\"#tabs-panel-mkcform-field-labels\" class=\"nav-tab-link\" data-type=\"tabs-panel-mkcform-field-labels\" class=\"nav-tab-link\">" . __( 'Titles', 'mk_form_class' ) . "</a>";
		$output .= "\n\t\t\t\t</li>";
		if( $this->type_supports( 'choices', $field['type'] ) ) :
			$output .= "\n\t\t\t\t<li>";
			$output .= "\n\t\t\t\t\t<a href=\"#tabs-panel-mkcform-field-choices\" class=\"nav-tab-link\" data-type=\"tabs-panel-mkcform-field-choices\" class=\"nav-tab-link\">" . __( 'choices', 'mk_form_class' ) . "</a>";
			$output .= "\n\t\t\t\t</li>";
		endif;
		if( $this->type_supports( 'integrity-check', $field['type'] ) ) :
			$output .= "\n\t\t\t\t<li>";
			$output .= "\n\t\t\t\t\t<a href=\"#tabs-panel-mkcform-field-check\" class=\"nav-tab-link\" data-type=\"tabs-panel-mkcform-field-check\" class=\"nav-tab-link\">" . __( 'Checks', 'mk_form_class' ) . "</a>";
			$output .= "\n\t\t\t\t</li>";
			$output .= "\n\t\t\t</ul>";
		endif;
		
		// Labels Panel
		$output .= "\n\t\t\t<div id=\"tabs-panel-mkcform-field-labels\" class=\"wp-tab-panel tabs-panel-active\">";
		if( $this->type_supports( 'label', $field['type'] ) ) :
			$output .= "\n\t\t\t\t<p>";
			$output .= "\n\t\t\t\t\t<label for=\"{$this->args['prefix']}-{$field['slug']}-label\">" . __('Label :', 'mk_form_class') . "</label>";
			$output .= "\n\t\t\t\t\t<input type=\"text\" id=\"{$this->args['prefix']}-{$field['slug']}-label\" name=\"_mkcfield[label]\" value=\"".$field['label']."\" autocomplete=\"off\" />";	
			$output .= "\n\t\t\t\t</p>";
		endif;	
		if( $this->type_supports( 'placeholder', $field['type'] ) ) :
			$output .= "\n\t\t\t\t<p>";
			$output .= "\n\t\t\t\t\t<label for=\"{$this->args['prefix']}-{$field['slug']}-placeholder\">" . __('Placeholder :', 'mk_form_class') . "</label>";
			$output .= "\n\t\t\t\t\t<input type=\"text\" id=\"{$this->args['prefix']}-{$field['slug']}-placeholder\" name=\"_mkcfield[placeholder]\" value=\"".$field['placeholder']."\" autocomplete=\"off\" />";	
			$output .= "\n\t\t\t\t</p>";
		endif;	
		$output .= "\n\t\t\t</div>";
			
		// choices Panel
		if( $this->type_supports( 'choices', $field['type'] ) ) :
			$output .= "\n\t\t\t<div id=\"tabs-panel-mkcform-field-choices\" class=\"wp-tab-panel tabs-panel-inactive\">";
			$output .= $this->type_choices( $field );
			$output .= "\n\t\t\t</div>";
		endif;
		
		// Check Panel	
		if( $this->type_supports( 'integrity-check', $field['type'] ) ) :
			$output .= "\n\t\t\t<div id=\"tabs-panel-mkcform-field-check\" class=\"wp-tab-panel tabs-panel-inactive\">";
			$output .= "\n\t\t\t\t<p>";
			$output .= "\n\t\t\t\t\t<label for=\"{$this->args['prefix']}-{$field['slug']}-required\">" . __('Required field :', 'mk_form_class') . "</label>";
			$output .= "\n\t\t\t\t\t<input type=\"radio\" id=\"{$this->args['prefix']}-{$field['slug']}-required-y\" name=\"_mkcfield[required]\" value=\"1\" " . checked( (bool) $field['required'], true, false ) . " $disabled/>";
			$output .= "\n\t\t\t\t\t<label for=\"{$this->args['prefix']}-{$field['slug']}-required-y\">" . __( 'yes', 'mk_form_class' ) . "</label>";
			$output .= "\n\t\t\t\t\t<input type=\"radio\" id=\"{$this->args['prefix']}-{$field['slug']}-required-n\" name=\"_mkcfield[required]\" value=\"0\" " . checked( (bool) ! $field['required'], true, false ) . " $disabled/>";
			$output .= "\n\t\t\t\t\t<label for=\"{$this->args['prefix']}-{$field['slug']}-required-n\">" . __( 'no', 'mk_form_class' ) . "</label>";
			$output .= "\n\t\t\t\t</p>";
			$output .= "\n\t\t\t\t<p>";
			$output .= "\n\t\t\t\t\t<label for=\"{$this->args['prefix']}-{$field['slug']}-integrity_cb\">" . __('Field value :', 'mk_form_class') . "</label>";
			$output .= "\n\t\t\t\t\t<select id=\"{$this->args['prefix']}-{$field['slug']}-integrity_cb\" name=\"_mkcfield[integrity_cb]\" $disabled>";
			$output .= "\n\t\t\t\t\t\t<option value=\"\" ".selected( ! $field['integrity_cb'], true, false ).">" . __( 'No verification', 'mk_form_class' ). "</option>";
			foreach( $this->integrity_cb as $integrity )
				$output .= "\n\t\t\t\t\t\t<option value=\"{$integrity['cb']}\" ".selected( $integrity['cb'] === $field['integrity_cb'], true, false ).">{$integrity['label']}</option>";
			$output .= "\n\t\t\t\t\t</select>";
			$output .= "\n\t\t\t\t</p>";
			$output .= "\n\t\t\t</div>";
		endif;	
		
		// Actions
		$output .= "\n\t\t\t<div class=\"bottom\">";
		$output .= "\n\t\t\t\t<div class=\"inner\">";		
		$output .= "\n\t\t\t\t\t<a href=\"#\" class=\"button-delete\">".__( 'Delete', 'mk_form_class' )."</a>&nbsp;&nbsp;";
		if( $field['new'])
			$output .= "\n\t\t\t\t\t<a href=\"#\" class=\"button-secondary button-save addnewfield\" >".__( 'Add field', 'mk_form_class' )."</a>";	
		else
			$output .= "\n\t\t\t\t\t<a href=\"#\" class=\"button-secondary button-save updatefield\" >".__( 'Update field', 'mk_form_class' )."</a>";	
		$output .= "\n\t\t\t\t</div>";		
		$output .= "\n\t\t\t</div>";
		
		$output .= "\n\t\t</div>"; // End of .inner
		$output .= "\n</div>";
		
		return $output;	
	}

	/**
	 * Editeur des choices selon le type de champ
	 */
	function type_choices( $field ){
		$disabled = ( !$field['editable'] ) ? "disabled=\"disabled\"":"";
				
		$output = "";
		$output .= "\n<div class=\"field-choices\">";
		
		// None Option
		/*$output .= "\n\t<p style=\"border-bottom:dashed 1px #D4D4D4;padding-bottom:5px;\">";		
		if( ! empty( $field['none_option'] ) ) :
			$output .= "\n\t\t<input type=\"radio\" name=\"_mkcfield[default]\" value=\"-1\" $disabled/>";
			$output .= "\n\t\t<input type=\"text\" name=\"_mkcfield[none_option]\" value=\"".$val['none_option']."\" $disabled/>";
			if( !$disabled ) :
				$output .= "\n\t\t<a href=\"#del\" class=\"btn del\"/>del</a>";
			endif;	
		else :
			$output .= "\n\t\t<a href=\"#none\" >".__( 'Option none', 'mk_form_class' )."</a>";	
		endif;
		$output .= "\n\t</p>";*/
		// choices List
		$output .= "\n\t<div class=\"field-choices-list\">";
		if( ! empty( $field['choices'] ) ) :
			foreach( (array) $field['choices'] as $choice_value => $choice_label ) :
				$output .= "\n\t\t<div class=\"field-choice\">";
				if( $this->type_supports( 'multiselect', $field['type'] ) ) 
					$output .= "\n\t\t\t<input type=\"checkbox\" name=\"_mkcfield[default][]\" value=\"on\" ".checked( in_array( $choice_value,  $field['default'] ), true, false )." $disabled />";
				else
					$output .= "\n\t\t\t<input type=\"radio\" name=\"_mkcfield[default]\" value=\"$choice_value\" ".checked( $choice_value == $field['default'], true, false )." $disabled />";
				
				$output .= "\n\t\t\t<input type=\"text\" name=\"_mkcfield[choices][]\" value=\"$choice_label\" $disabled autocomplete=\"off\"/>";
				
				if( !$disabled ) :
					$output .= "\n\t\t\t<a href=\"#del\" class=\"btn add addinput\"/></a>";
					$output .= "\n\t\t\t<a href=\"#del\" class=\"btn del delinput\"/></a>";
				endif;
				$output .= "\n\t\t</div>";	
			endforeach;
		endif;
		
		$output .= "\n\t\t<div class=\"field-choice\">";
		if( $this->type_supports( 'multiselect', $field['type'] ) ) 
			$output .= "\n\t\t\t<input type=\"checkbox\" name=\"_mkcfield[default][]\" value=\"\" $disabled />";
		else			
			$output .= "\n\t\t\t<input type=\"radio\" name=\"_mkcfield[default]\" value=\"\" $disabled />";
		$output .= "\n\t\t\t<input type=\"text\" name=\"_mkcfield[choices][]\" value=\"\" $disabled autocomplete=\"off\"/>";
		
		if( !$disabled ) :
			$output .= "\n\t\t\t<a href=\"#del\" class=\"btn add addinput\"/></a>";
			$output .= "\n\t\t\t<a href=\"#del\" class=\"btn del delinput\"/></a>";
		endif;
		
		
		$output .= "\n\t\t</div>";			
		 	
		$output .= "\n\t</div>";
		
		$output .= "\n</div>"; // End of field-choices
		
		return $output;
	}

	/**
	 * Prévisualisation d'un champ
	 */
	function field_preview( $field ){
		$output = "";
		
		$value = $field['default'];		

		$output .= "\n<div><div id=\"field-preview-{$field['slug']}\" class=\"field-preview\">";	
		$output .= "\n\t<div class=\"inner\">";
				
		// Données embarquées
			// Identifiant du champ
			$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-slug\" name=\"{$this->args['prefix']}[{$field['slug']}][slug]\" value=\"".$field['slug']."\" />";
			// Ordre du champ
			$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-order\" name=\"{$this->args['prefix']}[{$field['slug']}][order]\" value=\"".$field['order']."\" />";		
			// Type
			$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-type\" name=\"{$this->args['prefix']}[{$field['slug']}][type]\" value=\"".$field['type']."\" />";
			// Intitulés
			if( $this->type_supports( 'label', $field['type'] ) )
				$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-label\" name=\"{$this->args['prefix']}[{$field['slug']}][label]\" value=\"".$field['label']."\" />";	
			if( $this->type_supports( 'placeholder', $field['type'] ) )
				$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-placeholder\" name=\"{$this->args['prefix']}[{$field['slug']}][placeholder]\" value=\"".$field['placeholder']."\" />";	
			// Valeur par défaut
			$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-default\" name=\"{$this->args['prefix']}[{$field['slug']}][default]\" value=\"".$field['default']."\" />";
			// choices
			if( $this->type_supports( 'choices', $field['type'] ) ) 	
				$output .= $this->hidden_choices( $field );
			// Vérifications	
			$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-required\" name=\"{$this->args['prefix']}[{$field['slug']}][required]\" value=\"".$field['required']."\" />";
			$output .= "\n\t\t<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-integrity_cb\" name=\"{$this->args['prefix']}[{$field['slug']}][integrity_cb]\" value=\"".$field['integrity_cb']."\" />";
				
		if( $this->type_supports( 'label', $field['type'] ) && $field['label'] )
			$output .= "\n\t\t\t<label class=\"field-label\">".$field['label']."</label>";
		
		switch( $field['type'] ) :
			case 'text' :				
				$output .= "\n\t\t\t<input type=\"text\" value=\"$value\" placeholder=\"".$field['placeholder']."\" />";
				break;
			case 'textarea' :				
				$output .= "\n\t\t\t<textarea placeholder=\"".$field['placeholder']."\" >$value</textarea>";
				break;
			case 'password' :				
				$output .= "\n\t\t\t<input type=\"password\" placeholder=\"".$field['placeholder']."\" />";
				break;		
			case 'radio' :
				foreach ( $field['choices'] as $opt_k => $opt_v ):
					if( empty( $opt_v ) ) continue;
					$output .= "\n\t\t\t<input id=\"mkcf-preview-field-{$field['slug']}-$opt_k\" type=\"radio\" value=\"$opt_k\" ". checked( $opt_k == $value ,true, false )." /><label for=\"mkcf-preview-field-{$field['slug']}-$opt_k\">$opt_v</label>";
				endforeach;
				break;
			case 'checkbox' :
				foreach ( $field['choices'] as $opt_k => $opt_v ):
					if( empty( $opt_v ) ) continue;
					$output .= "\n\t\t\t<input id=\"mkcf-preview-field-{$field['slug']}-$opt_k\" type=\"checkbox\" value=\"$opt_k\" ". checked( in_array( $opt_k, $value ),true, false )." /><label for=\"mkcf-preview-field-{$field['slug']}-$opt_k\">$opt_v</label>";
				endforeach;
				break;
			case 'dropdown' :
				$output .= "\n\t\t\t<select>";
				foreach ( $field['choices'] as $opt_k => $opt_v ):
					if( empty( $opt_v ) ) continue;
					$output .= "\n\t\t\t\t<option ". selected( $opt_k == $value, true, false ).">$opt_v</option>";
				endforeach;
				$output .= "\n\t\t\t</select>";	
				break;
			case 'select-multiple' :
				$output .= "\n\t\t\t<select multiple>";
				foreach ( $field['choices'] as $opt_k => $opt_v ):
					if( empty( $opt_v ) ) continue;
					$output .= "\n\t\t\t\t<option ". selected( in_array( $opt_k, $value ),true, false ).">$opt_v</option>";
				endforeach;
				$output .= "\n\t\t\t</select>";	
				break;
			case 'captcha' :
				require_once( MKFORMCLASS_DIR.'/assets/re-captcha/recaptchalib.php');
				$output .= recaptcha_get_html('6LdPY-ASAAAAAGHLQRw1p3VuNiQkpsjVHxzHRnQq');
				break;										
		endswitch;
		
		// Outils de contrôle de modification du champ
		$output .= "\n\t\t<div class=\"toolbox\">";
		$output .= "\n\t\t\t<a class=\"edit\" data-slug=\"".$field['slug']."\" data-prefix=\"".$this->args['prefix']."\"  href=\"#edit\"></a>";
		$output .= "\n\t\t\t<a class=\"move\" href=\"#move\"></a>";
		$output .= "\n\t\t\t<a class=\"delete\" href=\"#delete\"></a>";
		$output .= "\n\t\t</div>";
	
		$output .= "\n\t</div>";
		$output .= "\n</div></div>"; // End Of .field-preview
		
		return $output;		
	}

	/**
	 * Editeur des choices selon le type de champ
	 */
	function hidden_choices( $field ){
		$output = "";
		
		if( ! empty( $field['none_choice'] ) ) 
			$output .= "<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-none_choice\" name=\"{$this->args['prefix']}[{$field['slug']}][none_choice]\" value=\"".$val['none_choice']."\" />";
		if( ! empty( $field['all_choices'] ) ) 
			$output .= "<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-all_choices\" name=\"{$this->args['prefix']}[{$field['slug']}][all_choices]\" value=\"".$val['all_choices']."\" />";
		if( ! empty( $field['choices'] ) )
			foreach( (array) $field['choices'] as $value => $label )
				$output .= "<input type=\"hidden\" id=\"{$this->args['prefix']}-{$field['slug']}-choice-{$value}\" name=\"{$this->args['prefix']}[{$field['slug']}][choices][$value]\" value=\"$label\" />";
		
		return $output;
	}
	
	/**
	 * 
	 */
	function ajax_load_field_editor(){
		if( isset( $_POST['slug'] ) && isset( $_POST['prefix'] ) ) :	
			$field = $this->parse_field( $_POST[$_POST['prefix']][$_POST['slug']] );	
		elseif( isset( $_POST['type'] ) ) :
			$field = $this->parse_field( array( 'slug'=> uniqid(), 'type' => $_POST['type'], 'new' => true ) );
		endif;	
				
		echo $this->field_editor( $field );
		exit;
	}
	
	/**
	 * 
	 */
	function ajax_load_field_preview(){
		$field = $this->parse_field( $_POST['_mkcfield'] );	
				
		echo $this->field_preview( $field );
		exit;
	}
	
	/**
	 * SCRIPT LOADER
	 */	 
	/**
	 * Chargement des scripts 
	 */
	function script_loader(){
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );	
		add_action( 'admin_print_scripts', array( &$this, 'admin_print_scripts' ) );
		add_action( 'admin_footer', array( &$this, 'admin_footer' ), 11 );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}	 
	 
	/**
	 * 
	 */ 
	function admin_enqueue_scripts(){
		wp_enqueue_style( 'mk_form_class', MKFORMCLASS_URL.'/css/milk_form_class.css' );	
		wp_enqueue_script( 'mk_form_class', MKFORMCLASS_URL.'/js/milk_form_class.js', array( 'jquery', 'jquery-ui-sortable' ), '20130416', true );
	}
	
	/**
	 * 
	 */
	function admin_print_scripts(){
	?><style type="text/css">
		.accordion-section-title a.remove{
			background-image: url("<?php bloginfo('wpurl');?>/wp-admin/images/xit.gif");
		}
	</style><?php
	}

	/**
	 * 
	 */
	function admin_footer(){
	?><script type="text/javascript">/* <![CDATA[ */ /* ]]> */</script><?php
	}
	
	/**
	 * 
	 */
	function admin_init(){
		add_action( 'wp_ajax_load_field_editor', array( &$this, 'ajax_load_field_editor' ) );
		add_action( 'wp_ajax_load_field_preview', array( &$this, 'ajax_load_field_preview' ) );
	}