<?php
namespace tiFy\Core\Templates\Admin\Model\Messager;

use tiFy\Core\Control\Repeater\Repeater;
use tiFy\Core\Control\MediaFile\MediaFile;
use tiFy\Lib\Mailer\MailerNew;

class Messager extends \tiFy\Core\Templates\Admin\Model\Form
{
    /**
     * Tableau associatifs valeur => intitulé des groupes de déstinataire
     * @array
     */
    protected $GroupList = [];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
        add_filter('tiny_mce_before_init', array($this, 'tiny_mce_before_init'), 99, 2);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter( 'mce_css', array( $this, 'mce_css' ) );

        add_action('wp_ajax_tiFyCoreTemplatesAdminModelMessagerGroupListItem', array($this, '_ajaxGroupListItem'));
    }

    /**
     * Définition du tableau associatif [valeur => intitulé] des groupes de déstinataire
     * @var array
     */
    public function set_group_list()
    {
        return [];
    }

    /**
     * Définition de la cartographie des paramètres autorisés
     *
     * return array
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();
        array_push($params, 'GroupList');

        return $params;
    }

    /** == Permettre l'ajout d'un nouvel élément == **/
    public function set_add_new_item()
    {
        return false;
    }

    /** == Définition des messages de notification == **/
    public function set_notices()
    {
        return [
            'SenderEmpty'   => [
                'notice'        => 'error',
                'message'       => __('Veuillez renseigner l\'adresse de l\'expéditeur de l\'email.', 'tify'),
                'dismissible'   => true
            ],
            'SenderInvalid' => [
                'notice'        => 'error',
                'message'       => __('l\'adresse de messagerie de l\'expéditeur n\'est pas un email valide.', 'tify'),
                'dismissible'   => true
            ],
            'RecipientEmpty'   => [
                'notice'        => 'error',
                'message'       => __('Veuillez renseigner un destinataire ou un groupe de destinataires.', 'tify'),
                'dismissible'   => true
            ],
            'SubjectEmpty'   => [
                'notice'        => 'error',
                'message'       => __('Le sujet de votre mail ne devrait pas être vide.', 'tify'),
                'dismissible'   => true
            ],
            'MessageEmpty'  => [
                'notice'        => 'error',
                'message'       => __('Le message de votre mail ne peut pas être vide.', 'tify'),
                'dismissible'   => true
            ],
            'SendingEmailSuccessful'    => [
                'notice'        => 'success',
                'message'       => __('Email envoyé avec succès.', 'tify'),
                'dismissible'   => true
            ]
        ];
    }

    /**
     * @param $mceInit
     * @param $editor_id
     * @return mixed
     */
    final public function tiny_mce_before_init($mceInit, $editor_id)
    {
        if ($editor_id !== 'tiFyCoreTemplatesAdminModelMessager-editor') :
            return $mceInit;
        endif;

        $mceInit['toolbar1'] = 'formatselect,fontselect,fontsizeselect,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,bullist,numlist,outdent,indent,|,alignleft,aligncenter,alignright,alignjustify';
        $mceInit['toolbar2'] = 'link,unlink,|,hr,|,table,|,subscript,superscript,charmap,|,removeformat,|,pastetext,|,undo,redo';
        $mceInit['toolbar3'] = '';
        $mceInit['toolbar4'] = '';


        $mceInit['block_formats'] = 'Texte principal=div;Paragraphe=p;Titre 1=h1;Titre 2=h2;Titre 3=h3;Titre 4=h4;Titre 5=h5;Titre 6=h6';

        /**
         * Liste des polices
         * @see http://www.cssfontstack.com/
         */
        $mceInit['font_formats'] =
            "Arial=Arial,Helvetica Neue,Helvetica,sans-serif;" .
            //"Comic Sans MS=comic sans ms,marker felt-thin,arial,sans-serif;".
            "Courier New=Courier New,Courier,Lucida Sans Typewriter,Lucida Typewriter,monospace;" .
            "Georgia=Georgia,Times,Times New Roman,serif;" .
            //"Lucida=lucida sans unicode,lucida grande,sans-serif;".
            "Tahoma=Tahoma,Verdana,Segoe,sans-serif;" .
            "Times New Roman=TimesNewRoman,Times New Roman,Times,Baskerville,Georgia,serif;" .
            "Trebuchet MS=Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Tahoma,sans-serif;" .
            "Verdana=Verdana,Geneva,sans-serif;";

        $mceInit['table_default_attributes'] = json_encode(
            array(
                'width' => '600',
                'cellspacing' => '0',
                'cellpadding' => '0',
                'border' => '0'
            )
        );
        $mceInit['table_default_styles'] = json_encode(
            array(
                'border-collapse' => 'collapse',
                'mso-table-lspace' => '0pt',
                'mso-table-rspace' => '0pt',
                '-ms-text-size-adjust' => '100%',
                '-webkit-text-size-adjust' => '100%',
                'background-color' => '#FFFFFF',
                'border-top' => '0',
                'border-bottom' => '0'
            )
        );

        $mceInit['wordpress_adv_hidden'] = false;

        return $mceInit;
    }

    /**
     * Ajout des styles dans TinyMCE
     */
    final public function mce_css($mce_css)
    {
        return '';
        //return $mce_css = Emailing::tFyAppUrl() . '/assets/reset.css, ' . self::tFyAppUrl() . '/editor-style.css';
    }

    public function initParamGroupList()
    {
        $this->GroupList = $this->set_group_list();
    }

    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('tiFyCoreTemplatesAdminModelMessager', self::tFyAppAssetsUrl('Messager.css', get_class()), array(), '170912');
        Repeater::enqueue_scripts();
    }

    /**
     * Récupération de la reponse via Ajax
     */
    final public function _ajaxGroupListItem()
    {
        //check_ajax_referer('tiFyControlRepeater');
        $this->initParams();

        $index = $_POST['index'];
        //$value = $_POST['value'];
        $attrs = $_POST['attrs'];

        ob_start();
        call_user_func(array($this, 'group_list_item'), $index, ''/*$value*/, $attrs);
        $item = ob_get_clean();

        echo Repeater::itemWrap( $item, $index, ''/*$value*/, $attrs );

        wp_die();
    }

    /**
     * @param $index
     * @param $value
     * @param array $attrs
     */
    public function group_list_item($index, $value, $attrs = array())
    {
?>
<select name="<?php echo $attrs['name'];?>[<?php echo $index;?>]" class="widefat">
    <?php foreach((array)$this->GroupList as $valor => $label) : ?>
        <option value="<?php echo $valor;?>" <?php selected($valor, $value);?>><?php echo $label;?></option>
    <?php endforeach;?>
</select>
<?php
    }

    protected function process_bulk_action_email_send()
    {
        check_admin_referer($this->current_action());

        $data = $this->parse_postdata($_POST);

        $sendback = remove_query_arg(array('action', 'action2'), wp_get_referer());

        if (is_wp_error($data)) :
            return $this->set_current_notice($data->get_error_code());
        else :
            MailerNew::send($data);
            $sendback = add_query_arg(array('message' => 'SendingEmailSuccessful'), $sendback);
        endif;

        wp_redirect($sendback);
        exit;
    }

    protected function parse_postdata($postdata)
    {
        $postdata = array_map('wp_unslash', $postdata);
        extract($postdata);

        // Vérification de l'expéditeur
        if (empty($from)) :
            return new \WP_Error('SenderEmpty');
        endif;
        if (! \is_email($from)) :
            return new \WP_Error('SenderInvalid');
        endif;

        // Vérification des destinataires
        if (empty($to) && empty($group)) :
            return new \WP_Error('RecipientEmpty');
        endif;

        $subject = esc_html($subject);
        // Vérification du sujet
        if (empty($subject)) :
            return new \WP_Error('SubjectEmpty');
        endif;

        // Vérification du message
        if (empty($message)) :
            return new \WP_Error('MessageEmpty');
        endif;

        $pieces = ['from', 'to', 'subject', 'message'];

        return compact($pieces);
    }

    public function render()
    {
        $fields = ['from', 'to', 'group', 'subject', 'message'];
        foreach ($fields as $field) :
            ${$field} = isset($_POST[$field]) ? $_POST[$field] : '';
        endforeach;
?>
<div class="wrap">
    <h2><?php _e('Envoi de message', 'Theme'); ?></h2>

    <form method="post" action="">
        <?php wp_nonce_field('email_send');?>
        <?php $this->hidden_fields();?>

        <ul class="tiFyCoreTemplatesAdminModelMessager-mailOptions">
            <li class="tiFyCoreTemplatesAdminModelMessager-mailOption tiFyCoreTemplatesAdminModelMessager-mailOption--sender">
                <label class="tiFyCoreTemplatesAdminModelMessager-mailOptionLabel"><?php _e('Expéditeur :', 'Theme'); ?></label>
                <div class="tiFyCoreTemplatesAdminModelMessager-mailOptionContent">
                    <input type="text" class="widefat" name="from" value="<?php echo $from; ?>"/>
                </div>
            </li>
            <li class="tiFyCoreTemplatesAdminModelMessager-mailOption tiFyCoreTemplatesAdminModelMessager-mailOption--recipients">
                <label class="tiFyCoreTemplatesAdminModelMessager-mailOptionLabel"><?php _e('Pour :', 'Theme'); ?></label>
                <div class="tiFyCoreTemplatesAdminModelMessager-mailOptionContent">
                <?php
                    Repeater::display(
                        [
                            'name'                  => 'to',
                            'add_button_txt'        => __('Ajouter un destinataire', 'tify'),
                            'order'                 => false,
                            'value'                 => $to
                        ]
                    );
                ?>
                </div>
            </li>
            <?php if ($this->GroupList) :?>
            <li class="tiFyCoreTemplatesAdminModelMessager-mailOption tiFyCoreTemplatesAdminModelMessager-mailOption--mailingList">
                <label class="tiFyCoreTemplatesAdminModelMessager-mailOptionLabel"><?php _e('Liste :', 'Theme'); ?></label>
                <div class="tiFyCoreTemplatesAdminModelMessager-mailOptionContent">
                <?php
                    Repeater::display(
                        [
                            'name'                  => 'group',
                            'add_button_txt'        => __('Ajouter un groupe de destinataires', 'tify'),
                            'order'                 => false,
                            'value'                 => $group,
                            'ajax_action'           => 'tiFyCoreTemplatesAdminModelMessagerGroupListItem',
                            'item_cb'               => array($this, 'group_list_item')
                        ]
                    );
                ?>
                </div>
            </li>
            <?php endif;?>
            <li class="tiFyCoreTemplatesAdminModelMessager-mailOption tiFyCoreTemplatesAdminModelMessager-mailOption--subject">
                <label class="tiFyCoreTemplatesAdminModelMessager-mailOptionLabel"><?php _e('Sujet :', 'Theme'); ?></label>
                <div class="tiFyCoreTemplatesAdminModelMessager-mailOptionContent">
                    <input type="text" class="widefat" name="subject" value="<?php echo $subject;?>">
                </div>
            </li>
        </ul>
        <div class="tiFyCoreTemplatesAdminModelMessager-mailMessage">
            <?php
            @ \wp_editor(
                wp_unslash($message),
                'tiFyCoreTemplatesAdminModelMessager-editor',
                [
                    'wpautop'       => false,
                    'media_buttons' => true,
                    'textarea_name' => 'message',
                    'tinymce'       => [
                        'toolbar1'  =>  'bold,italic,underline,strikethrough,blockquote,|,' .
                                        'alignleft,aligncenter,alignright,alignjustify,|,' .
                                        'bullist,numlist,outdent,indent,|,link,unlink,hr',
                        'toolbar2'  =>  'pastetext,|,' .
                                        'formatselect,fontselect,fontsizeselect',
                        'toolbar3'  =>  'table,|,' .
                                        'forecolor,backcolor,|,' .
                                        'subscript,superscript,charmap,|,' .
                                        'removeformat,|,' .
                                        'undo,redo',
                        'toolbar4'  => ''
                    ]
                ]
            );
            ?>
        </div>
        <div class="tiFyCoreTemplatesAdminModelMessager-mailAttachment">

        </div>
        <br/>
        <div class="">
            <button type="submit" name="action" value="email_send" class="button-primary">
                <?php _e('Envoyer', 'tify');?>
            </button>
        </div>
    </form>
</div>
<?php
    }
}