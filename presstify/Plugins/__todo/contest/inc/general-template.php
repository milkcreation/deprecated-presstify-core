<?php
/* = HELPER = */
/** == == **/
function tify_contest_tpl( $template_name ){
	global $tify_contest;
	
	$template_name = 'tpl_'. $template_name;
	
	$args = $args = array_slice( func_get_args(), 1 );

	if( method_exists( $tify_contest->template, $template_name ) )
		return call_user_func_array( array( $tify_contest->template, $template_name ), $args );
}

/* = CLASSE = */
class tiFy_Contest_Template{
	public 	// Référence
			$iscroll,
			$master;
	
	/* = CONSTRUCTEUR = */
	function __construct( tiFy_Contest_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		$this->iscroll = New tiFy_Contest_TemplateInfiniteScroll;
	}
	
	/* = ÉLÉMENTS DE TEMPLATE = */
	/** == FORMULAIRE D'AUTHENTIFICATION == **/
	/*** === Affichage du formulaire d'authentification === ***/
	function tpl_login_form( $args = array() ){
		$defaults = array(
			'echo'					=> true,
			'redirect' 				=> ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'form_id' 				=> 'tify_contest-loginform',
			'label_username' 		=> _x( 'Identifiant', 'tify_contest_login_form', 'tify' ),
			'label_password' 		=> _x( 'Mot de passe', 'tify_contest_login_form', 'tify' ),
			'placeholder_username' 	=> _x( 'Identifiant', 'tify_contest_login_form', 'tify' ),
			'placeholder_password' 	=> _x( 'Mot de passe', 'tify_contest_login_form', 'tify' ),
			'label_remember' 		=> _x( 'Se souvenir de moi', 'tify_contest_login_form', 'tify' ),
			'label_log_in' 			=> _x( 'Connexion', 'tify_contest_login_form', 'tify' ),
			'id_username' 			=> 'tify_contest-user-login',
			'id_password' 			=> 'tify_contest-user-pass',
			'id_remember'	 		=> 'tify_contest-rememberme',
			'id_submit' 			=> 'tify_contest-submit',
			'remember' 				=> true,
			'value_username' 		=> '',
			'value_remember' 		=> false
		);
		$args = apply_filters( 'tify_contest_login_form_defaults', wp_parse_args( $args, $defaults ) );
		
		$login_form_top 	= apply_filters( 'login_form_top', '', $args );
		$login_form_middle 	= apply_filters( 'login_form_middle', '', $args );
		$login_form_bottom 	= apply_filters( 'login_form_bottom', '', $args );
		
		$login_form_top 	= apply_filters( 'tify_contest_login_form_top', $login_form_top, $args );
		$login_form_middle 	= apply_filters( 'tify_contest_login_form_middle', $login_form_middle, $args );
		$login_form_bottom 	= apply_filters( 'tify_contest_login_form_bottom', $login_form_bottom, $args );

		$form = '
			<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '" method="post">
				' . $login_form_top . '
				<p class="login-username">
					<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
					<input type="text" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" placeholder="' . esc_attr( $args['placeholder_username'] ) . '" size="20" />
				</p>
				<p class="login-password">
					<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
					<input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" placeholder="' . esc_attr( $args['placeholder_password'] ) . '" size="20" />
				</p>
				' . $login_form_middle . '
				' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
				<p class="login-submit">
					<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="button-primary" value="' . esc_attr( $args['label_log_in'] ) . '" />
					<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
				</p>
				' . $login_form_bottom . '
			</form>';		
		$form = apply_filters( 'tify_contest_login_form', $form, $args, $this );
					
		if ( $args['echo'] )
			echo $form;
		else
			return $form;
	}
	
