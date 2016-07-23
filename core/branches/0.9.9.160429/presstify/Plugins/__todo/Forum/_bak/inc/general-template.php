<?php
/**
 * -------------------------------------------------------------------------------
 *	Forums Templates
 * -------------------------------------------------------------------------------
 *
 * @name 		Milkcreation Forums
 * @package    	G!PRESS SNCF PROXIMITES - Espaces Collaboratifs
 * @copyright 	Milkcreation 2012
 * @link 		http://g-press.com/plugins/mocca
 * @author 		Jordy Manner
 * @version 	1.1
 */

/**
 * Affichage des forums sous forme de table 
 */
function mkforums_get_forum_topics_table( $args = array() ){
	// Traitement des arguments
	$defaults = array(
		'echo' => true,
		'table_columns' => array( 
			'title' => array( 
				'label' => __( 'Topics', 'milk-forums')
			), 
			'date' => array(
				'label' => __( 'Date', 'milk-forums')
			),
			'author' => array(
				'label' => __( 'Author', 'milk-forums')
			),
			'contrib' => array(
				'label' => __( 'Last contribs', 'milk-forums')
			),
		),
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);
	$args = wp_parse_args( $args, $defaults );
	extract( $args );	
		
	// Bypass	
	if( !$topics = mkforums_get_topics_for_forum( $args ) ) :
		$output  = "\n<h3>". __( 'This forum has no topics for this moment', 'milk-forums' )."</h3>";
	else :
		$output  = "\n<table class=\"mkforums-topics-table\">";
		// Caption
		$output .= "\n\t<caption>";
		$output .= "\n\t</caption>";
		// Head
		$output .= "\n\t<thead>";
		$output .= "\n\t\t<tr>";
		foreach( $table_columns as $table_column )
			$output .= "\n\t\t\t<th>{$table_column['label']}</th>";
		$output .= "\n\t\t</tr>";
		$output .= "\n\t</thead>";
		// Foot
		$output .= "\n\t<tfoot>";
		$output .= "\n\t\t<tr>";
		$output .= "\n\t\t\t<th colspan=\"".count($table_columns)."\"></th>";
		$output .= "\n\t\t</tr>";
		$output .= "\n\t</tfoot>";
		// Body
		$output .= "\n\t<tbody>";
		
		foreach( $topics as $topic ) :
			$output .= "\n\t\t<tr>";
			foreach( $table_columns as $topic_arg => $table_column )
				switch( $topic_arg ) :
					case 'title':
						$output .= "\n\t\t\t<td>". sprintf( '<a href="%1$s">%2$s<a>', get_permalink( $topic->ID),$topic->post_title ) ."</td>";
						break;
					case 'date':
						$output .= "\n\t\t\t<td>". sprintf( "%s", get_the_time( get_option('date_format'), $topic->ID ) ) ."</td>";		
						break;
					case 'author':
						$output .= "\n\t\t\t<td>". sprintf( "%s", get_the_author_meta( 'display_name', $topic->post_author ) ) ."</td>";	
						break;
					case 'contrib':
						if( $last_comment = get_comments( array( 'post_id' => $topic->ID, 'number' => 1, 'status' => 'approve' ) ) )
							$output .= "\n\t\t\t<td>". sprintf( __( 'on %s by %s', 'milk-forums' ), $last_comment[0]->comment_date_gmt, $last_comment[0]->comment_author ) ."</td>";	
						else
							$output .= "\n\t\t\t<td>".__( 'No contribution for this moment', 'milk-forums' )."</td>";
						break;
				endswitch;	
			$output .= "\n\t\t</tr>";
		endforeach;
		$output .= "\n\t</tbody>";
		$output .= "\n</table>";
	endif;
	
	if( $echo )
		echo $output;
	else
		return $output;	
}

/**
 * Chargement du template de contribution
 */
function mkforums_contribs_template_load( ){
	if( is_singular( 'mktopics' ) )
		add_filter( 'comments_template', 'mkforums_contribs_template_render' );
}
add_action( 'wp', 'mkforums_contribs_template_load' );

/**
 * Appel du template de contribution
 */
function mkforums_contribs_template_render(){
	$contribs_template = apply_filters( 'mkforums_contribs_template', MKFORUMS_DIR . '/themes/default/contribs.php' );
	return $contribs_template;
}

/**
 * 
 */
