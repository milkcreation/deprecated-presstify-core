<?php
/**
 * FONCTIONS PLUGGABLE (Fonction modifiable par le thème ou les plugins)
 *
 * @package WordPress
 */

/**
 * CHARACTERS
 */ 
 
if ( !function_exists( 'mk_chars_based_excerpt' ) ) : 
/**
 * Création d'un extrait de texte basé sur les nombre de caractères
 * 
 * @param (string) 	$text - chaîne de caractère à traiter
 * @param (int) 	$nb_chars - nombre maximum de caractères de la chaîne
 * @param (string) 	$more - délimiteur de fin de chaîne réduite (ex : [...])
 * @param (bool) 	$uncut_word	- préservation de la découpe de mots en fin de chaîne
 * 
 * @return string 
 */

function mk_chars_based_excerpt( $text, $args = array() ){
	$defaults = array(
		'nb_chars' => 255,
		'more' => '...',
		'uncut_word' => true,
		'use_teaser' => true
	);	
	$args = wp_parse_args( $args, $defaults );	
	extract( $args );
	
	$nb_chars = abs( $nb_chars );
	
	if ( $use_teaser && preg_match('/<!--more(.*?)?-->/', $text, $matches) ) :
		$texts = preg_split( '/<!--more(.*?)?-->/', $text );
		$teased = str_replace(']]>', ']]&gt;', $texts[0]);
		$teased = strip_tags( $teased );
		$teased = trim( $teased );
		if( $nb_chars > strlen( $teased ) ) :
			return $teased.$more;
		endif;	
	else :
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags( $text );
		$text = trim( $text );
		if( $nb_chars > strlen( $text ) ) return $text;	
	endif;		

	if( $uncut_word ):
		$text = substr( $text, 0, $nb_chars );
		$pos = strrpos( $text, " ");
		if( $pos === false )
			return substr( $text, 0, $nb_chars ) . $more;
		
		return substr( $text, 0, $pos ) . $more;
	else:
		return substr( $text, 0, $nb_chars ) . $more;	
	endif;
}
endif;  

  
if( ! function_exists( 'mk_multisort' ) ) : 
/**
 * Trie selon une valeur d'un tableau
 */
function mk_multisort( $array, $orderby = 'order' ){
	$orderly = array();	
	foreach ( (array)  $array as $key => $params )
		$orderly[$key] = $params[$orderby];
	
	if( !$orderly )
		return;		
	
	@array_multisort( $orderly , $array );

	return $array;
}
endif;

if( ! function_exists( 'mk_get_current_user_role' ) ) :
/**
 * Récupération du rôle d'un utilisateur
 */	
function mk_get_current_user_role( $user = null ) {
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
		
    $user_role = array_shift($user_roles);
    return $user_role;
};	
endif;

/**
 * FILES UPLOAD
 */

if( ! function_exists( 'mkpack_force_file_upload' ) ) :	
/**
 * Ajout d'un argument de requête pour l'url du fichier à uploader
 */ 
function mkpack_force_file_upload_query_vars( $aVars ) {
  $aVars[] .= 'file_upload_url';
  return $aVars;
}
add_filter( 'query_vars', 'mkpack_force_file_upload_query_vars' );

/**
 *  url de téléchargement du fichier fichier 
 */
function mkpack_file_upload_attachment_url( $attachment ) {
	if( ! $attachment_url = wp_get_attachment_url($attachment ) )
		return false;
	
	return add_query_arg( 'file_upload_url', $attachment_url, site_url('/') );	
}

/**
 *  Redirection vers la fonction d'upload de fichiers pour le front
 */
function mkpack_force_file_upload_wp( &$wp ) {
	if ( ! empty( $wp->query_vars['file_upload_url'] ) ) 
		add_action('template_redirect', 'mkpack_force_file_upload' );	
}
add_action( 'wp', 'mkpack_force_file_upload_wp' ); 


/**
 * 
 */
function mkpack_force_file_upload_file_type( $file ){
	switch( strrchr( basename( $file ), "." ) ) :
		case ".gz": 
			$type = "application/x-gzip"; 
			break;
		case ".tgz": 
			$type = "application/x-gzip"; 
			break;
		case ".zip": 
			$type = "application/zip"; 
			break;
		case ".pdf": 
			$type = "application/pdf"; 
			break;
		case ".png": 
			$type = "image/png"; 
			break;
		case ".gif": 
			$type = "image/gif"; 
			break;
		case ".jpg": 
			$type = "image/jpeg"; 
			break;
		case ".txt": 
			$type = "text/plain"; 
			break;
		case ".htm": 
			$type = "text/html"; 
			break;
		case ".html": 
			$type = "text/html"; 
			break;
		default: 
			$type = "application/octet-stream"; 
			break;
	endswitch;
	
	return $type;	
}
  
/**
 * Force le téléchargement d'un fichier plutôt que son affichage 
 */
