<?php
namespace tiFy\Core\Control\Calendar;

class Display
{    
    /**
     * Identifiant du calendrier
     */
    protected $Id                   = null;

    /**
     * Liste des Intitulés
     */
    protected $Labels               = array();

    /**
     * Date sélectionnée
     */
    protected $Selected             = null;

    /**
     * Premier jour du mois de la date sélectionnée
     */
    protected $FirstDay             = null;

    /**
     * Dernier jour du mois de la date sélectionnée
     */
    protected $LastDay              = null;

    /**
     * Aujourd'hui
     */
    protected $Today                = null;

    /**
     * Premier jour du calendrier (premier lundi)
     */
    protected $StartDay             = null;

    /**
     * Dernier jour du calendrier (dernier dimanche)
     */
    protected $EndDay               = null;

    /**
     * Mois précédent de la date sélectionnée
     */
    protected $PrevMonth            = null;

    /**
     * Mois suivant de la date selectionnée
     */
    protected $NextMonth            = null;
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct( $attrs = array() )
    {
        $this->Id = $attrs['id'];
        // Cartographie des intitulés
        $this->_mapLabels();
              
        //       
        $selected = isset( $attrs['selected'] ) ? $attrs['selected'] : date( 'Y-m-d', current_time( 'timestamp', false ) );
        
        // Définition du jour selectionné
        $this->Selected = new \DateTime( $selected );
        
        // Définition de la date d'aujourd'hui
        $this->Today = new \DateTime();
        
        // Définition du premier jour du mois
        $this->FirstDay = new \DateTime( $this->Selected->format( 'Y-m-d' ) );
        $this->FirstDay = $this->FirstDay->modify( 'first day of this month' );
        
        // Définition du dernier jour du mois
        $this->LastDay = new \DateTime( $this->Selected->format( 'Y-m-d' ) );
        $this->LastDay = $this->LastDay->modify( 'last day of this month' );
        
        // Définition du premier jour du calendrier (premier lundi)
        $this->StartDay = new \DateTime( $this->FirstDay->format( 'Y-m-d' ) );
        if( ! $this->StartDay->format( 'w' ) ) :
            $this->StartDay->modify( 'monday last week' ); 
        else :
            $this->StartDay->modify( 'monday this week' ); 
        endif;
        
        // Définition du dernier jour du calendrier
        $this->EndDay = new \DateTime( $this->LastDay->format( 'Y-m-d' ) );
        if( $this->EndDay->format( 'w' ) ) :
            $this->EndDay->modify( 'sunday this week' );
        endif;
        
        // Définition du mois précédent
        $this->PrevMonth = new \DateTime( $this->Selected->format( 'Y-m-d' ) );
        $this->PrevMonth->modify( '-1 month' )->modify( 'first day of this month' );
        
        // Définition du mois suivant
        $this->NextMonth = new \DateTime( $this->Selected->format( 'Y-m-d' ) );
        $this->NextMonth->modify( '+1 month' )->modify( 'first day of this month' );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de l'identifiant de calendrier
     */
    final protected function getId()
    {
        return $this->Id;
    }
    
    /**
     * Cartographie des intitulés
     */
    final protected function _mapLabels()
    {
        $defaults  = array(
            'days'          => array( 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam' ),
            'month'         => array( 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre' ),
            'nav'           => array( 'prev' => '&larr;', 'next'    => '&rarr;' )
        ); 
        
        foreach( $defaults as $section => $defaults_section ) :
            $this->Labels[$section] = ! empty( $this->Labels[$section] ) ? wp_parse_args( $this->Labels[$section], $defaults_section ) : $defaults_section;
        endforeach;
    }
            
    /**
     * Récupération d'un intitulé
     */
    final protected function getLabel( $section, $index )
    {
        if( isset( $this->Labels[$section][$index] ) )
            return $this->Labels[$section][$index];
    }
        
    /**
     * Affichage 
     */
    public function output()
    {
        $output = "";
        $output .= "<div class=\"tiFyCalendar\" data-tify_control=\"calendar\" data-id=\"". $this->getId() ."\">";
        $output .= $this->overlay();
        $output .= $this->nav();
        $output .= $this->header();
        $output .= $this->days();
        $output .= $this->footer();
        $output .= "</div>";
        
        return $output;
    }
      
    /**
     * Overlay
     */
    public function overlay()
    {
        return "<div class=\"tiFyCalendar-overlay\"><div class=\"tiFyCalendar-spinner sk-spinner sk-spinner-pulse\"></div></div>";
    }
    
    /**
     * Interface de navigation
     */
    public function nav()
    {
        return  "<ul class=\"tiFyCalendar-navItems\">".
                    "<li class=\"tiFyCalendar-navItem tiFyCalendar-navItem--prevMonth\">".
                        $this->prevMonthButton().
                    "</li>".
                    "<li class=\"tiFyCalendar-navItem tiFyCalendar-navItem--currentMonth\">".
                        $this->currentMonth().
                    "</li>".
                    "<li class=\"tiFyCalendar-navItem tiFyCalendar-navItem--nextMonth\">".
                        $this->nextMonthButton().
                    "</li>".
                "</ul>";
    }
    
    /**
     * Bouton d'affichage du mois précédent
     */
    public function prevMonthButton( $text = '' )
    {
        if( ! $text )
            $text = $this->getLabel( 'nav', 'prev' );
            
        return "<a href=\"#\" class=\"tiFyCalendar-navItemLink tiFyCalendar-navItemLink--prevMonth\" data-toggle=\"". $this->PrevMonth->format( 'Y-m' ) ."\">{$text}</a>";
    }
    
    /**
     * Bouton d'affichage du mois suivant
     */
    public function nextMonthButton( $text = '' )
    {
        if( ! $text )
            $text = $this->getLabel( 'nav', 'next' );      
        
        return "<a href=\"#\" class=\"tiFyCalendar-navItemLink tiFyCalendar-navItemLink--nextMonth\" data-toggle=\"". $this->NextMonth->format( 'Y-m' ) ."\">{$text}</a>";
    }
    
    /**
     * Affichage du mois courant
     */
    public function currentMonth( $format = null )
    {
        $text = ( $format === null ) ? $this->getLabel( 'month', ( $this->Selected->format( 'n' ) - 1 ) ) : date_i18n( $format, $this->Selected->getTimestamp() ) ;
        return "<span class=\"tiFyCalendar-navItemText tiFyCalendar-navItemText--current\">{$text}</span>";
    }
    
    /**
     * Entête
     */
    public function header( $format = null )
    {
        $output = "<ul class=\"tiFyCalendar-headerItems\">\n";        
        foreach( range( 0, 6, 1 ) as $i ) :
            $day    = new \DateTime( $this->StartDay->format( 'Y-m-d' ) );
            $day->add( new \DateInterval( "P{$i}D" ) );
            $text = ( $format === null ) ? $this->getLabel( 'days', $day->format( 'w' ) ) : date_i18n( $format, $day->getTimestamp() ) ;
            $output .= "\t<li class=\"tiFyCalendar-headerItem\">{$text}</li>\n";
        endforeach;
        $output .= "</ul>\n";
        
        return $output;
    }
    
    /**
     * Pied de page
     */
    public function footer()
    {
        return '';
    }
    
    /**
     * Affichage des jours
     */
    public function days()
    {
        $i = 0; 
        $a = $this->StartDay->diff( $this->EndDay )->days;
        $w = 0;
        $output = "<ul class=\"tiFyCalendar-datesItems\">\n";
        while( $i <= $a ) :
            $day = new \DateTime( $this->StartDay->format( 'Y-m-d' ) );
            $day->add( new \DateInterval( "P{$i}D" ) );
            $week = (int) $day->format( 'W' );
            if( ! $w ) :
                $w = $week;
                $output .= "\t<li class=\"tiFyCalendar-datesItem\">\n";
                $output .= "\t\t<ul class=\"tiFyCalendar-datesItemWeek tiFyCalendar-datesItemWeek--{$w}\">\n";
            elseif( $w !== $week  ) :
                $w = $week;
                $output .= "\t\t</ul>\n";
                $output .= "\t</li>\n";
                $output .= "\t<li class=\"tiFyCalendar-datesItem\">\n";
                $output .= "\t\t<ul class=\"tiFyCalendar-datesItemWeek tiFyCalendar-datesItemWeek--{$w}\">\n";
            endif;      
            
            $class = "tiFyCalendar-datesItemDay";
            $today_diff = $day->diff( $this->Today );
            if( ! $today_diff->days && ! $today_diff->invert ) :
                $class .= " tiFyCalendar-datesItemDay--today";
            endif;
            
            $selected_diff = $day->diff( $this->Selected );
            if( ! $selected_diff->days && ! $selected_diff->invert ) :
                $class .= " tiFyCalendar-datesItemDay--selected";
            endif;
            
            if( $day->format('n') !== $this->Selected->format( 'n' ) ) :
                $class .= " tiFyCalendar-datesItemDay--monthOut"; 
            endif;
                
            $output .= "\t\t\t<li class=\"{$class}\">". $this->dayRender( $day ) ."</li>\n";
            $i++;
        endwhile;
        $output .=  "\t\t</ul>\n";
        $output .=  "\t</li>\n";
        $output .= "</ul>\n";
        
        return $output;
    } 
    
    /**
     * Rendu d'un jour
     */
    public function dayRender( $day )
    {       
        return "<a href=\"#\" class=\"tiFyCalendar-datesItemDayLink\" data-toggle=\"". $day->format( 'Y-m-d' ) ."\">". date_i18n( 'd', $day->getTimestamp() ) ."</a>";
    }
}