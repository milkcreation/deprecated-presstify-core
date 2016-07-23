<?php
namespace tiFy\Components\CustomColumns\Taxonomy\Order;

use tiFy\Components\CustomColumns\Factory;

class Order extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> 	__( 'Ordre d\'affich.', 'tify' ),
			'position'	=> 3
		);	
	}
			
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $content, $column_name, $term_id )
	{	
		echo (int) get_term_meta( $term_id, '_order', true );
	}	
}