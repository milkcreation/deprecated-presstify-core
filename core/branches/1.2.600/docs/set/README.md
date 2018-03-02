# Jeux de fonctionnalités

Les jeux de fonctionnalités sont le support de développement personnalisé principal. 
Pour être actif ils doivent d'abord être enregistrés dans la base de jeux de fonctionnalités de PresstiFy.

## Enregistrement d'un jeu de configuration personnalisé

### METHODE 1 | Intégrateur - priorité basse

Enregistrement "semi-dynamique" YAML 
Editer le fichier de configuration config.yml dans votre dossier de configuration

```yml

...

set:
  %set_id%:
  	# Espace de nom du jeu de fonctionnalités
    namespace:    '%set_namespace%'
    # Répertoire de stockage du jeu de fonctionnalités
    base_dir:     '%set_base_dir%'
    # Nom de la classe principale - %set_id% par défaut
    bootstrap:    '%set_bootstrap%'

...

```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Enregistrement "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Set;

add_action( 'tify_set_load', 'my_tify_set_load' );
function my_tify_set_load()
{
    Set::load(
        '%set_id%',
        array(
		  	// Espace de nom du jeu de fonctionnalités
		    'namespace'		=> '%set_namespace%',
		   	// Répertoire de stockage du jeu de fonctionnalités
		    'base_dir'		=> '%set_base_dir%',
		   	// Nom de la classe principale - %set_id% par défaut
		    'bootstrap'		=> '%set_bootstrap%'	
       ), 
       true
    );
}
?>
```

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration .yml dans votre dossier de configuration.
/config/set/%set_id%.yml

```yml

```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Set;

add_action( 'tify_set_register', 'my_tify_set_register' );
function my_tify_set_register()
{
    return Set::register(
        '%set_id%',
        array(
        )
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier set/%set_id% de l'environnement de surcharge.
/app/Set/%set_id%/Config.php

```php
<?php
namespace App\Set\%set_id%

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