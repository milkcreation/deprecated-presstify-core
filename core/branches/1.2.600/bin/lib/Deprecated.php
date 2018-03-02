<?php


/**
 * FONCTIONS PLUGGABLE (Fonction modifiable par le thème ou les plugins)
 *
 * @package WordPress
 */

/**
 * CARACTERES
 */

if ( ! function_exists( 'tify_excerpt' ) ) :
/**
 * Création d'un extrait de texte basé sur les nombre de caractères
*
* @param (string) 	$string 	- chaîne de caractère à traiter
* @param (int) 	$max 		- nombre maximum de caractères de la chaîne
* @param (string) 	$teaser 	- délimiteur de fin de chaîne réduite (ex : [...])
* @param (string) 	$use_tag 	- Détection d'une balise d'arrêt du type <!--more-->
* @param (bool) 	$uncut		- préservation de la découpe de mots en fin de chaîne
*
* @return string
*/
function tify_excerpt( $string, $args = array() ){
	$defaults = array(
			'max' 			=> 255,
			'teaser' 		=> '...',
			'use_tag' 		=> true,
			'uncut' 		=> true
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$max = abs( $max );

	if ( $use_tag && preg_match('/<!--more(.*?)?-->/', $string, $matches) ) :
	$strings = preg_split( '/<!--more(.*?)?-->/', $string );
	$teased = str_replace(']]>', ']]&gt;', $strings[0]);
	$teased = strip_tags( $teased );
	$teased = trim( $teased );
	if( $max > strlen( $teased ) ) :
	return $teased . $teaser;
	endif;
	else :
	$string = str_replace(']]>', ']]&gt;', $string);
	$string = strip_tags( $string );
	$string = trim( $string );
	if( $max > strlen( $string ) ) return $string;
	endif;

	if( $uncut ):
	$string = substr( $string, 0, $max );
	$pos = strrpos( $string, " ");
	if( $pos === false )
		return substr( $string, 0, $max ) . $teaser;

		return substr( $string, 0, $pos ) . $teaser;
		else:
		return substr( $string, 0, $max ) . $teaser;
		endif;
}
endif;

if( ! function_exists( 'mk_multisort' ) ) :
/**
 * Trie selon une valeur d'un tableau
*/
function mk_multisort( $array, $orderby = 'order' ){
	$orderly = array();
	foreach ( (array) $array as $key => $params ) :
		$orderly[$key] = $params[$orderby];
	endforeach;

	if( ! $orderly )
		return;

	@array_multisort( $orderly , $array );

	return $array;
}
endif;

/**
 * FILES
 */
/**
 * Récupére l'url absolue d'une cible depuis un chemin
 */
function mktzr_get_url_from_path( $path = '' ) {
	$root = $_SERVER['DOCUMENT_ROOT'];
	$uri = preg_replace( "#^".preg_quote($root)."#", '', $path );
	$res = 'http';
	if ( isset( $_SERVER["HTTPS"] ) && ( $_SERVER["HTTPS"] == "on" ) )
		$res .= "s";
		$res .= "://";
		$res .= ( $_SERVER["SERVER_PORT"] != "80" ) ? $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$uri : $_SERVER["SERVER_NAME"].$uri;

		return $res;
}

/**
 * FILES
 */

if( ! function_exists( 'tify_get_relative_url' ) ) :
/**
 * Récupération du chemin relatif d'un fichier ou dossier de l'arborescence du site
*/
function tify_get_relative_url( $url, $original_url = null ){
	if( ! $original_url )
		$original_url = site_url();
		if( ( $path = preg_replace( '/'. preg_quote( $original_url, '/' ) .'/', '', $url ) ) && file_exists( ABSPATH .'/'. $path ) )
			return $path;
}
endif;

if( ! function_exists( 'tify_file_get_contents_curl' ) ) :
/**
 * Récupération des données d'un fichier distant (curl requis)
*/
function tify_file_get_contents_curl($url) 
{
	_deprecated_function( __FUNCTION__, '0.9.9.161008', 'tiFy\Lib\File::getContent( $url )' );	
	
	return tiFy\Lib\File::getContents( $url );
}
endif;

if( ! function_exists( 'mk_handle_upload' ) ) :
/**
 * Processus de chargement de fichier dans la médiathèque Wordpress
*
* $path Chemin absolu vers le fichier
*/
function mk_handle_upload( $path, $args = array( ) ){
	$pathinfo = pathinfo($path);
	extract( $pathinfo );

	// Traitement des arguments
	$defaults = array(
			'post_parent' 	=> 0,
			'remove' 		=> false, 	// Suppression du fichier d'originie
			'title' 		=> '',
			'content' 		=> '',
			'alt' 			=> '',
			'upload_subdir'	=> '',		// Chemin de dépôt du fichier de destination
			'dest_basename'	=> ''		// Nom du fichier de destination
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	// Traitement du fichier de destination dans le repertoire d'upload WP
	if( $upload_subdir )
		$upload_dir = array( 'path' => ABSPATH .'/' .untrailingslashit( $upload_subdir ), 'url' => site_url( '/' .untrailingslashit( $upload_subdir ) ) );
		else
			$upload_dir = wp_upload_dir( );

			if( ! file_exists( $upload_dir['path'] ) )
				mkdir( $upload_dir['path'], 0777, true );

				if( ! $dest_basename )
					$dest_basename = $basename;

					if( ! $exists = mk_media_file_exists( sanitize_file_name( $dest_basename ) ) )
						$dest_basename = wp_unique_filename( $upload_dir['path'], $dest_basename );

						// Déplacement du fichier d'origine vers le repertoire d'upload de Wordpress
						$ori_file = $dirname . "/$basename";
						$dest_file = $upload_dir['path'] . "/$dest_basename";
						/// Définition des permissions de fichier.
						$stat = stat( dirname( $dest_file ) );
						$perms = $stat['mode'] & 0000666;
						@ chmod( $dest_file, $perms );
						/// Dépôt du fichier dans le repertoire de destination
						if( $remove )
							$move_new_file = @ rename( $ori_file, $dest_file );
							else
								$move_new_file = @ copy( $ori_file, $dest_file );

								if( ! file_exists( $dest_file ) )
									return;

									// Suppression de la limitation pour le multisite
									if( is_multisite() ) delete_transient( 'dirsize_cache' );

									// Traitement des données en base du fichier media de destination
									/// Url de destination
									$url = $upload_dir['url'] . "/$dest_basename";
									/// Mime type
									$type_ext = wp_check_filetype_and_ext( $ori_file, $dest_basename );
									$mime_type = $type_ext['type'];
									//
									$name = trim( substr( $dest_basename, 0, -( 1 + strlen( $extension ) ) ) );
									// Titre et contenu
									$image_meta = @wp_read_image_metadata( $file );
									if( ! $title )
										if( $image_meta && trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
											$title = $image_meta['title'];
											else
												$title = $name;
												if( ! $content )
													if( $image_meta && trim( $image_meta['caption'] ) )
														$content = $image_meta['caption'];

														$postarr = array(
																'post_mime_type' 	=> $mime_type,
																'guid' 				=> $url,
																'post_title' 		=> $title,
																'post_content' 		=> $content
														);
														if( $exists ) $postarr['ID'] = $exists;

														// Insertion des données
														$post_id = wp_insert_attachment( $postarr, $dest_file, $post_parent );
														if ( ! is_wp_error( $post_id ) ) :
														wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $dest_file ) );
														if( $alt && ( wp_ext2type( $type_ext['ext'] ) == 'image' ) )
															update_post_meta( $post_id, '_wp_attachment_image_alt', $alt );
															endif;

															return $post_id;
}
endif;

if( ! function_exists( 'mk_media_file_exists' ) ) :
/**
 * Vérification l'existance d'un fichier dans la base média
*/
function mk_media_file_exists( $filename, $query_args = array( ) ){
	global $wpdb;
	$post_id = 0;

	$where =  "WHERE 1 = 1 AND post_type = 'attachment' AND guid LIKE '%$filename'";

	foreach( $query_args as $key => $value )
		$where .= " AND $key = '$value'";

		$post_id = $wpdb->get_var( "SELECT $wpdb->posts.ID FROM $wpdb->posts $where;" );

		return (int) $post_id;
}
endif;

if( ! function_exists( 'mk_unique_file_title' ) ) :
/**
 * Création d'un intitulé unique pour les fichiers média
*/
function mk_unique_file_title( $title, $basename = null,  $query_args = array( ) ){
	global $wpdb;

	$where =  "WHERE 1 AND post_type = 'attachment'";

	if( $basename ) :
		$where .= " AND guid NOT LIKE '%{$basename}'";
	endif;
	
	foreach( $query_args as $key => $value ) :
		$where .= " AND {$key} = '{$value}'";
	endforeach;
	
	$number = 0;
	while( $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} {$where} AND post_title = '{$title}'" ) ) :
		if ( ! preg_match( "/#". $number ."$/", $title ) ) :
			$title .= "#". ++$number;
		else :
			$title = preg_replace( "/#". $number ."$/", "#". ++$number, $title ) .'tutu';
		endif;
	endwhile;
	
	return $title;
}
endif;

/**
 * DATES
 */
if( ! function_exists( 'mk_touch_time' ) ) :
/**
 * Champs de formulaire de saisie de date
* @param array $args Argument de la fonction
*/
function mk_touch_time( $args = array() ){
	global $wp_locale, $post;

	$defaults = array(
			'period' => 'start',
			'selected' => false,
			'allday_hide' => false,
			'allday_checkbox' => false,
			'time' => false,
			'echo' => true,
			'name' => '_touch_time',
			'prefix' => 'touch_time'
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );

	$time_adj = current_time('timestamp');

	if( $period == 'start' )
		$time_adj = mktime( 0, 0 , 0, date('m'), date('d'), date('Y') );
		elseif( $period == 'end' )
		$time_adj = mktime( 23, 59 , 59, date('m'), date('d'), date('Y') );

		$event_date	= ( $selected )? $selected : gmdate( 'Y-m-d H:i:s', $time_adj );

		$jj = ( ( $d = substr( $event_date, 8, 2 ) ) && is_numeric( $d ) ) ? $d : 0;
		$mm = ( ( $m = substr( $event_date, 5, 2 ) ) && is_numeric( $m ) ) ? $m : 0;
		$aa = ( ( $y = substr( $event_date, 0, 4 ) ) && is_numeric( $y ) ) ? $y : 0;
		$hh = ( ( $h = substr( $event_date, 11, 2 ) ) && is_numeric( $h ) ) ? $h : 0;
		$mn = ( ( $i = substr( $event_date, 14, 2 ) ) && is_numeric( $i ) ) ? $i : 0;
		$ss = ( ( $s = substr( $event_date, 17, 2 ) ) && is_numeric( $s ) ) ? $s : 0;

		$month = "<select id=\"$prefix-$period-mm\" name=\"".$name."[mm]\" autocomplete=\"off\">\n";
		if( zeroise( $mm, 2 ) == '00' ) :
		$month .= "\t\t\t<option value=\"" . zeroise( $mm, 2 ) . "\" ";
		$month .= selected( true, true, false );
		$month .= ">--</option>\n";
		endif;
		for ( $i = 1; $i < 13; $i = $i +1 ) {
			$month .= "\t\t\t<option value=\"" . zeroise( $i, 2 ) . "\" ";
			$month .= selected( $i == $mm  , true, false);
			$month .= ">" . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
		}
		$month .= "</select>";

		$day 	= "<input type=\"text\" id=\"$prefix-$period-jj\" name=\"".$name."[jj]\" value=\"". zeroise( $jj, 2 ) ."\" size=\"2\" maxlength=\"2\" autocomplete=\"off\" />";
		$year 	= "<input type=\"text\" id=\"$prefix-$period-aa\" name=\"".$name."[aa]\" value=\"". zeroise( $aa, 4 ) ."\" size=\"4\" maxlength=\"4\" autocomplete=\"off\" />";
		$time_type = ( $time )? 'text' : 'hidden';
		$hour 	= "<input type=\"$time_type\" id=\"$prefix-$period-hh\" name=\"".$name."[hh]\" value=\"". zeroise( $hh, 2 ) ."\" size=\"2\" maxlength=\"2\" autocomplete=\"off\" />";
		$minute = "<input type=\"$time_type\" id=\"$prefix-$period-mn\" name=\"".$name."[mn]\" value=\"". zeroise( $mn, 2 ) ."\" size=\"2\" maxlength=\"2\" autocomplete=\"off\" />";

		$timestampwrap 	= "\n<div class=\"timestamp-wrap\">";
		$timestampwrap .= sprintf( __( '%2$s %1$s %3$s', 'tify' ), $month, $day, $year );
		$timestampwrap .= "\n<span class=\"mkmsgr-time\"";
		if( !$time )
			$timestampwrap .= " style=\"display:none;\"";
			$timestampwrap .= ">";
			$timestampwrap .= sprintf( __( 'à %1$s : %2$s', 'tify' ), $hour, $minute );
			$timestampwrap .= "</span>";
			$timestampwrap .= "\n</div>";
			$timestampwrap .= "\n<input type=\"hidden\" id=\"$prefix-$period-ss\" name=\"".$name."[ss]\" value=\"". zeroise( $ss, 2 ) ."\" />";

			if( $echo )
				echo $timestampwrap;
				else
					return $timestampwrap;
}
endif;

if( ! function_exists( 'mk_translate_touchtime' ) ) :
/**
 * Translation du tableau date au format sql
*/
function mk_translate_touchtime( $datetime ){
	foreach ( array('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit )
		if ( ! isset( $datetime[$timeunit] ) )
			return false;

			$aa = $datetime['aa'];
			$mm = $datetime['mm'];
			$jj = $datetime['jj'];
			$hh = $datetime['hh'];
			$mn = $datetime['mn'];
			$ss = $datetime['ss'];
			$aa = ($aa <= 0 ) ? date('Y') : $aa;
			$mm = ($mm <= 0 ) ? date('n') : $mm;
			$jj = ($jj > 31 ) ? 31 : $jj;
			$jj = ($jj <= 0 ) ? date('j') : $jj;
			$hh = ($hh > 23 ) ? $hh -24 : $hh;
			$mn = ($mn > 59 ) ? $mn -60 : $mn;
			$ss = ($ss > 59 ) ? $ss -60 : $ss;
			$datetime = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss );
			if( wp_checkdate( $mm, $jj, $aa, $datetime  ) )
				return $datetime;
}
endif;

/**
 * PHP
 */
if( !function_exists( 'hex2RGB' ) ) :
/**
 * Convert a hexa decimal color code to its RGB equivalent
*
* @param string $hexStr (hexadecimal color value)
* @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
* @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
* @return array or string (depending on second parameter. Returns False if invalid hex color value)
*/
function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
	$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
	$rgbArray = array();
	if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
		$colorVal = hexdec($hexStr);
		$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
		$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
		$rgbArray['blue'] = 0xFF & $colorVal;
	} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
		$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
		$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
		$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
	} else {
		return false; //Invalid hex color code
	}
	return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}
