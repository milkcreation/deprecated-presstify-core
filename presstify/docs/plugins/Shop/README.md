# Boutique en ligne

Plugin PresstiFy de gestion de boutiques en ligne.

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/plugins/Shop.yml

```yml
# @var array $products Déclaration des gammes de produits
products:
  product:
    singular:               "<?php _e('Produit', 'tify'); ?>"
    plural:                 "<?php _e('Produits', 'tify'); ?>"

    category:
      taxonomy:             'product_cat'

# @var array $roles Liste des roles
# @see \tiFy\Core\Roles\Roles
roles:
  # @var string $id Identifiant de qualification du rôle
  shop_manager:
      # @var string $display_name Nom d'affichage
      display_name:         "<?php _e('Gestionnaire boutique', 'tify'); ?>"

      # @var bool|array $admin_ui Interface dédiée
      admin_ui:
        global:
          admin_menu:
            menu_title:   "<?php _e('Gestionnaires', 'tify'); ?>"
            icon_url:     'dashicons-businessman'
            position:     71
        list:
          admin_menu:
            menu_title:   "<?php _e('Tous les gestionnaires', 'tify'); ?>"

  # @var string $id Identifiant de qualification du rôle
  customer:
    # @var string $display_name Nom d'affichage
    display_name:           "<?php _e('Client', 'tify'); ?>"

    # @var bool|array $admin_ui Interface dédiée
    admin_ui:
      global:
        admin_menu:
          menu_title:   "<?php _e('Clients', 'tify'); ?>"
          icon_url:     'dashicons-cart'
          position:     72
      list:
        admin_menu:
          menu_title:   "<?php _e('Tous les clients', 'tify'); ?>"
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Plugins;

add_action('tify_plugins_register', 'my_tify_plugins_register');
function my_tify_plugins_register()
{
    return Plugins::register(
        'Shop',
        [
            // @var array $products Déclaration des gammes de produits
            'products' => [
                // @var string $post_type Identifiant de qualification du type de post de la gamme de produit
                'product' => [
                    // @var bool|string Liste des attributs de configuration du type de post
                    // @see \tiFy\Core\CustomType\CustomType\README.md
                    
                    // @var string $singular Intitulé d'un élément de la gamme
                    'singular' => __('Produit', 'tify'),
                    // @var string $plural Intitulé de plusieurs éléments de la gamme
                    'plural' => __('Produits', 'tify'),

                    // @var bool|array $category Gestion de la hiérarchisation par catégorie de la gamme de produit
                    'category'=> [
                        // Liste des attributs de configuration de la taxonomy
                        // @see \tiFy\Core\CustomType\CustomType\README.md    
                        'taxonomy' => 'product_cat'
                    ]
                ]
            ],
            
            // @var array $roles Déclarations des roles et habilitations de la boutique
            'roles' => [
                // @var string $id Identifiant de qualification d'un rôle ex. Gestionnaire boutique
                'shop_manager' => [
                    // @var bool|string Liste des attributs de configuration du rôle
                    // @see \tiFy\Core\CustomType\CustomType\README.md
                    
                    // @var string $display_name Nom d'affichage
                    'display_name' => __('Gestionnaire boutique', 'tify'),
                    
                    // @var bool|array $admin_ui Interface d'administration dédiée
                    // @var see \tiFy\Core\Ui\Ui\README.md
                    'admin_ui' => [
                        'global' => [
                            'admin_menu' => [
                                'menu_title' => __('Gestionnaires', 'tify'),
                                'icon_url' => 'dashicons-businessman',
                                'position' => 71
                            ]
                        ],
                        
                        'list' => [
                            'admin_menu' => [
                                'menu_title' => __('Tous les gestionnaires', 'tify'),
                            ]
                        ]
                    ]
                ],
                
                // @var string $id Identifiant de qualification d'un rôle ex. Gestionnaire boutique
                'customer'      => [
                    // @var bool|string Liste des attributs de configuration du rôle
                    // @see \tiFy\Core\CustomType\CustomType\README.md
                    
                    // @var string $display_name Nom d'affichage
                    'display_name' => __('Client', 'tify'),
                    
                    // @var bool|array $admin_ui Interface d'administration dédiée
                    // @var see \tiFy\Core\Ui\Ui\README.md
                    'admin_ui' => [
                        'global' => [
                            'admin_menu' => [
                                'menu_title' => __('Clients', 'tify'),
                                'icon_url' => 'dashicons-cart',
                                'position' => 72
                            ]
                        ],
                        
                        'list' => [
                            'admin_menu' => [
                                'menu_title' => __('Tous les clients', 'tify'),
                            ]
                        ]
                    ]
                ]
            ],
            
            // @var array $login Déclarations attributs de configuration de l'interface d'authentification à la boutique
            // @see \tiFy\Components\Login\Login
            'login' => [
                'cb' => '\tiFy\Plugins\Shop\App\Components\Login\Factory',
                'roles' => ['customer', 'shop_manager']
            ]
        ]
    );
}
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier plugins/Shop de l'environnement de surcharge.
/app/plugins/Shop/Config.php

```php
<?php
namespace App\Plugins\Shop;

