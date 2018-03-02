# Extensions

## Déclaration et Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration .yml dans votre dossier de configuration.
/config/plugins/%plugin_id%.yml

```yml
param_1 :   ''
param_2 :   ''
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
use tiFy\Plugins;

add_action('tify_plugins_register', 'my_tify_plugins_register');
function my_tify_plugins_register()
{
    return Plugins::register(
        '%plugin_id%',
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
/app/Plugins/%plugin_id%/Config.php

```php
<?php
namespace MyNamespace\Plugins\%plugin_id%

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