function mkforums_list_contribs($args = array(), $comments = null ) {
	global $wp_query, $comment_alt, $comment_depth, $comment_thread_alt, $overridden_cpage, $in_comment_loop;

	$in_comment_loop = true;

	$comment_alt = $comment_thread_alt = 0;
	$comment_depth = 1;

	$defaults = array('walker' => null, 'max_depth' => '', 'style' => 'ul', 'callback' => null, 'end-callback' => null, 'type' => 'all',
		'page' => '', 'per_page' => '', 'avatar_size' => 32, 'reverse_top_level' => null, 'reverse_children' => '');

	$r = wp_parse_args( $args, $defaults );

	// Figure out what comments we'll be looping through ($_comments)
	if ( null !== $comments ) {
		$comments = (array) $comments;
		if ( empty($comments) )
			return;
			$_comments = $comments;
	} else {
		if ( empty($wp_query->comments) )
			return;
		$_comments = $wp_query->comments;
	}
	
	// Récupération
	$global_opts = mkforums_contribs_parse_params( get_option( 'mkforums_contribs_global_params', mkforums_contribs_default_params('global_params') ), 'global_params' );
	$email_opts = mkforums_contribs_parse_params( get_option( 'mkforums_contribs_email_params', mkforums_contribs_default_params('email_params') ), 'email_params' );
	$moderation_opts = mkforums_contribs_parse_params( get_option( 'mkforums_contribs_moderation_params', mkforums_contribs_default_params('moderation_params') ), 'moderation_params' );
		
	//	
	if ( '' === $r['per_page'] && $global_opts['page_contribs'] )
		$r['per_page'] = get_query_var('comments_per_page');

	if ( empty($r['per_page']) ) {
		$r['per_page'] = 0;
		$r['page'] = 0;
	}

	if ( '' === $r['max_depth'] ) {
		if ( $global_opts['thread_contribs'] )
			$r['max_depth'] = $global_opts['thread_contribs_depth'];
		else
			$r['max_depth'] = -1;
	}

	if ( '' === $r['page'] ) {
		if ( empty($overridden_cpage) ) {
			$r['page'] = get_query_var('cpage');
		} else {
			$threaded = ( -1 != $r['max_depth'] );
			$r['page'] = ( 'newest' == $global_opts['default_contribs_page'] ) ? get_comment_pages_count($_comments, $r['per_page'], $threaded) : 1;
			set_query_var( 'cpage', $r['page'] );
		}
	}
	// Validation check
	$r['page'] = intval($r['page']);
	if ( 0 == $r['page'] && 0 != $r['per_page'] )
		$r['page'] = 1;

	if ( null === $r['reverse_top_level'] )
		$r['reverse_top_level'] = ( 'desc' == $global_opts['contribs_order'] );

	extract( $r, EXTR_SKIP );

	if ( empty($walker) )
		$walker = new Walker_Comment;

	$walker->paged_walk($_comments, $max_depth, $page, $per_page, $r);
	$wp_query->max_num_comment_pages = $walker->max_pages;

	$in_comment_loop = false;
}

/**
 * 
 */
function mkforums_contribs_form( $args = array(), $post_id = null ) {
	global $user_identity, $id;

	if ( null === $post_id )
		$post_id = $id;
	else
		$id = $post_id;

	$commenter = wp_get_current_commenter();
	
	// Récupération des paramètres globaux
	$params = mkforums_contribs_get_params( 'global_params' );
		
	$req = $params[ 'require_name_email' ];
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$fields =  array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
		            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .
		            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website' ) . '</label>' .
		            '<input id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
	);

	$required_text = sprintf( ' ' . __('Required fields are marked %s'), '<span class="required">*</span>' );
	$defaults = array(
		'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
		'must_log_in'          => '<p class="must-log-in">' .  sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.' ) . ( $req ? $required_text : '' ) . '</p>',
		'comment_notes_after'  => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'title_reply'          => __( 'Leave a Reply' ),
		'title_reply_to'       => __( 'Leave a Reply to %s' ),
		'cancel_reply_link'    => __( 'Cancel reply' ),
		'label_submit'         => __( 'Post Comment' ),
	);

	$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

	?>
		<?php if ( comments_open() ) : ?>
			<?php do_action( 'comment_form_before' ); ?>
			<div id="respond">
				<h3 id="reply-title"><?php comment_form_title( $args['title_reply'], $args['title_reply_to'] ); ?> <small><?php cancel_comment_reply_link( $args['cancel_reply_link'] ); ?></small></h3>
				<?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
					<?php echo $args['must_log_in']; ?>
					<?php do_action( 'comment_form_must_log_in_after' ); ?>
				<?php else : ?>
					<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>">
						<?php do_action( 'comment_form_top' ); ?>
						<?php if ( is_user_logged_in() ) : ?>
							<?php echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity ); ?>
							<?php do_action( 'comment_form_logged_in_after', $commenter, $user_identity ); ?>
						<?php else : ?>
							<?php echo $args['comment_notes_before']; ?>
							<?php
							do_action( 'comment_form_before_fields' );
							foreach ( (array) $args['fields'] as $name => $field ) {
								echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
							}
							do_action( 'comment_form_after_fields' );
							?>
						<?php endif; ?>
						<?php echo apply_filters( 'comment_form_field_comment', $args['comment_field'] ); ?>
						<?php echo $args['comment_notes_after']; ?>
						<p class="form-submit">
							<input name="submit" type="submit" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>" />
							<?php comment_id_fields( $post_id ); ?>
						</p>
						<?php do_action( 'comment_form', $post_id ); ?>
					</form>
				<?php endif; ?>
			</div><!-- #respond -->
			<?php do_action( 'comment_form_after' ); ?>
		<?php else : ?>
			<?php do_action( 'comment_form_comments_closed' ); ?>
		<?php endif; ?>
	<?php
}