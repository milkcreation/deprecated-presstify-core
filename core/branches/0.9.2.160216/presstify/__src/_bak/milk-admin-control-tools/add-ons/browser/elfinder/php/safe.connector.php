<?php
error_reporting(0); // Set E_ALL for debuging

if( ! function_exists( 'mkpack_search_target_dir' ) ):
/**
 * Récupére le chemin absolu vers une cible
 */
function mkpack_search_target_dir( &$directory ='', $target = 'gpress' ) {
	if( !$directory )
		$directory = dirname(__FILE__);
	foreach( glob( $directory . "/*" ) as $f ) :
		$location = ( is_dir($f) )? $f : dirname($f);
		if ( $target == basename( $f ) )			
			return  str_replace( "\\", "/", $location );
	endforeach;
	if( is_dir( dirname( $directory ) ) )
		return mkpack_search_target_dir( dirname( $directory ), $target );
}
endif;

if( ! function_exists( 'mkpack_search_target_url' ) ):
/**
 * Récupére le chemin absolu vers une cible
 */
function mkpack_search_target_url( &$directory ='', $target = 'gpress' ) {
	$dir = mkpack_search_target_dir( $directory, $target );
	$root = $_SERVER['DOCUMENT_ROOT'];
	$uri = preg_replace( "#^".preg_quote($root)."#", '', $dir  );
	$target_url = 'http';
	if ( isset( $_SERVER["HTTPS"] ) && ( $_SERVER["HTTPS"] == "on" ) ) 
		$target_url .= "s";
	$target_url .= "://";
	$target_url .= ( $_SERVER["SERVER_PORT"] != "80" ) ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$uri : $_SERVER["SERVER_NAME"].$uri;
	
	return $target_url;	 
}
endif;

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';


/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

$opts = array(
	//'debug' => true,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => mkpack_search_target_dir(),         // path to files (REQUIRED)
			'URL'           => mkpack_search_target_url(), // URL to files (REQUIRED)
			'accessControl' => 'access'             // disable and hide dot starting files (OPTIONAL)
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

