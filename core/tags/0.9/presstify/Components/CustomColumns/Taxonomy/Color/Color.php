<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Color;

use tiFy\Components\CustomColumns\Factory;

class Color extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> 	__( 'Couleur', 'tify' ),
			'position'	=> 1
		);	
	}
			
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $content, $column_name, $term_id )
	{	
		if( $color = get_term_meta( $term_id, '_color', true ) ) 
			echo "<div style=\"width:80px;height:80px;display:block;border:solid 1px #CCC;background-color:#F4F4F4;position:relative;\"><div style=\"position:absolute;top:5px;right:5px;bottom:5px;left:5px;background-color:{$color}\"></div></div>";
	}
}