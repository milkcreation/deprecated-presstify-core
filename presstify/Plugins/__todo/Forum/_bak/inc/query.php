<?php
/**
 * -------------------------------------------------------------------------------
 *	Query
 * -------------------------------------------------------------------------------
 *
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 */

/**
 * Ajout d'une variable à la requête WP_Query
 * pour le filtrage par forum
 */
function mkforums_add_query_vars($aVars) {
  $aVars[] .= 'mkforum_id';
  $aVars[] .= 'mkforums_post_contrib';
  return $aVars;
}
add_filter('query_vars', 'mkforums_add_query_vars'); 