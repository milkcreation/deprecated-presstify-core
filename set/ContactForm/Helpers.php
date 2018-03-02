<?php
namespace
{
    use tiFy\Set\ContactForm\ContactForm;
    use tiFy\Core\Forms\Forms;
    use tiFy\Core\Router\Taboox\Helpers\ContentHook;

    /**
     * Vérification d'affichage du formulaire de contact sur une page
     *
     * @param int|object $post Page de contenu du site d'affichage du formulaire de contact. Par défaut la vérification est faites sur la page courante.
     *
     * @return bool
     */
    function tify_set_contactform_is($post = 0)
    {
        return ContentHook::is('tiFySetContactForm', $post);
    }

    /**
     * Récupération de la identifiant de la page d'affichage du formulaire de contact
     *
     * @param int $default Valeur de retour par défaut
     *
     * @return null|int
     */
    function tify_set_contactform_hook_id($default = 0)
    {
        return ContentHook::get('tiFySetContactForm', $default);
    }

    /**
     * Affichage du formulaire de contact
     */
    function tify_set_contactform_display($echo = true)
    {
        return ContactForm::displayForm($echo);
    }
}