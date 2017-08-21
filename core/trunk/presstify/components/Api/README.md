# Gestionnaire d'API

Permet de configurer les identifiants et d'interagir avec les API de nombreux webservices :
- Facebook
- GoogleMap
- Recaptcha
- Vimeo
- Youtube

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/Api.yml

```yml
google-map:
  key:
recaptcha:
  sitekey:
  secretkey:
youtube:
  key:
vimeo:
  client_id:
  client_secret:
facebook:
  app_id:
  app_secret:
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Params;

add_action( 'tify_params_set', 'my_tify_params_set' );
function my_tify_params_set()
{
    return Params::set(
        'components', 
        'Api',
        array(
            'google-map'     => array( 
                'key'           => '1234567890ABCDEF'
            )
        ), 
        true
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier app d'un plugin, d'un set ou du theme.
/app/Components/Api/Config.php

```php
<?php
namespace MyNamespace\App\Components\Api

class Config extends \tiFy\Abstracts\Config
{
    public function sets( $attrs )
    {
        $attrs['google-map'] = '1234567890ABCDEF';
        
        return $attrs;
    }
}
?>
```
