<?php
/* = HELPER = */
/** == DECLARATION == **/
/*** === Déclaration d'un formulaire ===
 * Usage : 
	add_action( 'tify_contest_register', {function_hook_name} );
	function {function_hook_name}(){
		tify_contest_register( {$contest_id}, $params );
	}
	// (string) $contest_id : 32 chars max
 	// (array) 	$params 	: 	array(
	//								'form'				=> array(),			// (requis) Paramètres du formulaire de participation @see tify_form
	//								'participations'	=> array(
	//									'start'				=> false,		// Date de début du jeu concours
	//									'end'				=> false,		// Date de fin du jeu concours
	//									'max'				=> -1			// Nombre de participation maximum autorisée pour le jeu concours (-1 illimité)
	//									'user'				=> array(
	//										'roles'				=> array()	// (string|array) Liste des roles autorisés a participer (laisser vide pour tous)
	//										'max'				=> -1		// Nombre de participation maximum par utilisateur (-1 illimité)
	//									)
	//								'poll'				=> array(
	//									'start'				=> false, 		// Date de début d'ouverture au vote
	//									'end'				=> false		// Date de fin d'ouverture au vote
	//									'max'				=> -1			// Nombre de vote maximum autorisé pour le jeu concours (-1 illimité)
	//									'user'				=> array(
	//										'roles'				=> array()	// (string|array) Liste des roles autorisés a voter (laisser vide pour tous)
	//										'max'				=> -1		// Nombre de vote maximum par utilisateur (-1 illimité)
	//									)
	//								)			
 	//							);
***/
function tify_contest_register( $contest_id, $params = array() ){
	global $tify_contest;
	
	return $tify_contest->register( $contest_id, $params );
}

/** == RECUPERATION D'ELEMENTS == **/
/*** === Récupération des arguments d'un jeu concours === ***/
function tify_contest_get( $contest_id ){
	global $tify_contest;
	
	if( $tify_contest->is_registred( $contest_id ) )
		return $tify_contest->registred_contest[$contest_id];
}

/*** === Récupération des arguments d'un jeu concours === ***/
function tify_contest_get_extra( $contest_id, $extra = false ){
	global $tify_contest;
	
	if( ! $tify_contest->is_registred( $contest_id ) )
		return;
	
	if( ! $extra )
		return $tify_contest->registred_contest[$contest_id]['extras'];
	elseif( $extra && isset( $tify_contest->registred_contest[$contest_id]['extras'][$extra] ) )
		return $tify_contest->registred_contest[$contest_id]['extras'][$extra];
}

/*** === Récupération de la liste des jeux concours === ***/
function tify_contest_get_list( $args = array() ){
	global $tify_contest;
	
	return $tify_contest->get_list( $args );
}

/*** === Récupération d'une participation à un jeu concours === ***/
function tify_contest_get_part( $part_id ){
	global $tify_contest;
	
	return $tify_contest->db_participation->get_part( (int) $part_id );
}

/*** === Récupération d'une metadonnée de participation à un jeu concours === ***/
function tify_contest_get_part_meta( $part_id, $meta_key, $single = true ){
	global $tify_contest;
	
	return $tify_contest->db_participation->get_part_meta( (int) $part_id, $meta_key, $single );
}

/** == TEST DE VALIDITE == **/
/*** === Vérifie si un jeux concours est enregistré === ***/
function tify_contest_is_registered( $contest_id ){
	global $tify_contest;
	
	return $tify_contest->is_registered( $contest_id );
}

/*** === Vérifie si un jeux concours est ouvert === ***/
function tify_contest_is_participation_open( $contest_id ){
	global $tify_contest;
	
	return $tify_contest->capabilities->is_participation_open( $contest_id );
}

/*** === Vérifie si un jeux concours a été ouvert === ***/
function tify_contest_is_participation_opened( $contest_id ){
	global $tify_contest;
	
	return $tify_contest->capabilities->is_participation_opened( $contest_id );
}

/*** === Vérifie si un jeux concours a été fermé === ***/
function tify_contest_is_participation_closed( $contest_id ){
	global $tify_contest;
	
	return $tify_contest->capabilities->is_participation_closed( $contest_id );
}

/*** === === ***/
function tify_contest_has_winner( $contest_id ){
	global $tify_contest;
	
	return $tify_contest->capabilities->has_winner( $contest_id );
}

/*** === === ***/
function tify_contest_get_winners( $contest_id ){
	global $tify_contest;
	
	return $tify_contest->capabilities->get_winners( $contest_id );
}

/** == MESSAGES D'ERREURS == **/
/*** === Récupération du titre d'erreur === ***/
function tify_contest_error_title( $code = '' ){
	global $tify_contest;

	return $tify_contest->template->error_title();
}

/*** === Récupération du message d'erreur === ***/
function tify_contest_error_message( $code = '' ){
	global $tify_contest;

	return $tify_contest->template->error_message();
}