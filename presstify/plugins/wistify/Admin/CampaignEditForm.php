<?php
namespace tiFy\Plugins\Wistify\Admin;

use tiFy\Entity\AdminView\EditForm;
use tiFy\Plugins\Wistify\Wistify;

class CampaignEditForm extends EditForm
{
	/* = ARGUMENTS = */	
	public	$current_step;
	
	private	// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( Wistify $master )
	{
		// Définition des classe de référence
		$this->master 	= $master;
				
		// Paramétrage
		/// Environnement
		$this->current_step = isset( $_REQUEST['step'] ) ? (int) $_REQUEST['step'] : 1;
		
		/// Argument par défaut d'un élément
		$this->item_defaults = array( 
			'campaign_uid' 		=> tify_generate_token(), 
			'campaign_status' 	=> 'auto-draft', 
			'campaign_step' 	=> 1 
		);
		
		/// Notifications
		$this->notifications = array(
			'updated' 						=> array(
				'message'		=> __( 'La campagne a été enregistré avec succès', 'tify' ),
				'type'			=> 'success',
				'dismissible'	=> true
			),
			'invalid_format_from_email' 	=> array(
				'message'		=> __( 'Le format de l\'email de l\'expéditeur n\'est pas valide', 'tify' ),
				'type'			=> 'error'
			)
		);
	}
	
