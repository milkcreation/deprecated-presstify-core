# Affichage de fil d'Ariane

Permet d'afficher le fil d'Ariane des page de contenus Wordress 

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/Breadcrumb.yml

```yml
id:           ''
class:        ''
before:       ''
after:        ''
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
        'Breadcrumb',
        array(
            'id'        => '',
            'class'     => '',
            'before'    => '',
            'after'     => ''
        )
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php le dossier components/Breadcrumb de l'environnement de surcharge.
/app/Components/Breadcrumb/Config.php

```php
<?php
namespace App\Components\Breadcrumb

class Config extends \tiFy\App\Config
{
    public function sets( $attrs )
    {
        $attrs['id'] = '';
        $attrs['class'] = '';
        $attrs['before'] = '';
        $attrs['after'] = '';
        return $attrs;
    }
}
?>
```