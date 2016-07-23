<?php
/**
 * --------------------------------------------------------------------------------
 *	Bootstrap
 * --------------------------------------------------------------------------------
 * 
 * @name 		Milkcreation Post Showcase
 * @package    	Worpdress Extend Milkcreation Pack
 * @copyright 	Milkcreation 2011
 * @link 		http://g-press.com/wemp/milk-zip-files
 * @author 		Jordy Manner
 * @version 	1.1

Plugin Name: Zip Files
Plugin URI: http://g-press.com/wemp/milk-zip-files
Description:  
version: 1.1
Author: Milkcreation
Author URI: http://milkcreation.fr/

*/
function Zip( $source, $destination ){
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }
	
    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace('\\', '/', realpath($source));

    if (is_dir($source) === true)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file)
        {
            $file = str_replace('\\', '/', realpath($file));

            if (is_dir($file) === true)
            {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            }
            else if (is_file($file) === true)
            {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
    }
    else if (is_file($source) === true)
    {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

function mkzf_add_menu(){
	add_options_page('Export du repertoire d\'upload', 'Export du repertoire d\'upload', 'manage_options', 'milk-zip-files', 'mkzf_render_page');
}
add_action('admin_menu', 'mkzf_add_menu');

function mkzf_render_page(){
?>		
	<div class="wrap nosubsub">
	<?php screen_icon(); ?>
	<h2><?php _e( 'Export du repertoire d\'upload', 'milk-zip-files' );?></h2>
	
	<div class="tool-box">
		<form id="posts-filter" action="" method="get">
			<?php wp_nonce_field( 'milk-zip-files_generate' );?>
			<p><a href="<?php echo wp_nonce_url( 'options-general.php?page=milk-zip-files&action=generate', 'milk-zip-files_generate' );?>" class="button-secondary"><?php _e( 'Générer le zip', 'milk-zip-files' );?></a></p>
		<?php if( file_exists( WP_CONTENT_DIR.'/zipped-upload_dir.zip' ) ) : ?>
			<p><a href="<?php echo wp_nonce_url( 'options-general.php?page=milk-zip-files&action=upload', 'milk-zip-files_upload' );?>" class="button-secondary"><?php _e( 'Télécharger le zip', 'milk-zip-files' );?></a></p>
		<?php endif; ?>
		</form>
	</div>
	</div>
<?php	
}

function mkzf_handle_actions(){
	// Bypass	
	if( !( isset( $_REQUEST['action'] ) ) || !( isset( $_REQUEST['page'] ) ) || ( $_REQUEST['page']!= 'milk-zip-files' )  )
		return;
	
	$action = $_REQUEST['action'];
	
	switch( $action ) :
		case 'generate' :
			check_admin_referer( 'milk-zip-files_generate' );
			
			Zip( WP_CONTENT_DIR.'/uploads', WP_CONTENT_DIR.'/zipped-upload_dir.zip' );
			break;
		case 'upload' :
			check_admin_referer( 'milk-zip-files_upload' );

			$url = WP_CONTENT_URL.'/zipped-upload_dir.zip';			
			$filename = 'zipped-upload_dir.zip';			
				
			clearstatcache();		
			nocache_headers();
			
			ob_start();		
			ob_end_clean();	
			
			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header("Pragma: no-cache");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
			header("Expires: 0"); 
			header("Content-Description: File Transfer");
			header("Accept-Ranges: bytes"); 
			header("Content-Type: application/force-download");
			header('Content-Disposition: attachment; filename="' .$filename. '"');
			header("Content-Transfer-Encoding: application/octet-stream\n");
			@set_time_limit(0);
			$fp = @fopen($url, 'rb');		
			if ($fp !== false) {
				while (!feof($fp)) {
					echo fread($fp, 8192);
				}
				fclose($fp);
			} else {
				@readfile($url);
			}
			ob_flush();
			
			exit;

			break;	
	endswitch;	
}
add_action( 'admin_init', 'mkzf_handle_actions' );
