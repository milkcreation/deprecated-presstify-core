# Colonnes personnalisées

Permet l'ajout de colonnes personnalisées dans les pages liste de l'interface d'administration des post ou des taxonomies.

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/components/CustomColumns.yml

```yml
# @var string $object post_type|taxonomy
post_type :
  # @var string $object_type Type de post natif WP ou custom_post_type 
  post :
    # Colonne PresstiFy native @see /wp-conten/mu-plugins/presstify/components/CustomColumns/PostType
    PostThumbnail :
    
  # @var string $object_type Type de post natif WP ou custom_post_type
  page :
    # @var string $id Identifiant de qualification unique
    %%custom_id#1%% :
      # @var string $title Intitulé de la colonne
      title :       <?php _e('Colonne de test 1', 'tify');?>
      # @var int $position Position de la colonne
      position :    10
      # @var string $column Identification de la colonne + ID HTML
      column :      'colonne_test_1'
      # @var string $cb Fonction ou méthode ou classe de rappel (ex: méthode static)
      cb :          'MaClasseTest::colonneTest1'

# @var string $object post_type|taxonomy      
taxonomy :
  # @var string $object_type Type de taxonomy native WP ou custom_taxonomy
  category :
    # Colonne PresstiFy native @see /wp-conten/mu-plugins/presstify/components/CustomColumns/PostType
    Icon :
    
  # @var string $object_type Type de taxonomy native WP ou custom_taxonomy
  tag :
    # @var string $id Identifiant de qualification unique
    %%custom_id#2%% :
      # @var string $title Intitulé de la colonne
      title :       <?php _e('Colonne de test 2', 'tify');?>
      # @var int $position Position de la colonne
      position :    10
      # @var string $column Identification de la colonne + ID HTML
      column :      'colonne_test_1'
      # @var string $cb Fonction ou méthode ou classe de rappel (ex: fonction)
      cb :          'functionColonneTest1'
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
        'CustomColumns',
        [
            // @var string $object post_type|taxonomy
            'post_type' => [
                // @var string $object_type Type de post natif WP ou custom_post_type 
                'post'      => [
                    // Colonne PresstiFy native @see /wp-conten/mu-plugins/presstify/components/CustomColumns/PostType
                    'PostThumbnail'     => []
                ],
                  // @var string $object_type Type de post natif WP ou custom_post_type
                'page'    => [
                    // @var string $id Identifiant de qualification unique
                    '%%custom_id#1%%'   => [
                      // @var string $title Intitulé de la colonne
                      'title'       => __('Colonne de test 1', 'tify'),
                      // @var int $position Position de la colonne
                      'position'    => 10,
                      // @var string $column Identification de la colonne + ID HTML
                      'column'      => 'colonne_test_1',
                      // @var string $cb Fonction ou méthode ou classe de rappel (ex: méthode static)
                      'cb'          => 'MaClasseTest::colonneTest1'
                    ]
                ]
            ],
            
            // @var string $object post_type|taxonomy      
            'taxonomy'  => [
                // @var string $object_type Type de taxonomy native WP ou custom_taxonomy
                'category'      => [
                    // Colonne PresstiFy native @see /wp-conten/mu-plugins/presstify/components/CustomColumns/PostType
                    'Icon'          => []
                ],
            
                // @var string $object_type Type de taxonomy native WP ou custom_taxonomy
                'tag'           => [
                    // @var string $id Identifiant de qualification unique
                    '%%custom_id#2%%'   => [
                        // @var string $title Intitulé de la colonne
                        'title'     => __('Colonne de test 2', 'tify'),
                        // @var int $position Position de la colonne
                        'position'  => 10,
                        // @var string $column Identification de la colonne + ID HTML
                        'column'    => 'colonne_test_2',
                        // @var string $cb Fonction ou méthode ou classe de rappel (ex: fonction)
                        'cb'        => 'functionColonneTest2'
                    ]
                ]
            ]
        ]
    );
}
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier components/CustomColumns de l'environnement de surcharge.
/app/Components/CustomColumns/Config.php

```php
<?php
namespace App\Components\CustomColumns;

