<?php
/**
 * @Overridable 
 */
namespace tiFy\Core\Forms\Addons\Mailer;

use tiFy\Lib\Mailer\MailerNew;
use tiFy\Core\Forms\Form\Helpers;
use tiFy\Core\Options\Options;

class Mailer extends \tiFy\Core\Forms\Addons\Factory
{
    /**
     * Identifiant
     * @var string
     */
    public $ID = 'mailer';
    
    /**
     * Définition des options de champ de formulaire par défaut
     * @var array {
     *      @param bool $show Affichage de l'intitulé et de la valeur de saisie du champ dans le corps du mail
     * }
     */
    public $default_field_options = [
        'show'         => false
    ];

    /**
     * Définition des options de formulaire par défaut
     * @var array {
     *      @param bool|array $admin {
     *          Affichage de l'intitulé et de la valeur de saisie du champ dans le corps du mail
     *
     *          @param string $id Identifiant qualificatif. Utilisé notamment pour determiner les noms d'enregistrement des options en base de données
     *          @param bool $confirmation Activation de l'interface d'administration de l'email de confirmation de reception à destination des utilisateurs
     *          @param bool $notification Activation de l'interface d'administration de l'email de notification à destination des administrateurs de site
     *      }
            @param bool|array $confirmation {
     *          Attributs de configuration d'expédition de l'email de confirmation de reception à destination des utilisateurs
     *          @see \tiFy\Lib\Mailer\MailerNew
     *      }
     *      @param bool|array $notification {
     *          Attributs de configuration d'expédition de l'email de notification à destination des administrateurs de site
     *          @see \tiFy\Lib\Mailer\MailerNew
     *      }
     *      @param string $option_name_prefix Prefixe du nom d'enregistrement des options d'expédition de mail
     *      @param string|array $template {
     *              Chemin relatif du theme courant vers le gabarit du mail
     *
     *              @param slug Chemin absolu vers le nom du fichier de gababit (hors extension)
     *              @param $name Modifieur de nom de gabarit
     *              @param $args Liste d'arguments complémentaires pass"s dans le gabarit
     *      }
     * }
     */
    public $default_form_options = [
        'admin'                 => true,
        'confirmation'          => [
            'to'                    => '%%email%%'
        ],
        'notification'          => [],
        'option_name_prefix'    => '',
        'template'              => []
    ];
    
    /**
     * CONSTRUCTEUR 
     */
    public function __construct() 
    {
        parent::__construct();

        $this->default_form_options['notification'] = [
            'subject'            => sprintf(__('Vous avez une nouvelle demande de contact sur le site %s', 'tify'), get_bloginfo('name'))
        ];

        // Définition des fonctions de court-circuitage
        $this->callbacks = [
            'handle_successfully' => [$this, 'cb_handle_successfully']
        ];
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Après l'initialisation de l'addon
     */
    public function afterInit()
    {
        $id = @ sanitize_html_class(base64_encode($this->form()->getUID()));

        // Bypass
        if ($admin = $this->getFormAttr('admin')) :
            // Définition des attributs de configuration de l'interface d'administration
            $defaults = [
                'form_id'               => $this->form()->getId(),
                'confirmation'          => true,
                'notification'          => true
            ];
            $args = \wp_parse_args($admin, $defaults);

            // Déclaration de l'interface d'administration des options d'expédition de mail
            Options::registerNode(
                array(
                    'id'            => 'tiFyFormMailer_' . $id,
                    'title'         => $this->form()->getTitle(),
                    'cb'            => self::getOverride('tiFy\Core\Forms\Addons\Mailer\Taboox\Option\MailOptions\Admin\MailOptions'),
                    'args'          => $args
                )
            );
        endif;

        // Définition du prefixe du nom de l'option d'enregistrement en base de données
        if(! $this->getFormAttr('option_name_prefix'))
            $this->setFormAttr('option_name_prefix', 'tiFyFormMailer_'. $id);
        $option_name_prefix = $this->getFormAttr('option_name_prefix');

        // Définition des attributs de l'email de confirmation
        $confirmation = $this->getFormAttr('confirmation');
        if (get_option($option_name_prefix . '-confirmation') === 'off') :
            $this->setFormAttr('confirmation', false);
        elseif( $from = get_option($option_name_prefix . '-sender')) :
            $confirmation['from'] = $from;
            $this->setFormAttr('confirmation', $confirmation);
        endif;

        // Définition des attributs de l'email de notification
        $notification = $this->getFormAttr('notification');
        if (get_option($option_name_prefix . '-notification') === 'off') :
            $this->setFormAttr('notification', false);
        elseif ($to = get_option($option_name_prefix . '-recipients')) :
            $notification['to'] = $to;
            $this->setFormAttr('notification', $notification);
        endif;
    }

    /**
     * Avant la redirection
     */
    public function cb_handle_successfully($handle)
    {
        // Expédition du message de confirmation
        if ($options = $this->getFormAttr('confirmation')) :
            $options = $this->parseOptions($options, 'confirmation');

            MailerNew::send($options);
        endif;

        // Expédition du message de notification
        if ($options = $this->getFormAttr('notification')) :
            $options = $this->parseOptions($options, 'notification');

            MailerNew::send($options);
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Traitement des options
     */
    final protected function parseOptions($options, $context)
    {
        $options = Helpers::parseMergeVars($options, $this->form());

        // Définition du sujet du mail
        if(! isset($options['subject'])) :
            $options['subject'] = sprintf(__('Nouvelle demande sur le site %1$s', 'tify'), get_bloginfo('name'));
        endif;

        // Définition du destinataire
        if(! isset($options['to'])) :
            $options['to'] = get_option('admin_email');
        endif;

        // Définition du message
        if(empty($options['message'])) :
            $subject = $options['subject'];

            $fields = array();
            foreach ((array) $this->form()->fields() as $field ) :
                if( ! $this->getFieldAttr( $field, 'show', false ) ||
                    ! $field->typeSupport( 'request' ) ||
                    in_array($field->getType(), ['password', 'file'])
                ) :
                    continue;
                endif;

                $fields[$field->getSlug()] =  [
                    'label'     => $field->getLabel(),
                    'value'     => $field->getValue()
                ];
            endforeach;

            $slug = 'message';
            $name = $context;

            if ($template = $this->getFormAttr('template')) :
                if (is_array($template)) :
                    if (isset($template['slug'])) :
                        $slug = $template['slug'];
                    endif;
                    if (isset($template['name'])) :
                        $name = $template['name'];
                    endif;
                else :
                    $slug = (string) $template;
                endif;
            endif;

            ob_start();
            self::tFyAppGetTemplatePart($slug, $name, compact('context', 'subject', 'fields'));
            $options['message'] = ob_get_clean();
        endif;

        return $options;
    }
}