# Tâches Planifiées

## Créer une tâche

METHODE 1

```yml
[task_id]:
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

METHODE 2

```php
<?php
use tiFy\Core\Cron\Cron;

add_action( 'tify_cron_register', 'my_tify_cron_register' );
function my_tify_cron_register()
{
    return Cron::register(
        [task_id],
        array(
            
        )
    );
}
?>
```

## Test de la tâche en mode console

MONITORING - Ouvrir le fichier de log depuis une console

```bash
$ tail -f /wp-content/uploads/tFyLogs/[task_id]-%Y-%m-%d.log
```

EXECUTION - Lancer l'exécution de la tâche depuis une autre console 
 
```bash
$ curl https://port01.tigreblanc.fr/sedea-pro.fr/wp-cron.php?tFy_doing_cron=[task_id]
```
