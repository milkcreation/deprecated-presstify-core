<?php
namespace tiFy\Core\Taboox\Taxonomy\Icon\Admin;

class Icon extends \tiFy\Core\Taboox\Admin
{
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Traitement des arguments
		$this->args = wp_parse_args( 
			$this->args, 
			array(
				'name' 		=> '_icon',
				'dir' 		=> \tiFy\tiFy::$AbsDir .'/vendor/Assets/svg'
			)
		);
		$this->args['dir'] = wp_normalize_path( rtrim( $this->args['dir'], '/' ) );
		
		tify_meta_term_register( $current_screen->taxonomy, $this->args['name'], true );
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'tify_control-dropdown_images' );
		wp_enqueue_script( 'tify_control-dropdown_images' );
	}
	
	/* = FORMULAIRE DE SAISIE = */	
	public function form( $term, $taxonomy )
	{
		$choices = array();
		foreach( (array) glob( $this->args['dir']. '/*' ) as $filename ) :
			$name 	= basename( $filename );
			$url 	= \tiFy\Lib\File::getRelativeFilename( $filename );
			
			$choices[$name] = $url;			
		endforeach;		

		tify_control_dropdown_images(
			array(
				'name'		=> "tify_meta_term[{$this->args['name']}]",
				'choices'	=> $choices,
				'selected'	=> get_term_meta( $term->term_id, $this->args['name'], true )
			)
		);
	}
}