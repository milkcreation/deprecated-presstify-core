<?php
namespace tiFy\Core\Taboox\Taxonomy\Order\Helpers;

use tiFy\Core\Taboox\Helpers;

class Order extends Helpers
{
	/* = ARGUMENTS = */
	// Identifiant des fonctions
	protected $ID 				= 'term_order';
	
	// Liste des methodes à translater en Helpers
	protected $Helpers 			= array( 'Get' );
		
	/* == Récupération de l'url de l'image d'entête == **/
	public static function Get( $taxonomy, $args = array() )
	{
		return get_terms(
			wp_parse_args(
				array( 
					'taxonomy' 		=> $taxonomy,
					'meta_query'	=> array(
					    array(
					       'relation'      => 'OR',
    						array(
    							'key' 		=> '_order',
    							'value'		=> 0,
    							'compare'	=> '>=',
    							'type'		=> 'NUMERIC'
    						),
                            array(
        						'key' 		=> '_order',
                                'compare'	=> 'NOT EXISTS',
                           )
				        )
					),
					'orderby'		=>'meta_value_num', 
					'order'			=>'ASC' 					
				),
				$args
			)
		);
	}
}

