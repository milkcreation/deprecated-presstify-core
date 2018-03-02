# Composants Dynamiques

## Déclaration et Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration .yml dans votre dossier de configuration.
/config/components/%component_id%.yml

```yml
param_1 :   ''
param_2 :   ''
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
use tiFy\Components;

add_action('tify_components_register', 'my_tify_components_register');
function my_tify_components_register()
{
    return Components::register(
        '%component_id%',
        array(
            'param_1'	=> '',
            'param_2'	=> ''
        )
    );
}
```

### METHODE 3 | Développeur avancé - priorité haute (recommandée)

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier components/%component_id% de l'environnement de surcharge.
/app/Components/%component_id%/Config.php

```php
<?php
namespace App\Components\%component_id%

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
            'param_1'   => '',
            'param_2'   => ''
        );
    }
}
```