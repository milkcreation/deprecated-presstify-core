# ContactForm

Le jeu de fonctionnalités ContactForm permet de créer de facon rapide un formulaire de contact pour votre site.
Nativement, il est doté :
 - d'un formulaire de contact préconfiguré
 - de l'interface d'administration permettant de configurer un email de notification aux administrateurs ainsi qu'un email de confirmation à l'expéditeur du mail.
 - de l'interface d'administration permettant de router automatiquement l'affichage du formulaire de contact sur une page du site.


## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/set/ContactForm.yml

```yml
# @var string|bool $content_display Affichage du contenu de la page
#   'before'|true : Affiche le contenu du post avant le formulaire
#   'after' : Affiche le contenu du post après le formulaire
#   'hide' : Masque le contenu du post
#   'only' : Affiche seulement le contenu du post, le formulaire est masqué et doit être appelé manuellement
#   false : Masque à la fois le contenu du post et le formulaire
content_display:            'before'

# @var array $form Attributs de configuration du formulaire
# @see \tiFy\Core\Forms\Forms::register()
form:
  title:            "<?php _e( 'Formulaire de contact', 'tify' );?>"
  container_class:  'tiFySetContactForm'
  fields:
    -
      slug:         'lastname'
      label:        "<?php _e( 'Nom', 'tify' );?>"
      placeholder:  "<?php _e( 'Renseignez votre nom', 'tify' );?>"
      type:         'input'
      required:     true
    -
      slug:         'firstname'
      label:        "<?php _e( 'Prénom', 'tify' );?>"
      placeholder:  "<?php _e( 'Renseignez votre prénom', 'tify' );?>"
      type:         'input'
      required:     true
    -
      slug:         'email'
      label:        "<?php _e( 'Adresse mail', 'tify' );?>"
      placeholder:  "<?php _e( 'Indiquez votre adresse email', 'tify' );?>"
      type:         'input'
      integrity_cb: 'is_email'
      required:     true
    -
      slug:         'subject'
      label:        "<?php _e( 'Sujet du message', 'tify' );?>"
      placeholder:  "<?php _e( 'Sujet de votre message', 'tify' );?>"
      type:         'input'
      required:     true
    -
      slug:         'message'
      label:        "<?php _e( 'Message', 'tify' );?>"
      placeholder:  "<?php _e( 'Votre message', 'tify' );?>"
      type:         'textarea'
      required:     true
    -
      slug:         'captcha'
      label:        "<?php _e( 'Code de sécurité', 'tify' );?>"
      placeholder:  "<?php _e( 'Code de sécurité', 'tify' );?>"
      type:         'simple-captcha-image'
  addons:
    mailer:       true

# @param array $router {
#     Attributs de configuration de la page d'affichage du formulaire
#
#     @param string $title Intitulé de qualification de la route
#     @param string $desc Texte de description de la route
#     @param string object_type Type d'objet (post|taxonomy) en relation avec la route
#     @param string object_name Nom de qualification de l'objet en relation (ex: post, page, category, tag ...)
#     @param string option_name Clé d'index d'enregistrement en base de données
#     @param int selected Id de l'objet en relation
#     @param string list_order Ordre d'affichage de la liste de selection de l'interface d'administration
#     @param string show_option_none Intitulé de la liste de selection de l'interface d'administration lorsqu'aucune relation n'a été établie
# }
router:              true
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Set;

add_action('tify_set_register', 'my_tify_set_register');
function my_tify_set_register()
{
    return Set::register( 
        'ContactForm',
        [
            /**
             * @param bool|string $content_display Affichage du contenu de la page
             *   'before'|true Affiche le contenu du post avant le formulaire
             *   'after' Affiche le contenu du post après le formulaire
             *   'hide' Masque le contenu du post
             *   'only' Affiche seulement le contenu du post, le formulaire est masqué et doit être appelé manuellement
             *   false Masque à la fois le contenu du post et le formulaire
             */
            'content_display'   => true,
            
            /**
             * @var array $form Attributs de configuration du formulaire
             * @see \tiFy\Core\Forms\Forms::register()
             */
            'form'      => [
                'title'             => __('Formulaire de contact', 'tify'),
                'container_class'   => 'tiFySetContactForm',
                'fields'            => [
                    [
                        'slug'          => 'lastname',
                        'label'         => __('Nom', 'tify' ),
                        'placeholder'   => __('Renseignez votre nom', 'tify'),
                        'type'          => 'input',
                        'required'      => true
                    ],
                    [
                        'slug'          => 'firstname',
                        'label'         => __('Prénom', 'tify' ),
                        'placeholder'   => __('Renseignez votre prénom', 'tify'),
                        'type'          => 'input',
                        'required'      => true
                    ],
                    [
                        'slug'          => 'email',
                        'label'         => __('Adresse mail', 'tify' ),
                        'placeholder'   => __('Indiquez votre adresse email', 'tify'),
                        'type'          => 'input',
                        'integrity_cb'  => 'is_email',
                        'required'      => true
                    ],
                    [
                        'slug'          => 'subject',
                        'label'         => __('Sujet du message', 'tify'),
                        'placeholder'   => __('Sujet de votre message', 'tify'),
                        'type'          => 'input',
                        'required'      => true
                    ],
                    [
                        'slug'          => 'message',
                        'label'         => __('Message', 'tify'),
                        'placeholder'   => __('Votre message', 'tify'),
                        'type'          => 'textarea',
                        'required'      => true
                    ],
                    [
                        'slug'          => 'captcha',
                        'label'         => __('Code de sécurité', 'tify'),
                        'placeholder'   => __('Code de sécurité', 'tify'),
                        'type'          => 'simple-captcha-image'
                    ]
                ],
                'addons'            => [
                    'mailer'            => true
                ]
            ],
            
            /**
             * @param array $router {
             *      Attributs de configuration de la page d'affichage du formulaire
             * 
             *      @param string $title Intitulé de qualification de la route
             *      @param string $desc Texte de descritpion de la route
             *      @param string object_type Type d'objet (post|taxonomy) en relation avec la route
             *      @param string object_name Nom de qualification de l'objet en relation (ex: post, page, category, tag ...)
             *      @param string option_name Clé d'index d'enregistrement en base de données
             *      @param int selected Id de l'objet en relation
             *      @param string list_order Ordre d'affichage de la liste de selection de l'interface d'administration
             *      @param string show_option_none Intitulé de la liste de selection de l'interface d'administration lorsqu'aucune relation n'a été établie
             * }
             */
            'router'         => true
        ]
    );
}
?>
```
### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier set/ContactForm de l'environnement de surcharge.
/app/Set/ContactForm/Config.php

