# Composants Natifs

## Déclaration et Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Déclaration et Configuration "semi-dynamique" YAML 
Créer un fichier de configuration .yml dans votre dossier de configuration.
/config/core/%core_id%.yml

```yml
param_1 :   ''
param_2 :   ''
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Déclaration et Configuration "dynamique" PHP 
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

Le composant natif doit être déclaré
Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier core/%core_id% de l'environnement de surcharge.
/app/Core/%core_id%/Config.php

```php
<?php
namespace App\Core\%core_id%

class Config extends \tiFy\App\Config
{
    /**
     * Traitement global des attributs de configuration
     * 
     * @param array $attrs
     * 
     * @return array|mixed
     */
    public function sets($attrs = array())
    {
        return array(
            'param_1'	=> '',
            'param_2'	=> ''
        );
    }
}
```