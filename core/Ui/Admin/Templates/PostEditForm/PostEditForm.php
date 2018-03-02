<?php
namespace tiFy\Core\Ui\Admin\Templates\PostEditForm;

class PostEditForm extends \tiFy\Core\Ui\Admin\Templates\EditForm\EditForm
{
    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        parent::admin_enqueue_scripts();

        $post_type = $this->item->post_type;

        \wp_enqueue_script('post');

        /*
         if (post_type_supports($post_type, 'editor') && !wp_is_mobile() && ! ($is_IE && preg_match( '/MSIE [5678]/', $_SERVER['HTTP_USER_AGENT'])) && apply_filters('wp_editor_expand', true, $post_type)) :

            wp_enqueue_script('editor-expand');
            $_content_editor_dfw = true;
            $_wp_editor_expand = ( get_user_setting( 'editor_expand', 'on' ) === 'on' );
        endif;
        */

        if (wp_is_mobile()) :
            \wp_enqueue_script('jquery-touch-punch');
        endif;
    }

    /**
     * Affichage du formulaire de saisie
     *
     * @return string
     */
    public function form()
    {
        $post = $this->item;
        $post_ID = $this->item->ID;
        $post_type = $this->item->post_type;
        $post_type_object = get_post_type_object($post_type);
        $permalink = get_permalink($post_ID);
        if (!$permalink) :
            $permalink = '';
        endif;

        $viewable = is_post_type_viewable($post_type_object);
        $_wp_editor_expand = $_content_editor_dfw = false;

        self::tFyAppGetTemplatePart(
            'form',
            $post_type,
            compact(
                'post',
                'post_ID',
                'post_type',
                'post_type_object',
                'permalink',
                'viewable',
                '_wp_editor_expand',
                '_content_editor_dfw'
            )
        );
    }

    /**
     * Affichage de l'interface de saisie
     *
     * @return string
     */
    public function display()
    {
        $post = $this->item;
        $post_type = $this->item->post_type;
    ?>

<?php do_action( 'edit_form_top', $post); ?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
        <div id="post-body-content">
            <?php $this->form();?>

            <?php do_action('edit_form_after_editor', $post); ?>
        </div>

        <div id="postbox-container-1" class="postbox-container">
            <?php $this->submitdiv(); ?>

            <?php do_meta_boxes($post_type, 'side', $post);?>
        </div>

        <div id="postbox-container-2" class="postbox-container">
            <?php do_meta_boxes(null, 'normal', $post); ?>

            <?php ('page' == $post_type) ? do_action('edit_page_form', $post) : do_action('edit_form_advanced', $post); ?>

            <?php do_meta_boxes(null, 'advanced', $post); ?>
        </div>

    </div>
    <br class="clear" />
</div>
<?php
    }
}