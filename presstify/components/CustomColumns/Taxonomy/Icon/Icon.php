<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Icon;

use tiFy\Components\CustomColumns\Factory;

class Icon extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> 	__( 'Icone.', 'tify' ),
			'position'	=> 1,
			'name'		=> '_icon',
			'dir' 		=> \tiFy\tiFy::$AbsDir .'/vendor/Assets/svg',
				
		);	
	}
			
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $content, $column_name, $term_id )
	{	
		if( ( $icon = get_term_meta( $term_id, self::getAttrs( 'name' ), true ) ) && ( file_exists( self::getAttrs( 'dir' ) ."/{$icon}" ) ) && ( $data = file_get_contents( self::getAttrs( 'dir' ) ."/{$icon}" ) ) ) 
			echo "<img src=\"data:image/svg+xml;base64,". base64_encode( $data ) ."\" width=\"80\" height=\"80\" />";
	}
}