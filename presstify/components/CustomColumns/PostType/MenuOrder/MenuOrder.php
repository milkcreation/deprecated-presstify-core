<?php
namespace tiFy\Components\CustomColumns\PostType\MenuOrder;

use tiFy\Components\CustomColumns\Factory;

class MenuOrder extends Factory
{
	/* = DEFINITION DES ARGUMENTS PAR DEFAUT = */
	public function getDefaults()
	{
		return array(
			'title'		=> 	__( 'Ordre d\'affich.', 'tify' ),
			'position'	=> 2
		);	
	}
			
	/* = AFFICHAGE DU CONTENU DES CELLULES DE LA COLONNE = */
	public function content( $column, $post_id )
	{	
		$level = 0;
		$post = get_post( $post_id );
		
		if ( 0 == $level && (int) $post->post_parent > 0 ) :
			$find_main_page = (int) $post->post_parent;
			while ( $find_main_page > 0 ) :
				$parent = get_post( $find_main_page );

				if ( is_null( $parent ) )
					break;

				$level++;
				$find_main_page = (int) $parent->post_parent;
			endwhile;
		endif;
		$_level = "";
		
		for( $i=0; $i < $level; $i++ ) :
			$_level .= "<strong>&mdash;</strong> ";
		endfor;
		
		echo $_level. $post->menu_order;		
	}	
}