<?php
namespace tiFy\Core\Forms\Addons\Mailer\Taboox\Options\MailOptions\Admin;

use tiFy\Core\Forms\Forms;
use tiFy\Core\Field\Field;
use tiFy\Core\Control\Control;

class MailOptions extends \tiFy\Core\Taboox\Options\Admin
{
    /**
     * Prefixe du nom d'enregistrement des options en base de données
     * @var string
     */
    protected $OptionNamePrefix;

    /**
     * Liste des noms d'enregistement des options
     * @var array
     */
    protected $OptionNames = [];

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    public function init()
    {
        if (!$form = Forms::get($this->args['form_id'])) :
            return;
        endif;

        $this->OptionNamePrefix = $form->getForm()->getAddonAttr('mailer', 'option_name_prefix', 'tiFyFormMailer_'. $this->args['form_id']);

        $option_names = $form->getForm()->getAddonAttr('mailer', 'option_names', []);
        foreach (['confirmation', 'sender', 'notification', 'recipients'] as $option) :
            $this->OptionNames[$option] = !empty($option_names[$option]) ? $option_names[$option] : $this->OptionNamePrefix . '-' . $option;
        endforeach;
    }

    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    public function admin_init()
    {
        \register_setting(
            $this->page, $this->OptionNames['confirmation']
        );
        \register_setting(
            $this->page, $this->OptionNames['sender'],
            [$this, 'sanitize_sender']
        );
        \register_setting(
            $this->page,
            $this->OptionNames['notification']
        );
        \register_setting(
            $this->page,
            $this->OptionNames['recipients'],
            [$this, 'sanitize_recipients']
        );
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        Field::enqueue('ToggleSwitch');
        Control::enqueue_scripts('repeater');
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    public function form()
    {
        $confirmation = isset($this->args['confirmation']) ? $this->args['confirmation'] : true;
        $notification = isset($this->args['notification']) ? $this->args['notification'] : true;
    ?>
    <?php if ($confirmation) :?>
        <h3><?php _e( 'Message de confirmation de réception de la demande', 'tify' );?></h3>

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><?php _e('Envoyer un message de confirmation de réception à l\'utilisateur', 'tify'); ?></th>
                <td>
                <?php
                    echo Field::ToggleSwitch(
                        [
                            'name'    => $this->OptionNames['confirmation'],
                            'checked' => ($notification = get_option($this->OptionNames['confirmation'], 'off')) ? $notification : 'off'
                        ]
                    );
                ?>
                </td>
            </tr>

            <?php $s = get_option($this->OptionNames['sender']); ?>
            <?php $value['email'] = !empty($s['email']) ? $s['email'] : get_option('admin_email');
            $value['name'] = !empty($s['name']) ? $s['name'] : ''; ?>
            <tr>
                <th scope="row"><?php _e('Email de l\'expéditeur (requis)', 'tify'); ?></th>
                <td>
                    <div class="tify_input_email">
                    <?php
                        echo Field::Text(
                            [
                                'name'  => $this->OptionNames['sender'] . "[email]",
                                'value' => $value['email'],
                                'attrs' => [
                                    'placeholder'  => __('Email (requis)', 'tify'),
                                    'size'         => 40,
                                    'autocomplete' => 'off'
                                ]
                            ]
                        );
                    ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Nom de l\'expéditeur (optionnel)', 'tify'); ?></th>
                <td>
                    <div class="tify_input_user">
                    <?php
                        echo Field::Text(
                            [
                                'name'  => $this->OptionNames['sender'] . "[name]",
                                'value' => $value['name'],
                                'attrs' => [
                                    'placeholder'  => __('Nom (optionnel)', 'tify'),
                                    'size'         => 40,
                                    'autocomplete' => 'off'
                                ]
                            ]
                        );
                    ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    <?php endif;?>

    <?php if ($notification) : ?>
        <h3><?php _e( 'Message de notification aux administrateurs', 'tify' );?></h3>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e( 'Envoyer un message de notification aux administrateurs du site', 'tify' );?></th>
                    <td>
                    <?php
                        echo Field::ToggleSwitch(
                            [
                                'name'    => $this->OptionNames['notification'],
                                'checked' => ($notification = get_option($this->OptionNames['notification'], 'off')) ? $notification : 'off'
                            ]
                        );
                    ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        <?php
            Control::Repeater(
                [
                    'add_button_txt' => __('Ajouter un destinataire', 'tify'),
                    'value'          => get_option($this->OptionNames['recipients']),
                    'name'           => $this->OptionNames['recipients'],
                    'item_cb'        => get_called_class() . '::recipients_item_cb'
                ],
                true
            );
        ?>
    <?php endif;?>
    <?php
    }

