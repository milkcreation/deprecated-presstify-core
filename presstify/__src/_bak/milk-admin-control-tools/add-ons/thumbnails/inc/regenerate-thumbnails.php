<?php /*

**************************************************************************

Plugin Name:  Regenerate Thumbnails
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/regenerate-thumbnails/
Description:  Allows you to regenerate all thumbnails after changing the thumbnail sizes.
Version:      2.2.3
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Copyright (C) 2008-2011 Viper007Bond

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class RegenerateThumbnails {
	var $menu_id;

	function __construct() {
		add_action( 'admin_enqueue_scripts',                   array( &$this, 'admin_enqueues' ) );
		add_filter( 'media_row_actions',                       array( &$this, 'add_media_row_action' ), 10, 2 );
		add_action( 'admin_head-upload.php',                   array( &$this, 'add_bulk_actions_via_javascript' ) );
		add_action( 'admin_action_bulk_regenerate_thumbnails', array( &$this, 'bulk_action_handler' ) ); // Top drowndown
		add_action( 'admin_action_-1',                         array( &$this, 'bulk_action_handler' ) ); // Bottom dropdown (assumes top dropdown = default value)
	}

	// Enqueue the needed Javascript and CSS
	function admin_enqueues( $hook_suffix ) {
		if( ( isset( $_REQUEST['page']) && ( ( $_REQUEST['page'] == 'mkpack_tools' ) || ( $_REQUEST['page'] == 'mkact_tools' ) ) ) && ( isset( $_REQUEST['tab'] ) && ( $_REQUEST['tab'] == 'post-thumbnails' ) ) ) :
			wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'js/jquery.ui.progressbar.min.js', dirname( __FILE__ ) ), array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6' );
			wp_enqueue_style( 'jquery-ui-smoothness', plugins_url( 'css/smoothness/jquery-ui-1.8.22.custom.css', dirname( __FILE__ ) ), array(), '1.8.22' );
		endif;
	}

	// Add a "Regenerate Thumbnails" link to the media row actions
	function add_media_row_action( $actions, $post ) {
		if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) || ! current_user_can( regenerate_thumbs_cap() ) )
			return $actions;

		$url = wp_nonce_url( admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $post->ID ), 'regenerate-thumbnails' );
		$actions['regenerate_thumbnails'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( __( "Regenerate the thumbnails for this single image", 'mkact-addon-thb' ) ) . '">' . __( 'Regenerate Thumbnails', 'mkact-addon-thb' ) . '</a>';

		return $actions;
	}


	// Add "Regenerate Thumbnails" to the Bulk Actions media dropdown
	function add_bulk_actions( $actions ) {
		$delete = false;
		if ( ! empty( $actions['delete'] ) ) {
			$delete = $actions['delete'];
			unset( $actions['delete'] );
		}

		$actions['bulk_regenerate_thumbnails'] = __( 'Regenerate Thumbnails', 'mkact-addon-thb' );

		if ( $delete )
			$actions['delete'] = $delete;

		return $actions;
	}


	// Add new items to the Bulk Actions using Javascript
	// A last minute change to the "bulk_actions-xxxxx" filter in 3.1 made it not possible to add items using that
	function add_bulk_actions_via_javascript() {
		if ( ! current_user_can( regenerate_thumbs_cap() ) )
			return;
?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('select[name^="action"] option:last-child').before('<option value="bulk_regenerate_thumbnails"><?php echo esc_attr( __( 'Regenerate Thumbnails', 'mkact-addon-thb' ) ); ?></option>');
			});
		</script>
<?php
	}


	// Handles the bulk actions POST
	function bulk_action_handler() {
		if ( empty( $_REQUEST['action'] ) || ( 'bulk_regenerate_thumbnails' != $_REQUEST['action'] && 'bulk_regenerate_thumbnails' != $_REQUEST['action2'] ) )
			return;

		if ( empty( $_REQUEST['media'] ) || ! is_array( $_REQUEST['media'] ) )
			return;

		check_admin_referer( 'bulk-media' );

		$ids = implode( ',', array_map( 'intval', $_REQUEST['media'] ) );

		// Can't use wp_nonce_url() as it escapes HTML entities
		wp_redirect( add_query_arg( '_wpnonce', wp_create_nonce( 'regenerate-thumbnails' ), admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $ids ) ) );
		exit();
	}	
}

// Start up this plugin
New RegenerateThumbnails;

function regenerate_thumbs_cap(){
	return apply_filters( 'regenerate_thumbs_cap', 'manage_options' );
}
// The user interface plus thumbnail regenerator
	function regenerate_interface() {
	?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap regenthumbs">
	<h2><?php _e('Regenerate Thumbnails', 'mkact-addon-thb'); ?></h2>

<?php
		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( ! current_user_can( regenerate_thumbs_cap() ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Form nonce check
			check_admin_referer( 'regenerate-thumbnails' );
			
			// Create the list of image IDs
			if ( ! empty( $_REQUEST['ids'] ) ) {
				$images = array_map( 'intval', explode( ',', trim( $_REQUEST['ids'], ',' ) ) );
				$ids = implode( ',', $images );
			} else {
				$args = array(
					'post_type' => 'attachment',
					'post_status' => 'inherit',
					'post_mime_type' => 'image',
					'posts_per_page' => -1,
					'orderby' => 'ID',
					'order' => 'DESC'
				);
				$images_query = New WP_Query();
				$images = $images_query->query( $args );
				if( ! ( $images_query->post_count > 0 ) )
					return;
				// Generate the list of IDs
				$ids = array();
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );
			}
			
			echo '	<p>' . __( "Please be patient while the thumbnails are regenerated. This can take a while if your server is slow (inexpensive hosting) or if you have many images. Do not navigate away from this page until this script is done or the thumbnails will not be resized. You will be notified via this page when the regenerating is completed.", 'mkact-addon-thb' ) . '</p>';

			$count = $images_query->post_count;//count( $images );

			$text_goback = ( ! empty( $_GET['goback'] ) ) ? sprintf( __( 'To go back to the previous page, <a href="%s">click here</a>.', 'mkact-addon-thb' ), 'javascript:history.go(-1)' ) : '';
			$text_failures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s). To try regenerating the failed images again, <a href="%4$s">click here</a>. %5$s', 'mkact-addon-thb' ), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'tools.php?page=regenerate-thumbnails&goback=1' ), 'mkact-addon-thb' ) . '&ids=' ) . "' + rt_failedlist + '", $text_goback );
			$text_nofailures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were 0 failures. %3$s', 'mkact-addon-thb' ), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>


	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'mkact-addon-thb' ) ?></em></p></noscript>

	<div id="regenthumbs-bar" style="position:relative;height:25px;">
		<div id="regenthumbs-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="regenthumbs-stop" id="regenthumbs-stop" value="<?php _e( 'Abort Resizing Images', 'mkact-addon-thb' ) ?>" /></p>

	<h3 class="title"><?php _e( 'Debugging Information', 'mkact-addon-thb' ) ?></h3>

	<p>
		<?php printf( __( 'Total Images: %s', 'mkact-addon-thb' ), $count ); ?><br />
		<?php printf( __( 'Images Resized: %s', 'mkact-addon-thb' ), '<span id="regenthumbs-debug-successcount">0</span>' ); ?><br />
		<?php printf( __( 'Resize Failures: %s', 'mkact-addon-thb' ), '<span id="regenthumbs-debug-failurecount">0</span>' ); ?>
	</p>

	<ol id="regenthumbs-debuglist">
		<li style="display:none"></li>
	</ol>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?php echo $ids; ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$("#regenthumbs-bar").progressbar();
			$("#regenthumbs-bar-percent").html( "0%" );

			// Stop button
			$("#regenthumbs-stop").click(function() {
				rt_continue = false;
				$('#regenthumbs-stop').val("<?php echo esc_quotes( __( 'Stopping...', 'mkact-addon-thb' ) ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#regenthumbs-debuglist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function RegenThumbsUpdateStatus( id, success, response ) {
				$("#regenthumbs-bar").progressbar( "value", ( rt_count / rt_total ) * 100 );
				$("#regenthumbs-bar-percent").html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$("#regenthumbs-debug-successcount").html(rt_successes);
					$("#regenthumbs-debuglist").append("<li>" + response.success + "</li>");
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$("#regenthumbs-debug-failurecount").html(rt_errors);
					$("#regenthumbs-debuglist").append("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function RegenThumbsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$('#regenthumbs-stop').hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?php echo $text_failures; ?>';
				} else {
					rt_resulttext = '<?php echo $text_nofailures; ?>';
				}

				$("#message").html("<p><strong>" + rt_resulttext + "</strong></p>");
				$("#message").show();
			}

			// Regenerate a specified image via AJAX
			function RegenThumbs( id ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: "regeneratethumbnail", id: id },
					success: function( response ) {
						if ( response.success ) {
							RegenThumbsUpdateStatus( id, true, response );
						}
						else {
							RegenThumbsUpdateStatus( id, false, response );
						}

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					},
					error: function( response ) {
						RegenThumbsUpdateStatus( id, false, response );

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						} 
						else {
							RegenThumbsFinishUp();
						}
					}
				});
			}

			RegenThumbs( rt_images.shift() );
		});
	// ]]>
	</script>
<?php
		}

		// No button click? Display the form.
		else {
?>
	<form method="post" action="">
<?php wp_nonce_field('regenerate-thumbnails') ?>

	<p><?php printf( __( "Use this tool to regenerate thumbnails for all images that you have uploaded to your blog. This is useful if you've changed any of the thumbnail dimensions on the <a href='%s'>media settings page</a>. Old thumbnails will be kept to avoid any broken images due to hard-coded URLs.", 'mkact-addon-thb' ), admin_url( 'options-media.php' ) ); ?></p>

	<p><?php printf( __( "You can regenerate specific images (rather than all images) from the <a href='%s'>Media</a> page. Hover over an image's row and click the link to resize just that one image or use the checkboxes and the &quot;Bulk Actions&quot; dropdown to resize multiple images (WordPress 3.1+ only).", 'mkact-addon-thb' ), admin_url( 'upload.php' ) ); ?></p>

	<p><?php _e( "Thumbnail regeneration is not reversible, but you can just change your thumbnail dimensions back to the old values and click the button again if you don't like the results.", 'mkact-addon-thb' ); ?></p>

	<p><?php _e( 'To begin, just press the button below.', 'mkact-addon-thb' ); ?></p>

	<p><input type="submit" class="button hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php _e( 'Regenerate All Thumbnails', 'mkact-addon-thb' ) ?>" /></p>

	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'mkact-addon-thb' ) ?></em></p></noscript>

	</form>
<?php
		} // End if button
?>
</div>

<?php
	}


// Process a single image ID (this is an AJAX handler)
	function ajax_process_image() {
		@error_reporting( 0 ); // Don't break the JSON result

		header( 'Content-type: application/json' );

		$id = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( __( 'Failed resize: %s is an invalid image ID.', 'mkact-addon-thb' ), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( ! current_user_can( regenerate_thumbs_cap() ) )
			die_json_error_msg( $image->ID, __( "Your user account doesn't have permission to resize images", 'mkact-addon-thb' ) );

		$fullsizepath = get_attached_file( $image->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			die_json_error_msg( $image->ID, sprintf( __( 'The originally uploaded image file cannot be found at %s', 'mkact-addon-thb' ), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); // 5 minutes per image should be PLENTY

		$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

		if ( is_wp_error( $metadata ) )
			die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			die_json_error_msg( $image->ID, __( 'Unknown failure reason.', 'mkact-addon-thb' ) );

		// If this fails, then it just means that nothing was changed (old value == new value)
		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.', 'mkact-addon-thb' ), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}
	add_action( 'wp_ajax_regeneratethumbnail', 'ajax_process_image' );

	// Helper to make a JSON error message
	function die_json_error_msg( $id, $message ) {
		die( json_encode( array( 'error' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s', 'mkact-addon-thb' ), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
	}


	// Helper function to escape quotes in strings for use in Javascript
	function esc_quotes( $string ) {
		return str_replace( '"', '\"', $string );
	}
?>