endif;

/**
 * EMAILING
 */
if( !function_exists( 'mk_email_boilerplate' ) ) :
/**
 * Formatage HTML de l'email
*
* @see http://htmlemailboilerplate.com/
*/
function mk_email_boilerplate( $object, $message ){
	/*<!-- ***************************************************
	 ********************************************************

	 HOW TO USE: Use these code examples as a guideline for formatting your HTML email. You may want to create your own template based on these snippets or just pick and choose the ones that fix your specific rendering issue(s). There are two main areas in the template: 1. The header (head) area of the document. You will find global styles, where indicated, to move inline. 2. The body section contains more specific fixes and guidance to use where needed in your design.

	 DO NOT COPY OVER COMMENTS AND INSTRUCTIONS WITH THE CODE to your message or risk spam box banishment :).

	 It is important to note that sometimes the styles in the header area should not be or don't need to be brought inline. Those instances will be marked accordingly in the comments.

	 ********************************************************
	 **************************************************** -->

	 <!-- Using the xHTML doctype is a good practice when sending HTML email. While not the only doctype you can use, it seems to have the least inconsistencies. For more information on which one may work best for you, check out the resources below.

	 UPDATED: Now using xHTML strict based on the fact that gmail and hotmail uses it.  Find out more about that, and another great boilerplate, here: http://www.emailology.org/#1

	 More info/Reference on doctypes in email:
	 Campaign Monitor - http://www.campaignmonitor.com/blog/post/3317/correct-doctype-to-use-in-html-email/
	 Email on Acid - http://www.emailonacid.com/blog/details/C18/doctype_-_the_black_sheep_of_html_email_design
	 -->*/
	$output  = "";
	$output .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">";
	$output .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\">";
	$output .= "\n<head>";
	$output .= "\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
	$output .= "\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"/>";
	$output .= "\n\t<title>{$object}</title>";
	$output .= "\n\t<style type=\"text/css\">";

	/***********
	 Originally based on The MailChimp Reset from Fabio Carneiro, MailChimp User Experience Design
	 More info and templates on Github: https://github.com/mailchimp/Email-Blueprints
	 http://www.mailchimp.com &amp; http://www.fabio-carneiro.com

	 INLINE: Yes.
	 ***********/
	/* Client-specific Styles */
	$output .= "\n\t\t#outlook a {padding:0;}";/* Force Outlook to provide a "view in browser" menu link. */
	$output .= "\n\t\tbody{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}";
	/* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
	$output .= "\n\t\t.ExternalClass {width:100%;}";/* Force Hotmail to display emails at full width */
	$output .= "\n\t\t.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}";/* Force Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */
	$output .= "\n\t\t#backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}";
	/* End reset */

	/* Some sensible defaults for images
	 1. "-ms-interpolation-mode: bicubic" works to help ie properly resize images in IE. (if you are resizing them using the width and height attributes)
	 2. "border:none" removes border when linking images.
	 3. Updated the common Gmail/Hotmail image display fix: Gmail and Hotmail unwantedly adds in an extra space below images when using non IE browsers. You may not always want all of your images to be block elements. Apply the "image_fix" class to any image you need to fix.

	 Bring inline: Yes.
	 */
	$output .= "\n\t\timg {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}";
	$output .= "\n\t\ta img {border:none;}";
	$output .= "\n\t\t.image_fix {display:block;}";

	/** Yahoo paragraph fix: removes the proper spacing or the paragraph (p) tag. To correct we set the top/bottom margin to 1em in the head of the document. Simple fix with little effect on other styling. NOTE: It is also common to use two breaks instead of the paragraph tag but I think this way is cleaner and more semantic. NOTE: This example recommends 1em. More info on setting web defaults: http://www.w3.org/TR/CSS21/sample.html or http://meiert.com/en/blog/20070922/user-agent-style-sheets/

	Bring inline: Yes.
	**/
	$output .= "\n\t\tp {margin: 1em 0;}";

	/** Hotmail header color reset: Hotmail replaces your header color styles with a green color on H2, H3, H4, H5, and H6 tags. In this example, the color is reset to black for a non-linked header, blue for a linked header, red for an active header (limited support), and purple for a visited header (limited support).  Replace with your choice of color. The !important is really what is overriding Hotmail's styling. Hotmail also sets the H1 and H2 tags to the same size.

	Bring inline: Yes.
	**/
	$output .= "\n\t\th1, h2, h3, h4, h5, h6 {color: black !important; line-height: 100%; }";
	$output .= "\n\t\th1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}";
	$output .= "\n\t\th1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {";
	$output .= "\n\t\t\tcolor: red !important;";/* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
	$output .= "\n\t\t}";
	$output .= "\n\t\th1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {";
	$output .= "\n\t\t\tcolor: purple !important;";/* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
	$output .= "\n\t\t}";

	/** Outlook 07, 10 Padding issue: These "newer" versions of Outlook add some padding around table cells potentially throwing off your perfectly pixeled table.  The issue can cause added space and also throw off borders completely.  Use this fix in your header or inline to safely fix your table woes.

	More info: http://www.ianhoar.com/2008/04/29/outlook-2007-borders-and-1px-padding-on-table-cells/
	http://www.campaignmonitor.com/blog/post/3392/1px-borders-padding-on-table-cells-in-outlook-07/

	H/T @edmelly

	Bring inline: No.
	**/
	$output .= "\n\t\ttable td {border-collapse: collapse;}";

	/* Styling your links has become much simpler with the new Yahoo.  In fact, it falls in line with the main credo of styling in email, bring your styles inline.  Your link colors will be uniform across clients when brought inline.

	Bring inline: Yes. */
	$output .= "\n\t\ta:link { color: blue; }";
	$output .= "\n\t\ta:visited { color: purple; }";
	$output .= "\n\t\ta:hover { color: red; }";

	/* Or to go the gold star route...
		a:link { color: orange; }
		a:visited { color: blue; }
		a:hover { color: green; }
		*/

	/***************************************************
	 ****************************************************
	 MOBILE TARGETING

	 Use @media queries with care.  You should not bring these styles inline -- so it's recommended to apply them AFTER you bring the other stlying inline.

	 Note: test carefully with Yahoo.
	 Note 2: Don't bring anything below this line inline.
	 ****************************************************
	 ***************************************************/

	/* NOTE: To properly use @media queries and play nice with yahoo mail, use attribute selectors in place of class, id declarations.
	 table[class=classname]
	 Read more: http://www.campaignmonitor.com/blog/post/3457/media-query-issues-in-yahoo-mail-mobile-email/
	 */
	$output .= "\n\t\t@media only screen and (max-device-width: 480px) {";

	/* A nice and clean way to target phone numbers you want clickable and avoid a mobile phone from linking other numbers that look like, but are not phone numbers.  Use these two blocks of code to "unstyle" any numbers that may be linked.  The second block gives you a class to apply with a span tag to the numbers you would like linked and styled.

	Inspired by Campaign Monitor's article on using phone numbers in email: http://www.campaignmonitor.com/blog/post/3571/using-phone-numbers-in-html-email/.

	Step 1 (Step 2: line 224)
	*/
	$output .= "\n\t\t\ta[href^=\"tel\"], a[href^=\"sms\"] {";
	$output .= "\n\t\t\t\ttext-decoration: none;";
	$output .= "\n\t\t\t\tcolor: black;";
	$output .= "\n\t\t\t\tpointer-events: none;";
	$output .= "\n\t\t\t\tcursor: default;";
	$output .= "\n\t\t\t}";
	$output .= "\n\t\t\t.mobile_link a[href^=\"tel\"], .mobile_link a[href^=\"sms\"] {";
	$output .= "\n\t\t\t\ttext-decoration: default;";
	$output .= "\n\t\t\t\tcolor: orange !important;";
	$output .= "\n\t\t\t\tpointer-events: auto;";
	$output .= "\n\t\t\t\tcursor: default;";
	$output .= "\n\t\t\t}";
	$output .= "\n\t\t}";

	/* More Specific Targeting */

	$output .= "\n\t\t@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {";
	/* You guessed it, ipad (tablets, smaller screens, etc) */

	/* Step 1a: Repeating for the iPad */
	$output .= "\n\t\t\ta[href^=\"tel\"], a[href^=\"sms\"] {";
	$output .= "\n\t\t\t\ttext-decoration: none;";
	$output .= "\n\t\t\t\tcolor: blue;";
	$output .= "\n\t\t\t\tpointer-events: none;";
	$output .= "\n\t\t\t\tcursor: default;";
	$output .= "\n\t\t\t}";
	$output .= "\n\t\t\t.mobile_link a[href^=\"tel\"], .mobile_link a[href^=\"sms\"] {";
	$output .= "\n\t\t\t\ttext-decoration: default;";
	$output .= "\n\t\t\t\tcolor: orange !important;";
	$output .= "\n\t\t\t\tpointer-events: auto;";
	$output .= "\n\t\t\t\tcursor: default;";
	$output .= "\n\t\t\t}";
	$output .= "\n\t\t}";

	/* Put your iPhone 4g styles in here */
	$output .= "\n\t\t@media only screen and (-webkit-min-device-pixel-ratio: 2) {";
	$output .= "\n\t\t\t";
	$output .= "\n\t\t}";

	/* Following Android targeting from:
	 http://developer.android.com/guide/webapps/targeting.html
	 http://pugetworks.com/2011/04/css-media-queries-for-targeting-different-mobile-devices/  */
	/* Put CSS for low density (ldpi) Android layouts in here */
	$output .= "\n\t\t@media only screen and (-webkit-device-pixel-ratio:.75){";
	$output .= "\n\t\t\t";
	$output .= "\n\t\t}";

	/* Put CSS for medium density (mdpi) Android layouts in here */
	$output .= "\n\t\t@media only screen and (-webkit-device-pixel-ratio:1){";
	$output .= "\n\t\t\t";
	$output .= "\n\t\t}";

	/* Put CSS for high density (hdpi) Android layouts in here */
	$output .= "\n\t\t@media only screen and (-webkit-device-pixel-ratio:1.5){";
	$output .= "\n\t\t\t";
	$output .= "\n\t\t}";

	/* end Android targeting */
	$output .= "\n\t</style>";

	/* Targeting Windows Mobile */
	$output .= "\n\t<!--[if IEMobile 7]>";
	$output .= "\n\t\t<style type=\"text/css\">";
	$output .= "\n\t\t\t";
	$output .= "\n\t\t</style>";
	$output .= "\n\t<![endif]-->";

	/***************************************************
	 ****************************************************
	 END MOBILE TARGETING
	 ****************************************************
	 ****************************************************/

	/* Target Outlook 2007 and 2010 */
	$output .= "\n\t<!--[if gte mso 9]>";
	$output .= "\n\t\t<style>";
	$output .= "\n\t\t\t";
	$output .= "\n\t\t</style>";
	$output .= "\n\t<![endif]-->";
	$output .= "\n</head>";

	$output .= "\n<body>";

	/* Wrapper/Container Table: Use a wrapper table to control the width and the background color consistently of your email. Use this approach instead of setting attributes on the body tag. */
	$output .= "\n\t<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"backgroundTable\">";
	$output .= "\n\t\t<tr>";
	$output .= "\n\t\t\t<td>";

	/* Tables are the most common way to format your email consistently. Set your table widths inside cells and in most cases reset cellpadding, cellspacing, and border to zero. Use nested tables as a way to space effectively in your message.*/
	$output .= $message;
	/* End example table */

	/* Yahoo Link color fix updated: Simply bring your link styling inline. */
	//$output .= "\n\t\t\t\t<a href=\"http://htmlemailboilerplate.com\" target =\"_blank\" title=\"Styling Links\" style=\"color: orange; text-decoration: none;\">Coloring Links appropriately</a>";

	/* Gmail/Hotmail image display fix: Gmail and Hotmail unwantedly adds in an extra space below images when using non IE browsers.  This can be especially painful when you putting images on top of each other or putting back together an image you spliced for formatting reasons.  Either way, you can add the 'image_fix' class to remove that space below the image.  Make sure to set alignment (don't use float) on your images if you are placing them inline with text.*/
	//$output .= "\n\t\t\t\t<img class=\"image_fix\" src=\"full path to image\" alt=\"Your alt text\" title=\"Your title text\" width=\"x\" height=\"x\" />";

	/* Step 2: Working with telephone numbers (including sms prompts).  Use the "mobile-link" class with a span tag to control what number links and what doesn't in mobile clients. */
	//$output .= "\n\t\t\t\t<span class=\"mobile_link\">123-456-7890</span>";

	$output .= "\n\t\t\t</td>";
	$output .= "\n\t\t</tr>";
	$output .= "\n\t</table>";
	/* End of wrapper table */
	 
	$output .= "\n</body>";
	$output .= "\n</html>";

	return $output;
}
endif;

