<?php
namespace tiFy\Core\Entity\Db;

/** == == **/
function tify_query( $table ){
	global $tiFy;
	
	if( ! isset( $tiFy->Query->registred[$table] ) )
		return;
	
	return $tiFy->Query->registred[$table];
}

/** == == **/
function tify_query_field( $name ){
	global $tiFy;
	
	// Bypass
	if( ! $current = $tiFy->Query->current )
		return;
	
	if( $field =  $tiFy->Query->registred[$current]->get_field( $name ) )
		return $field;
}

/** == == **/
function tify_query_meta( $meta_key, $single = true ){
	global $tify;
	
	// Bypass
	if( ! $current = $tiFy->Query->current )
		return;
	
	if( $field =  $tiFy->Query->registred[$current]->get_meta( $meta_key, $single ) )
		return $field;
}

/** == == **/
function tify_query_get_adjacent( $previous = true, $args = array() ){
	global $tiFy;
	
	// Bypass
	if( ! $current = $tiFy->Query->current )
		return;
	
	if( $adjacent = $tiFy->Query->registred[$current]->get_adjacent( $previous, $args ) )
		return $adjacent;
}

/** == == **/
function tify_query_reset_itemdata(){
	
}