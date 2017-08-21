# Affichage de fil d'Ariane

Permet d'afficher le fil d'Ariane des page de contenus Wordress 

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/Breadcrumb.yml

```yml
# Id HTML du conteneur
id:           ''

# Classe HTML du conteneur
class:        ''

# Contenu HTML affiché avant le fil d'Ariane 
before:       ''

# Contenu HTML affiché après le fil d'Ariane
after:        ''
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
        'Breadcrumb',
        array(
            'id'        => '',
            'class'     => '',
            'before'    => '',
            'after'     => ''
        ), 
        true
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier app d'un plugin, d'un set ou du theme.
/app/Components/Breadcrumb/Config.php

```php
<?php
namespace MyNamespace\App\Components\Breadcrumb

class Config extends \tiFy\Abstracts\Config
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