    /**
     * Méthode de rappel du formulaire de création d'un destinataire de notification
     *
     * @param $index
     * @param $value
     * @param array $attrs
     *
     * @return string
     */
    public static function recipients_item_cb($index, $value, $attrs = [])
    {
        $defaults = [
            'email' => '',
            'name'  => ''
        ];
        $value = \wp_parse_args($value, $defaults);

?>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><?php _e('Email du destinataire (requis)', 'tify'); ?></th>
        <td>
            <div class="tify_input_email">
            <?php
                echo Field::Text(
                    [
                        'name'  => "{$attrs['name']}[{$index}][email]",
                        'value' => $value['email'],
                        'attrs' => [
                            'placeholder'  => __('Email du destinataire', 'tify'),
                            'size'         => 40,
                            'autocomplete' => 'off'
                        ]
                    ]
                );
            ?>
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Nom du destinataire (optionnel)', 'tify'); ?></th>
        <td>
            <div class="tify_input_user">
            <?php
                echo Field::Text(
                    [
                        'name'  => "{$attrs['name']}[{$index}][name]",
                        'value' => $value['name'],
                        'attrs' => [
                            'placeholder'  => __('Nom du destinataire', 'tify'),
                            'size'         => 40,
                            'autocomplete' => 'off'
                        ]
                    ]
                );
            ?>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<?php
    }
    
    /**
     * Vérification du format de l'email de l'expéditeur
     *
     * @param array $sender Attributs de l'expéditeur
     *
     * @return array
     */
    public function sanitize_sender($sender)
    {
        if (empty($sender['email'])) :
            \add_settings_error(
                $this->page,
                'sender-email_empty',
                sprintf(
                    __('L\'email "%s" ne peut être vide', 'tify'),
                    __('Expéditeur du message de confirmation de reception', 'tify')
                )
            );
        elseif (!\is_email($sender['email'])) :
            \add_settings_error(
                $this->page,
                'sender-email_format',
                sprintf(
                    __('Le format de l\'email "%s" n\'est pas valide', 'tify'),
                    __('Expéditeur du message de confirmation de reception', 'tify')
                )
            );
        endif;
        
        return $sender;
    } 
    
    /**
     * Vérification du format de l'email du destinataire de notification
     *
     * @param array $sender Attributs des destinataires
     *
     * @return array
     */
    public function sanitize_recipients($recipients)
    {
        if ($recipients) :
            foreach ($recipients as $recipient => $recip) :
                if (empty($recip['email'])) :
                    \add_settings_error(
                        $this->page,
                        $recipient . '-email_empty',
                        sprintf(
                            __('L\'email du destinataire des messages de notification #%d ne peut être vide', 'tify'),
                            $recipient+1
                        )
                    );
                elseif (!is_email($recip['email'])) :
                    \add_settings_error(
                        $this->page,
                        $recipient . '-email_format',
                        sprintf(
                            __('Le format de l\'email du destinataire des messages de notification #%d n\'est pas valide', 'tify'),
                            $recipient+1
                        )
                    );
                endif;
            endforeach;
        endif;

        return $recipients;
    }
}