	/*** === Affichage du bouton de récupération de mot de passe oublié === ***/
	function tpl_lostpassword_link( $args = array() ){
		$defaults = array(
			'echo'		=> true,
			'redirect' 	=> '',
			'label'		=> __( 'Mot de passe oublié', 'tify' ),
			'title'		=> __( 'Récupération de mot de passe perdu', 'tify' )	
		);
		$args = wp_parse_args( $args, $defaults );

		$output = "<a href=\"". wp_lostpassword_url( $args['redirect'] ) ."\" title=\"". $args['title'] ."\" class=\"tify_forum-lostpassword_button\">". $args['label'] ."</a>";
		$output = apply_filters( 'tify_contest_lostpassword_link', $output, $args, $this );
		
		if ( $args['echo'] )
			echo $output;
		else
			return $output;
	}
	
	/*** === Affichage du bouton de déconnection === ***/
	function tpl_logout_button( $args = array() ){
		$defaults = array(
			'redirect' 	=> home_url(),
			'text'		=> __( 'Déconnexion', 'tify' ),
			'echo'		=> true
		);
		$args = wp_parse_args( $args, $defaults );
		
		$output  = "";
		$output .= "<a href=\"". wp_logout_url( $args['redirect'] ) ."\" title=\"". __( 'Se déconnecter du site', 'tify' ) ."\" class=\"tify_forum-logout_button\">". $args['text'] ."</a>";
		$output = apply_filters( 'tify_contest_logout_button', $output, $args, $this );
		
		if ( $args['echo'] )
			echo $output;
		else
			return $output;
	}
	
	/** == INSCRIPTION == **/
	/*** === Formulaire d'inscription === ***/
	function tpl_subscribe_form( $echo = true ){
		return tify_form_display( $this->master->forms->subscribe_form_id, $echo );
	}
	
	/*** === Bouton d'accès au formulaire d'inscription === ***/
	function tpl_subscribe_button( $args = array() ){
		$defaults = array(
			'url' 	=> $this->master->hook_page_permalink(),
			'text'	=> __( 'S\'inscrire', 'tify' )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$subscribe_link = esc_url( add_query_arg( array( 'tify_forum' => 'subscribe' ), $args['url'] ) );
		
		$output  = "";
		$output .= "<a href=\"". $subscribe_link ."\" title=\"". __( 'Inscription au forum', 'tify' ) ."\" class=\"tify_forum-subscribe_button\">". $args['text'] ."</a>";
		
		return apply_filters( 'tify_forum_subscribe_button', $output, $args, $this );
	}
	
	/*** === Bouton d'accès aux réglages des paramètres du compte === ***/
	function tpl_account_button( $args = array() ){
		// Bypass
		if( ! $this->master->contributors->has_account( get_current_user_id() ) )
			return;
		$defaults = array(
			'url'	=> $this->master->hook_page_permalink(),
			'text'	=> __( 'Modifier mes paramètres', 'tify' )
		);
		$args = wp_parse_args( $args, $defaults );
		
		$account_link = esc_url( add_query_arg( array( 'tify_forum' => 'account' ), $args['url'] ) );
		
		$output  = "";
		$output .= "<a href=\"". $account_link ."\" title=\"". __( 'Modification des paramètres du compte', 'tify' ) ."\" class=\"tify_forum-account_button\">". $args['text'] ."</a>";
		
		return apply_filters( 'tify_forum_account_button', $output, $args, $this );
	}
	
	/** = VOTES == **/
	/*** === Formulaire de soumission de vote === ***/
	function tpl_poll_form( $part_id = null, $echo = true ){
		if( ! $part_id )
			$part_id = get_query_var( 'tify_contest_part', 0 );
		if( current_user_can( 'tify_contest_poll', $part_id ) )
			return tify_form_display( 'tify_contest_poll_submit', $echo );
		elseif( $echo )
			echo tify_contest_error_message();	
		else
			return tify_contest_error_message();	
	}
	
	/** == PARTICIPATIONS == **/
	/*** === Formulaire de participation === ***/
	function tpl_participation_form( $contest_id, $echo = true ){
		return tify_form_display( $contest_id, $echo );
	}
	
	/*** === Liste des participations === ***/
	function tpl_participations_list( $query_args = array(), $echo = true ){

	}

	/*** === Affichage d'une participation === ***/
	function tpl_participation_display( $echo = true ){

	}

	/** == CLASSEMENT == **/
	public function tpl_ranking_updated( $args = array() ){
		if( ! $last_update = get_option( 'tify_contest_ranking_last_handle_ts' ) )
			return;
		$defaults = array(
			'text'			=> __( 'Dernière mise à jour du classement : %s', 'tify' ),
			'date_format'	=> 'd/m/Y H:i:s',
			'echo'			=> true
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );		
		
		$output = sprintf( $text, date_i18n( $date_format,( $last_update + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) ) );
		if( $echo )
			echo $output;
		else
			return $output;	
	}

	/** == == **/
	public function tpl_iscroll_button( $args = array(), $echo = true ){
		return $this->iscroll->display( $args, $echo );
	}
	
	/* = ERREURS = */
	/** == Vérifie l'existance d'erreurs == **/
	public function get_errors(){
		if( is_wp_error( $this->master->errors ) )
			return $this->master->errors;
	}
	
	/** == Récupération du titre d'erreur == **/
	public function error_title( $code = '' ){
		if( $this->get_errors() && ( $data = $this->master->errors->get_error_data( $code ) ) && isset( $data['title'] ) )
			return $data['title'];
	}
	
	/** == Récupération du message d'erreur == **/
	public function error_message( $code = '' ){
		if( $this->get_errors() )
			return $this->master->errors->get_error_message( $code );
	}	
}

class tiFy_Contest_TemplateInfiniteScroll{
	/* = ARGUMENTS = */
	public	// Chemins
			$dir,
			$uri,
			// Configuration
			$instance,
			$config = array();
			
