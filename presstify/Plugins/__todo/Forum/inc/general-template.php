<?php
class tiFy_Forum_TemplateMain{
	public 	//
			$current_topic = null,
			$contribution_form_error,
			$topic_query,
			$contrib_query,
			
			// Référence
			$master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_Forum_Master $master ){
		// Instanciation de la classe de référence
		$this->master = $master;
		
		// Actions et Filtres Wordpress
		add_action( 'init', array( $this, 'wp_init' ) );
		add_action( 'wp', array( $this, 'wp' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_filter( 'the_content', array( $this, 'wp_the_content' ) );		
				
		// Actions et Filtres PressTiFy
		tify_require_lib( 'login' );
		$this->login = new tiFy_Login( 'tify_forum' );		
		
		add_filter( 'mktzr_breadcrumb_is_page', array( $this, 'tify_breadcrumb_is_page' ), 10, 3 );
		
		// Actions et filtres tiFy Forum		
		add_filter( 'tify_forum_contrib_form_defaults', array( $this, 'contrib_form_defaults' ) );		
	}
	
	/* = ACTIONS ET FILTRES WORDPRESS = */
	/** == Initialisation globale == **/
	final public function wp_init(){		
		$this->login->redirect_to = $this->master->hook_page_permalink();		
	}
	
	/** == == **/
	final public function wp(){
		if( get_query_var( 'tify_forum', null ) === 'topic' )
			$this->current_topic = ( ! empty( $_REQUEST['id'] ) ) ? $this->master->topic->get( (int) $_REQUEST['id'] ) : null;
	}
	
	/** == Mise en file des scripts == **/
	final public function wp_enqueue_scripts(){
		wp_enqueue_style( 'tify_forum-theme', $this->master->uri ."/css/theme.css", array( ), '151202' );

		switch( get_query_var( 'tify_forum', 'home' ) ) :
			default :
			case 'home' :
				tify_control_enqueue( 'quicktags_editor' );
				break;
			case 'subscribe' :

				break;
			case 'account' :

				break;
			case 'topic' :
				tify_control_enqueue( 'quicktags_editor' );
				break;
		endswitch;
	}
	
	/** == Modification du contenu == **/
	function wp_the_content( $content ){
		// Bypass
		if( ! in_the_loop() )
			return $content;
		if( ! is_singular() )
			return $content;		
		if( $this->master->hook_page_id() != get_the_ID() )
			return;
		
		$template = get_query_var( 'tify_forum', 'home' ); 
	
		// Reset Content
		$content  = "";		
		switch( $template ) :
			default :
			case 'home' :
				if( ! is_user_logged_in() ) :
					$content .= $this->tpl_login_form();
					$content .= $this->tpl_lostpassword_button() ."&nbsp;". $this->tpl_subscribe_button();
				else :
					$content .= $this->tpl_account_button() ."&nbsp;". $this->tpl_logout_button();
				endif;
				$content .= $this->tpl_topic_list();
				$content .= $this->tpl_topic_pagination();
				$content .= $this->tpl_topic_form();
									
				$content = apply_filters( 'tify_forum_template_home', $content, $this );	
				break;
			case 'subscribe' :
				if( is_user_logged_in() ) :
					$content .= "<p class=\"tify_forum-notice\">". __( 'Vous êtes déjà connecté', 'tify' ) ."<p>";
				else :
					$content .= "<h3>". __( 'Inscription', 'tify' ) ."</h3>";
					$content .= "<div id=\"tify_forum-subscribe_form\">". $this->tpl_subscribe_form() ."</div>";
				endif;
								
				$content = apply_filters( 'tify_forum_template_subscribe', $content, $this );
				break;
			case 'account' :
				if( ! is_user_logged_in() ) :
					$content .= __( 'Cet espace est réservé aux utilisateurs connectés', 'tify' );
				elseif( ! $this->master->contributor->has_account() ) :
					$content .= __( 'La modification de paramètres du compte est réservée uniquement aux contributeurs de forum', 'tify' );
				else :
					$content .= "<h3>". __( 'Modifier mes paramètres', 'tify' ) ."</h3>";
					$content .= "<div id=\"tify_forum-subscribe_form\">". $this->tpl_subscribe_form() ."</div>";
				endif;				
				$content = apply_filters( 'tify_forum_template_account', $content, $this );
				break;
			case 'topic' :				
				$content .= $this->tpl_contribution_list();
				$content .= $this->tpl_contribution_pagination();
				$content .= $this->tpl_contribution_form();
				$content = apply_filters( 'tify_forum_template_topic', $content, $this );
				break;
		endswitch;
	 
		return $content;		
	}

	/* = ACTIONS ET FILTRES PRESSTIFY = */	
	/** == Modification du Fil d'Ariane des pages de templates == **/
	function tify_breadcrumb_is_page( $output, $separator, $ancestors ){
		// Bypass
		if( get_the_ID() !== $this->master->hook_page_id() )
			return $output;
		if( ! $template = get_query_var( 'tify_forum', 'home' ) )
			return $output;
		if( $template == 'home' )
			return $output;
		
		$output = $ancestors . $separator . "<a href=\"". get_permalink() ."\" title=\"". __( 'Retour vers l\'accueil des forums', 'tify' ) ."\">". get_the_title() ."</a>" . $separator;
			
		switch( $template ) :
			case 'subscribe' :
				$output .= "<span class=\"current\">". __( 'Inscription', 'tify' ) ."</span>";
				break;
			case 'account' :
				$output .= "<span class=\"current\">". __( 'Mon compte', 'tify' ) ."</span>";
				break;
			case 'topic' :				
				$output .= "<span class=\"current\">". sprintf( __( 'Sujet : %s', 'tify' ), $this->current_topic->topic_title ) ."</span>";
				break;
		endswitch;
		
		return $output;
	}
	
	/* = ÉLÉMENTS DE TEMPLATE = */
	/** == FORMULAIRE D'AUTHENTIFICATION ** ==/
	/*** === Affichage du formulaire d'authentification === ***/
	public function tpl_login_form( $args = array() ){
		return $this->login->form();
	}
	
	/*** === Affichage du bouton de récupération de mot de passe oublié === ***/
	public function tpl_lostpassword_button( $args = array() ){
		return $this->login->lostpassword_link();
	}
	
	/*** === Affichage du bouton de déconnection === ***/
	public function tpl_logout_button( $args = array() ){
		return $this->login->logout_link();
	}
	
	/** == FORMULAIRE D'INSCRIPTION == **/
	/*** === Affichage du formulaire d'inscription === ***/
	public function tpl_subscribe_form(){
		return tify_form_display( $this->master->forms->subscribe_form_id, false );
	}
	
	/*** === Affichage du bouton d'accès au formulaire d'inscription === ***/
	public function tpl_subscribe_button( $args = array() ){
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
	public function tpl_account_button( $args = array() ){
		// Bypass
		if( ! $this->master->contributor->has_account( get_current_user_id() ) )
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
	
	/** == SUJETS DE FORUM == **/	
	/*** === Affichage de la liste des sujets == **/
	public function tpl_topic_list(){
		$this->topic_query = tify_query( 'tify_forum_topic' );
		$this->topic_query->query( array( 'per_page' => 10, 'paged' => ( ( $paged = get_query_var( 'paged', 0 ) ) ? $paged : 1 ) ) );
		
		$output = "";
		if( $this->topic_query->have_items() ) :
			$output .= "<div class=\"tify_forum-topic_list\">\n";
			$output .= "\t<div class=\"thead\">\n";
			$output .= "\t\t<div class=\"tr\">\n";
			$output .= "\t\t\t<div class=\"th topic\">". __( 'Sujet', 'tify' ). "</div>\n";
			$output .= "\t\t\t<div class=\"th contrib_number\">". __( 'Réponses', 'tify' )."</div>\n";
			$output .= "\t\t\t<div class=\"th last_contrib\">". __( 'Dernière réponse', 'tify' )."</div>\n";
			$output .= "\t\t</div>\n";
			$output .= "\t</div>\n";
			$output .= "\t<div class=\"tbody\">\n";
			while( $this->topic_query->have_items() ) : $this->topic_query->the_item();
				$topic_link = add_query_arg( array( 'tify_forum' => 'topic', 'id' => tify_query_field( 'id' ) ), $this->master->hook_page_permalink() );
				$output .= "\t\t<div class=\"tr\">\n";
				$output .= "\t\t\t<div class=\"td topic\"><a href=\"". $topic_link ."\" title=\"". sprintf( __( 'Consulter le sujet : %s', 'tify' ), tify_query_field( 'title' ) ) ."\">". tify_query_field( 'title' ) ."</a></div>\n";
				$output .= "\t\t\t<div class=\"td contrib_number\">". ( ! empty( tify_query_field( 'contrib_count' ) ) ? (int) tify_query_field( 'contrib_count' ) : '0' ) ."</div>\n";
				$output .= "\t\t\t<div class=\"td last_contrib\">";
				if( $last = $this->master->contribution->last( tify_query_field( 'id' ) ) )	
					$output .= sprintf( __( 'par %s, le %s à %s', 'tify' ), $last->contrib_author, mysql2date( get_option( 'date_format'), $last->contrib_date ), mysql2date( get_option( 'time_format'), $last->contrib_date ) );
				else
					$output .= __( 'Il n\'y a pour l\'instant aucune discussion sur ce sujet', 'tify' );
				$output .= "\t\t\t</div>\n";
				$output .= "\t\t</div>\n";
			endwhile;
			$output .= "\t</div>\n";
			$output .= "</div>\n";			
		endif;
		
		return apply_filters( 'tify_forum_topic_list', $output );
	}

	/*** === === ***/
	public function tpl_topic_pagination(){
		return tify_pagination( 
			array(
				'class'	=> 'pagination',
				'query'	=> $this->topic_query,
				'echo'	=> false
			)
		);
	}
		
	/*** === Affichage du formulaire de soumission de nouveau sujet === ***/
	public function tpl_topic_form(){
		$output  = "";
		$output .= "<div id=\"tify_forum-respond\">\n";
		$output .= "\t<form name=\"tify_forum_topic_form\" method=\"post\" action=\"". add_query_arg( array( 'action' => 'add_topic' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ."\">\n";
		$output .= "\t\t". wp_referer_field( false );
		$output .= "\t\t<label>". __( 'Titre du sujet', 'tify' ) ."</label><input type=\"text\" value=\"\"/>\n";
		$output .= "\t\t". tify_control_quicktags_editor( array( 'id' => 'tify_forum_topic_form', 'name' => 'content' ) );
		$output .= "\t\t<button type=\"submit\" name=\"tify_forum_topic_form-submit\" >". __( 'Contribuer', 'tify' ) ."</button>\n";
		$output .= "\t</form>\n";
		$output .= "</div>\n";		
		
		return $output;
	}
	
	/** == CONTRIBUTIONS == **/
	/*** === Affichage de la liste des contributions === ***/	
	public function tpl_contribution_list( ){
		$this->contrib_query = tify_query( 'tify_forum_contribution' );
		$this->contrib_query->query( array( 'contrib_topic_id' => $this->current_topic->topic_id, 'per_page' => 50, 'paged' => ( ( $paged = get_query_var( 'paged', 0 ) ) ? $paged : 1 ) ) );
		
		$output  = "";
		if( $this->contrib_query->have_items() ) :			
			$output .= "<div class=\"tify_forum-contrib_list\">\n";
			$output .= "\t<ul>\n";
			while( $this->contrib_query->have_items() ) : $this->contrib_query->the_item();
				$output .= "\t\t<li>";
				$output .= "\t\t\t<ul>";
				$output .= "\t\t\t\t<li>". tify_query_field( 'author' ) ."</li>";
				$output .= "\t\t\t\t<li>". tify_query_field( 'date' ) ."</li>";
				$output .= "\t\t\t\t<li>". tify_query_field( 'content' ) ."</li>";
				$output .= "\t\t\t</ul>";
				$output .= "\t\t</li>\n";
			endwhile;
			$output .= "\t</ul>\n";
			$output .= "</div>\n";
		else :
			$output .= __( 'Il n\'y a pour l\'instant aucune discussion sur ce sujet', 'tify' );
		endif;
		
		return $output;
	}
	
	/*** === === ***/
	public function tpl_contribution_pagination(){
		return tify_pagination( 
			array(
				'class'	=> 'pagination',
				'query'	=> $this->contrib_query,
				'echo'	=> false
			)
		);
	}
	
	/*** === Formulaire de contribution === ***/
	public function tpl_contribution_form() {
		$output  = "";

		if ( true === true ) :
			$output .= "<div id=\"tify_forum-respond\">\n";
			$output .= "\t<form method=\"post\" name=\"tify_forum_contribution_form\" action=\"". add_query_arg( array( 'action' => 'add_contribution' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) ."\">\n";
			$output .= "\t\t<input type=\"hidden\" name=\"contrib_topic_id\" value=\"". $this->current_topic->topic_id ."\" />\n";
			$output .= "\t\t". wp_referer_field( false );
			$output .= "\t\t". tify_control_quicktags_editor( array( 'id' => 'tify_forum_contrib-'. $_REQUEST['id'], 'name' => 'content' ) );
			$output .= "\t\t<button type=\"submit\" name=\"tify_forum_contribution_form-submit\">". __( 'Contribuer', 'tify' ) ."</button>\n";
			$output .= "\t</form>\n";
			$output .= "</div>\n";				
		else :
			$output .= "<p class=\"tify_forum-notice\">". __( 'Désolé mais les contributions de ce sujet sont closes', 'tify' ) ."</p>";
		endif;
		
		return $output;
	}
	
	/*** === Notification du formulaire de contribution === ***/
	public function tpl_contribution_form_error(){
		$output = "";	
		$output .=	"<ul>";
		if( is_wp_error( $this->contribution_form_error ) )	
			foreach( $this->contribution_form_error->get_messages() as $msg )	
				$output .= "<ol>{$msg}</ol>";	
		$output .= "</ul>";	
		
		return $output;
	}
}