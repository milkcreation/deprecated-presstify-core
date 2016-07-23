<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Icon;

use tiFy\Components\CustomColumns\Factory;

class Icon extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> 	__( 'Ordre d\'affich.', 'tify' ),
			'position'	=> 1
		);	
	}
			
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $content, $column_name, $term_id )
	{	
		if( ( $icon = get_term_meta( $term_id, '_icon', true ) ) && ( file_exists( get_template_directory() ."/images/moods/{$icon}.svg" ) ) && ( $data = file_get_contents( get_template_directory() ."/images/moods/{$icon}.svg" ) ) ) 
			echo "<img src=\"data:image/svg+xml;base64,". base64_encode( $data ) ."\" width=\"80\" height=\"80\" />";
	}
}