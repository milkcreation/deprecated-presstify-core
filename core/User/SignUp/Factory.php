<?php

namespace tiFy\Core\User\SignUp;

use tiFy\Core\Forms\Forms;

final class Factory extends \tiFy\App\FactoryConstructor
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        parent::__construct($id, $attrs);

        // Déclaration des événements
        $this->appAddAction('tify_form_register');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Déclaration du formulaire
     */
    final public function tify_form_register()
    {
        $attrs = $this->getAttrList();

        $defaults = [
            'fields' => [
                [
                    'slug'         => 'login',
                    'label'        => __('Identifiant', 'tify'),
                    'type'         => 'input',
                    'required'     => true,
                    'addons'       => [
                        'user'  => [
                            'userdata' => 'user_login'
                        ]
                    ]
                ],
                [
                    'slug'         => 'email',
                    'label'        => __('Adresse e-mail', 'tify'),
                    'placeholder'  => __('Indiquez votre e-mail', 'tify'),
                    'type'         => 'input',
                    'integrity_cb' => 'is_email',
                    'required'     => true,
                    'addons'       => [
                        'user'  => [
                            'userdata' => 'user_email'
                        ]
                    ]
                ],
                [
                    'slug'         => 'password',
                    'label'        => __('Mot de passe', 'tify'),
                    'placeholder'  => __('Renseignez un mot de passe', 'tify'),
                    'type'         => 'password',
                    'integrity_cb' => 'is_valid_password',
                    'required'     => true,
                    'addons'       => [
                        'user'  => [
                            'userdata' => 'user_pass'
                        ]
                    ]
                ],
                [
                    'slug'         => 'confirm',
                    'label'        => __('Confirmation de mot de passe', 'tify'),
                    'placeholder'  => __('Confirmez votre mot de passe', 'tify'),
                    'type'         => 'password',
                    'integrity_cb' => [
                        'function' => 'compare',
                        'args'     => ['%%password%%'],
                        'error'    => __('Les champs "Mot de passe" et "Confirmation de mot de passe" doivent correspondre',
                            'tify')
                    ],
                    'required'     => true
                ],
                [
                    'slug'         => 'captcha',
                    'label'        => __('Code de sécurité', 'tify'),
                    'type'         => 'simple-captcha-image',
                    'required'     => true
                ]
            ],
            'addons' => ['user' => true]
        ];
        $attrs = array_merge($defaults, $attrs);

        return Forms::register('tiFyCore-userSignUp--' . $this->getId(), $attrs);
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage du formulaire
     *
     * @return string
     */
    public function form()
    {
        return Forms::display('tiFyCore-userSignUp--' . $this->getId());
    }

    /**
     * Affichage du formulaire
     * @use \tiFy\Core\User\SignUp\SignUp::get($name)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->form();
    }
}