function mkpack_force_file_upload(){
	if( empty( $_REQUEST['file_upload_url'] ) )
		return;
	
	$url = urldecode( $_REQUEST['file_upload_url'] );
	
	if( ( $match = preg_split( '/wp-content/', $url ) ) && isset( $match[1]) ) :
		$upload_path = WP_CONTENT_DIR.dirname( $match[1] );
		$upload_url = WP_CONTENT_URL.dirname( $match[1] );
	else :
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['path'];
		$upload_url = $upload_dir['url']; 
	endif;

	$file = basename( $url );
	$file_url = urlencode( $upload_url.'/'.$file );	
	$filename = $upload_path.'/'.$file;
	
	if( ! file_exists($filename) ) :
		wp_die( __( 'File not found', 'milk-pack') );
		exit;
	endif;	
	
	$filesize =  @filesize( $filename );

	$rangefilesize	=  $filesize-1; 
	$fileinfo = wp_check_filetype_and_ext( $filename, $filename );	
	$filetype = mkpack_force_file_upload_file_type( $filename );

	if( ini_get('zlib.output_compression') ) 
		ini_set('zlib.output_compression', 'Off');

	clearstatcache();		
	nocache_headers();
	ob_start();		
	ob_end_clean();	
	
	header("Pragma: no-cache");
	header("Expires: 0"); 
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public, max-age=0");
	header("Content-Description: File Transfer");
	header("Accept-Ranges: bytes"); 
	
	if( $filesize )
		header("Content-Length: ". (string) $filesize. "" );
	if( $filesize && $rangefilesize )
		header("Content-Range: bytes 0-".(string) $rangefilesize. "/".(string) $filesize ."");
	
	if( isset($fileinfo['type']) )
		header("Content-Type: ". (string)$fileinfo['type'] );
	else
		header("Content-Type: application/force-download");
	
	header("Content-Disposition: attachment; filename=".str_replace(' ', '\\', $file )  );
	header("Content-Transfer-Encoding: $filetype\n");
		
	@set_time_limit(0);
	
	$fp = @fopen($filename, 'rb');		
	if ($fp !== false) {
		while (!feof($fp)) {
			echo fread($fp, 8192);
		}
		fclose($fp);
	} else {
		@readfile($filename);
	}
	ob_flush();
	exit();
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
		'selected' => '',
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

	$jj = mysql2date( 'd', $event_date, false );
	$mm = mysql2date( 'm', $event_date, false );
	$aa = mysql2date( 'Y', $event_date, false );
	$hh = mysql2date( 'H', $event_date, false );
	$mn = mysql2date( 'i', $event_date, false );
	$ss = mysql2date( 's', $event_date, false );

	$month = "<select id=\"$prefix-$period-mm\" name=\"".$name."[mm]\">\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$month .= "\t\t\t<option value=\"" . zeroise($i, 2) . "\" ";
		$month .= selected( $i == $mm  , true, false);
		$month .= ">" . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
	}
	$month .= "</select>";

	$day 	= "<input type=\"text\" id=\"$prefix-$period-jj\" name=\"".$name."[jj]\" value=\"$jj\" size=\"2\" maxlength=\"2\" autocomplete=\"off\" />";
	$year 	= "<input type=\"text\" id=\"$prefix-$period-aa\" name=\"".$name."[aa]\" value=\"$aa\" size=\"4\" maxlength=\"4\" autocomplete=\"off\" />";
	$time_type = ( $time )? 'text' : 'hidden';
	$hour 	= "<input type=\"$time_type\" id=\"$prefix-$period-hh\" name=\"".$name."[hh]\" value=\"$hh\" size=\"2\" maxlength=\"2\" autocomplete=\"off\" />";
	$minute = "<input type=\"$time_type\" id=\"$prefix-$period-mn\" name=\"".$name."[mn]\" value=\"$mn\" size=\"2\" maxlength=\"2\" autocomplete=\"off\" />";

	$timestampwrap 	= "\n<div class=\"timestamp-wrap\">";
	$timestampwrap .= sprintf( __( '%2$s %1$s %3$s', 'mktzr' ), $month, $day, $year );
	$timestampwrap .= "\n<span class=\"mkmsgr-time\"";
	if( !$time )
		$timestampwrap .= " style=\"display:none;\"";
	$timestampwrap .= ">";
	$timestampwrap .= sprintf( __( 'à %1$s : %2$s', 'mktzr' ), $hour, $minute );
	$timestampwrap .= "</span>";
	$timestampwrap .= "\n</div>";
	$timestampwrap .= "\n<input type=\"hidden\" id=\"$prefix-$period-ss\" name=\"".$name."[ss]\" value=\"$ss\" />";
	
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
 * Verification d'un taxonomie pour un type de post
 */
if ( !function_exists('has_taxonomy_for_object_type') ) {
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
 * Suppression d'une taxonomie pour un type de post
 */
if ( ! function_exists( 'unregister_taxonomy_for_object_type' ) ) {
	function unregister_taxonomy_for_object_type( $taxonomy, $object_type) {
		global $wp_taxonomies;
		if( $key = has_taxonomy_for_object_type( $taxonomy, $object_type) ) :
			if( isset( $wp_taxonomies[$taxonomy]->object_type[ $key ] ) ) :
				unset( $wp_taxonomies[$taxonomy]->object_type[ $key ] );
			elseif( ( isset( $wp_taxonomies[$taxonomy]->object_type[0] ) ) && ( $wp_taxonomies[$taxonomy]->object_type[0] === $object_type ) ) :
				unset( $wp_taxonomies[$taxonomy]->object_type[ 0 ] );
			else :
				return false;
			endif;
			return true;
		endif;

		return false;
	}
}