```php
<?php
namespace App\Set\ContactForm;

class Config extends \tiFy\App\Config
{
    public function sets($attrs = [])
    {
        return [
           /**
            * @param bool|string $content_display Affichage du contenu de la page
            *   'before'|true Affiche le contenu du post avant le formulaire
            *   'after' Affiche le contenu du post après le formulaire
            *   'hide' Masque le contenu du post
            *   'only' Affiche seulement le contenu du post, le formulaire est masqué et doit être appelé manuellement
            *   false Masque à la fois le contenu du post et le formulaire
            */
           'content_display'   => true,
           
           /**
            * @var array $form Attributs de configuration du formulaire
            * @see \tiFy\Core\Forms\Forms::register()
            */
           'form'      => [
               'title'              => __('Formulaire de contact', 'tify'),
               'container_class'    => 'tiFySetContactForm',
               'fields'             => [
                   [
                       'slug'          => 'lastname',
                       'label'         => __('Nom', 'tify' ),
                       'placeholder'   => __('Renseignez votre nom', 'tify'),
                       'type'          => 'input',
                       'required'      => true
                   ],
                   [
                       'slug'          => 'firstname',
                       'label'         => __('Prénom', 'tify' ),
                       'placeholder'   => __('Renseignez votre prénom', 'tify'),
                       'type'          => 'input',
                       'required'      => true
                   ],
                   [
                       'slug'          => 'email',
                       'label'         => __('Adresse mail', 'tify' ),
                       'placeholder'   => __('Indiquez votre adresse email', 'tify'),
                       'type'          => 'input',
                       'integrity_cb'  => 'is_email',
                       'required'      => true
                   ],
                   [
                       'slug'          => 'subject',
                       'label'         => __('Sujet du message', 'tify'),
                       'placeholder'   => __('Sujet de votre message', 'tify'),
                       'type'          => 'input',
                       'required'      => true
                   ],
                   [
                       'slug'          => 'message',
                       'label'         => __('Message', 'tify'),
                       'placeholder'   => __('Votre message', 'tify'),
                       'type'          => 'textarea',
                       'required'      => true
                   ],
                   [
                       'slug'          => 'captcha',
                       'label'         => __('Code de sécurité', 'tify'),
                       'placeholder'   => __('Code de sécurité', 'tify'),
                       'type'          => 'simple-captcha-image'
                   ]
               ],
               'addons'            => [
                   'mailer'            => true
               ]
           ],
           
           /**
            * @param array $router {
            *      Attributs de configuration de la page d'affichage du formulaire
            * 
            *      @param string $title Intitulé de qualification de la route
            *      @param string $desc Texte de descritpion de la route
            *      @param string object_type Type d'objet (post|taxonomy) en relation avec la route
            *      @param string object_name Nom de qualification de l'objet en relation (ex: post, page, category, tag ...)
            *      @param string option_name Clé d'index d'enregistrement en base de données
            *      @param int selected Id de l'objet en relation
            *      @param string list_order Ordre d'affichage de la liste de selection de l'interface d'administration
            *      @param string show_option_none Intitulé de la liste de selection de l'interface d'administration lorsqu'aucune relation n'a été établie
            * }
            */
           'router'         => true
       ];
    }
}
?>
```