	/* = DECLENCHEURS = */
	/* == Chargement de l'écran courant == */
	final public function current_screen( $current_screen )
	{
		switch( $this->current_step ) :
			case 2 :
				add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 99, 2 );
				break;
		endswitch;
	}
	
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function admin_enqueue_scripts()
	{
		// Initialisation des scripts
		wp_enqueue_style( 'tify_wistify_campaign', $this->master->admin->uri .'/css/campaign-edit.css', array( ), '150403' );

		switch( $this->current_step ) :
			case 1 :
				tify_control_enqueue( 'text_remaining' );
				break;
			case 2 :
				wp_enqueue_script( 'tify_wistify_campaign-step2', $this->master->admin->uri .'/js/campaign-edit-step2.js', array( 'jquery' ), '150928', true );
				// Actions et Filtres Wordpress
				add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 99, 2 );
				break;
			case 3 :
				wp_enqueue_style( 'tify_wistify_campaign-step3', $this->master->admin->uri .'/css/campaign-edit-step3.css', array( 'tify_suggest' ), '150918' );
				wp_enqueue_script( 'tify_wistify_campaign-step3', $this->master->admin->uri .'/js/campaign-edit-step3.js', array( 'jquery', 'tify_suggest' ), '150918', true );
				break;
			case 4 :						
				tify_control_enqueue( 'switch' );
			break;
			case 5 :
				wp_enqueue_style( 'tify_wistify_campaign-step5', $this->master->admin->uri .'/css/campaign-edit-step5.css', array( 'tify_controls-touch_time' ), '150918' );
				wp_enqueue_script( 'tify_wistify_campaign-step5', $this->master->admin->uri .'/js/campaign-edit-step5.js', array( 'jquery', 'tify_controls-touch_time' ), '150918', true );
				wp_localize_script( 'tify_wistify_campaign-step5', 'wistify_campaign', array( 
						'total_in' 			=> __( 'sur un total de', 'tify' ),
						'preparing' 		=> __( 'Préparation en cours ...', 'tify' ),
						'emails_ready' 		=> __( 'Emails prêts', 'tify' )				
					)
				);
			break;
		endswitch;
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/*** === === ***/
	final public function tiny_mce_before_init( $mceInit, $editor_id )
	{
		if( $editor_id !== 'wistify_campaign_content_html' )
			return $mceInit;
		
		$mceInit['toolbar1'] 			= 'bold,italic,underline,strikethrough,blockquote,|,alignleft,aligncenter,alignright,alignjustify,|,bullist,numlist,outdent,indent,|,link,unlink,hr';
		$mceInit['toolbar2'] 			= 'pastetext,|,formatselect,fontselect,fontsizeselect';
		$mceInit['toolbar3'] 			= 'table,|,forecolor,backcolor,|,subscript,superscript,charmap,|,removeformat,|,undo,redo';
		$mceInit['toolbar4'] 			= '';	
		
		
		$mceInit['block_formats'] 		= 	'Paragraphe=p;Paragraphe sans espace=div;Titre 1=h1;Titre 2=h2;Titre 3=h3;Titre 4=h4';
		
		$mceInit['font_formats'] 		= 	"Arial=arial,helvetica neue,helvetica,sans-serif;".
											"Comic Sans MS=font-family:comic sans ms,marker felt-thin,arial,sans-serif;".
											"Courier New=courier new,courier,lucida sans typewriter,lucida typewriter,monospace;".
											"Georgia=georgia,times,times new roman,serif;".
											"Lucida=lucida sans unicode,lucida grande,sans-serif;".
								 			"Tahoma=tahoma,verdana,segoe,sans-serif;".
								 			"Times New Roman=times new roman,times,baskerville,georgia,serif;".
								 			"Trebuchet MS=trebuchet ms,lucida grande,lucida sans unicode,lucida sans,tahoma,sans-serif;".
								 			"Verdana=verdana,geneva,sans-serif";
																					
		$mceInit['table_default_attributes'] 	= json_encode( 
			array(
				'width' 					=> '600',
				'cellspacing'				=> '0', 
				'cellpadding'				=> '0', 
				'border'					=> '0'
			)
		);
		$mceInit['table_default_styles'] 		= json_encode( 
			array(				
				'border-collapse' 			=> 'collapse',
				'mso-table-lspace' 			=> '0pt',
				'mso-table-rspace' 			=> '0pt',
				'-ms-text-size-adjust' 		=> '100%',
				'-webkit-text-size-adjust' 	=> '100%',
				'background-color' 			=> '#FFFFFF',
				'border-top' 				=> '0',
				'border-bottom' 			=> '0'
			) 
		);
		
		$mceInit['wordpress_adv_hidden'] 	= false;
			
		return $mceInit;
	}
	
	/* = AFFICHAGE = */
	/** == Champs cachés == **/
	public function hidden_fields()
	{
	?>
		<input type="hidden" id="current_step" name="current_step" value="<?php echo $this->current_step; ?>" />		
		<input type="hidden" id="campaign_id" name="campaign_id" value="<?php echo esc_attr( $this->item->campaign_id );?>" />
		<input type="hidden" id="campaign_uid" name="campaign_uid" value="<?php echo esc_attr( $this->item->campaign_uid );?>" />
		<input type="hidden" id="campaign_author" name="campaign_author" value="<?php echo esc_attr( $this->item->campaign_author ); ?>" />
		<input type="hidden" id="campaign_date" name="campaign_date" value="<?php echo esc_attr( $this->item->campaign_date );?>" />
		<input type="hidden" id="campaign_status" name="campaign_status" value="<?php echo esc_attr( $this->item->campaign_status ); ?>" />
		<input type="hidden" id="campaign_step" name="campaign_step" value="<?php echo esc_attr( $this->item->campaign_step ); ?>" />
	<?php
	}
	
	/** == Navigation haute (étapes) == **/
	public function top_nav()
	{
		$step_title = array( 
			1 => __( 'Informations générales', 'tify' ),
			2 => __( 'Préparation du Message', 'tify' ),
			3 => __( 'Choix des destinataires', 'tify' ),
			4 => __( 'Options d\'envoi', 'tify' ),
			5 => __( 'Test et distribution', 'tify' )
		);
	?>	
		<ul id="step-breadcrumb">
		<?php foreach( range( 1, 5, 1 ) as $step ) :?>
			<li <?php if( $step === $this->current_step ) echo 'class="current"';?>>
			<?php $step_txt = sprintf( __( 'Étape %d', 'tify' ), $step ). "<br><span style=\"font-size:0.7em\">". $step_title[$step] ."</span>";?>
			<?php if( ( $step <= $this->item->campaign_step ) && ( $step != $this->current_step ) ) :?>
				<a href="<?php echo add_query_arg( array( $this->primary_key => $this->item->{$this->primary_key}, 'step' => $step ), $this->base_url );?>"><?php echo $step_txt;?></a>
			<?php else :?>
				<span><?php echo $step_txt;?></span>
			<?php endif;?>	
			</li>
		<?php endforeach;?>
		</ul>
	<?php
	}
	
	/** == Formulaire d'édition == **/
	public function form()
	{		
	?>	
		<div id="wistify_campaign-edit">
			<?php $this->top_nav();?>
			<div id="step-edit-<?php echo $this->current_step;?>">
				<?php call_user_func( array( $this, 'step_'. $this->current_step ) );?>
			</div>
		</div>
	<?php	
	}
	
	/** == ETAPE #1 - INFORMATIONS GENERALES == **/
	public function step_1( )
	{
	?>	
		<input type="text" autocomplete="off" id="title" value="<?php echo esc_attr( $this->item->campaign_title );?>" size="30" name="campaign_title" placeholder="<?php _e( 'Intitulé de la campagne', 'tify' );?>">
		
		<?php tify_control_text_remaining( array( 'id' => 'content', 'name' => 'campaign_description', 'value' => esc_html( $this->item->campaign_description ), 'attrs' => array( 'placeholder' => __( 'Brève description de la campagne', 'tify' ) ) ) );?>
	<?php
	}

	/** == ETAPE #2 - PERSONNALISATION DU MESSAGE == **/
	public function step_2( )
	{
		$content_html = $this->item->campaign_content_html;
		
		// Personnalisation de l'éditeur	
		add_filter( 'mce_css', create_function( '$mce', 'return "'. $this->master->admin->uri . '/css/editor-style.css";' ) );

		wp_editor( 	
			$content_html, 
			'wistify_campaign_content_html', 
			array(
				'wpautop'		=> false,
				'media_buttons'	=> true,
				'textarea_name'	=> 'campaign_content_html'
			) 
		);
		/* ?>
		<a href=\"\" id="wistify_campaign_content_html-preview" type="button">preview</a>
		<?php */	
	}

	/** == ETAPE #3 - DESTINATAIRES == **/
	public function step_3( )
	{
		$total = 0;
		tify_suggest( 
			array(
				'id'			=> 'recipient-search',
				'placeholder'	=> __( 'Tapez un email ou un intitulé', 'tify' ),
				'ajax_action'	=> 'wistify_autocomplete_recipients'
			)
		);
	?>

		<div style="padding:5px;"><i class="fa fa-info-circle" style="font-size:24px; vertical-align:middle; color:#1E8CBE;"></i>&nbsp;&nbsp;<b><?php _e( 'Emails : abonné ou utilisateur Wordpress | Intitulés : liste/groupe de diffusion ou rôle Wordpress', 'tify' );?></b></div>
		<ul id="recipients-list">
		<?php if( isset( $this->item->campaign_recipients['wystify_subscriber'] ) ) :?>
			<?php foreach( (array) $this->item->campaign_recipients['wystify_subscriber'] as $recipient ) : if( ! $this->master->db->subscriber->select()->row_by_id( $recipient ) ) continue; ?>
				<li data-numbers="1">
					<span class="ico">
						<i class="fa fa-user"></i>
						<i class="badge wisti-logo"></i>
					</span>
					<span class="label"><?php echo $this->master->db->subscriber->select()->cell_by_id( $recipient, 'email' );?></span>
					<span class="type"><?php _e( 'Abonné', 'tify' );?></span>
					<a href="" class="tify_button_remove remove"></a>					
					<input type="hidden" name="campaign_recipients[wystify_subscriber][]" value="<?php echo $recipient;?>">	
				</li>	
			<?php $total++; endforeach;?>
		<?php endif; ?>
		<?php if( isset( $this->item->campaign_recipients['wystify_mailing_list'] ) ) :?>
			<?php foreach( (array) $this->item->campaign_recipients['wystify_mailing_list'] as $list_id ) : $numbers = $this->master->db->subscriber->select->count( array( 'list_id' => $list_id, 'status' => 'registred', 'active' => 1 ) );?>
				<li data-numbers="<?php echo $numbers;?>">
					<span class="ico">
						<i class="fa fa-group"></i>
						<i class="badge wisti-logo"></i>
					</span>
					<span class="label"><?php echo $this->master->db->list->select()->cell_by_id( $list_id, 'title' );?></span>
					<span class="type"><?php _e( 'Liste de diffusion', 'tify' );?></span>
					<span class="numbers"><?php echo $numbers;?></span>
					<a href="" class="tify_button_remove remove"></a>					
					<input type="hidden" name="campaign_recipients[wystify_mailing_list][]" value="<?php echo $list_id;?>">	
				</li>	
			<?php $total+= $numbers; endforeach;?>
		<?php endif; ?>
		<?php /*if( isset( $this->item->campaign_recipients['wordpress_user'] ) ) :?>
			<?php foreach( (array) $this->item->campaign_recipients['wordpress_user'] as $recipient ) :?>
				<li data-numbers="1">
					<span class="ico">
						<i class="fa fa-user"></i>
						<i class="badge dashicons dashicons-wordpress"></i>
					</span>
					<span class="label"><?php echo get_userdata( $recipient )->user_email;?></span>
					<span class="type"><?php _e( 'Utilisateur Wordpress', 'tify' );?></span>
					<a href="" class="tify_button_remove remove"></a>					
					<input type="hidden" name="campaign_recipients[wordpress_user][]" value="<?php echo $recipient;?>">	
				</li>	
			<?php $total++; endforeach;?>
		<?php endif; ?>
		<?php if( isset( $this->item->campaign_recipients['wordpress_role'] ) ) :?>
			<?php foreach( (array) $this->item->campaign_recipients['wordpress_role'] as $recipient ) : $user_query = new WP_User_Query( array( 'role' => $recipient ) ); $numbers = $user_query->get_total();?>
				<?php $roles = get_editable_roles(); $role = $roles[$recipient];?>
				<li data-numbers="<?php echo $numbers;?>">
					<span class="ico">
						<i class="fa fa-group"></i>
						<i class="badge dashicons dashicons-wordpress"></i>
					</span>
					<span class="label"><?php echo translate_user_role( $role['name'] );?></span>
					<span class="type"><?php _e( 'Groupe d\'utilisateurs Wordpress', 'tify' );?></span>
					<span class="numbers"><?php echo $numbers;?></span>
					<a href="" class="tify_button_remove remove"></a>					
					<input type="hidden" name="campaign_recipients[wordpress_role][]" value="<?php echo $recipient;?>">	
				</li>	
			<?php $total+= $numbers; endforeach;?>
		<?php endif;*/ ?>
		</ul>
		<div id="recipients-total">
			<span class="label"><?php _e( 'Total :', 'tify' );?></span>&nbsp;<strong class="value"><?php echo $total;?></strong>
		</div>
	<?php
	}
	
	/** == ETAPE #4 - OPTIONS DE MESSAGE == **/
	public function step_4( )
	{
		// Définition des options par defaut
		$defaults = array(
			'subject' 		=> $this->item->campaign_title,
			'from_email'	=> ( $from_email = $this->master->options->get( 'wistify_contact_information', 'contact_email' ) ) ? $from_email : get_option( 'admin_email' ),
			'from_name'		=> ( $from_name = $this->master->options->get( 'wistify_contact_information', 'contact_name' ) ) ? $from_name : ( ( $user = get_user_by( 'email', get_option( 'admin_email' ) ) ) ? $user->display_name : '' ),
			'headers'		=> array(
				'Reply-To'		=> ( $reply_to = $this->master->options->get( 'wistify_contact_information', 'reply_to' ) ) ? $reply_to : ''
			),
			'important'		=> 'off',
			'track_opens'	=> 'on',
			'track_clicks'	=> 'on'	
		);
		$this->item->campaign_message_options = wp_parse_args( $this->item->campaign_message_options, $defaults );
	?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php _e( 'Sujet du message', 'tify' );?></th>
					<td><input type="text" name="campaign_message_options[subject]" value="<?php echo $this->item->campaign_message_options['subject'];?>" class="widefat" /></td>
				</tr>
				<tr>
					<th><?php _ex( 'Email de l\'expéditeur', 'wistify', 'tify' );?></th>
					<td><input type="text" name="campaign_message_options[from_email]" value="<?php echo $this->item->campaign_message_options['from_email'];?>" class="widefat" /></td>
				</tr>
				<tr>
					<th><?php _ex( 'Nom de l\'expéditeur', 'wistify', 'tify' );?></th>
					<td><input type="text" name="campaign_message_options[from_name]" value="<?php echo $this->item->campaign_message_options['from_name'];?>" class="widefat" /></td>
				</tr>
				<tr>
					<th><?php _ex( 'Email de réponse', 'wistify', 'tify' );?></th>
					<td><input type="text" name="campaign_message_options[headers][Reply-To]" value="<?php echo $this->item->campaign_message_options['headers']['Reply-To']?>" class="widefat" /></td>
				</tr>
				<tr>
					<th><?php _e( 'Marqué le message comme important', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => 'campaign_message_options[important]', 'checked' => ( $this->item->campaign_message_options['important'] ? $this->item->campaign_message_options['important'] : 'off' ) ) );?></td>
				</tr>
				<tr>
					<th><?php _e( 'Suivi de l\'ouverture des messages', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => 'campaign_message_options[track_opens]', 'checked' => ( $this->item->campaign_message_options['track_opens'] ? $this->item->campaign_message_options['track_opens'] : 'on' ) ) );?></td>
				</tr>
				<tr>
					<th><?php _e( 'Suivi des clics depuis les liens du message', 'tify' );?></th>
					<td><?php tify_control_switch( array( 'name' => 'campaign_message_options[track_clicks]', 'checked' => ( $this->item->campaign_message_options['track_clicks'] ? $this->item->campaign_message_options['track_clicks'] : 'on'  ) ) );?></td>
				</tr>
			</tbody>
		</table>
	<?php
	}

	/** == ETAPE #5 - OPTIONS D'ENVOI == **/
	public function step_5( )
	{
		$defaults = array(
			'test_email' 	=> wp_get_current_user()->user_email,
		);
		$this->item->campaign_send_options = wp_parse_args( $this->item->campaign_send_options, $defaults );

		$total  = 0;
		if( isset( $this->item->campaign_recipients['wystify_subscriber'] ) )
			foreach( $this->item->campaign_recipients['wystify_subscriber'] as $subscriber_id )
				if( $this->master->db->subscriber->select()->cell_by_id( $subscriber_id, 'status' ) === 'registred' )
					$total++;
		if( isset( $this->item->campaign_recipients['wystify_mailing_list'] ) )
			foreach( $this->item->campaign_recipients['wystify_mailing_list'] as $list_id )
				$total += $this->master->db->subscriber->select()->count( array( 'list_id' => $list_id, 'status' => 'registred' ) );
			
		$set_send_active = $this->master->db_queue->has_campaign(  $this->item->campaign_id );	
	?>
		<div class="tifybox">
			<h3><?php _e( 'Tester la campagne', 'tify' );?></h3>
			<div class="inside">
				<div id="send-test">		
					<div id="send-test-submit" data-tags="wistify_campaign-<?php echo $this->item->campaign_id;?>">
						<?php wp_nonce_field( 'wistify_messages_send', '_wty_messages_send_ajax_nonce', false ); ?>
						<input type="text" id="wistify_messages_send_to_email" name="campaign_send_options[test_email]" value="<?php echo $this->item->campaign_send_options['test_email'];?>" size="80" autocomplete="off"/>
						<input type="hidden" id="wistify_messages_send_subject" value="[TEST] <?php echo esc_attr( $this->item->campaign_message_options['subject'] );?>"/>
						<input type="hidden" id="wistify_messages_send_service_account" value="<?php echo tify_generate_token();?>"/>
						
						<button class="button-secondary"><i class="fa fa-paper-plane"></i><div class="tify_spinner"></div></button>	
					</div>
					<em style="margin-top:5px;display:block;color:#999;font-size:0.9em;"><?php _e( 'La visualisation en ligne et le lien de désinscription resteront actifs pendant 60 minutes après l\'expédition de ce mail.<br />La désinscription n\'affectera pas les abonnements relatifs à l\'email d\'expédition de test, le système procède à une désinscription pour un compte de service fictif.', 'tify');?></em>
					<div id="send-test-resp">
						<span class="email"></span>
						<span class="status"></span>
						<span class="_id"></span>
						<span class="reject_reason"></span>
					</div>
				</div>	
			</div>	
		</div>
		
		<div id="prepare" class="tifybox">
			<h3><?php _e( 'Préparation de la campagne', 'tify' );?></h3>
			<div class="inside">
				<div id="logs">
				 	<div class="duplicates">
						<h5><?php _e( 'Doublons supprimés', 'tify' );?> (<span class="total"></span>)</h5>
						<ul></ul>
					</div>
					<div class="invalids">
						<h5><?php _e( 'Emails invalides', 'tify' );?> (<span class="total"></span>)</h5>
						<ul></ul>
					</div>
					<div class="not_found">
						<h5><?php _e( 'Correspondances introuvables', 'tify' );?> (<span class="total"></span>)</h5>
						<ul></ul>
					</div>
				</div>
									
				<div id="actions">		
					<a href="#" id="campaign-prepare" class="button-primary button-wistify-action" style="margin-top:10px;"><?php _e( 'Préparer la campagne', 'tify' );?></a>
				</div>
				
				<div id="totals">
					<h5><?php _e( 'Totaux', 'tify' );?> : </h5>
					<ul>
						<li class="expected"><?php _e( 'Attendus', 'tify' );?> : <span class="value"><?php echo $total;?></span></li>
						<li class="processed"><?php _e( 'Mis en file', 'tify' );?> : <span class="value"><?php echo $this->master->db_queue->count_items( array( 'campaign_id' => $this->item->campaign_id ) );?></span></li>
					</ul>
				</div> 
			</div>	
		</div>		
					
		<?php tify_progress();?>
	<?php	
	}
	
	/** Affichage des actions secondaires de la boîte de soumission
	public function minor_actions(){?>
		<?php if( $this->current_step === 5 ) : $set_send_active = $this->master->db_queue->has_campaign(  $this->item->campaign_id );?>
		<div id="programmation">
			<h4><?php _e( 'Date d\'envoi : ', 'tify' );?></h4>
			<div class="inside">
				<ul>
					<li>
					<?php tify_control_touch_time( 
			 			array( 
			 				'name' 		=> 'campaign_send_datetime', 
			 				'id' 		=> 'campaign_send_datetime', 
			 				'value' 	=> ( $this->item->campaign_send_datetime !== '0000-00-00 00:00:00' ) ? $this->item->campaign_send_datetime : date( 'Y-m-d H:00:00', current_time( 'timestamp' ) ),
							'hour'		=> '<span style="vertical-align:middle;display:inline-block;height:1em;margin-bottom:0.5em;">h 00</span>',
							'minute'	=> false,
							'second'	=> false,
							'time_sep'	=> false
						) 
					);?> 
					</li>
					
					<li id="set_send" class="<?php echo ( $set_send_active ) ? 'active': '' ;?>">
						<label>
							<strong><?php _e( 'Envoyer la campagne', 'tify' );?></strong>&nbsp;&nbsp;
							<input type="checkbox" name="campaign_status" value="send" <?php echo ( ! $set_send_active ) ? 'disabled="disabled" autocomplete="off"': '' ;?> />
						</label>
					</li>
				</ul>			 	
			</div>	
		</div>
		<hr>
		<?php endif;?>
		<?php if( ( $this->current_step > 1 ) || ( $this->item->campaign_step > $this->current_step ) ) :?>
		<div class="nav">
			<?php if( $this->current_step > 1 ) :?>
			<a href="<?php echo add_query_arg( array( 'step' => $this->current_step-1, $this->primary_key => $this->item->campaign_id ), $this->base_url );?>" class="prev button-secondary"><?php _e( 'Étape précédente', 'tify' );?></a>
			<?php endif;?>
			<?php if( $this->item->campaign_step > $this->current_step ) :?>
			<a href="<?php echo add_query_arg( array( 'step' => $this->current_step+1, $this->primary_key => $this->item->campaign_id ), $this->base_url );?>" class="next button-secondary"><?php _e( 'Étape suivante', 'tify' );?></a>
			<?php endif;?>
		</div>
		<?php endif;?>
	<?php
	}	 == **/
	
	/** == Affichage des actions principales de la boîte de soumission 
	public function major_actions(){
	?>
		<div class="deleting">			
			<a href="<?php echo wp_nonce_url( 
	        					add_query_arg( 
        							array( 
        								'page' 				=> $_REQUEST['page'], 
        								'action' 			=> 'trash', 
        								$this->primary_key 			=> $this->item->{$this->db->primary_key}
									),
									admin_url( 'admin.php' ) 
								),
								'wistify_campaign_trash_'. $this->item->{$this->db->primary_key} 
							);?>" title="<?php _e( 'Mise à la corbeille de l\'élément', 'tify' );?>">
				<?php _e( 'Déplacer dans la corbeille', 'tify' );?>
			</a>
		</div>	
		<div class="publishing">
			<?php submit_button( __( 'Sauver les modifications', 'tify' ), 'primary', 'submit', false ); ?>
		</div>
	<?php
	}== **/
	
	/* = TRAITEMENT DES DONNEES = */
	/** == Traitement des données à enregistrer == **/
	public function parse_postdata( $data )
	{
		// Identifiant
		if( ! empty( $data['campaign_id'] ) )
			 $data['campaign_id'] = (int) $data['campaign_id'];
		// Token
		if( empty( $data['campaign_uid'] ) )
			 $data['campaign_uid'] = tify_generate_token();
		// Auteur
		if( empty( $data['campaign_author'] ) )
			$data['campaign_author'] = get_current_user_id();
		// Date de création
		if( empty( $data['campaign_date'] ) || ( $data['campaign_date'] === '0000-00-00 00:00:00' ) )
			$data['campaign_date'] = current_time( 'mysql', false );
		// Date de modification
		if( $data['campaign_date'] !== '0000-00-00 00:00:00' ) :
			$data['campaign_modified'] = current_time( 'mysql', false );
			$data['item_meta']['_edit_last'] = $data['user_ID'];
		endif;
		// Status
		if( ! empty( $data['campaign_title'] ) && ( $data['campaign_status'] === 'auto-draft' ) )
			 $data['campaign_status'] = 'edit';				
		/// Etape
		if( ( $data['campaign_step'] < 5 ) && ( (int) $data['campaign_step'] === $this->current_step ) )
			$data['campaign_step'] = (int) ++$data['campaign_step'];		
		// Titre
		if( ! empty( $data['campaign_title'] ) )
			 $data['campaign_title'] = wp_unslash( $data['campaign_title'] );
		// Description
		if( ! empty( $data['campaign_description'] ) )
			$data['campaign_description'] = wp_unslash( $data['campaign_description'] );
		// Contenu HTML
		if( ! empty( $data['campaign_content_html'] ) )
			$data['campaign_content_html'] = wp_unslash( $data['campaign_content_html'] );
		// Sujet du message
		if( ! empty( $data['campaign_message_options']['subject'] ) )
			 $data['campaign_message_options']['subject'] = wp_unslash( $data['campaign_message_options']['subject'] );
		// Destinataires
		if( ( $this->current_step === 3 ) && empty( $data['campaign_recipients'] ) )
			$data['campaign_recipients'] = array();	  
				
		return $data;
	}

	/** == Éxecution de l'action - édition == **/
	public function process_bulk_action_edit()
	{			
		$this->item = $this->table->select()->row_by_id( (int) $_GET[$this->primary_key] );
		
		if ( ! $this->item )
			wp_die( __( 'Vous tentez de modifier un contenu qui n’existe pas. Peut-être a-t-il été supprimé ?', 'tify' ) );		
		if ( ! current_user_can( 'edit_posts' ) )
			wp_die( __( 'Vous n’avez pas l’autorisation de modifier ce contenu.', 'tify' ) );
		
		if( ! in_array( $this->item->campaign_status, array( 'edit', 'ready', 'draft', 'auto-draft' ) ) )
			wp_die( sprintf( __( 'Le statut actuel de la campagne ne permet pas de la modifier.', 'tify' ) ) );
		
		if( ! isset( $_GET['step'] ) ) :
			$sendback = add_query_arg( array( $this->primary_key => $this->item->{$this->primary_key}, 'step' => $this->item->campaign_step ), $this->base_url );		
			wp_redirect( $sendback );
			exit;
		elseif( (int) $_GET['step'] > $this->item->campaign_step ) :
			wp_die( __( 'Ne soyez pas trop impatient et complétez d\'abord toutes les étapes précédentes', 'tify' ) );		
		endif;					
	}
}