class Config extends \tiFy\App\Config
{
    public function sets($attrs = [])
    {
        return [
            // @var string $object post_type|taxonomy
            'post_type' => [
                // @var string $object_type Type de post natif WP ou custom_post_type 
                'post'      => [
                    // Colonne PresstiFy native @see /wp-conten/mu-plugins/presstify/components/CustomColumns/PostType
                    'PostThumbnail'     => []
                ],
                  // @var string $object_type Type de post natif WP ou custom_post_type
                'page'    => [
                    // @var string $id Identifiant de qualification unique
                    '%%custom_id#1%%'   => [
                      // @var string $title Intitulé de la colonne
                      'title'       => __('Colonne de test 1', 'tify'),
                      // @var int $position Position de la colonne
                      'position'    => 10,
                      // @var string $column Identification de la colonne + ID HTML
                      'column'      => 'colonne_test_1',
                      // @var string $cb Fonction ou méthode ou classe de rappel (ex: méthode static)
                      'cb'          => 'MaClasseTest::colonneTest1'
                    ]
                ]
            ],
            
            // @var string $object post_type|taxonomy      
            'taxonomy'  => [
                // @var string $object_type Type de taxonomy native WP ou custom_taxonomy
                'category'      => [
                    // Colonne PresstiFy native @see /wp-conten/mu-plugins/presstify/components/CustomColumns/PostType
                    'Icon'          => []
                ],
            
                // @var string $object_type Type de taxonomy native WP ou custom_taxonomy
                'tag'           => [
                    // @var string $id Identifiant de qualification unique
                    '%%custom_id#2%%'   => [
                        // @var string $title Intitulé de la colonne
                        'title'     => __('Colonne de test 2', 'tify'),
                        // @var int $position Position de la colonne
                        'position'  => 10,
                        // @var string $column Identification de la colonne + ID HTML
                        'column'    => 'colonne_test_2',
                        // @var string $cb Fonction ou méthode ou classe de rappel (ex: fonction)
                        'cb'        => 'functionColonneTest2'
                    ]
                ]
            ]
        ];
    }
}
```

## Déclaration d'un colonne personnalisée

Permet de déclarer une colonne personnalisée de manière ponctuelle depuis une fonction ou une méthode.

```php
<?php
use tiFy\Components\CustomColumns\CustomColumns;

add_action('tify_custom_columns_register', 'my_tify_custom_columns_register');
function my_tify_components_register()
{
    return CustomColumns::register(
        // @var string $id Identifiant de qualification unique
        '%%custom_id#1%%',
        [
            // @var string $object post_type|taxonomy (requis)
            'object'        => 'post_type',
            // @var string $object_type (requis)
            'object_type'   => 'page',
            // @var string $title Intitulé de la colonne
            'title'         => __('Colonne de test 1', 'tify'),
            // @var int $position Position de la colonne
            'position'      => 10,
            // @var string $column Identification de la colonne + ID HTML
            'column'        => 'colonne_test_1',
            // @var string $cb Fonction ou méthode ou classe de rappel (ex: méthode static)
            'cb'            => 'MaClasseTest::colonneTest1'
        ]
    );
}
```

## Héritage des classes de rappel

L'attribut "cb" peut être une fonction, une méthode ou une classe de rappel; lorsqu'il s'agit d'une classe de rappel celle ci peux herités d'une des classes d'abstraction :
- tiFy\Components\CustomColumns\PostType
- tiFy\Components\CustomColumns\Taxonomy

### Colonne personnalisée de type de post
```php
<?php
namespace App\Components\CustomColumns\PostType\MyCustomColumns;

class MyCustomColumns extends \tiFy\Components\CustomColumns\PostType
{
    /**
     * Affichage du contenu de la colonne
     *
     * @param string $column Identification de la colonne
     * @param int $post_id Identifiant du post
     *
     * @return string
     */
    public function content($column, $post_id)
    {
        _e('Personnalisation du contenu', 'tify');
    }
}
```

### Colonne personnalisée de taxonomy
```php
<?php
namespace App\Components\CustomColumns\Taxonomy\MyCustomColumns;

class MyCustomColumns extends \tiFy\Components\CustomColumns\Taxonomy
{
    /**
     * Affichage du contenu de la colonne
     *
     * @param string $content Contenu de la colonne
     * @param string $column_name Identification de la colonne
     * @param int $term_id Identifiant du terme
     *
     * @return string
     */
    public function content($content, $column_name, $term_id)
    {
        _e('Personnalisation du contenu', 'tify');
    }
}
```