<?php
namespace tiFy\Core\Forms\Addons\Mailer\Taboox\Option\MailOptions\Admin;

use tiFy\Core\Forms\Forms;
use tiFy\Core\Control\Switcher\Switcher;

class MailOptions extends \tiFy\Core\Taboox\Option\Admin
{
    /**
     * Prefixe du nom d'enregistrement des options en base de données
     * @var string
     */
    protected $OptionNamePrefix;

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    public function admin_init()
    {
        if (! $form = Forms::get($this->args['form_id']))
            return;

        $this->OptionNamePrefix = $form->getForm()->getAddonAttr('mailer', 'option_name_prefix', $this->args['form_id']);

        register_setting($this->page, $this->OptionNamePrefix .'-confirmation');
        register_setting($this->page, $this->OptionNamePrefix .'-sender', array( $this, 'sanitize_sender'));
        register_setting($this->page, $this->OptionNamePrefix .'-notification');
        register_setting($this->page, $this->OptionNamePrefix .'-recipients', array( $this, 'sanitize_recipients'));

    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     */
    public function admin_enqueue_scripts()
    {
        Switcher::enqueue_scripts();
        tify_control_enqueue('dynamic_inputs');
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
                    <th scope="row"><?php _e( 'Envoyer un message de confirmation de réception à l\'utilisateur', 'tify' );?></th>
                    <td>
                        <?php
                        Switcher::display(
                            [
                                'name'      => $this->OptionNamePrefix . '-confirmation',
                                'checked'   => get_option($this->OptionNamePrefix .'-confirmation', 'off')
                            ]
                        );
                        ?>
                    </td>
                </tr>
                
                <?php $s = get_option( $this->OptionNamePrefix .'-sender' );?>
                <?php $value['email'] = ! empty( $s['email'] ) ?  $s['email'] : get_option( 'admin_email' ); $value['name'] = ! empty( $s['name'] ) ? $s['name'] : '';?>
                <tr>
                    <th scope="row"><?php _e( 'Email de l\'expéditeur (requis)', 'tify' );?></th>
                    <td>
                        <div class="tify_input_email">
                            <input type="text" name="<?php echo $this->OptionNamePrefix;?>-sender[email]" placeholder="<?php _e( 'Email (requis)', 'tify' );?>" value="<?php echo $value['email'];?>" size="40" autocomplete="off">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Nom de l\'expéditeur (optionnel)', 'tify' );?></th>
                    <td>
                        <div class="tify_input_user">
                            <input type="text" name="<?php echo $this->OptionNamePrefix;?>-sender[name]" placeholder="<?php _e( 'Nom (optionnel)', 'tify' );?>" value="<?php echo $value['name'];?>" size="40" autocomplete="off">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php endif;?>

        <?php if ($notification) :?>
        <h3><?php _e( 'Message de notification aux administrateurs', 'tify' );?></h3>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e( 'Envoyer un message de notification aux administrateurs du site', 'tify' );?></th>
                    <td>
                    <?php
                        Switcher::display(
                            [
                                'name'      => $this->OptionNamePrefix .'-notification',
                                'checked'   => get_option($this->OptionNamePrefix .'-notification', 'off')
                            ]
                        );
                    ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <?php 
            tify_control_dynamic_inputs( 
                array( 
                    'default'               => array( 'name'=> '', 'email' => get_option( 'admin_email' ) ),
                    'add_button_txt'        => __( 'Ajouter un destinataire', 'tify' ),
                    'values'                => get_option( $this->OptionNamePrefix .'-recipients' ),
                    'name'                  => $this->OptionNamePrefix .'-recipients',
                    'sample_html'           => 
                        "<table class=\"form-table\">\n".
                        "\t<tbody>\n".
                        "\t\t<tr>\n".
                        "\t\t\t<th scope=\"row\">". __( 'Email du destinataire (requis)', 'tify' ) ."</th>\n".
                        "\t\t\t<td>\n".
                        "\t\t\t\t<div class=\"tify_input_email\">\n".
                        "\t\t\t\t\t<input type=\"text\" name=\"%%name%%[%%index%%][email]\" value=\"%%value%%[email]\" placeholder=\"". __( 'Email de l\'expéditeur', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n".
                        "\t\t\t\t</div>\n".
                        "\t\t\t</td>\n".
                        "\t\t</tr>\n".
                        "\t\t<tr>\n".
                        "\t\t\t<th scope=\"row\">". __( 'Nom du destinataire (optionnel)', 'tify' ) ."</th>\n".
                        "\t\t\t<td>\n".
                        "\t\t\t\t<div class=\"tify_input_user\">\n".
                        "\t\t\t\t\t<input type=\"text\" name=\"%%name%%[%%index%%][name]\" value=\"%%value%%[name]\" placeholder=\"". __( 'Nom de l\'expéditeur', 'tify' ) ."\" size=\"40\" autocomplete=\"off\">\n".
                        "\t\t\t\t</div>\n".
                        "\t\t\t</td>\n".
                        "\t\t</tr>\n".    
                        "\t</tbody>\n".
                        "</table>"
                ) 
            );
        ?>
        <?php endif;?>
    <?php
    }
    
    /**
     * Vérification du format de l'email de l'expéditeur
     */
    public function sanitize_sender( $sender )
    {
        if( empty( $sender['email'] ) ) :
            add_settings_error( $this->page, 'sender-email_empty', sprintf( __( 'L\'email "%s" ne peut être vide', 'tify' ), __( 'Expéditeur du message de confirmation de reception', 'tify' ) ) ); 
        elseif( ! is_email( $sender['email'] ) ) :
            add_settings_error( $this->page, 'sender-email_format', sprintf( __( 'Le format de l\'email "%s" n\'est pas valide' ), __( 'Expéditeur du message de confirmation de reception', 'tify' ) ) ); 
        endif;
        
        return $sender;
    } 
    
    /**
     * Vérification du format de l'email du destinataire de notification
     */
    public function sanitize_recipients( $recipients )
    {
        foreach( (array) $recipients as $recipient => $recip ) :
            if( empty( $recip['email'] ) ) :
                add_settings_error( $this->page, $recipient .'-email_empty', sprintf( __( 'L\'email du destinataire des messages de notification #%d ne peut être vide', 'tify' ), $recipient+1 ) ); 
            elseif( ! is_email( $recip['email'] ) ) :
                add_settings_error( $this->page, $recipient .'-email_format', sprintf( __( 'Le format de l\'email du destinataire des messages de notification #%d n\'est pas valide', 'tify' ), $recipient+1 ) ); 
            endif;
        endforeach;
        
        return $recipients;
    }
}