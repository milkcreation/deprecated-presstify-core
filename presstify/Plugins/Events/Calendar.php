<?php
namespace tiFy\Plugins\Events;

use tiFy\Lib\Calendar as tiFyLibCalendar;

class Calendar extends tiFyLibCalendar
{
	public $labels = array(
		'days'	=> array( 'D', 'L', 'M', 'M', 'J', 'V', 'S' )
	);

	public function display() 
	{		
		$from 		= new \DateTime( $this->selected->format( 'Y-m-d' ) );
		$from->setTime( 0, 0, 0 ); 
		$to			= new \DateTime( $this->selected->format( 'Y-m-d' ) );
		$to->setTime( 23, 59, 59 );
		$tyevquery	= 'uniq';
		$tyevfrom	= $from->format( 'Y-m-d H:i:s' );
		$tyevto		= $to->format( 'Y-m-d H:i:s' );
		$query_args = array( 
			'post_type' 	=> 'phenix_show', 
			'tyevquery' 	=> $tyevquery, 
			'tyevfrom' 		=> $tyevfrom, 
			'tyevto' 		=> $tyevto 
		);
		
		$events_query = new \WP_Query;
		$events	= $events_query->query( $query_args );
		
		$output  = 	"<div id=\"tify_calendar\" class=\"tify_calendar\" data-action=\"{$this->id}\">\n".
        			"\t<div class=\"wrapper\">\n".
        			
					"\t\t<div class=\"col date-selected\">\n".
					"\t\t\t<div class=\"top\">\n".
					"\t\t\t<span class=\"prev\">". $this->prev_month_button( '<i class="fa fa-chevron-left"></i>' ) ."</span>\n".
					"\t\t\t<span class=\"dayname\">". date_i18n( 'l', $this->selected->getTimestamp() ) ."</span>\n".
					"\t\t\t</div>\n".
					"\t\t\t<span class=\"daynumber\">". $this->selected->format( 'd' )."</span>\n".
					"\t\t</div>\n".
					
					"\t\t<div class=\"col date-selector\">\n".
					"\t\t\t<div class=\"top\">\n".
                    "\t\t\t\t\t". $this->current_month( 'F Y' ).
               		"\t\t\t\t\t<span class=\"next\">". $this->next_month_button( '<i class="fa fa-chevron-right"></i>' ) ."</span>\n".
            		"\t\t\t</div>\n".
            		"\t\t\t". $this->header() ."\n".
            		"\t\t\t". $this->dates() ."\n".
            		"\t\t</div>\n".
					"\t</div>\n";
					
     	if( $events_query->have_posts() ) :
			$output .= "\t<ul class=\"events_list\">";
			while( $events_query->have_posts() ) : $events_query->the_post();
				$genre = ( phenix_show_get_genres( get_the_ID() ) ) ? phenix_show_get_main_genre( get_the_ID() ) : false;
			
				$output .= 	"\t\t<li style=\"display:block;width:100%;\">\n";				
				$output .= 	"\t\t\t<a href=\"". get_permalink() ."\"".
							" title=\"". sprintf( __( 'Voir le spectacle %s', 'phenix' ), get_the_title() ) ."\"";
				if( $genre )
					$output .= " class=\"genre cat-item-{$genre->term_id}\"";
				$output .= ">";
				if( $genre )
					$output .= "\t\t\t\t<i class=\"phenix-sprite genre-{$genre->slug}\"></i>\n";
				$output .= 	"\t\t\t\t". get_the_title() ."\n".
							"\t\t\t</a>\n".
							"\t\t</li>\n";							
			endwhile;
			$output .= "\t</ul>";		
		endif;
		$output  .=	"</div>\n";
											
		return $output; 
    }

	/** == == **/
	public function day_render( $day )
	{
		if( Query::RangeCount( $day->format( 'Y-m-d 00:00:00' ), $day->format( 'Y-m-d  23:59:59' ) ) )
			return "<a href=\"#\" data-toggle_date=\"". $day->format( 'Y-m-d' ) ."\" class=\"has_event\">". date_i18n( 'd', $day->getTimestamp() ) ."</a>";
		else
			return "<span>". date_i18n( 'd', $day->getTimestamp() ) ."</span>";
	}  
}