<?php
namespace tiFy\Lib;

class Calendar
{
	/* = ARGUMENTS = */
	public 		$labels,     
				$id,
				$selected		= null,			// Date courante sélectionnée
				$first			= null,			// Premier jour du mois pour la date courante
				$last			= null,			// Dernier jour du mois pour la date courante
				$start			= null,			// Premier jour du calendrier
				$end			= null;			// Dernier jour du calendrier
  	
  	/* = CONSTRUCTEUR = */
    public function __construct( $id = null )
    {		
		// Identifiant
		if( ! $this->id )
			$this->id = ( ! $id ) ? get_class( $this ) : $id;
		
		// Cartographie des intitulé
		$this->_map_label();
		
		add_action( 'wp_ajax_tify_calendar_'. $this->id, array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_calendar_'. $this->id, array( $this, 'wp_ajax' ) );
    }
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Action Ajax == **/
	final public function wp_ajax()
	{
		$this->display( $_POST['date'] );
		exit;
	}	
	
	/* = PARAMETRAGE = */
	/** == Traitement de la date == **/
	protected function _parse_date( $date = null )
	{
		if( ! $date )
			$date = date( 'Y-m-d', current_time( 'timestamp', false ) );            
        
		// Jour selectionné
		$this->selected	= new \DateTime( $date );
		// Aujourd'hui
		$this->today 	= new \DateTime();
		// Premier jour du mois
		$this->first	= new \DateTime( $this->selected->format( 'Y-m-d' ) );  
		$this->first 	= $this->first->modify( 'first day of this month' );
		// Dernier jour du mois
		$this->last		= new \DateTime( $this->selected->format( 'Y-m-d' ) );
		$this->last 	= $this->last->modify( 'last day of this month' );
		// Premier jour du calendrier
		$this->start	= new \DateTime( $this->first->format( 'Y-m-d' ) );
		if( ! $this->start->format( 'w' ) ) 
			$this->start->modify( 'monday last week' );	
		else 
			$this->start->modify( 'monday this week' );	

		// Dernier jour du calendrier	
		$this->end 		= new \DateTime( $this->last->format( 'Y-m-d' ) );
		if( $this->end->format( 'w' ) ) 
			$this->end->modify( 'sunday this week' );
		// Mois précédent
		$this->prev 	= new \DateTime( $this->selected->format( 'Y-m-d' ) );
		$this->prev->modify( '-1 month' )->modify( 'first day of this month' );
		// Mois suivant
		$this->next 	= new \DateTime( $this->selected->format( 'Y-m-d' ) );
		$this->next->modify( '+1 month' )->modify( 'first day of this month' );
	}
	
	/** == Cartographie des intitulés == **/
	private function _map_label()
	{
		$defaults  = array(
			'days' 			=> array( 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ),
			'month' 		=> array( 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ),
			'nav'			=> array( 'prev' => '&larr;', 'next'	=> '&rarr;' )
		); 
		
		foreach( $defaults as $section => $labels )
			$this->label_{$section} = ( isset( $this->labels[$section] ) ) ? $this->labels[$section] : $labels;
	}
	    
	/* = AFFICHAGE = */
	/** == == **/
	public function get_label( $section, $index )
	{
		return $this->label_{$section}[$index];
	}
	
	/** == == **/  
    public function display( $date = null, $echo = true ) 
    {	
    	$this->_parse_date( $date );
        $output = 	"<div id=\"tify_calendar\" class=\"tify_calendar\" data-action=\"{$this->id}\">".
          				"<div class=\"overlay\"><div class=\"sk-spinner sk-spinner-pulse\"></div></div>".
                        $this->nav().
                        $this->header().
						$this->dates().
					"</div>";
		
		if( $echo )
			echo $output;
		else
			return $output; 
    }
	
	/** == Interface de navigation == **/
    public function nav()
    {
    	return	"<ul class=\"navi\">".
                	"<li class=\"prev\">".
                		$this->prev_month_button().
                	"</li>".
                    "<li class=\"current\">".
                    	$this->current_month().
                    "</li>".
               		"<li class=\"next\">".
               			$this->next_month_button().
               		"</li>".
            	"</ul>";
    }
	
	/** == Bouton d'affichage du mois précédent == **/
	public function prev_month_button( $text = '' )
	{
		if( ! $text )
			$text = $this->get_label( 'nav', 'prev' );
			
		return "<a href=\"#\" data-toggle=\"". $this->prev->format( 'Y-m' ) ."\">{$text}</a>";
	}
	
	/** == Bouton d'affichage du mois suivant == **/
	public function next_month_button( $text = '' )
	{
		if( ! $text )
			$text = $this->get_label( 'nav', 'next' );		
		
		return "<a href=\"#\" data-toggle=\"". $this->next->format( 'Y-m' ) ."\">{$text}</a>";
	}
	
	/** == Affichage du mois courant == **/
	public function current_month( $format = null )
	{
		$text = ( $format === null ) ? $this->get_label( 'month', ( $this->selected->format( 'n' ) - 1 ) ) : date_i18n( $format, $this->selected->getTimestamp() ) ;
		return "<span>{$text}</span>";
	}
		
	/** == Entête == **/
	public function header( $format = null )
	{
		$output = "<ul class=\"header\">\n";		
		foreach( range( 0, 6, 1 ) as $i ) :
			$day 	= new \DateTime( $this->start->format( 'Y-m-d' ) );
			$day->add( new \DateInterval( "P{$i}D" ) );
			$text = ( $format === null ) ? $this->get_label( 'days', $day->format( 'w' ) ) : date_i18n( $format, $day->getTimestamp() ) ;
			$output .= "\t<li>{$text}</li>\n";
		endforeach;
		$output .= "</ul>\n";
		
		return $output;
	}
	
	/** == Jours == **/
	public function dates()
	{
		$i = 0; 
		$a = $this->start->diff( $this->end )->days;		
		$w = 0;
		$output = "<ul class=\"dates\">\n";
		while( $i <= $a ) :
			$day = new \DateTime( $this->start->format( 'Y-m-d' ) );
			$day->add( new \DateInterval( "P{$i}D" ) );
			$week = (int) $day->format( 'W' );
			if( ! $w ) :
				$w = $week;
				$output .= 	"\t<li>\n".
							"\t\t<ul id=\"week-{$w}\" class=\"week\">\n";
			elseif( $w !== $week  ) :
				$w = $week;
				$output .= 	"\t\t</ul>\n".
							"\t</li>\n".
							"\t<li>\n".
							"\t\t<ul id=\"week-{$w}\" class=\"week\">\n";
			endif;		
			
			$class = "date";
			$today_diff = $day->diff( $this->today );
			if( ! $today_diff->days && ! $today_diff->invert )
				$class .= " today";
			
			$selected_diff = $day->diff( $this->selected );
			if( ! $selected_diff->days && ! $selected_diff->invert )
				$class .= " selected";
			
			if( $day->format('n') !== $this->selected->format( 'n' ) )
				$class .= " month-out";	
				
			$output .= "\t\t\t<li class=\"{$class}\">". $this->day_render( $day ) ."</li>\n";
			$i++;
		endwhile;
		$output .= 	"\t\t</ul>\n";
		$output .= 	"\t</li>\n";
		$output .= "</ul>\n";
		
		return $output;
	} 
	
	/** == == **/
	public function day_render( $day )
	{		
		return "<a href=\"#\" data-toggle=\"". $day->format( 'Y-m-d' ) ."\">". date_i18n( 'd', $day->getTimestamp() ) ."</a>";
	} 
		
	/** == == **/
	public function overlay()
	{
		return "<div class=\"overlay\"><div class=\"sk-spinner sk-spinner-pulse\"></div></div>";
	}
}