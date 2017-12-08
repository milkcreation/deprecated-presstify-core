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
use tiFy\Components;

add_action('tify_components_register', 'my_tify_components_register');
function my_tify_components_register()
{
    return Components::register(
        'Api',
        array(
            'google-map'     => array( 
                'key'           => '1234567890ABCDEF'
            )
        )
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le le dossier components/Api de l'environnement de surcharge.
/app/Components/Api/Config.php

```php
<?php
namespace App\Components\Api

class Config extends \tiFy\App\Config
{
    public function sets( $attrs )
    {
        $attrs['google-map'] = '1234567890ABCDEF';
        
        return $attrs;
    }
}
?>
```