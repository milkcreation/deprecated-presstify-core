# Interfaces d'authentification

Composant PresstiFy permettant de déclarer des interfaces d'authentification.

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/Login.yml

```yml

```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Core;

add_action('tify_components_register', 'my_tify_components_register');
function my_tify_components_register()
{
    return Core::register(
        'Login',
        [
            'MyLoginUi' => [
                'login_form'    => [
                    'id'       => 'tiFyLogin-Form--' . $this->getId(),
                    'fields'   => ['username', 'password', 'remember', 'submit']
                ],
                'logout_link'   => [],
                'lost_password_link' => [],
                'roles'         => ['subscriber'],
                'redirect_url'  => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'],
                'attempt'       => -1,
                'errors_map'    => []
            ]
        ]
    );
}
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier core/Options de l'environnement de surcharge.
/app/Core/Options/Config.php

```php
<?php
namespace App\Core\Options;

class Config extends \tiFy\App\Config
{
    public function sets($attrs = [])
    {
        return [
           // @var string $hookname Identifiant de qualification de la page d'accroche d'affichage.
           'hookname'      => 'settings_page_tify_options',
           
           // @var string $menu_slug Identifiant de qualification du menu.
           'menu_slug'     => 'tify_options',
           
           // @var string $cap Habilitation d'accès
           'cap'           => 'manage_options',
           
           // @var string $page_title Intitulé de la page
           'page_title'    => "<?php _e('Options du thème', 'tify'); ?>",
           
           // @var string $menu_title
           'menu_title'    => "<?php bloginfo('name'); ?>",
           
           // @var array $admin_page Attributs de configuration de la page des options
           'admin_page'    => [],
           
           // @var array $admin_bar Attributs de configuration de la barre d'administration
           'admin_bar'     => [],
           
           // @var array $box Attributs de configuration de la boite à onglet
           'box'           => [],
           
           // @var array $nodes Liste des greffons
           'nodes'         => [],
           
           // @var string $render Style d'affichage de la page (standard|metaboxes|@todo méthode personnalisée|@todo function personnalisée).
           'render'        => 'standard'
       ];
    }
}
```

## Déclaration ponctuelle d'un greffon

Permet de déclarer une colonne personnalisée de manière ponctuelle depuis une fonction ou une méthode.

```php
<?php
use tiFy\Core\Options\Options;

add_action('tify_options_register_node', 'my_tify_options_register_node');
function my_tify_options_register_node()
{
    return Options::registerNode(
        // Attributs de configuration du greffon
        // @see tiFy\Core\Taboox\Taboox::registerNode
        [
           // @var string $id Identifiant du greffon.
           'id'         => '%%node_id%%',
           // @var string $title Titre du greffon.
           'title'      => __('Mon greffon personnalisé', 'Theme'),
           // @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
           
           // @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
           
           // @var string $parent Identifiant du greffon parent.
           
           // @var string $cap Habilitation d'accès au greffon.
           
           // @var bool $show Affichage/Masquage du greffon.
           
           // @var int $position Ordre d'affichage du greffon.
           
           // @var string $object post_type|taxonomy|user|option
           'object'     => 'option'
           // @var string $object_type
           
           // @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
        ]
    );
}
```