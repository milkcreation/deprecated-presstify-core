<?php
namespace tiFy\Plugins\Events;

class GeneralTemplate
{
	/* = ARGUMENTS = */
	private static $Calendar;
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		self::$Calendar = new Calendar( 'tify_events' );
	}
	
	/* = CONTRÔLEUR = */
	/** == == **/
	public static function DateRender( $date, $echo = true )
	{		
		$output = "";
	
		if( ! $output = apply_filters( 'tify_events_date_display', '', $date ) ) :
			if( ! $date['end_date'] )
				$output .= sprintf( __( 'le %s', 'tify' ), mysql2date( 'l d M', $date['start_date'] ) );
			elseif( (int) substr( $date['end_date'], 0, 4 ) > (int) substr( $date['start_date'], 0, 4 ) )
				$output .= sprintf( __( 'du %s au %s', 'tify' ), mysql2date( 'l d M Y', $date['start_date'] ), mysql2date( 'l d M Y', $date['end_date'] ) );
			else
				$output .= sprintf( __( 'du %s au %s', 'tify' ), mysql2date( 'l d M', $date['start_date'] ), mysql2date( 'l d M', $date['end_date'] ) );
			
			if( ! $date['end_time'] ) :
				$output .= sprintf( __( ' à %s', 'tify' ), preg_replace( '/^(\d{2}):(\d{2}):(\d{2})$/', '$1h$2', $date['start_time'] ) );
			else :
				$output .= sprintf( __( ' de %s à %s', 'tify' ), preg_replace( '/^(\d{2}):(\d{2}):(\d{2})$/', '$1h$2', $date['start_time'] ), preg_replace( '/^(\d{2}):(\d{2}):(\d{2})$/', '$1h$2', $date['end_time'] ) );
			endif;
		endif;
		
		if( $echo )
			echo $output;
		
		return $output;
	}
	
	/** == Affichage du calendrier == **/
	public static function Calendar( $date = null, $echo = true )
	{
		if( $echo )
			echo self::$Calendar->_parse_display( $date );
		else
			return self::$Calendar->_parse_display( $date );
	}
	
	
}