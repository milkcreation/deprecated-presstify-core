<?php
namespace tiFy\Components\CustomColumns\PostType\Subtitle;

use tiFy\Components\CustomColumns\Factory;

class Subtitle extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> __( 'Sous-titre', 'tify' ),
			'position'	=> 3
		);
	}
	
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $column, $post_id )
	{
		if(  $subtitle = get_post_meta( $post_id, '_subtitle', true ) )
			echo $subtitle;
		else
			echo "<em style=\"color:#AAA;\">". __( 'Aucun', 'tify' ) ."</em>";
	}
}