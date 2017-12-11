# Sidebar

Le composant dynamique Sidebar a été conçu pour donner la possibilité d'intégré à l'interface utilisateur du site internet une barre latérale.
Cette barre latérale peut être enrichie de greffons (nodes). Un node peut aussi bien être un menu de navigation, des lien vers les réseaux sociaux, une barre de bascule de langage ...

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Activation et Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/Sidebar.yml

```yml
# @var string $pos Position de l'interface left (default)|right.
pos:                'left'

# @var string $initial Etat initial de l'interface closed (default)|opened.
initial:            'closed'

# @var string|int $width Largeur de l'interface en px ou en %. Si l'unité de valeur n'est pas renseignée l'unité par défault est le px.
width:              '300px'

# @var int $z-index Profondeur de champs.
z-index:            99990

# @var string|int $min-width Largeur de la fenêtre du navigateur en px ou %, à partir de laquelle l'interface est active. Si l'unité de valeur n'est pas renseignée l'unité par défault est le px.
min-width:          '991px'

# @var bool $animated Activation de l'animation à l'ouverture et la fermeture
animated:           true

# @var bool|string $toggle Activation et contenu de bouton de bascule. Si la valeur booléene active ou desactive le bouton; la valeur chaîne de caractère active et affiche la chaîne ex : <span>X</span>
toggle:             true

# @var bool $enqueue_scripts Mise en file automatique des scripts (dans tous les contextes)
enqueue_scripts:    true  

# @var array[] {
#   Liste des greffons (node) Elements de menu
#   @var string $id Identifiant du greffon
#   @var string $class Classe HTML du greffon
#   @var string $content Contenu du greffon
#   @var int $position Position du greffon 
# }
nodes:
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Activation et Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Components;

add_action('tify_components_register', 'my_tify_components_register');
function my_tify_components_register()
{
    return Components::register(
        'Sidebar',
        [
            /** @var string $pos Position de l'interface left (default)|right. */
            'pos'               => 'left',
            
            /** @var string $initial Etat initial de l'interface closed (default)|opened. */
            'initial'           => 'closed',
            
            /** @var string|int $width Largeur de l'interface en px ou en %. Si l'unité de valeur n'est pas renseignée l'unité par défault est le px. */
            'width'             => '300px',
            
            /** @var int $z-index Profondeur de champs. */
            'z-index'           => 99990,
            
            /** @var string|int $min-width Largeur de la fenêtre du navigateur en px ou %, à partir de laquelle l'interface est active. Si l'unité de valeur n'est pas renseignée l'unité par défault est le px. */
            'min-width'         => '991px',
            
            /** @var bool $animated Activation de l'animation à l'ouverture et la fermeture */
            'animated'          => true,
            
            /** @var bool|string $toggle Activation et contenu de bouton de bascule. Si la valeur booléene active ou desactive le bouton; la valeur chaîne de caractère active et affiche la chaîne ex : <span>X</span> */
            'toggle'            => true,
            
            /** @var bool $enqueue_scripts Mise en file automatique des scripts (dans tous les contextes) */
            'enqueue_scripts'   => true, 
            
            /**
             * @var array[] {
             *      Liste des greffons (node) Elements de menu
             * 
             *      @var string $id Identifiant du greffon
             *      @var string $class Classe HTML du greffon
             *      @var string $content Contenu du greffon
             *      @var int $position Position du greffon 
             * }
             */ 
            'nodes'         => []
        ]
    );
}
?>
```

### METHODE 3 | Développeur avancé - priorité haute

Le composants doit être d'abord être activé avant d'être configuré.

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le le dossier components/Sidebar de l'environnement de surcharge.
/app/Components/Sidebar/Config.php

```php
<?php
namespace App\Components\Sidebar;

class Config extends \tiFy\App\Config
{
    /**
     * Définition globale des attributs de configuration
     * 
     * @param mixed $attrs Liste des attributs existants
     * 
     * @return array|mixed
     */
    public function sets($attrs = [])
    {
        return [
            /** @var string $pos Position de l'interface left (default)|right. */
            'pos'               => 'left',
            
            /** @var string $initial Etat initial de l'interface closed (default)|opened. */
            'initial'           => 'closed',
            
            /** @var string|int $width Largeur de l'interface en px ou en %. Si l'unité de valeur n'est pas renseignée l'unité par défault est le px. */
            'width'             => '300px',
            
            /** @var int $z-index Profondeur de champs. */
            'z-index'           => 99990,
            
            /** @var string|int $min-width Largeur de la fenêtre du navigateur en px ou %, à partir de laquelle l'interface est active. Si l'unité de valeur n'est pas renseignée l'unité par défault est le px. */
            'min-width'         => '991px',
            
            /** @var bool $animated Activation de l'animation à l'ouverture et la fermeture */
            'animated'          => true,
            
            /** @var bool|string $toggle Activation et contenu de bouton de bascule. Si la valeur booléene active ou desactive le bouton; la valeur chaîne de caractère active et affiche la chaîne ex : <span>X</span> */
            'toggle'            => false,
            
            /** @var bool $enqueue_scripts Mise en file automatique des scripts (dans tous les contextes) */
            'enqueue_scripts'   => true, 
            
            /**
             * @var array[] {
             *   Liste des greffons (node) Elements de menu
             * 
             *   @var string $id Identifiant du greffon
             *   @var string $class Classe HTML du greffon
             *   @var string $content Contenu du greffon
             *   @var int $position Position du greffon 
             * }
             */ 
            'nodes'         => []
        ];
    }
}
?>
```

## Déclaration dynamique de greffon

Créer un fichier Nodes.php dans le dossier app d'un plugin, d'un set ou du theme.
/app/Components/Sidebar/Nodes.php

```php
<?php
/** @Override */
namespace App\Components\Sidebar;

class Nodes extends \tiFy\Components\Sidebar\Nodes
{
    /**
     * Contenu du greffon mycontent
     */
    public function mynode_node_content()
    {
        ob_start();
        ?><div>MyContent</div><?php
        
        return ob_get_clean();
    }
    
    /**
     * Classe de conteneur HTML du greffon mycontent
     */
    public function mynode_node_class()
    {
        return 'myNodeCustomClass';
    }
}
```