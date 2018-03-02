<?php
/**
 * @name ContactToggle
 * @desc Controleur d'affichage de la fenêtre de dialogue des informations de contact
 * @package presstiFy
 * @namespace tiFy\Set\ContactToggle\Control\ContactToggle
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Set\ContactToggle\Control;

use tiFy\Core\Control\Control;

/**
 * @override \App\Set\ContactToggle\Control\ContactToggle\ContactToggle
 *
 * <?php
 * namespace \App\Core\Control\ContactToggle
 *
 * class ContactToggle extends \tiFy\Set\ContactToggle\Control\ContactToggle\ContactToggle
 * {
 *
 * }
 */

class ContactToggle extends \tiFy\Core\Control\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        /**
         * Déclaration des actions Ajax
         */
        $this->tFyAppAddAction('wp_ajax_tiFySetCoreControl_ContactToggle', 'wp_ajax');
        $this->tFyAppAddAction('wp_ajax_nopriv_tiFySetCoreControl_ContactToggle', 'wp_ajax');

        \wp_register_script(
            'tify_control-contact_toggle',
            self::tFyAppAssetsUrl('ContactToggle.js', get_class()),
            ['jquery', 'tify_control-modal'],
            170301,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @uses add_action('wp_enqueue_scripts', 'tify_control_contact_toggle_enqueue_scripts');|add_action('admin_enqueue_scripts', 'tify_control_contact_toggle_enqueue_scripts');
     * @uses \tiFy\Core\Control\Control::enqueue_scripts('contact_toggle') - Appel depuis les classes et/ou méthodes
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        Control::enqueue_scripts('modal');
        \wp_enqueue_script('tify_control-contact_toggle');
    }

    /**
     * Récupération ajax des données
     *
     * @return string
     */
    public function wp_ajax()
    {
        check_ajax_referer('tiFySetContatToggleControl-Modal');

        // Définition des attributs
        $attrs = \wp_unslash($_POST['attrs']);

        /**
         * @var string $id Identifiant de qualification du contrôleur d'affichage
         * @var string $container_id ID HTML du conteneur de notification
         * @var string $container_class Classe HTML du conteneur de notification
         * @var array $container_attrs Attributs HTML du conteneur
         * @var array $options Options d'affichage
         * @var bool $animation Activation de l'animation
         * @var string $dialog_size Taille d'affichage de la fenêtre de dialogue normal|lg|sm|full
         * @var string|bool|callable $backdrop_close_button Affichage d'un bouton fermeture externe. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
         * @var string|bool|callable $header Affichage de l'entête de la fenêtre de dialogue. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
         * @var string|bool|callable $body Affichage du corps de la fenêtre de dialogue. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
         * @var string|bool|callable $footer Affichage du pied de la fenêtre de dialogue. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
         * @var bool $in_footer Ajout automatique de la fenêtre de dialogue dans le pied de page du site
         */
        extract($attrs);

        // Traitement des valeurs booléen
        foreach(['backdrop_close_button', 'header', 'body', 'footer'] as $bool) :
            if (in_array(${$bool}, ['true', 'false'])) :
                ${$bool} = filter_var(${$bool}, FILTER_VALIDATE_BOOLEAN);
            endif;
        endforeach;


        $output = "";
        // Bouton de fermeture externe
        if ($backdrop_close_button) :
            if (is_bool($backdrop_close_button) || is_callable($backdrop_close_button)) :
                ob_start();
                call_user_func(is_bool($backdrop_close_button) ? get_called_class() . '::backdropCloseButton' : $backdrop_close_button, $attrs);
                $output .= ob_get_clean();
            else :
                $output .= $backdrop_close_button;
            endif;
        endif;

        // Entête de la fenêtre de dialogue
        if ($header) :
            if (is_bool($header) || is_callable($header)) :
                ob_start();
                call_user_func(is_bool($header) ? get_called_class() . '::header' : $header, $attrs);
                $header = ob_get_clean();
            endif;
        endif;

        // Corps de la fenêtre de dialogue
        if ($body) :
            if (is_bool($body) || is_callable($body)) :
                ob_start();
                call_user_func(is_bool($body) ? get_called_class() . '::body' : $body, $attrs);
                $body = ob_get_clean();
            endif;
        endif;

        // Pied de la fenêtre de dialogue
        if ($footer) :
            if (is_bool($footer) || is_callable($footer)) :
                ob_start();
                call_user_func(is_bool($footer) ? get_called_class() . '::footer' : $footer, $attrs);
                $footer = ob_get_clean();
            endif;
        endif;

        // Fenêtre de dialogue
        $size = is_string($dialog_size) ? "modal-{$dialog_size}" : "modal-normal";
        ob_start();
        self::tFyAppGetTemplatePart('dialog', $id, compact('size', 'header', 'body', 'footer', 'attrs'));
        $output .= ob_get_clean();

        echo $output;
        exit;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage de la fenêtre de dialogue
     *
     * @uses \tify_control_contact_toggle($attrs, $echo) - Appel depuis les templates uniquement
     * @uses \tiFy\Core\Control\Control::display('contact_toggle', $attrs, $echo); - Appel depuis les classes et/ou méthodes
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $id Identifiant de qualification du contrôleur d'affichage
     *      @var string $container_id ID HTML du conteneur de notification
     *      @var string $container_class Classe HTML du conteneur de notification
     *      @var array $container_attrs Attributs HTML du conteneur
     *      @var array $options Options d'affichage
     *      @var bool $animation Activation de l'animation
     *      @var string $dialog_size Taille d'affichage de la fenêtre de dialogue normal|lg|sm|full
     *      @var bool|string $backdrop_close_button Affichage d'un bouton fermeture externe. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
     *      @var bool|string $header Affichage de l'entête de la fenêtre. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
     *      @var bool|string $body Affichage du corps de la fenêtre. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
     *      @var bool|string $footer Affichage d'un bouton fermeture externe. Chaine de caractère à afficher ou booléen pour activer désactiver ou fonction/méthode d'affichage.
     *      @var bool $in_footer Ajout automatique de la fenêtre de dialogue dans le pied de page du site
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected function display($attrs = [], $echo = true)
    {
        // Traitement des attributs de configuration
        $defaults = [
            'id'              => 'tiFySetContatToggleControl-Modal-' . $this->getId(),
            'container_id'    => 'tiFySetContatToggleControl-Modal--' . $this->getId(),
            'container_class' => '',
            'container_attrs' => [],

            'options' => [
                'backdrop' => true,
                'keyboard' => true,
                'focus'    => true,
                'show'     => true
            ],

            'animation'   => true,
            'dialog_size' => 'normal',

            'backdrop_close_button' => true,
            'header'                => true,
            'body'                  => true,
            'footer'                => true,

            'in_footer' => true
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        $attrs['container_class'] = 'tiFySetContatToggleControl-Modal' . ($attrs['container_class'] ? " {$attrs['container_class']}" : '');
        $attrs['container_attrs']['data-options'] = rawurlencode(
            json_encode(
                [
                    'ajax_action' => 'tiFySetCoreControl_ContactToggle',
                    'ajax_nonce'  => wp_create_nonce('tiFySetContatToggleControl-Modal'),
                    'attrs'       => $attrs,
                ]
            )
        );

        // Court-circutage des éléments d'affichage de la modale. Ces éléments seront chargés via ajax.
        $attrs['header'] = false;
        $attrs['body'] = false;
        $attrs['footer'] = false;

        return Control::Modal($attrs, $echo);
    }

    /**
     * Affichage d'un controleur d'exécution de l'affichage d'une boîte de dialogue
     *
     * @uses \tify_control_modal_trigger($attrs, $echo); - Appel depuis les templates uniquement
     * @uses \tiFy\Core\Control\Control::call('modal', 'trigger', $attrs, $echo) - Appel depuis les classes et/ou méthodes
     *
     * @param array $attrs {
     *      Liste des attributs de configuration
     *
     *      @var string $id Identifiant de qualification du contrôleur d'affichage
     *      @var string $container_id ID HTML du conteneur de notification
     *      @var string $container_class Classe HTML du conteneur de notification
     *      @var array $container_attrs Attributs HTML du conteneur
     *      @var string $container_tag Balise HTML
     *      @var string $text Texte
     *      @var string $target Identifiant de qualification de la fenêtre de dialogue à lancer
     *      @var bool|array $modal {
     *          Liste des attributs de configuration de la fenêtre de dialogue
     *          @see \tiFy\Core\Control\Modal\Modal::display()
     *      }
     * }
     * @param bool $echo Activation de l'affichage
     *
     * @return string
     */
    protected function trigger($attrs = [], $echo = true)
    {
        $defaults = [
            'id'              => 'tiFySetContatToggleControl-ModalTrigger-' . $this->getId(),
            'container_id'    => 'tiFySetContatToggleControl-ModalTrigger--' . $this->getId(),
            'container_class' => 'btn btn-primary button-primary',
            'container_attrs' => [],
            'container_tag'   => 'button',
            'text'            => __('Lancer', 'tify'),
            'target'          => 'tiFySetContatToggleControl-Modal-' . $this->getId(),
            'modal'           => true
        ];
        $attrs = \wp_parse_args($attrs, $defaults);

        /**
         * @var string $id Identifiant de qualification du contrôleur d'affichage
         * @var string $container_id ID HTML du conteneur de notification
         * @var string $container_class Classe HTML du conteneur de notification
         * @var array $container_attrs Attributs HTML du conteneur
         * @var string $container_tag Balise HTML
         * @var string $text Texte
         * @var string $target Identifiant de qualification de la fenêtre de dialogue à lancer
         * @var bool|array $modal {
         *          Liste des attributs de configuration de la fenêtre de dialogue
         *          @see \tiFy\Core\Control\Modal\Modal::display()
         *      }
         */
        extract($attrs);

        $output  = "";
        $output .= "<{$container_tag}";

        // Attributs du conteneur
        $container_attrs['id'] = $container_id;
        $container_attrs['class'] = "tiFyControl-modalTrigger tiFySetContatToggleControl-ModalTrigger" . ($container_class ? " {$container_class}" : '');
        $container_attrs['data-toggle'] = "tiFyControl-Modal";
        $container_attrs['data-target'] = isset($modal['id']) ? $modal['id'] : "{$target}";
        if (($container_tag === 'a') && !isset($container_attrs['href'])) :
            $container_attrs['href'] = "#{$container_id}";
        endif;
        foreach ($container_attrs as $k => $v) :
            $output .= " {$k}=\"{$v}\"";
        endforeach;

        $output .= ">";
        $output .= $text;
        $output .= "</{$container_tag}>";

        // Chargement de la modal
        if ($modal) :
            if(!is_array($modal)) :
                $modal = [];
            endif;
            if (!isset($modal['id'])) :
                $modal['id'] = $target;
            endif;
            if (!isset($modal['options'])) :
                $modal['options'] = ['show' => false];
            elseif (!isset($modal['options']['show'])) :
                $modal['options']['show'] = false;
            endif;

            call_user_func(get_called_class() . '::display', $modal);
        endif;

        echo $output;
    }

    /**
     * Affichage du bouton de fermeture externe
     *
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/backdrop_close_button.php - Surchage globale
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/backdrop_close_button-%%modal_id%%.php - Surchage qualifiée
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    public static function backdropCloseButton($attrs)
    {
        return self::tFyAppGetTemplatePart('backdrop_close_button', $attrs['id'], $attrs);
    }

    /**
     * Affichage de l'entête de la fenêtre de dialogue
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/header.php - Surchage globale
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/header-%%modal_id%%.php - Surchage qualifiée
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    public static function header($attrs = [])
    {
        return self::tFyAppGetTemplatePart('header', $attrs['id'], $attrs);
    }

    /**
     * Affichage de corps de la fenêtre de dialogue
     *
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/body.php - Surchage globale
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/body-%%modal_id%%.php - Surchage qualifiée
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    public static function body($attrs = [])
    {
        return self::tFyAppGetTemplatePart('body', $attrs['id'], $attrs);
    }

    /**
     * Affichage du pied de la fenêtre de dialogue
     *
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/footer.php - Surchage globale
     * @override file wp-content/themes/%%current_theme%%/templates/set/ContactToggle/Control/footer-%%modal_id%%.php - Surchage qualifiée
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    public static function footer($attrs = [])
    {
        return self::tFyAppGetTemplatePart('footer', $attrs['id'], $attrs);
    }

    /**
     * Réponse
     */
    public function response($query_args = [])
    {
        return get_option('admin_email');
    }
}