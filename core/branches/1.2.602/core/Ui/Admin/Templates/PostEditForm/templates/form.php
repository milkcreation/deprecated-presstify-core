<?php
/**
 * @var \WP_Post $post Objet post Wordpress
 * @var string $post_type Type de post
 * @var \WP_Post_Type $post_type_object Objet post_type Wordpress
 * @var bool $viewable Indicateur d'affichage du post sur l'interface utilisateur
 * @var bool $_wp_editor_expand
 * @var bool $_content_editor_dfw
 */

global $pagenow,
       $is_lynx, $is_gecko, $is_winIE, $is_macIE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone, $is_IE, $is_edge,
       $is_apache, $is_IIS, $is_iis7, $is_nginx;
?>

<?php if (post_type_supports($post_type, 'title')) : ?>
<div id="titlediv">
    <div id="titlewrap">
        <?php $title_placeholder = apply_filters('enter_title_here', __( 'Enter title here' ), $post); ?>
        <label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo $title_placeholder; ?></label>
        <input type="text" name="post_title" size="30" value="<?php echo esc_attr($post->post_title); ?>" id="title" spellcheck="true" autocomplete="off" />
    </div>

    <?php do_action('edit_form_before_permalink', $post); ?>

    <div class="inside">
    <?php if ($viewable) : ?>
        <?php $sample_permalink_html = $post_type_object->public ? get_sample_permalink_html($post->ID) : ''; ?>

        <?php
            if (has_filter('pre_get_shortlink') || has_filter('get_shortlink')) :
                $shortlink = wp_get_shortlink($post->ID, 'post');

                if (!empty($shortlink) && $shortlink !== $permalink && $permalink !== home_url('?page_id=' . $post->ID)) :
                    $sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr($shortlink) . '" /><button type="button" class="button button-small" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val());">' . __('Get Shortlink') . '</button>';
                endif;
            endif;
        ?>
        <?php if ($post_type_object->public && !('pending' == get_post_status($post) && !current_user_can($post_type_object->cap->publish_posts))) : $has_sample_permalink = $sample_permalink_html && 'auto-draft' != $post->post_status; ?>
        <div id="edit-slug-box" class="hide-if-no-js">
            <?php if ($has_sample_permalink) : echo $sample_permalink_html; endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>

    <?php wp_nonce_field('samplepermalink', 'samplepermalinknonce', false); ?>
</div><!-- /titlediv -->
<?php endif; ?>

<?php do_action('edit_form_after_title', $post);?>

<?php if (post_type_supports($post_type, 'editor')) : ?>
<div id="postdivrich" class="postarea<?php if ($_wp_editor_expand) : ?> wp-editor-expand<?php endif; ?>">
    <?php
        wp_editor(
            $post->post_content,
            'content',
            [
                '_content_editor_dfw' => $_content_editor_dfw,
                'drag_drop_upload' => true,
                'tabfocus_elements' => 'content-html,save-post',
                'editor_height' => 300,
                'tinymce' => [
                    'resize' => false,
                    'wp_autoresize_on' => $_wp_editor_expand,
                    'add_unload_trigger' => false,
                    'wp_keep_scroll_position' => ! $is_IE
                ]
            ]
        );
    ?>
    <table id="post-status-info">
        <tbody>
            <tr>
                <td id="wp-word-count" class="hide-if-no-js">
                    <?php printf(__('Word count: %s'), '<span class="word-count">0</span>'); ?>
                </td>
                <td class="autosave-info">
                    <span class="autosave-message">&nbsp;</span>
                    <?php if ('auto-draft' != $post->post_status) : ?>
                    <span id="last-edit">
                    <?php
                        if ($last_user = get_userdata(get_post_meta($post_ID, '_edit_last', true))) :
                            printf( __( 'Last edited by %1$s on %2$s at %3$s' ), esc_html( $last_user->display_name ), mysql2date( __( 'F j, Y' ), $post->post_modified ), mysql2date( __( 'g:i a' ), $post->post_modified ) );
                        else :
                            printf( __( 'Last edited on %1$s at %2$s' ), mysql2date( __( 'F j, Y' ), $post->post_modified ), mysql2date( __( 'g:i a' ), $post->post_modified ) );
                        endif;
                    ?>
                    </span>
                    <?php endif;?>
                </td>
                <td id="content-resize-handle" class="hide-if-no-js"><br /></td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>