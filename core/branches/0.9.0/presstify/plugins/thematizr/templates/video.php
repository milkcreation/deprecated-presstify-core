<?php
global $post;
wp_enqueue_style( "mediaelement");
wp_enqueue_script( "mediaelement");
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<title><?php echo $post->post_title;?></title>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<meta name="author" content="Jordy Manner">
<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
<meta name="format-detection" content="telephone=no" />
<meta property="og:type" content="movie" /> 
<meta property="og:title" content="<?php echo $post->post_title;?>" />
<meta property="og:video" content="<?php echo get_permalink( $post->ID );?>" />

<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link href="<?php echo get_permalink( $post->ID ); ?>" rel="canonical">
<link href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" type="image/x-icon" rel="icon" />
<link href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.png" type="image/png" rel="shortcut icon" />
<link rel="apple-touch-icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon-iphone.png" />
<link rel="apple-touch-icon-precomposed" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon-iphone.png" />
<?php wp_print_styles( "mediaelement"); ?>
<style>
body {
    border: 0 none;
    font-size: 100%;
    margin: 0;
    padding: 0;
}
a, abbr, acronym, address, applet, b, big, blockquote, button, canvas, caption, center, cite, code, dd, del, dfn, div, dl, dt, em, embed, fieldset, font, form, h1, h2, h3, h4, h5, h6, hr, html, i, iframe, img, ins, kbd, label, legend, li, menu, object, ol, p, pre, q, s, samp, small, span, strike, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var {
    background: none repeat scroll 0 0 rgba(0, 0, 0, 0);
    border: 0 none;
    font-size: 100%;
    margin: 0;
    padding: 0;
}
html {
    overflow: hidden;
}
body {
    background-color: #000000;
    color: #FFFFFF;
    font: 12px Arial,sans-serif;
    height: 100%;
    overflow: hidden;
    position: absolute;
    width: 100%;
}
.full-frame {
    height: 100%;
    width: 100%;
}
.milk-video{
	width:100%;
	max-width: 100%;
	max-height:100%;
	margin: 0 auto;	
}	
</style>
<?php wp_print_scripts("jquery");?>
</head>
<body>
<?php echo mk_video_stream_html( array( 'src' => wp_get_attachment_url( $post->id ) ) );?>
<?php wp_print_scripts("mediaelement");?>
<script>
	jQuery(document).ready(function($){
		// add mime-type aliases to MediaElement plugin support
		mejs.plugins.silverlight[0].types.push('video/x-ms-wmv');
		mejs.plugins.silverlight[0].types.push('audio/x-ms-wma');
	
		$(function () {
			var settings = {};
	
			if ( typeof _wpmejsSettings !== 'undefined' )
				settings.pluginPath = _wpmejsSettings.pluginPath;
	
			$('.milk-video').mediaelementplayer( settings );
		});
	});
</script>
</body>
</html>