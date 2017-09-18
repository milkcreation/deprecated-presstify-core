# Mise à jour

Permet de déclarer des mises à jour des composants, plugins, sets, themes

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/core/Update.yml

```yml
%update_id%:
  # Habilitation d'exécution de la mise à jour
  cap:      'manage_options'   
  # Notification de mise à jour de l'interface d'administration
  admin_notice:
    # Message de la notification - %s lien de déclenchement de la mise à jour
    message:    <?php _e( 'Des mises à jour sont disponibles %s', 'tify' );?>
    # Page d'affichage de la notification - \WP_Screen::id | '' pour toutes les pages de l'interface d'administration
    screen_id:  ''
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Core;

add_action( 'tify_core_register', 'my_tify_core_register' );
function my_tify_core_register()
{
    return Core::register(
        'Update',
        array(
            '%update_id%'       => array(
                // Habilitation d'exécution de la mise à jour
                'cap'             => 'manage_options',
                // Notification de mise à jour de l'interface d'administration
                'admin_notice'    => array(
                    /// Message de la notification - %s lien de déclenchement de la mise à jour
                    'message'           => __( 'Des mises à jour sont disponibles %s', 'tify' ),
                    /// Page d'affichage de la notification - \WP_Screen::id | '' pour toutes les pages de l'interface d'administration
                    'screen_id'         => ''
                )
            )
        )
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier core/Update de l'environnement de surcharge.
/app/Core/Update/Config.php

```php
<?php
namespace App\Core\Update

class Config extends \tiFy\App\Config
{
    public function set_%update_id%( $attrs = array() )
    {
        // Habilitation d'exécution de la mise à jour
        $attrs['cap'] = 'manage_options';
        // Notification de mise à jour de l'interface d'administration
        $attrs['admin_notice'] = array(
            /// Message de la notification - %s lien de déclenchement de la mise à jour
            'message'       => __( 'Des mises à jour sont disponibles %s', 'tify' ),
            /// Page d'affichage de la notification - \WP_Screen::id | '' pour toutes les pages de l'interface d'administration
            'screen_id'     => ''
        );
        return $attrs;
    }
}
?>
```

## Déclaration ponctuelle de mises à jour

Dans une fonction ou un objet

```php
<?php
use tiFy\Core\Update\Update;

add_action('tify_update_register', 'my_tify_update_register');
function my_tify_update_register()
{
    return Update::register(
        '%update_id%', 
        array(
            // Habilitation d'exécution de la mise à jour
            'cap'             => 'manage_options',
            // Notification de mise à jour de l'interface d'administration
            'admin_notice'          => array(
                /// Message de la notification - %s lien de déclenchement de la mise à jour
                'message'           => __( 'Des mises à jour sont disponibles %s', 'tify' ),
                /// Page d'affichage de la notification - \WP_Screen::id | '' pour toutes les pages de l'interface d'administration
                'screen_id'         => ''
            )
        )
    );
}
?>
```

## Création de mise à jour

```php
<?php
namespace MyNamespace\App\Core\Update;

class %Update_Id% extends \tiFy\Core\Update\Factory
{
    function version_%numéro de version%()
    {
        faire quelque chose ...
        
        if($success) :
            return true;
        else :
            return new \WP_Error( 'Echec_Code', 'Message d\'échec' );
        endif;
    }
}
?>
```
