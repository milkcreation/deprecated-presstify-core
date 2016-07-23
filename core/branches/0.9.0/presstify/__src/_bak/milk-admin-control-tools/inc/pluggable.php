<?php
/**
 * --------------------------------------------------------------------------------
 *	Pluggable
 * --------------------------------------------------------------------------------
 * 
 * @name 		Admin Control Tools
 * @package    	Worpdress Extends Milkcreation Suite
 * @copyright 	Milkcreation 2012
 * @link 		http://wpextend.milkcreation.fr/admin-control-tools
 * @author 		Jordy Manner
 * @version 	1.1
 */ 

 
/**
 * URL
 */
if( ! function_exists( 'mkpack_get_current_page_url' ) ):
/**
 * Récupération de l'url courante 
 */
function mkpack_get_current_page_url() {
	$isHTTPS = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
	$port = (isset($_SERVER["SERVER_PORT"]) && ((!$isHTTPS && $_SERVER["SERVER_PORT"] != "80") || ($isHTTPS && $_SERVER["SERVER_PORT"] != "443")));
	$port = ($port) ? ':'.$_SERVER["SERVER_PORT"] : '';
	$url = ($isHTTPS ? 'https://' : 'http://').$_SERVER["SERVER_NAME"].$port.$_SERVER["REQUEST_URI"];
	
	return $url;	
}
endif;