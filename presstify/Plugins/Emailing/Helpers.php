<?php
/** == Appel de template == **/
function tify_emailing_template( $template_name )
{
	$template_name = 'tpl_'. $template_name;
	
	$args = $args = array_slice( func_get_args(), 1 );

	if( method_exists( $wistify->templates, $template_name ) )
		return call_user_func_array( array( $wistify->templates, $template_name ), $args );
}

/** == Récupération d'option == **/
function tify_emailing_get_option()
{
	
}

/* = CAMPAGNES = */
/** == Récupération du titre d'une campagne ==
 * @param (int) $id ID de la campagne
 */
function tify_emailing_campaign_title( $id )
{
	return tiFy\Plugins\Emailing\GeneralTemplate::CampaignTitle( $id );
}

/** == Liste déroulante des campagnes == **/
function tify_emailing_campaign_dropdown( $args = array() )
{
	return tiFy\Plugins\Emailing\GeneralTemplate::CampaignDropdown( $args );
}

/* = LISTES DE DIFFUSION = */
/** == Liste déroulante des listes de diffusion == **/
function tify_emailing_mailinglist_dropdown( $args = array() )
{
	return tiFy\Plugins\Emailing\GeneralTemplate::MailingListDropdown( $args );
}

/* = WALKERS = */
/** == Liste déroulante des campagnes == **/
class tiFy_Emailing_Walker_CampaignDropdown extends \Walker 
{
	/* = ARGUMENTS = */
	public $db_fields = array ( 
		'id' 		=> 'campaign_id', 
		'parent' 	=> '' 
	);
	
	/* = CONTRÔLEURS = */
	/** == == **/
	public function start_el( &$output, $campaign, $depth = 0, $args = array(), $id = 0 ) 
	{
		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $campaign->campaign_id ) . "\"";
		if ( $campaign->campaign_id == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		if( $args['show_date'] )
			$output .= ( $args['show_date'] === true ) ? mysql2date( get_option( 'date_format' ), $campaign->campaign_date ) : mysql2date( $args['show_date'], $campaign->campaign_date );
				
		$output .= wp_unslash( $campaign->campaign_title );
		$output .= "</option>\n";
	}
}

/** == Liste déroulante des listes de diffusion == **/
class tiFy_Emailing_Walker_MailingListsDropdown extends \Walker 
{
	/* = ARGUMENTS = */
	public $db_fields = array ( 'id' => 'list_id', 'parent' => '' );

	/* = CONTRÔLEURS = */
	/** == == **/
	public function start_el( &$output, $mailing_list, $depth = 0, $args = array(), $id = 0 ) 
	{
		$DbSubscriber = tify_db_get( 'wistify_subscriber' );

		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $mailing_list->list_id ) . "\"";
		if ( $mailing_list->list_id == $args['selected'] )
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= $mailing_list->list_title;
		
		if( $args['show_count'] )
			$output .= "  (". (int) $DbSubscriber->select()->count( array( 'list_id' => $mailing_list->list_id ) ) .")";
				
		$output .= "</option>\n";
	}
}