/**
 * WORDPRESS ENHANCED
 */

/**
 * Suppression d'un type de post (personnalisé ou non)
 * Usage for a custom post type named 'movies':
 * unregister_post_type( 'movies' );
 *
 * Usage for the built in 'post' post type:
 * unregister_post_type( 'post', 'edit.php' );
 */
if ( ! function_exists( 'unregister_post_type' ) ) :
function unregister_post_type( $post_type, $slug = '' ){
	global $wp_post_types;

	if ( isset( $wp_post_types[ $post_type ] ) ) :
	unset( $wp_post_types[ $post_type ] );
	$slug = ( !$slug ) ? 'edit.php?post_type=' . $post_type : $slug;
	remove_menu_page( $slug );
	endif;
}
endif;

/**
 * Verification d'un taxonomie pour un type de post
 */
if ( !function_exists( 'has_taxonomy_for_object_type' ) ) {
	function has_taxonomy_for_object_type( $taxonomy, $object_type) {
		global $wp_taxonomies;

		if ( !isset($wp_taxonomies[$taxonomy]) )
			return false;

			if ( ! get_post_type_object($object_type) )
				return false;
					
				return ( array_search($object_type, $wp_taxonomies[$taxonomy]->object_type) !== false );
	}
}

/**
 * Load a template part into a template
 *
 */
