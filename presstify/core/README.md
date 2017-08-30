# Composants Natifs

## Déclaration et Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration .yml dans votre dossier de configuration.
/config/core/%core_id%.yml

```yml
param_1 :   ''
param_2 :   ''
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
use tiFy\Core;

add_action('tify_core_register', 'my_tify_core_register');
function my_tify_core_register()
{
    return Core::register(
        '%core_id%',
        array(
            'param_1'	=> '',
            'param_2'	=> ''
        )
    );
}
```

### METHODE 3 | Développeur avancé - priorité haute (recommandée)

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier app d'un plugin, d'un set ou du theme.
/app/Core/%core_id%/Config.php

```php
<?php
namespace MyNamespace\App\Components\%core_id%

class Config extends \tiFy\App\Config
{
    public function sets()
    {
        return array(
            'param_1'	=> '',
            'param_2'	=> ''
        );
    }
}
?>
```