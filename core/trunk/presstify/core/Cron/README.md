# Tâches Planifiées

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/core/Cron.yml

```yml
%task_id%:
  # Identifiant unique d'accorche de la tâche planifiée (optionnel)
  # default : tiFyCoreCron--[task_id]
  hook:         ''
  
  # Intitulé de la tâche planifiée (recommandé)
  # default : [task_id]
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
    name:       [task_id]
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
        array(
            '%task_id%'        => array(
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
                'args'          => array(),
                // Chemins de classe de surcharge
                'path'          => array(),
                // Attributs de journalisation des données
                'log'           => true
            )
        )
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
    public function set_%task_id%()
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
        '%task_id%', 
        array(
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
            'args'          => array(),
            // Chemins de classe de surcharge
            'path'          => array(),
            // Attributs de journalisation des données
            'log'           => true
        )
    );
}
?>
```

## Test de la tâche en mode console

### MONITORING

Ouvrir le fichier de log depuis une console

```bash
$ tail -f /wp-content/uploads/tFyLogs/%task_id%-%Y-%m-%d.log
```

### TEST

https://port01.tigreblanc.fr/sedea-pro.fr/?tFyCronDoing=%task_id%

### EXECUTION 

Lancer l'exécution de la tâche depuis une autre console
 
```bash
$ curl https://port01.tigreblanc.fr/sedea-pro.fr/?tFyCronDoing=%task_id%
```