class Config extends \tiFy\App\Config
{
    public function sets($attrs = [])
    {
        return [
            // @var array $products Déclaration des gammes de produits
            'products' => [
                // @var string $post_type Identifiant de qualification du type de post de la gamme de produit
                'product' => [
                    // @var bool|string Liste des attributs de configuration du type de post
                    // @see \tiFy\Core\CustomType\CustomType\README.md
        
                    // @var string $singular Intitulé d'un élément de la gamme
                    'singular' => __('Produit', 'tify'),
                    // @var string $plural Intitulé de plusieurs éléments de la gamme
                    'plural' => __('Produits', 'tify'),
        
                    // @var bool|array $category Gestion de la hiérarchisation par catégorie de la gamme de produit
                    'category' => [
                        // Liste des attributs de configuration de la taxonomy
                        // @see \tiFy\Core\CustomType\CustomType\README.md
                        'taxonomy' => 'product_cat'
                    ]
                ]
            ],
        
            // @var array $roles Déclarations des roles et habilitations de la boutique
            'roles' => [
                // @var string $id Identifiant de qualification d'un rôle ex. Gestionnaire boutique
                'shop_manager' => [
                    // @var bool|string Liste des attributs de configuration du rôle
                    // @see \tiFy\Core\CustomType\CustomType\README.md
        
                    // @var string $display_name Nom d'affichage
                    'display_name' => __('Gestionnaire boutique', 'tify'),
        
                    // @var bool|array $admin_ui Interface d'administration dédiée
                    // @var see \tiFy\Core\Ui\Ui\README.md
                    'admin_ui' => [
                        'global' => [
                            'admin_menu' => [
                                'menu_title' => __('Gestionnaires', 'tify'),
                                'icon_url' => 'dashicons-businessman',
                                'position' => 71
                            ]
                        ],
        
                        'list' => [
                            'admin_menu' => [
                                'menu_title' => __('Tous les gestionnaires', 'tify'),
                            ]
                        ]
                    ]
                ],
        
                // @var string $id Identifiant de qualification d'un rôle ex. Gestionnaire boutique
                'customer'      => [
                    // @var bool|string Liste des attributs de configuration du rôle
                    // @see \tiFy\Core\CustomType\CustomType\README.md
        
                    // @var string $display_name Nom d'affichage
                    'display_name' => __('Client', 'tify'),
        
                    // @var bool|array $admin_ui Interface d'administration dédiée
                    // @var see \tiFy\Core\Ui\Ui\README.md
                    'admin_ui' => [
                        'global' => [
                            'admin_menu' => [
                                'menu_title' => __('Clients', 'tify'),
                                'icon_url' => 'dashicons-cart',
                                'position' => 72
                            ]
                        ],
        
                        'list' => [
                            'admin_menu' => [
                                'menu_title' => __('Tous les clients', 'tify'),
                            ]
                        ]
                    ]
                ]
            ],
            
            // @var array $login Déclarations attributs de configuration de l'interface d'authentification à la boutique
            // @see \tiFy\Components\Login\Login
            'login' => [
                'cb' => '\tiFy\Plugins\Shop\App\Components\Login\Factory',
                'roles' => ['customer', 'shop_manager']
            ]
        ];
    }
}
```