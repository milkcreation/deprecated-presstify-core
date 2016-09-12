<?php
namespace tiFy\Lib\Facebook\JSInit;

class JSInit
{
	/* = ARGUMENTS = */
	private static $HookTag = false;
	private static $FBInit	= array();
	
	
	/* = CONSTRUCTEUR = */
	public function __construct( $app_id= null, $graph_version = 'v2.4', $options = array() )
	{
		add_action( 'wp_footer', array( $this, 'FBRoot' ), 1 );
		
		$fb_init = function() use( $app_id, $graph_version, $options ){
			self::FBInit( $app_id, $graph_version, $options );
		};
		add_action( 'wp_footer', $fb_init, 9999 );
	}
	
	/** == Balise d'accroche JS == **/
	public static function FBRoot()
	{
		if( self::$HookTag )
			return;
	?><div id="fb-root"></div><?php
		self::$HookTag = true;
	}
	
	/** == Initialisation de la librairie JS == **/
	/** @see https://developers.facebook.com/docs/javascript/reference/FB.init/v2.4 **/
	public static function FBInit( $app_id = null, $graph_version = 'v2.4', $options = array() )
	{
		if( in_array( $app_id, self::$FBInit ) )
			return;
		
		$src = ( @get_headers( 'http://connect.facebook.net/'. get_locale() .'/sdk.js' ) ) ? '//connect.facebook.net/'. get_locale() .'/sdk.js' : '//connect.facebook.net/en_US/sdk.js';
		?><script type="text/javascript">/* <![CDATA[ */window.fbAsyncInit=function(){FB.init({appId:'<?php echo $app_id;?>',status:true,cookie:true,xfbml:true,version:'<?php echo $graph_version;?>'});};(function(d, s, id){var js,fjs=d.getElementsByTagName(s)[0]; if(d.getElementById(id)) return; js = d.createElement(s);js.id=id;js.src="<?php echo $src;?>";fjs.parentNode.insertBefore(js, fjs);}(document,'script','facebook-jssdk'));/* ]]> */</script><?php	
		
		array_push( self::$FBInit, $app_id );
	}
}