if ( !function_exists('has_template_part') ) :
function has_template_part( $slug, $name = null ) {
	do_action( "has_template_part_{$slug}", $slug, $name );

	$templates = array();
	$name = (string) $name;
	if ( '' !== $name )
		$templates[] = "{$slug}-{$name}.php";

		$templates[] = "{$slug}.php";

		return locate_template($templates, false, false);
}
endif;

/**
 * Récupération du rôle d'un utilisateur
 */
if( ! function_exists( 'get_user_role' ) ) :
function get_user_role( $user = null ) {
	if( is_object( $user ) ) :
	elseif( is_int( $user ) ) :
	$user = new WP_User( $user );
	elseif( is_user_logged_in() ) :
	global $current_user;
	$user = new WP_User( $current_user );
	else :
	return;
	endif;

	if( ! $user )
		return;
		if( !$user->ID )
			return;
			if( ! $user_roles = $user->roles )
				return;

				$user_role = array_shift( $user_roles );
				return $user_role;
};
endif;

/**
 * Vérification du rôle d'un utilisateur
 */
if( ! function_exists( 'tify_check_user_role' ) ) :
function tify_check_user_role( $role, $user_id = null ) {

	if ( is_numeric( $user_id ) )
		$user = get_userdata( $user_id );
		else
			$user = wp_get_current_user();

			if ( empty( $user ) )
				return false;

				return in_array( $role, (array) $user->roles );
}
endif;


