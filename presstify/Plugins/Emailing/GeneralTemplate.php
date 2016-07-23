<?php
namespace tiFy\Plugins\Emailing;

class GeneralTemplate
{
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}
	
	/* = ACTIONS = */
	/** == Initialisation global == **/
	public function init()
	{
		// Déclaration de la variable de requête 
		add_rewrite_tag( '%wistify%', '([^&]+)' );
		// Déclaration de la régle de réécriture
		$rewrite_rules = get_option( 'rewrite_rules' );
		if( ! in_array( '^wistify/?', array_keys( $rewrite_rules ) ) ) :
			add_rewrite_rule( '^wistify/?', 'index.php?wistify=true', 'top' );
			flush_rewrite_rules( );
			wp_redirect( ( stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		endif;
	}
	
	/** == Affichage en ligne d'une campagne == **/ 
	public function template_redirect()
	{
		// Bypass
		if( ! get_query_var('wistify') )
			return;		
		if( ! preg_match( '/\/wistify\/(.*)\//', $_SERVER['REQUEST_URI'], $action ) )
			return;
		
		switch( $action[1] ) :
			case 'preview' :
				self::tpl_preview();
				break;
			case 'archive' :
				self::tpl_archive();
				break;
			case 'unsubscribe' :
				self::tpl_unsub();
				break;
			case 'subscribe_list' :
				self::tpl_subscribe_list();
				break;
			case 'unsubscribe_list' :
				self::tpl_unsubscribe_list();
				break;			
			default :
				self::tpl_404();
				break;
		endswitch;
	
		exit;
	}
	
	/* = CAMPAGNES = */
	/** == Récupération du titre d'une campagne ==
	 * @param (int) $id ID de la campagne
	 */
	public static function CampaignTitle( $id )
	{
		return tify_db_get( 'wistify_campaign' )->select()->cell_by_id( $id, 'title' );
	}
	
	/** == Liste déroulante des campagnes == **/
	public static function CampaignDropdown( $args = array() )
	{
		$defaults = array(
			'show_option_all' 	=> '', 
			'show_option_none' 	=> '',
			'show_date' 		=> false, // ou date format
			
			'orderby' 			=> 'id', 
			'order' 			=> 'ASC',
			'status' 			=> array(),
			'include' 			=> '',
			'exclude' 			=> '', 
			
			'echo' 				=> 1,
			'selected' 			=> 0,
			'name' 				=> 'campaign_id', 
			'id' 				=> '',
			'class' 			=> 'wistify_campaigns_dropdown', 
			'tab_index' 		=> 0,
			'hide_if_empty' 	=> false, 
			'option_none_value' => -1
		);
	
		$r = wp_parse_args( $args, $defaults );
		$option_none_value = $r['option_none_value'];
	
		$tab_index = $r['tab_index'];
	
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		
		// Requête de récupération
		$query_args = array();
		$query_args['orderby'] = $r['orderby'];
		$query_args['order'] = $r['order'];
		$query_args['status'] = ( empty( $r['status'] ) ) ? array( 'edit', 'preparing', 'ready', 'send', 'forwarded' ) : $r['status'];	
		if( $r['exclude'] )
			$query_args['exclude'] = $r['exclude'];
		if( $r['include'] )
			$query_args['item__in'] = $r['include'];
	
		$campaigns = \tify_db_get( 'wistify_campaign' )->select()->rows( $query_args );
		
		$name = esc_attr( $r['name'] );
		$class = esc_attr( $r['class'] );
		$id = $r['id'] ? esc_attr( $r['id'] ) : $name;
	
		if ( ! $r['hide_if_empty'] || ! empty( $campaigns ) )
			$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
		else
			$output = '';
		
		if ( empty( $campaigns ) && ! $r['hide_if_empty'] && ! empty( $r['show_option_none'] ) ) 
			$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>{$r['show_option_none']}</option>\n";
	
	
		if ( ! empty( $campaigns ) ) :
			if ( $r['show_option_all'] ) 
				$output .= "\t<option value='0' ". ( ( '0' === strval( $r['selected'] ) ) ? " selected='selected'" : '' ) .">{$r['show_option_all']}</option>\n";
	
			if ( $r['show_option_none'] )
				$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' ". selected( $option_none_value, $r['selected'], false ) .">{$r['show_option_none']}</option>\n";
			$walker = new \tiFy_Emailing_Walker_CampaignDropdown;
			$output .= call_user_func_array( array( &$walker, 'walk' ), array( $campaigns, -1, $r ) );
		endif;
	
		if ( ! $r['hide_if_empty'] || ! empty( $campaigns ) )
			$output .= "</select>\n";
	
		if ( $r['echo'] )
			echo $output;
	
		return $output;
	}
	
	/* = LISTES DE DIFFUSION = */
	/** == Liste déroulante des listes de diffusion == **/
	public static function MailingListDropdown( $args = array() )
	{	
		$defaults = array(
			'show_option_all' 	=> '', 
			'show_option_none' 	=> '',
			'show_count'        => false,
			
			'orderby' 			=> 'id', 
			'order' 			=> 'ASC',
			'include' 			=> '',
			'exclude' 			=> '', 
			
			'echo' 				=> 1,
			'selected' 			=> 0,
			'name' 				=> 'list_id', 
			'id' 				=> '',
			'class' 			=> 'wistify_mailing_lists_dropdown', 
			'tab_index' 		=> 0,
			'hide_if_empty' 	=> false, 
			'option_none_value' => -1
		);
	
		$r = wp_parse_args( $args, $defaults );
		$option_none_value = $r['option_none_value'];
	
		$tab_index = $r['tab_index'];
	
		$tab_index_attribute = '';
		if ( (int) $tab_index > 0 )
			$tab_index_attribute = " tabindex=\"$tab_index\"";
		
		// Requête de récupération
		$query_args = array();
		$query_args['orderby'] = $r['orderby'];
		$query_args['order'] = $r['order'];
		$query_args['status'] = ( empty( $r['status'] ) ) ? 'publish' : $r['status'];	
		if( $r['exclude'] )
			$query_args['exclude'] = $r['exclude'];
		if( $r['include'] )
			$query_args['item__in'] = $r['include'];
	
		$mailing_lists = \tify_db_get( 'wistify_list' )->select()->rows( $query_args );
		
		$name = esc_attr( $r['name'] );
		$class = esc_attr( $r['class'] );
		$id = $r['id'] ? esc_attr( $r['id'] ) : $name;
	
		if ( ! $r['hide_if_empty'] || ! empty( $mailing_lists ) )
			$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute autocomplete=\"off\">\n";
		else
			$output = '';
		
		if ( empty( $mailing_lists ) && ! $r['hide_if_empty'] && ! empty( $r['show_option_none'] ) ) 
			$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' selected='selected'>{$r['show_option_none']}</option>\n";
	
		if ( ! empty( $mailing_lists ) ) :
			if ( $r['show_option_all'] ) 
				$output .= "\t<option value='-1' ". ( ( '-1' === strval( $r['selected'] ) ) ? " selected='selected'" : '' ) .">{$r['show_option_all']}</option>\n";
	
			if ( $r['show_option_none'] )
				$output .= "\t<option value='" . esc_attr( $option_none_value ) . "' ". selected( $option_none_value, $r['selected'], false ) .">{$r['show_option_none']}</option>\n";
			$walker = new \tiFy_Emailing_Walker_MailingListsDropdown;
			$output .= call_user_func_array( array( &$walker, 'walk' ), array( $mailing_lists, -1, $r ) );
		endif;
	
		if ( ! $r['hide_if_empty'] || ! empty( $mailing_lists ) )
			$output .= "</select>\n";
	
		if ( $r['echo'] )
			echo $output;
	
		return $output;
	}
	
		
	/* = TEMPLATES = */
	/** == 404 == **/
	public static function tpl_404()
	{
		echo 'Wistify 404';	
	}
	
	/** == Prévisualisation de la campagne == **/
	public static function tpl_preview()
	{
		if( empty( $_REQUEST['c'] ) )
			return self::tpl_404();
		// Récupération de la campagne	
		if( ! $c = tify_db_get( 'wistify_campaign' )->select()->row_by( 'uid', $_REQUEST['c'] ) )
			return self::tpl_404();
		
		return self::html_content_output( $html, false, false );
	}
	
	/** == Affichage de la campagne en ligne == **/
	public static function tpl_archive()
	{
		if( empty( $_REQUEST['c'] ) )
			return self::tpl_404();
		if( empty( $_REQUEST['u'] ) )
			return self::tpl_404();

		// Récupération de la campagne	
		if( ! $c = \tify_db_get( 'wistify_campaign' )->select()->row_by( 'uid', $_REQUEST['c'] ) )
			return self::tpl_404();
		
		// Récupération de l'abonné
		if( ! $u = \tify_db_get( 'wistify_subscriber' )->select()->row_by( 'uid', $_REQUEST['u'] ) )
			$u = get_transient( 'wty_account_'. $_REQUEST['u'] );

		if( ! $u ) return self::tpl_404();
		
		// Affichage de la campagne
		echo self::html_output( $c->campaign_id, false, false );		
	}

	/** == Affichage du formulaire de désinscription == **/
	public static function tpl_unsub()
	{
		if( empty( $_REQUEST['c'] ) )
			return self::tpl_404();
		if( empty( $_REQUEST['u'] ) )
			return self::tpl_404();
		
		// Récupération de la campagne	
		if( ! $c = \tify_db_get( 'wistify_campaign' )->select()->row_by( 'uid', $_REQUEST['c'] ) )
			return self::tpl_404();
		
		// Récupération de l'abonné
		if( $u = \tify_db_get( 'wistify_subscriber' )->select()->row_by( 'uid', $_REQUEST['u'] ) ) :
			if( $list_ids = \tify_db_get( 'wistify_list' )->get_subscriber_list_ids( $u->subscriber_id ) ) :
				foreach( $list_ids as $list_id ) :
					\tify_db_get( 'wistify_list_relationships' )->insert_subscriber_for_list( (int) $u->subscriber_id, $list_id, 0 );
				endforeach;
				if( \tify_db_get( 'wistify_list_relationships' )->is_orphan( $u->subscriber_id ) )
					\tify_db_get( 'wistify_list_relationships' )->insert_subscriber_for_list( (int) $u->subscriber_id, 0, 1 );
			endif;
		else :
			$u = get_transient( 'wty_account_'. $_REQUEST['u'] );
		endif;
		if( ! $u ) return self::tpl_404();
		
		// Affichage de la confirmation de désinscription
		_e( 'Vous êtes désormais désinscrit.', 'tify' );		
	}
	
	/** == Affichage du formulaire de désinscription == **/
	public static function tpl_subscribe_list()
	{
		if( empty( $_REQUEST['u'] ) )
			return self::tpl_404();
			
		// Récupération de l'abonné
		if( $u = \tify_db_get( 'wistify_subscriber' )->select()->row_by( 'uid', $_REQUEST['u'] ) ) :
			$list_uid = isset( $_REQUEST['l'] ) ? $_REQUEST['l'] : 0;				
			if(  $list_uid ) :
				$l = \tify_db_get( 'wistify_list' )->select()->row_by( 'uid', $list_uid );
				\tify_db_get( 'wistify_list_relationships' )->insert_subscriber_for_list( $u->subscriber_id, $l->list_id, 1 );
			else :
				\tify_db_get( 'wistify_list_relationships' )->insert_subscriber_for_list( $u->subscriber_id, 0, 1 );
			endif;
		else :
			$u = get_transient( 'wty_account_'. $_REQUEST['u'] );
		endif;	

		if( ! $u ) return self::tpl_404();
		
		// Affichage de la confirmation de d'inscription
		_e( 'Félicitations, vous êtes désormais inscrit à la newsletter.', 'tify' );		
	}
	
	/** == Affichage du formulaire de désinscription == **/
	public static function tpl_unsubscribe_list()
	{
		if( empty( $_REQUEST['u'] ) )
			return self::tpl_404();
		
		// Récupération de l'abonné
		if( $u = \tify_db_get( 'wistify_subscriber' )->select()->row_by( 'uid', $_REQUEST['u'] ) ) :
			$list_uid = isset( $_REQUEST['l'] ) ? $_REQUEST['l'] : 0;
			if(  $list_uid ) :
				$l = \tify_db_get( 'wistify_list' )->select()->row_by( 'uid', $list_uid );
				\tify_db_get( 'wistify_list_relationships' )->insert_subscriber_for_list( $u->subscriber_id, $l->list_id, 0 );
			else :
				\tify_db_get( 'wistify_list_relationships' )->insert_subscriber_for_list( $u->subscriber_id, 0, 0 );
			endif;
		else :
			$u = get_transient( 'wty_account_'. $_REQUEST['u'] );
		endif;

		if( ! $u ) return self::tpl_404();
		
		// Affichage de la confirmation de d'inscription
		_e( 'Votre demande de désinscription à la newsletter a été prise en compte.', 'tify' );		
	}	
	
	/** == == **/
	public static function tpl_subscribe_form( $echo = true )
	{
		$output = "";
		if( $title = tify_emailing_get_option( 'wistify_subscribe_form', 'title' ) )
			$output .= "<h3>{$title}</h3>";
		$output .= tify_form_display( 'tify_wistify_subscribe', false );
		
		if( $echo )
			echo $output;
		else
			return $output;		
	}
	
	/** == == **/
	public static function html_output( $campaign_id, $archive = true, $unsub = true )
	{
		$output  = "";
		$output .= self::html_head( $campaign_id );
		$output .= self::html_body( $campaign_id );
		$output .= self::html_content( $campaign_id, $archive, $unsub );
		$output .= self::html_footer( $campaign_id ); 
		
		$dom = new \DOMDocument( );
	    @$dom->loadHTML( $output );
			
		return $dom->saveHTML();		
	}	
	
	/** == == **/
	public static function html_head( $campaign_id )
	{
		if( ! $c = \tify_db_get( 'wistify_campaign' )->select()->row_by_id( $campaign_id ) )
			return;

		$subject = isset( $c->campaign_message_options['subject'] ) ? $c->campaign_message_options['subject'] : $c->campaign_title;
		$subject = wp_unslash( $subject );
		
		return "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">".
				"<html xmlns=\"http://www.w3.org/1999/xhtml\">".
					"<head>".
						"<meta content=\"text/html; charset=UTF-8\" http-equiv=\"Content-Type\">".
						"<meta content=\"width=device-width, initial-scale=1.0\" name=\"viewport\">".
						"<title>{$subject}</title>".
						"<style type=\"text/css\">". file_get_contents( dirname( __FILE__ ) . "/assets/html_message.css" ) ."</style>".
					"</head>";
	}
	
	/** == == **/
	public static function html_body_attrs( $campaign_id )
	{
		return "marginwidth=\"0\" marginheight=\"0\" style=\"margin:0;padding:0;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;background-color:#F2F2F2;height:100%!important;width:100%!important;\" offset=\"0\" topmargin=\"0\" leftmargin=\"0\"";
	}
	
	/** == == **/
	public static function html_body( $campaign_id )
	{
		$body_attrs = self::html_body_attrs( $campaign_id );
		
		return "<body {$body_attrs}>";
	}
	
	/** == == **/
	public static function html_content( $campaign_id, $archive = true, $unsub = true )
	{
		if( ! $html = \tify_db_get( 'wistify_campaign' )->select()->cell_by_id( $campaign_id, 'content_html' ) )
			return;
		
		return self::html_content_output( $html, $archive, $unsub );
	}
	
	/** == == **/
	public static function html_content_output( $html, $archive = true, $unsub = true )
	{			
		$output = "";		
		$output .=	"<center>".
						"<table id=\"bodyTable\" style=\"border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;-ms-text-size-adjust:100%;-webkit-text-size-adjust: 100%;margin:0;padding:0;background-color:#F2F2F2;width:100%!important;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">".
							"<tbody>".
								"<tr>".
									"<td id=\"bodyCell\" style=\"mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;margin: 0;padding: 20px;border-top: 0;height: 100% !important;width: 100% !important;\" valign=\"top\" align=\"center\">".
										"<!-- BEGIN TEMPLATE // -->";
		if( $archive )
			$output .= self::html_archive( $html );		
		
		$output .= $html;
		
		if( $unsub )
			$output .= self::html_unsub( $html );
											
		$output .=						"<!-- // END TEMPLATE -->".
									"</td>".
								"</tr>".
							"</tbody>".
						"</table>".
					"</center>";
						
		return $output;
	}
	
	/** == == **/
	public static function html_archive( $html )
	{
		if( ! preg_match( '/\*\|ARCHIVE\|\*/', $html, $matches ) )		
			return "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"0\" align=\"center\" style=\"border-collapse:collapse;mso-table-lspace: 0pt;mso-table-rspace:0pt;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;\">".
						"<tbody>".
							"<tr>".
								"<td style=\"padding-top: 9px;padding-right:18px;padding-bottom:9px;padding-left:18px;mso-table-lspace:0pt;mso-table-rspace:0pt;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#606060;font-family:Helvetica;font-size:11px;line-height:125%;text-align:left;\">".
									"<div style=\"text-align: center;\">".
										"<a href=\"*|ARCHIVE|*\" style=\"font-size:11px;word-wrap:break-word;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#606060;font-weight:normal;text-decoration:underline;\">".
						 					__( 'Visualiser ce mail dans votre navigateur internet', 'tify' ).
						 				"</a>".
						 			"</div>".
					 			"</td>".
					 		"</tr>".
						"</tbody>".
					"</table>";
	}
	
	/** == == **/
	public static function html_unsub( $html )
	{
		if( ! preg_match( '/\*\|UNSUB\|\*/', $html, $matches ) )		
			return "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"0\" align=\"center\" style=\"border-collapse: collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;\">".
						"<tbody>".
							"<tr>".
								"<td style=\"padding-top: 9px;padding-right:18px;padding-bottom:9px;padding-left:18px;mso-table-lspace:0pt;mso-table-rspace:0pt;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#606060;font-family:Helvetica;font-size:11px;line-height:125%;text-align:left;\">".
									"<div style=\"text-align: center;\">".
										"<a href=\"*|UNSUB|*\" style=\"font-size:11px;word-wrap:break-word;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;color:#606060;font-weight:normal;text-decoration:underline;\">".
						 					__( 'Désinscription', 'tify' ).
						 				"</a>".
						 			"</div>".
					 			"</td>".
					 		"</tr>".
					 	"</tbody>".
					 "</table>";
	}

	/** == == **/
	public static function html_footer( $campaign_id )
	{
		$output  = "";
		$output .= 	"</body>";
		$output .= "</html>";
		
		return $output;
	}
}