	/* = CONTRUCTEUR = */
	function __construct(){
		// Définition des chemins
		$this->dir = dirname( __FILE__ );
		$this->uri = plugin_dir_url( __FILE__ );
		
		// Actions et Filtres Wordpress
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'wp_ajax_tify_contest_template_infinite_scroll', array( $this, 'wp_ajax' ) );
		add_action( 'wp_ajax_nopriv_tify_contest_template_infinite_scroll', array( $this, 'wp_ajax' ) );	
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	function wp_enqueue_scripts(){
		wp_enqueue_script( 'jquery' );
	}
	
	/** == Chargement des post == **/
	function wp_ajax(){
		$contest_id = $_POST['query_args']['contest_id'];
		
		tify_query( 'tify_contest_part' )->query( 
			array( 
					'contest_id' 	=> $contest_id, 
					'status' 		=> ( current_user_can( 'administrator' ) ? 'any' : 'publish' ), 
					'orderby' 		=> 'item__in', 
					'include' 		=> tiFy_Contest_Ranking::get_contest_parts_ids( $contest_id, $_POST['per_page'], $_POST['from'] )
				) 
			);
		$output = "";
		if( tify_query( 'tify_contest_part' )->have_items() &&  tify_query( 'tify_contest_part' )->found_items > $_POST['from'] ) :
			$n = 0; 
			while( tify_query( 'tify_contest_part' )->have_items() ) : tify_query( 'tify_contest_part' )->the_item();
				$sign = ( $n++%2 )? '+' : '-'; $neg = ( $n%2 )? '+' : '-'; $deg = rand( 2,10 );
				ob_start();
				get_template_part( 'inc/challenges/templates/challenge-participation-link');				
				$output .= 	"<li id=\"". tify_query( 'tify_contest_part' )->found_items."\" style=\"-ms-transform:rotate({$sign}.{$deg}deg);-webkit-transform:rotate({$sign}.{$deg}deg);transform:rotate({$sign}.{$deg}deg);\">".
							ob_get_contents() .
							"</li>";
				ob_end_clean();
			endwhile;
		else :
			$output .= "<!-- tiFy_Contest_TemplateInfiniteScroll_End -->";
		endif;
		echo $output;
		exit;
	}

