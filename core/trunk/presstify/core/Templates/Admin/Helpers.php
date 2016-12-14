<?php
namespace tiFy\Core\Templates\Admin;

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