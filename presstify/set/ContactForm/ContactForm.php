<?php
namespace tiFy\Set\ContactForm;

use tiFy\Core\Forms\Forms;
use tiFy\Core\Options\Options;
use tiFy\Core\Router\Router;
use tiFy\Core\Router\Taboox\Helpers\ContentHook;

class ContactForm extends \tiFy\App\Set
{
    /**
     * Liste des actions à déclencher
     * @var string[]
     * @see https://codex.wordpress.org/Plugin_API/Action_Reference
     */
    protected $tFyAppActions = ['tify_form_register', 'tify_router_register'];

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();

        // Initialisation des fonctions d'aide à la saisie
        include self::tFyAppDirname() . '/Helpers.php';

        add_filter('the_content', 'tiFy\Set\ContactForm\ContactForm::the_content');
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration de formulaire
     *
     * @return void
     */
    public function tify_form_register()
    {
        $attrs = self::tFyAppConfig('form');

        return Forms::register(
            (isset($attrs['ID']) ? $attrs['ID'] : 'tiFySetContactForm'),
            $attrs
        );
    }

    /**
     * Déclaration de route
     *
     * @return void
     */
    public function tify_router_register()
    {
        // Bypass
        if(! $router = self::tFyAppConfig('router'))
            return;

        $defaults = ['title' => __('Formulaire de contact', 'tify'), 'option_name' => 'tiFySetContactForm-hook_id'];

        $args = is_bool($router) ? $defaults : \wp_parse_args($router, $defaults);

        return Router::register(
            'tiFySetContactForm',
            $args
        );
    }

    /**
     *
     */
    final public static function the_content($content)
    {
        // Bypass
        if (! in_the_loop())
            return $content;
        if (! is_singular())
            return $content;
        if (ContentHook::get('tiFySetContactForm') !== get_the_ID())
            return $content;

        // Masque le contenu et le formulaire sur la page d'accroche
        if (! $content_display = self::tFyAppConfig('content_display')) :
            return '';

        // Affiche uniquement le contenu de la page
        elseif ($content_display === 'only') :
            return $content;
        endif;

        $output  = "";
        if (($content_display === 'before') || ($content_display === true)) :
            $output .= $content;
        endif;

        $output .= self::displayForm(false);
        if ($content_display === 'after' ) :
            $output .= $content;
        endif;

        remove_filter(current_filter(), __METHOD__, 10);

        return $output;
    }

    /**
     *
     */
    public static function displayForm($echo = true)
    {
        $form_attrs = self::tFyAppConfig('form');
        $form_id = isset($form_attrs['ID']) ? $form_attrs['ID'] : 'tiFySetContactForm';

        return Forms::display($form_id, $echo);
    }
}