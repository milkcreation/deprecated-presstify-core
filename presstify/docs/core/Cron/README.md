# Tâches Planifiées

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/core/Cron.yml

```yml
my_custom_task:
  # Identifiant unique d'accorche de la tâche planifiée (optionnel)
  # default : tiFyCoreCron--my_custom_task
  hook:         ''
  
  # Intitulé de la tâche planifiée (recommandé)
  # default : my_custom_task
  title:        ''
  
  # Description de la tâche planifiée (recommandé)
  desc:         ''
  
  # Date d'exécution de la tâche planifiée (recommandé)
  # default : <?php echo mktime( date( 'H' )-1, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );?>
  timestamp:    ''
  
  # Fréquence d'exécution de la tâche planifiée (recommandé)
  # default : 'daily'
  recurrence:   ''
  
  # Arguments passés dans la tâche planifiée (optionnel)
  args:         []
            
  # Chemins de classe de surcharge (optionnel)
  path:         []
  
  # Activation de la journalisation (optionnel)
  log:
    ## Nom du fichier
    name:       my_custom_task
    ## Format du fichier de log
    format:     ''
    ## Rotation de fichier
    rotate:     10
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
        'Cron',
        [
            'my_custom_task' => [
                // Identifiant unique d'accorche de la tâche planifiée
                'hook'          => '',
                // Intitulé de la tâche planifiée
                'title'         => '',
                // Description de la tâche planifiée
                'desc'          => '',
                // Date d'exécution de la tâche planifiée
                'timestamp'     => '',
                // Fréquence d'exécution de la tâche planifiée
                'recurrence'    => 'daily',
                // Arguments passés dans la tâche planifiée
                'args'          => [],
                // Chemins de classe de surcharge
                'path'          => [],
                // Attributs de journalisation des données
                'log'           => true
            ]
        ]
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier app d'un plugin, d'un set ou du theme.
/app/Core/Cron/Config.php

```php
<?php
namespace App\Core\Cron;

class Config extends \tiFy\App\Config
{
    public function my_custom_task()
    {
        return [
            // Identifiant unique d'accorche de la tâche planifiée
            'hook'          => '',
            // Intitulé de la tâche planifiée
            'title'         => '',
            // Description de la tâche planifiée
            'desc'          => '',
            // Date d'exécution de la tâche planifiée
            'timestamp'     => '',
            // Fréquence d'exécution de la tâche planifiée
            'recurrence'    => 'daily',
            // Arguments passés dans la tâche planifiée
            'args'          => [],
            // Chemins de classe de surcharge
            'path'          => [],
            // Attributs de journalisation des données
            'log'           => true
        ];
    }
}
?>
```

## Déclaration ponctuelle de tâches planifiées

Dans une fonction ou un objet

```php
<?php
use tiFy\Core\Cron;

add_action( 'tify_cron_register', 'my_tify_cron_register' );
function my_tify_cron_register()
{
    return Cron::register(
        'my_custom_task', 
        [
            // Identifiant unique d'accorche de la tâche planifiée
            'hook'          => '',
            // Intitulé de la tâche planifiée
            'title'         => '',
            // Description de la tâche planifiée
            'desc'          => '',
            // Date d'exécution de la tâche planifiée
            'timestamp'     => '',
            // Fréquence d'exécution de la tâche planifiée
            'recurrence'    => 'daily',
            // Arguments passés dans la tâche planifiée
            'args'          => [],
            // Chemins de classe de surcharge
            'path'          => [],
            // Attributs de journalisation des données
            'log'           => true
        ]
    );
}
?>
```
## Mise en place de la tâche planifier

Modifier le fichier de configuration wp-config.php pour désactiver la tâche cron Web

```php
/** CRON **/
define('DISABLE_WP_CRON', true);
```

Editer les tâches planifiées du serveur ... 

```bash
$ sudo crontab -e
```
... et ajouter la tâche suivante (passage toutes les minutes)

```bash
* * * * * curl -I [url_du_site]/wp-cron.php?doing_wp_cron > /dev/null 2>&1
```

## Test de la tâche en mode console

### Tester depuis un navigateur

[url_du_site]/?tFyCronDoing=my_custom_task

### Tester depuis une console

Lancer l'exécution de la tâche depuis une autre console
 
```bash
$ curl [url_du_site]/?tFyCronDoing=my_custom_task
```

### Monitoring de la tâche d'import

Ouvrir le fichier de log depuis une console

```bash
$ tail -f /wp-content/uploads/tFyLogs/my_custom_task-%Y-%m-%d.log
```