if( ! function_exists( 'tify_svg_img_src' ) ) :
/**
 *
*/
function tify_svg_img_src( $filename ){
	if( 'svg' !== pathinfo( $filename, PATHINFO_EXTENSION ) )
		return;
	if( ! $svg = @ file_get_contents( $filename ) )
		return;
	if( ! $base64_img = base64_encode( $svg ) )
		return;

	return 'data:image/svg+xml;base64,'.$base64_img;
}
endif;

if( ! function_exists( 'tify_custom_image_src' ) ) :
/**
 * @return array(
 		'file' 		=> (string)	// Nom du fichier image
 		'width'		=> (int) 	// Largeur de l'image
 		'height'	=> (int)	// Hauteur de l'image
 		'mime-type'	=> (string)	// MimeType de l'image
 		'url'		=> (string)	// Url vers le dossier de l'image
 		'dirname'	=> (string)	// Chemin vers le dossier de l'image
 )
*/
function tify_custom_attachment_image( $attachment_id = null, $size = array( /* width, height, crop */ ) ){
	// Bypass
	if(  ! $attachment_id )
		return;
		if( ! $filename = get_attached_file( $attachment_id ) )
			return;

			// Récupération des attributs de taille
			$width = 0; $height = 0; $crop = false;
			@list( $width, $height, $crop ) = $size;
			$filename = get_attached_file( $attachment_id );
			$url = wp_get_attachment_url( $attachment_id );

			if( $image = tify_image_make_intermediate_size( $filename, $width, $height, $crop ) ) :
			$image['url'] = dirname( $url );
			$image['dirname'] = dirname( $filename );
			return 	$image;
			endif;
}
endif;

