<?php

namespace tiFy\Core\Forms\Form;

abstract class AbstractDependency extends \tiFy\App
{
    /**
     * Formulaire de référence
     * @var null|\tiFy\Core\Forms\Form\Form
     */
    private $Form = null;

    /**
     * CONSTRUCTEUR
     *
     * @param \tiFy\Core\Forms\Form\Form $Form Formulaire de référence
     *
     * @return void
     */
    public function __construct(\tiFy\Core\Forms\Form\Form $Form)
    {
        parent::__construct();

        // Définition du formulaire de référence
        $this->Form = $Form;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération du formulaire de référence
     *
     * @return \tiFy\Core\Forms\Form\Form
     */
    final public function getForm()
    {
        return $this->Form;
    }

    /**
     * Récupération d'un champ selon son identifiant de qualification
     *
     * @param string $slug Identifiant de qualification
     *
     * @return null|\tiFy\Core\Forms\Form\Field
     */
    final public function getField($slug)
    {
        if ($form = $this->getForm()) :
            return $form->getField($slug);
        endif;
    }

    /**
     * Récupération d'un addon du formulaire de référence
     *
     * @param string $name Nom de qualification de l'addon
     *
     * @return null|\tiFy\Core\Forms\Addons\Factory
     */
    final public function getAddon($name)
    {
        if ($form = $this->getForm()) :
            return $form->getAddon($name);
        endif;
    }

    /**
     * Récupération de la classe de rappel des notifications
     *
     * @return null|\tiFy\Core\Forms\Form\Notices
     */
    final public function getNotices()
    {
        // Bypass
        if ($form = $this->getForm()) :
            return $this->getForm()->notices();
        endif;
    }

    /**
     * Vérification d'existance d'erreur de traitement
     *
     * @return bool
     */
    public function hasError()
    {
        // Bypass
        if (!$notices = $this->getNotices()) :
            return false;
        endif;

        return $notices->has('error');
    }

    /**
     * Ajout d'une notification d'erreur de traitement
     *
     * @param string $message
     * @param mixed $data
     *
     * @return void
     */
    public function addError($message, $data = '')
    {
        // Bypass
        if (!$notices = $this->getNotices()) :
            return null;
        endif;

        return $notices->add('error', $message, $data);
    }

    /**
     * Récupération de la liste des notifications d'erreur de traitement
     *
     * @param array $args Attributs de récupération de la liste des erreurs
     *
     * @return array
     */
    public function getErrors($args = [])
    {
        // Bypass
        if (!$notices = $this->getNotices()) :
            return [];
        endif;

        return $notices->getByData('error', $args);
    }
}