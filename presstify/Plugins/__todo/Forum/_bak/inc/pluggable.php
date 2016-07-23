<?php
/**
 * --------------------------------------------------------------------------------
 *	Pluggable
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Plugins Pack Suite
 * @package    	Wordpress
 * @copyright 	Milkcreation 2011
 * @link 		http://wpextend.milkcreation.fr/milk-plugins-pack-suite
 * @author 		Jordy Manner
 * @version 	2.1
 */

/**
 * OPTIONS
 */
if( ! function_exists( 'mkpack_do_settings_sections' ) ):
	
/**
 * Affichage des sections d'options sous forme de tabulations
 */
function mkpack_do_settings_sections( $page ){
	global $wp_settings_sections, $wp_settings_fields;
	
	echo "\n<style type=\"text/css\">";	
	echo "\n\t.mknav-tabs .tabpanel{";
	echo "\n\t\tdisplay:none;";
	echo "\n\t}";
	echo "\n\t.mknav-tabs .tabpanel.active{";
	echo "\n\t\tdisplay:inherit;";
	echo "\n\t}";
	echo "</style>";
	
	echo "\n<div class=\"mknav-tabs\">";
	settings_fields( $page );
	echo "\n\t<h2 class=\"nav-tab-wrapper\">";
	$n = 0;
	foreach ( (array) $wp_settings_sections[$page] as $section ):			 
		$class = "";
		if( !isset( $_GET['tab-active'] ) && !$n )
			$class = "nav-tab-active";	
		elseif( isset( $_GET['tab-active'] ) &&  ( $_GET['tab-active'] == $n ) )
			$class = "nav-tab-active";	

		echo "\n\t<a class=\"nav-tab $class\" href=\"". add_query_arg( array('tab-active'=>$n), site_url( $_SERVER['REQUEST_URI'] ) ). "#{$section['id']}\">{$section['title']}</a></li>";
		$n++;
	endforeach;	
	echo "\n\t</h2>";
	
	reset( $wp_settings_sections[$page] ); 
	$n = 0;
	foreach ( (array) $wp_settings_sections[$page] as $section ):
		if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
					continue;
		$class = "";
		if( !isset( $_GET['tab-active'] ) && !$n )
			$class = "active";	
		elseif( isset( $_GET['tab-active'] ) &&  ( $_GET['tab-active'] == $n ) )
			$class = "active";
						
		echo "\n\t<div id=\"{$section['id']}\" class=\"tabpanel $class\">";
		call_user_func($section['callback'], $section);	
		echo "\n\t\t<table  class=\"form-table\">";			
		do_settings_fields($page, $section['id']);
		echo "\n\t\t</table>";
		echo "\n\t</div>";
		$n++;
	endforeach;
	echo "\n</div>";		 
}
endif;