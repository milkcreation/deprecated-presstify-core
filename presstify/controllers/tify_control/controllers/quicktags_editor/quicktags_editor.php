<?php
/**

);
 */
class tiFy_Control_QuicktagsEditor extends tiFy_Control{
	public $id = 'quicktags_editor';
	
	/* = Déclaration des scripts = */
	public function register_scripts(){
		wp_register_style( 'tify_controls-quicktags_editor', $this->uri ."/quicktags_editor.css", array( 'font-awesome' ), '141212' );
		wp_register_script( 'tify_controls-quicktags_editor', $this->uri ."/quicktags_editor.js", array( 'jquery', 'quicktags' ), '141212', true );
		
	}
	
	public function enqueue_scripts(){
		wp_enqueue_style( 'tify_controls-quicktags_editor' );
		wp_enqueue_script( 'tify_controls-quicktags_editor' );
	}
		
	/* = Affichage du controleur = */
	public function display( $args = array() ){
		static $instance = 0; $instance++;
		
		$defaults = array(
			'id'				=> 'tify_control_quicktags_editor-'. $instance,
			'class'				=> '',
			'name'				=> 'tify_control_quicktags_editor-'. $instance,
			'value'				=> '',
			'preview'			=> false,
			'editor'			=> 'textarea', // textarea | input
			// Liste des boutons natifs, mettre à null pour désactiver tous les boutons natifs
			'buttons'			=> array( 
				'link', 'strong', 'code', 'del', 'fullscreen', 'em', 'li', 'img', 'ol', 'block', 'ins', 'more', 'ul', 'spell', 'close' 
			),
			/**
			 * Liste de boutons personnalisés
			 * @see https://codex.wordpress.org/Quicktags_API
			 * 	array(
			 * 		array(
			 * 			id, display, arg1, arg2, access_key, title, priority
			 *		),
			 * 		array(
			 * 			id, display, arg1, arg2, access_key, title, priority
			 *		)
			 * 	)
			 * 
		   	 * 	// Exemple de personnalisation
				tify_control_quicktags_editor( 
					array( 
						'buttons' 		=> null, 
						'add_buttons'	=> array(
							array( '_strong', __( 'Gras', 'tify' ), '<strong>', '</strong>', 'b', __( 'Texte en gras', 'tify' ), 10 ),
							array( '_em', __( 'Italique', 'tify' ), '<em>', '</em>', 'i', __( 'Texte en italique', 'tify' ), 20 ),
							array( '_ins', __( 'Souligné', 'tify' ), '<ins>', '</ins>', 's', __( 'Soulignement du texte', 'tify' ), 30 ),
							array( '_del', __( 'Barré', 'tify' ), '<del>', '</del>', 'd', __( 'Texte barré', 'tify' ), 40 ),
							array( '_ul', __( 'Liste', 'tify' ), '<ul>\n', '</ul>\n\n', 'u', __( 'Liste', 'tify' ), 50 ),
							array( '_li', __( 'Puce de liste', 'tify' ), '\t<li>', '</li>\n\n', 'l', __( 'Puce de liste', 'tify' ), 60 ),
							array( '_ol', __( 'Numéro de liste', 'tify' ), '\t<ol>', '</ol>\n\n', 'o', __( 'Numéro de liste', 'tify' ), 70 ),
							array( '_block', __( 'Citation', 'tify' ), '\n\n<blockquote>', '</blockquote>\n\n', 'q', __( 'Citation', 'tify' ), 80 ),
							array( '_code', __( 'Code', 'tify' ), '<code>', '</code>', 'c', __( 'Code', 'tify' ), 90 )
						)
					)
			 	) 
			 */ 
			'add_buttons'		=> array( )
		);
		$args = wp_parse_args( $args, $defaults );
		
		// Traitement des boutons natifs
		if( is_null( $args['buttons'] ) ) :
			$args['buttons'] = ' ';
		elseif( ! empty( $args['buttons'] ) && is_array( $args['buttons'] ) ) :
			if( $args['editor'] === 'input' )
				$args['buttons'] = array_diff(  $args['buttons'], array( 'fullscreen', 'li', 'img', 'ol', 'block', 'more', 'ul' ) );
			
			$args['buttons'] = implode( ',', $args['buttons'] );
		endif;
		
		// Traitement des boutons personnalisés
		/*if( ! empty( $args['add_buttons'] ) && is_array( $args['add_buttons'] ) ) :
			foreach( $args['add_buttons'] as $param ) 
		endif;*/
		
		$this->instance_args[$instance] = $args;
		extract( $args );
		
		$output  = "";
		$output .= "<div id=\"tify_control_quicktags_editor-wrapper-{$instance}\" class=\"tify_control_quicktags_editor-wrapper\">\n";
		if( $editor === 'textarea' )
			$output .= "\t<textarea id=\"{$id}\" name=\"{$name}\" class=\"tify_control_quicktags_editor\">{$value}</textarea>\n";
		elseif( $editor === 'input' )
			$output .= "\t<input id=\"{$id}\" name=\"{$name}\" class=\"tify_control_quicktags_editor\" value=\"{$value}\"/>\n";
		$output .= "</div>\n";
		
		add_action( 'wp_footer', array( $this, 'wp_footer' ), 99 );
		
		return $output;
	}
	
	final public function wp_footer(){		
		if ( ! wp_script_is( 'quicktags' ) ) return;
	?><script type="text/javascript">/* <![CDATA[ */
		<?php foreach( $this->instance_args as $instance => $args ) :?>
		var tify_QtagsEd<?php echo $instance;?> = quicktags({ 
				id 		: '<?php echo $args['id'];?>',
				buttons : '<?php echo $args['buttons'];?>'
			});
			console.log( tify_QtagsEd<?php echo $instance;?>.id );
			<?php if( ! empty( $args['add_buttons'] ) ) :?>
				<?php foreach( $args['add_buttons'] as $i => $param ) :?>
					QTags.addButton( '<?php echo $param[0];?>', '<?php echo $param[1];?>', '<?php echo $param[2];?>', '<?php echo $param[3];?>', '<?php echo $param[4];?>', '<?php echo $param[5];?>', '<?php echo $param[6];?>', tify_QtagsEd<?php echo $instance;?>.id );
				<?php endforeach;?>				
			<?php endif;?>
			jQuery.each( edButtons, function( u, v ){
				if( ( v !== undefined ) && ( v.instance === tify_QtagsEd<?php echo $instance;?>.id ) )
					console.log( v );
			});
		<?php endforeach;?>
		/**
		 *	// Exemple de personnalisation du comportement d'un bouton
		 * 	// -> Modification de l'intitulé de la fenêtre de saisie du bouton lien
		 * 	QTags.LinkButton.prototype.callback = function( e, c, ed, defaultValue ) {
		 * 		var URL, t = this;
		 * 		if ( ! defaultValue )
		 * 			defaultValue = 'http://';
		 * 		if ( t.isOpen(ed) === false ) {
		 * 			URL = prompt( "Saisissez l'adresse du site", defaultValue );
		 * 			if ( URL ) {
		 * 				t.tagStart = '<a href="' + URL + '">';
		 * 				QTags.TagButton.prototype.callback.call(t, e, c, ed);
		 *			}
		 * 		} else {
		 * 			QTags.TagButton.prototype.callback.call(t, e, c, ed);
		 * 		}
		 *	}						
		 */
		/* ]]> */</script><?php	
	}
}