if( ! function_exists( 'tify_custom_image_src' ) ) :
/** == Générateur d'une version d'image ==
 * @see  /wp-includes/media.php > image_make_intermediate_size( $file, $width, $height, $crop = false )
* @param (bool) $force
**/
function tify_image_make_intermediate_size( $file, $width, $height, $crop = false, $force = false ) {
	if( $force )
		return image_make_intermediate_size( $file, $width, $height, $crop = false );

		if ( $width || $height ) {
			$editor = wp_get_image_editor( $file );

			if ( is_wp_error( $editor ) || is_wp_error( $editor->resize( $width, $height, $crop ) ) )
				return false;

				if( ( $filename = $editor->generate_filename() ) && file_exists( $filename ) ) :
				$size = $editor->get_size();
				return array(
						'path'      => $filename,
						'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
						'width'     => $size['width'],
						'height'    => $size['height'],
						//'mime-type' => $editor->mime_type
				);
				endif;
					
				$resized_file = $editor->save();

				if ( ! is_wp_error( $resized_file ) && $resized_file ) {
					unset( $resized_file['path'] );
					return $resized_file;
				}
		}
		return false;
}
endif;

/** == Nettoyage du cache ==  **/
if( ! function_exists( 'tify_purge_transient' ) ) :
function tify_purge_transient( $prefix = null, $expiration = null ){
	global $wpdb;

	$expire = ( is_null( $expiration ) ) ? time() : time() - $expiration;


	$query  = "SELECT REPLACE(option_name, '_transient_timeout_{$prefix}', '{$prefix}') AS transient_name";
	$query .= " FROM {$wpdb->options}";
	$query .= " WHERE option_name LIKE '_transient_timeout_{$prefix}%%'";
	if( $expire )
		$query .= " AND option_value < %d";

		$transients = $wpdb->get_col( $wpdb->prepare( $query, $expire ) );

		if( empty( $transients ) )
			return;

			foreach( $transients as $transient )
				delete_transient( $transient );

				return $transients;
}
endif;

/** == Création d'un token == **/
if( ! function_exists( 'tify_generate_token' ) ) :
function tify_generate_token( $length = 32 ){
	if( function_exists( 'openssl_random_pseudo_bytes' ) ) :
	$token = base64_encode( openssl_random_pseudo_bytes( $length, $strong ) );
	if($strong == TRUE)
		return strtr(substr($token, 0, $length), '+/=', '-_,'); //base64 is about 33% longer, so we need to truncate the result
		endif;

		//fallback to mt_rand if php < 5.3 or no openssl available
		$characters = '0123456789';
		$characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/+';
		$charactersLength = strlen($characters)-1;
		$token = '';

		//select some random characters
		for ($i = 0; $i < $length; $i++) {
			$token .= $characters[mt_rand(0, $charactersLength)];
		}

		return $token;
}
endif;