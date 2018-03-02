<?php 
namespace tiFy\Core\Templates\Traits\Form;

trait Notices
{
	/* == Notifications prédéfinies == **/
	public static function defaultsNotices()
	{
		//notice : error (admin) danger(front), warning, success, info (par defaut )		
		return array(
			'activated' 				=> array(
				'message'			=> __( 'L\'élément a été activé avec succès.', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			),
			'deactivated' 				=> array(
				'message'			=> __( 'L\'élément a été désactivé avec succès.', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			),
			'trashed' 					=> array(
				'message'			=> __( 'L\'élément a été déplacé dans la corbeille', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			),
			'untrashed' 				=> array(
				'message'			=> __( 'L\'élément a été restauré', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			),
			'created' 					=> array(
				'message'			=> __( 'L\'élément a été créé avec succès', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			),
			'updated' 				=> array(
				'message'			=> __( 'L\'élément a été mis à jour', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			)
		);
	}
	
	/*** === Traitement de la cartographie des notifications === ***/
	public static function parseNotices( $notices = array() )
	{	
		// Attributs par défaut 
		$defaults = array( 
			'message' 		=> '', 
			'query_arg' 	=> 'message', 
			'notice' 		=> 'info', 
			'dismissible' 	=> false 
		);
		
		// Traitement des vues personnalisées
		foreach( $notices as $id => &$attrs ) :
			if( is_string( $attrs ) ) :
				$attrs = array( 'message' => $attrs );
			endif;
			$attrs = wp_parse_args( $attrs, $defaults );
		endforeach;
		
		return wp_parse_args( $notices, self::defaultsNotices() );
	}
}