# Interface d'administration

Permet de gérer l'interface d'administration : 
- Activation d'éléments
- Desactivation 
- Nettoyage
- ...

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/AdminUI.yml

```yml
admin_bar_menu_logo :
remove_admin_bar_menu:
admin_footer_text:
remove_menu:
unregister_widget:
remove_support_post:
remove_support_page:
remove_dashboard_meta_box:
remove_meta_box_post:
remove_meta_box_page:
disable_comment:                false
disable_post_category:          false
disable_post_tag:               false
disable_post:                   false
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Components;

add_action('tify_components_register', 'my_tify_components_register');
function my_tify_components_register()
{
    return Components::register(
        'AdminUI',
        array(
            'remove_admin_bar_menu'     => array( 
                'wp_logo', 'about', 'wporg', 'documentation', 'support-forums', 'feedback', 'site-name', 'view-site', 'updates', 'comments', 'new-content', 'my-account', 'disable_post', 'disable_post_category', 'disable_post_tag', 'disable_comment' 
            )
        )
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier components/AminUI de l'environnement de surcharge.
/app/Components/AdminUI/Config.php

```php
<?php
namespace App\Components\AdminUI

class Config extends \tiFy\App\Config
{
    public function set_remove_admin_bar_menu()
    {
        return array( 'wp_logo', 'about', 'wporg', 'documentation', 'support-forums', 'feedback', 'site-name', 'view-site', 'updates', 'comments', 'new-content', 'my-account', 'disable_post', 'disable_post_category', 'disable_post_tag', 'disable_comment' );
    }
}
?>
```