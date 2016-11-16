<?php
namespace tiFy\Core\Admin;

class Helpers
{
	/* = STATUTS = */
	/** == Récupération des attributs == **/
	public static function getStatus( $status, $singular = true, $statuses = array() )
	{
		if( empty( $statuses[$status] ) ) :
			return $status;
		elseif( is_string( $statuses[$status] ) ) :
			return $statuses[$status];
		elseif( $singular && ! empty( $statuses[$status]['singular'] ) ) :
			return $statuses[$status]['singular'];
		elseif( ! $singular && ! empty( $statuses[$status]['plural'] ) ) :
			return $statuses[$status]['plural'];
		else :
			return $status;
		endif;		
	}
	
	/* = LISTE = */
	/** == NOTIFICATIONS == **/
	/*** === Cartographie des notifications === ***/
	public static function ListTableNoticesMap( $notices = array() )
	{	
		//notice :error, warning, success, info (par defaut )		
		$defaults = array(
			'deleted' 					=> array(
				'message'			=> __( 'L\'élément a été supprimé définitivement', 'tify' ),
				'query_arg'			=> 'message',
				'notice'			=> 'success',
				'dismissible' 		=> false
			),
			'trashed' 					=> array(
				'message'			=> __( 'L\'élément a été placé dans la corbeille', 'tify' ),
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
		
		// Traitement des vues personnalisées
		$_notices = array();
		foreach( (array) $notices as $nid => $nattr ) :
			if( is_string( $nattr ) ) :
				$_notices[$nid] = array(
					'message'			=> $nattr,
					'query_arg'			=> 'message',
					'notice'			=> 'info',
					'dismissible' 		=> false
				);
			else :
				$_notices[$nid] = wp_parse_args( (array) $nattr, array( 'message' => '', 'query_arg' => 'message', 'notice' => 'info', 'dismissible' => false ) );
			endif;
		endforeach;
		
		return wp_parse_args( $_notices, $defaults );
	}
	
	/** == VUES FILTRÉES == **/
	/*** === Cartographie des vues filtrées === ***/
	public static function ListTableFilteredViewsMap( $views = array() )
	{
		$_views = array();
		foreach( (array) $views as $vid => $vattr ) :
			if( is_string( $vattr ) ) :
				$_views[$vid] = $vattr;
			else :
				$_views[$vid] = self::ListTableFilteredViewLink( $vattr );
			endif;
		endforeach;
		
		return $_views;
	}
	
	/** === Traitement des arguments d'une vue filtrée == **/
	public static function ListTableFilteredViewLink( $args = array() )
	{
		static $index;

		$defaults = array(
			'label'					=> sprintf( __( 'Filtre #%d', 'tify' ), $index++ ),	
			'title'					=> '',
			'class'					=> '',
			'link_attrs'			=> array(),
			'base_uri'				=> set_url_scheme( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ),
			'current'				=> false,
			'hide_empty'			=> false,
			'count'					=> 0,	
			'add_query_args'		=> false,
			'remove_query_args'		=> false,						
			'count_query_args'		=> false,
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );
		
		if( $hide_empty && ! $count )
			return;
		
		// Traitement des arguments
		/// Traitement de l'url de requête
		$parsed_request_uri = parse_url( $_SERVER['REQUEST_URI'] );
		parse_str( $parsed_request_uri['query'], $request_query_args );
		
		/// Traitement de l'url de la vue
		$parsed_base_uri = parse_url( $base_uri );
		parse_str( $parsed_base_uri['query'], $base_query_args );
		
		/// Traitement des arguments de requête à ajouter
		if( ! empty( $add_query_args ) )
			$base_query_args = wp_parse_args( $add_query_args, $base_query_args );	
		
		/// Traitement des argument de requête à supprimer
		if( empty( $remove_query_args ) ) :
			$remove_query_args = array();
		elseif( $remove_query_args === true ) :
			$remove_query_args = array();
		elseif( is_string( $remove_query_args ) ) :
			$remove_query_args = array( $remove_query_args );
		endif;		
		array_push( $remove_query_args, 'action', 'action2', 'filter_action' );		
		foreach( $remove_query_args as $key ) :
			unset( $base_query_args[$key] );
		endforeach;
				
		/// Traitement du lien	
		$href = esc_url( add_query_arg( $base_query_args, $parsed_base_uri['scheme'] .'://'. $parsed_base_uri['host'] . $parsed_base_uri['path'] ) );
		
		/// Vérifie si le lien est actif				
		if( ! is_null( $current ) ) :
			if( ! empty( $add_query_args ) && is_array( $base_query_args ) && is_array( $request_query_args ) && ! @array_diff_assoc( $base_query_args, $request_query_args ) ) :
				$current = true;
			elseif( empty( $add_query_args ) && is_array( $request_query_args ) && ! @array_diff( $request_query_args, $base_query_args ) ) :
				$current = true;
			endif;	
		endif;
		
		/// Définition de l'intitulé
		if( is_array( $label ) && isset( $label['singular'] ) && isset( $label['plural'] )  ) :
			$text = _n( $label['singular'], $label['plural'], $count );
		else :
			$text = (string) $label; 
		endif;
		
		$output  = "";
		$output .= "<a href=\"{$href}\"";
		$output .= " class=\"". ( $current ? 'current' : '' ) ." {$class}\"";
		if( ! empty( $link_attrs ) ) :
			foreach( $link_attrs as $i => $j ) :
				$output .= " {$i}=\"{$j}\"";
			endforeach;
		endif;
		if( $title )
			$output .= " title=\"{$title}\"";
		$output .= ">{$text}";	
		$output .= " <span class=\"count\">({$count})</span>";
		$output .= "</a>";	
		
		return $output;
	}
	
	/* = ACTIONS SUR UN ELEMENT = */
	/** == Lien de déclenchement d'une action == **/
	public static function RowActionLink( $action, $args = array() )
	{
		$defaults = array(
			'label'					=> $action,	
			'title'					=> '',
			'class'					=> '',
			'link_attrs'			=> array(),
			'base_uri'				=> set_url_scheme( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ),
			'query_args'			=> array(),
			'nonce'					=> true,
			'referer'				=> true
		);
		$args = wp_parse_args( $args, $defaults );
		
		extract( $args );
	
		// Traitement des arguments
		/// Url de destination
		$href = add_query_arg( array_merge( $query_args, array( 'action' => $action ) ), $base_uri );
		
		if( $referer ) :
			if( is_bool( $referer ) )
				$referer = $base_uri;
			$href = add_query_arg( array( '_wp_http_referer' => urlencode( wp_unslash( $referer ) ) ), $href ); 
		endif;
		
		if( $nonce )
			$href = wp_nonce_url( $href, ( is_bool( $nonce ) ? -1 : $nonce ) );
		$href = esc_url( $href );
		
		$output  = "";
		$output .= "<a href=\"{$href}\"";
		if( $class )
			$output .= " class=\"{$class}\"";
		if( ! empty( $link_attrs ) ) :
			foreach( $link_attrs as $i => $j ) :
				$output .= " {$i}=\"{$j}\"";
			endforeach;
		endif;
		if( $title )
			$output .= " title=\"{$title}\"";
		$output .= ">{$label}</a>";	
		
		return $output;
	}
}