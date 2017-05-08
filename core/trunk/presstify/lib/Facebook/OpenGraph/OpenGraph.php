<?php
/** @see https://developers.facebook.com/docs/sharing/webmasters#markup **/
/** @see https://developers.facebook.com/docs/reference/opengraph#object-type **/
/** @see http://ogp.me/ **/
namespace tiFy\Lib\Facebook;

class OpenGraph
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		add_action( 'wp_head', array( $this, 'wp_head' ), 1 );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );
	}
	
	/* = ACTIONS = */
	/** == Langage Attribute de la balise HTML  == **/
	final public function language_attributes( $output )
	{
		if( is_admin() )
			return $output;
		
		$output .= ' xmlns:fb="http://www.facebook.com/2008/fbml"';
		
		return $output;
	}
	
	/** == Modification de l'entête du site == **/
	public function wp_head()
	{
		if( $this->app_id )
			echo '<meta content="'. $this->app_id .'" property="fb:app_id">';
	}
}