	/** == Mise en file des scripts == **/
	function wp_footer(){
		$config = self::getConfig( $this->instance );
	?><script type="text/javascript">/* <![CDATA[ */
		var tify_infinite_scroll_xhr;
		jQuery( document ).ready( function($){
			var handler = '#<?php echo $config['id'];?>',
				target	= '<?php echo $config['target'];?>';
				$target = ( ! target ) ? $( handler ).prev() : $( target );
				
			$( window ).scroll( function( e ) {
				if( ( tify_infinite_scroll_xhr === undefined ) && ! $(this).hasClass( 'ty_iscroll_complete' ) && isScrolledIntoView( $( handler ) ) )
					 $( handler ).trigger( 'click' );
			});
		
			$( handler ).click( function(e){
				if( $(this).hasClass( 'ty_iscroll_complete' ) )
					return false;
				
				$target.addClass( 'ty_iscroll_process' );
				$( handler ).addClass( 'ty_iscroll_process' );
					
				var action		= $(this).data( 'action' ),
					query_args 	= $(this).data( 'query_args' ),
					per_page 	= $(this).data( 'per_page' ),
					template 	= $(this).data( 'template' ),
					from 		= $( '> *', $target ).size();				
					
				tify_infinite_scroll_xhr = $.post( 
					tify_ajaxurl,
					{ action: action, query_args : query_args, per_page : per_page, template: template, from : from },
					function( resp ){
						$target.removeClass( 'ty_iscroll_process' );
						$( handler ).removeClass( 'ty_iscroll_process' );	
							
						$target.append( resp );
						var complete = resp.match(/<!-- tiFy_Contest_TemplateInfiniteScroll_End -->/);
						if( complete ){
							$target.addClass( 'ty_iscroll_complete' );
							$( handler ).addClass( 'ty_iscroll_complete' );
						}
						
						tify_infinite_scroll_xhr.abort();
						tify_infinite_scroll_xhr = undefined;
					}
				);
			});
		
			function isScrolledIntoView( elem ) {
				var docViewTop = $(window).scrollTop();
				var docViewBottom = docViewTop + $(window).height();
				var elemOffset = 0;
				  
				if( elem.data('offset') != undefined )
					elemOffset = elem.data( 'offset' );
			
				var elemTop = $(elem).offset().top;
				var elemBottom = elemTop + $(elem).outerHeight(true);
			  
				if( elemOffset != 0 )
					if(docViewTop - elemTop >= 0)
						elemTop = $(elem).offset().top + elemOffset;
					else
						elemBottom = elemTop + $(elem).outerHeight(true) - elemOffset;
			  
				if( ( elemBottom <= docViewBottom ) && ( elemTop >= docViewTop ) )
					return true;
			}
		});
		/* ]]> */</script><?php	
	}

	/* = GENERAL TEMPLATE = */
	function display( $args = array(), $echo = true ){
		global $wp_query;
		
		// Incrémentation de l'intance
		$this->instance++;		

		// Traitement des arguments
		$defaults = array(
			'id'			=> 'tify_contest_template_infinite_scroll-'. $this->instance,
			'class'			=> '',
			'text'			=> __( 'Voir plus', 'tify' ),
			'action'		=> 'tify_contest_template_infinite_scroll',
			'query_args' 	=> array(),
			'target'		=> '',
			'per_page'		=> 20,
			'template'		=> ''
		);	
		$config = wp_parse_args( $args, $defaults );
		self::setConfig( $this->instance, $config );
		
		extract( $config );	
			
		// Mise en file des scripts
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		
		$output  = "";
		$output .= 	"<a id=\"{$id}\"".
					" class=\"tify_contest_tpl_iscroll $class\"".
					" href=\"#tify_contest_template_infinite_scroll-{$this->instance}\"".
					" data-action=\"{$action}\"".
					" data-query_args=\"". ( htmlentities( json_encode( $query_args ) ) ) ."\"".
					" data-target=\"{$target}\"".
					" data-per_page=\"{$per_page}\"".
					" data-template=\"{$template}\"".
					">{$text}</a>";
		
		if( $echo )
			echo $output;
		else	
			return $output;	
	}
}