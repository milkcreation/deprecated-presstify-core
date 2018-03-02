# Boites à onglet de saisie

Permet de créer des interfaces de saisie (metaboxes) dans des boîtes à onglets .

## Configuration générale

### METHODE 1 | Intégrateur - priorité basse

Configuration "semi-dynamique" YAML 
Créer un fichier de configuration yml dans votre dossier de configuration.
/config/core/Taboox.yml

```yml
# @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
post_type :
    # @var string $object_name Identifiant de qualification du type d'objet Wordpress 
    # post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
    page :
        # @var array $box Attributs de configuration de la boîte à onglets
        box :
            # @var string $id Identifiant unique de qualification de la boîte à onglets
            id :    '%%my_page_id%%'
            # @var string $title Intitulé de la boîte à onglets
            title : <?php _e('Réglages des options de page', 'tify');?>
        # @var array $nodes Liste indexé de greffons
        nodes :
            - 
# @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
taxonomy :
    # @var string $object_name Identifiant de qualification du type d'objet Wordpress
    # post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
    category :
        # @var array $box Attributs de configuration de la boîte à onglets
        box :
            # @var string $id Identifiant unique de qualification de la boîte à onglets
            id :    '%%my_category_id%%'
            # @var string $title Intitulé de la boîte à onglets
            title : <?php _e('Réglages des options de la catégorie', 'tify');?>
        # @var array $nodes Liste indexé de greffons
        nodes :
            - 
# @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
options :
    # @var string $object_name Identifiant de qualification du type d'objet Wordpress
    # post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
    tify_options :
        # @var array $box Attributs de configuration de la boîte à onglets
        box :
            # @var string $id Identifiant unique de qualification de la boîte à onglets
            id :    '%%my_tify_options_id%%'
            # @var string $title Intitulé de la boîte à onglets
            title : <?php _e('Réglages des options du site', 'tify');?>
        # @var array $nodes Liste indexé de greffons
        nodes :
            - 
# @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
user :
    # @var string $object_name Identifiant de qualification du type d'objet Wordpress
    # post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
    edit :
        # @var array $box Attributs de configuration de la boîte à onglets
        box :
            # @var string $id Identifiant unique de qualification de la boîte à onglets
            id :    '%%my_user_id%%'
            # @var string $title Intitulé de la boîte à onglets
            title : <?php _e('Réglages des options utilisateur', 'tify');?>
        # @var array $nodes Liste indexé de greffons
        nodes :
            - 
```

### METHODE 2 | Intégrateur/Développeur - priorité moyenne

Configuration "dynamique" PHP 
Dans une fonction ou un objet

```php
<?php
use tiFy\Core;

add_action('tify_core_register', 'my_tify_core_register');
function my_tify_core_register()
{
    return Core::register(
        'Taboox',
        [
            // @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
            'post_type'   => [
                // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
                // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
                'page'      => [
                    /**
                     * @var array $box {
                     *      Attributs de configuration de la boîte à onglets
                     * 
                     *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                     *      @var string $title Intitulé de la boîte à onglets
                     * }
                     */
                    'box'       => [
                        'id'        => '%%my_page_id%%',
                        'title'     => __('Réglages des options de page', 'tify'),
                    ],
                    // @var array[] $nodes Liste indexé de greffons
                    'nodes'     => [
                        /**
                         * @var array $attrs {
                         *      Liste des attributs de configuration d'un greffon 
                         * 
                         *      @var string $id Identifiant du greffon.
                         *      @var string $title Titre du greffon.
                         *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                         *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                         *      @var string $parent Identifiant du greffon parent.
                         *      @var string $cap Habilitation d'accès au greffon.
                         *      @var bool $show Affichage/Masquage du greffon.
                         *      @var int $position Ordre d'affichage du greffon.
                         *      @var string $object_type post_type|taxonomy|user|options
                         *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                         *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                         * }
                         */
                        [
                            'id'        => '%%my_page_node_parent_id%%',
                            'title'     => __('greffon parent', 'tify'),
                            'cap'       => 'edit_posts',
                            'show'      => true,
                            'position'  => 1 
                        ],
                        [
                            'id'        => '%%my_page_node_enfant_id%%',
                            'parent'    => '%%my_page_node_parent_id%%',
                            'title'     => __('greffon enfant', 'tify'),
                            'cb'        => '',
                            'args'      => [],
                            'cap'       => 'edit_posts',
                            'show'      => true,
                            'position'  => 1,
                            'helpers'   => []   
                        ]
                    ]
                ] 
            ],
            // @var string $object_type type d'objet Wordpress (post_type|taxonomy|option|user)
            'taxonomy'   => [
                // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
                // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
                'category'      => [
                    /**
                     * @var array $box {
                     *      Attributs de configuration de la boîte à onglets
                     * 
                     *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                     *      @var string $title Intitulé de la boîte à onglets
                     * }
                     */
                    'box'       => [
                        'id'        => '%%my_category_id%%',
                        'title'     => __('Réglages des options de la catégorie', 'tify'),
                    ],
                    // @var array[] $nodes Liste indexé de greffons
                    'nodes'     => [
                        /**
                         * @var array $attrs {
                         *      Liste des attributs de configuration d'un greffon 
                         * 
                         *      @var string $id Identifiant du greffon.
                         *      @var string $title Titre du greffon.
                         *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                         *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                         *      @var string $parent Identifiant du greffon parent.
                         *      @var string $cap Habilitation d'accès au greffon.
                         *      @var bool $show Affichage/Masquage du greffon.
                         *      @var int $position Ordre d'affichage du greffon.
                         *      @var string $object_type post_type|taxonomy|user|options
                         *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                         *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                         * }
                         */
                        [
                            'id'        => '%%my_category_node_parent_id%%',
                            'title'     => __('greffon parent', 'tify'),
                            'cap'       => 'edit_posts',
                            'show'      => true,
                            'position'  => 1 
                        ],
                        [
                            'id'        => '%%my_category_node_enfant_id%%',
                            'parent'    => '%%my_category_node_parent_id%%',
                            'title'     => __('greffon enfant', 'tify'),
                            'cb'        => '',
                            'args'      => [],
                            'cap'       => 'edit_posts',
                            'show'      => true,
                            'position'  => 1,
                            'helpers'   => []   
                        ]
                    ]
                ] 
            ],
            // @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
            'options'   => [
                // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
                // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
                'tify_options'      => [
                    /**
                     * @var array $box {
                     *      Attributs de configuration de la boîte à onglets
                     * 
                     *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                     *      @var string $title Intitulé de la boîte à onglets
                     * }
                     */
                    'box'       => [
                        'id'        => '%%my_tify_options_id%%',
                        'title'     => __('Réglages des options du site', 'tify'),
                    ],
                    // @var array[] $nodes Liste indexé de greffons
                    'nodes'     => [
                        /**
                         * @var array $attrs {
                         *      Liste des attributs de configuration d'un greffon 
                         * 
                         *      @var string $id Identifiant du greffon.
                         *      @var string $title Titre du greffon.
                         *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                         *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                         *      @var string $parent Identifiant du greffon parent.
                         *      @var string $cap Habilitation d'accès au greffon.
                         *      @var bool $show Affichage/Masquage du greffon.
                         *      @var int $position Ordre d'affichage du greffon.
                         *      @var string $object_type post_type|taxonomy|user|options
                         *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                         *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                         * }
                         */
                        [
                            'id'        => '%%my_tify_options_node_parent_id%%',
                            'title'     => __('greffon parent', 'tify'),
                            'cap'       => 'manage_options',
                            'show'      => true,
                            'position'  => 1 
                        ],
                        [
                            'id'        => '%%my_tify_options_node_enfant_id%%',
                            'parent'    => '%%my_tify_options_node_parent_id%%',
                            'title'     => __('greffon enfant', 'tify'),
                            'cb'        => '',
                            'args'      => [],
                            'cap'       => 'manage_options',
                            'show'      => true,
                            'position'  => 1,
                            'helpers'   => []   
                        ]
                    ]
                ] 
            ],
            // @var string $object_type type d'objet Wordpress (post_type|taxonomy|options|user)
            'user'   => [
                // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
                // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
                'edit'      => [
                    /**
                     * @var array $box {
                     *      Attributs de configuration de la boîte à onglets
                     * 
                     *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                     *      @var string $title Intitulé de la boîte à onglets
                     * }
                     */
                    'box'       => [
                        'id'        => '%%my_user_id%%',
                        'title'     => __('Réglages des options utilisateur', 'tify'),
                    ],
                    // @var array[] $nodes Liste indexé de greffons
                    'nodes'     => [
                        /**
                         * @var array $attrs {
                         *      Liste des attributs de configuration d'un greffon 
                         * 
                         *      @var string $id Identifiant du greffon.
                         *      @var string $title Titre du greffon.
                         *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                         *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                         *      @var string $parent Identifiant du greffon parent.
                         *      @var string $cap Habilitation d'accès au greffon.
                         *      @var bool $show Affichage/Masquage du greffon.
                         *      @var int $position Ordre d'affichage du greffon.
                         *      @var string $object_type post_type|taxonomy|user|options
                         *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                         *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                         * }
                         */
                        [
                            'id'        => '%%my_user_node_parent_id%%',
                            'title'     => __('greffon parent', 'tify'),
                            'cap'       => 'edit_users',
                            'show'      => true,
                            'position'  => 1 
                        ],
                        [
                            'id'        => '%%my_user_node_enfant_id%%',
                            'parent'    => '%%my_user_node_parent_id%%',
                            'title'     => __('greffon enfant', 'tify'),
                            'cb'        => '',
                            'args'      => [],
                            'cap'       => 'edit_users',
                            'show'      => true,
                            'position'  => 1,
                            'helpers'   => []   
                        ]
                    ]
                ] 
            ]
        ]
    );
}
```

### METHODE 3 | Développeur avancé - priorité haute

Surcharge de configuration "dynamique" PHP
Créer un fichier Config.php dans le dossier core/Options de l'environnement de surcharge.
/app/Core/Taboox/Config.php

```php
<?php
namespace App\Core\Taboox;

class Config extends \tiFy\App\Config
{
    /**
     * Définition de boites à onglets de types de post
     * 
     * @param array $attrs Liste des attributs de configuration existants
     * 
     * @return array
     */
    public function set_post_type($attrs = [])
    {
        return [
            // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
           // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
           'page'      => [
                /**
                 * @var array $box {
                 *      Attributs de configuration de la boîte à onglets
                 * 
                 *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                 *      @var string $title Intitulé de la boîte à onglets
                 * }
                 */
               'box'       => [
                   'id'        => '%%my_page_id%%',
                   'title'     => __('Réglages des options de page', 'tify'),
               ],
               // @var array[] $nodes Liste indexé de greffons
               'nodes'     => [
                   /**
                    * @var array $attrs {
                    *      Liste des attributs de configuration d'un greffon 
                    * 
                    *      @var string $id Identifiant du greffon.
                    *      @var string $title Titre du greffon.
                    *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                    *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                    *      @var string $parent Identifiant du greffon parent.
                    *      @var string $cap Habilitation d'accès au greffon.
                    *      @var bool $show Affichage/Masquage du greffon.
                    *      @var int $position Ordre d'affichage du greffon.
                    *      @var string $object_type post_type|taxonomy|user|options
                    *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                    *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                    * }
                    */
                   [
                       'id'        => '%%my_page_node_parent_id%%',
                       'title'     => __('greffon parent', 'tify'),
                       'cap'       => 'edit_posts',
                       'show'      => true,
                       'position'  => 1 
                   ],
                   [
                       'id'        => '%%my_page_node_enfant_id%%',
                       'parent'    => '%%my_page_node_parent_id%%',
                       'title'     => __('greffon enfant', 'tify'),
                       'cb'        => '',
                       'args'      => [],
                       'cap'       => 'edit_posts',
                       'show'      => true,
                       'position'  => 1,
                       'helpers'   => []   
                   ]
               ]
           ] 
       ];
    }
    
    /**
     * Définition de boites à onglets de taxonomy
     * 
     * @param array $attrs Liste des attributs de configuration existants
     * 
     * @return array
     */
    public function set_taxonomy($attrs = [])
    {
        return [
            // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
            // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
            'category'      => [
                /**
                 * @var array $box {
                 *      Attributs de configuration de la boîte à onglets
                 * 
                 *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                 *      @var string $title Intitulé de la boîte à onglets
                 * }
                 */
                'box'       => [
                    'id'        => '%%my_category_id%%',
                    'title'     => __('Réglages des options de la catégorie', 'tify'),
                ],
                // @var array[] $nodes Liste indexé de greffons
                'nodes'     => [
                    /**
                     * @var array $attrs {
                     *      Liste des attributs de configuration d'un greffon 
                     * 
                     *      @var string $id Identifiant du greffon.
                     *      @var string $title Titre du greffon.
                     *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                     *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                     *      @var string $parent Identifiant du greffon parent.
                     *      @var string $cap Habilitation d'accès au greffon.
                     *      @var bool $show Affichage/Masquage du greffon.
                     *      @var int $position Ordre d'affichage du greffon.
                     *      @var string $object_type post_type|taxonomy|user|options
                     *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                     *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                     * }
                     */
                    [
                        'id'        => '%%my_category_node_parent_id%%',
                        'title'     => __('greffon parent', 'tify'),
                        'cap'       => 'edit_posts',
                        'show'      => true,
                        'position'  => 1 
                    ],
                    [
                        'id'        => '%%my_category_node_enfant_id%%',
                        'parent'    => '%%my_category_node_parent_id%%',
                        'title'     => __('greffon enfant', 'tify'),
                        'cb'        => '',
                        'args'      => [],
                        'cap'       => 'edit_posts',
                        'show'      => true,
                        'position'  => 1,
                        'helpers'   => []   
                    ]
                ]
            ] 
        ];
    }
    
    /**
     * Définition de boites à onglets d'options
     * 
     * @param array $attrs Liste des attributs de configuration existants
     * 
     * @return array
     */
    public function set_options($attrs = [])
    {
        return [
            // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
            // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
            'tify_options'      => [
                /**
                 * @var array $box {
                 *      Attributs de configuration de la boîte à onglets
                 * 
                 *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                 *      @var string $title Intitulé de la boîte à onglets
                 * }
                 */
                'box'       => [
                    'id'        => '%%my_tify_options_id%%',
                    'title'     => __('Réglages des options du site', 'tify'),
                ],
                // @var array[] $nodes Liste indexé de greffons
                'nodes'     => [
                    /**
                     * @var array $attrs {
                     *      Liste des attributs de configuration d'un greffon 
                     * 
                     *      @var string $id Identifiant du greffon.
                     *      @var string $title Titre du greffon.
                     *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                     *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                     *      @var string $parent Identifiant du greffon parent.
                     *      @var string $cap Habilitation d'accès au greffon.
                     *      @var bool $show Affichage/Masquage du greffon.
                     *      @var int $position Ordre d'affichage du greffon.
                     *      @var string $object_type post_type|taxonomy|user|options
                     *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                     *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                     * }
                     */
                    [
                        'id'        => '%%my_tify_options_node_parent_id%%',
                        'title'     => __('greffon parent', 'tify'),
                        'cap'       => 'manage_options',
                        'show'      => true,
                        'position'  => 1 
                    ],
                    [
                        'id'        => '%%my_tify_options_node_enfant_id%%',
                        'parent'    => '%%my_tify_options_node_parent_id%%',
                        'title'     => __('greffon enfant', 'tify'),
                        'cb'        => '',
                        'args'      => [],
                        'cap'       => 'manage_options',
                        'show'      => true,
                        'position'  => 1,
                        'helpers'   => []   
                    ]
                ]
            ] 
        ];
    }
    
    /**
     * Définition de boites à onglets d'utilisateur
     * 
     * @param array $attrs Liste des attributs de configuration existants
     * 
     * @return array
     */
    public function set_user($attrs = [])
    {
        return [
            // @var string $object_name Identifiant de qualification du type d'objet Wordpress 
            // post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile
            'edit'      => [
                /**
                 * @var array $box {
                 *      Attributs de configuration de la boîte à onglets
                 * 
                 *      @var string $id Identifiant unique de qualification de la boîte à onglets 
                 *      @var string $title Intitulé de la boîte à onglets
                 * }
                 */
                'box'       => [
                    'id'        => '%%my_user_id%%',
                    'title'     => __('Réglages des options utilisateur', 'tify'),
                ],
                // @var array[] $nodes Liste indexé de greffons
                'nodes'     => [
                    /**
                     * @var array $attrs {
                     *      Liste des attributs de configuration d'un greffon 
                     * 
                     *      @var string $id Identifiant du greffon.
                     *      @var string $title Titre du greffon.
                     *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
                     *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
                     *      @var string $parent Identifiant du greffon parent.
                     *      @var string $cap Habilitation d'accès au greffon.
                     *      @var bool $show Affichage/Masquage du greffon.
                     *      @var int $position Ordre d'affichage du greffon.
                     *      @var string $object_type post_type|taxonomy|user|options
                     *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
                     *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
                     * }
                     */
                    [
                        'id'        => '%%my_user_node_parent_id%%',
                        'title'     => __('greffon parent', 'tify'),
                        'cap'       => 'edit_users',
                        'show'      => true,
                        'position'  => 1 
                    ],
                    [
                        'id'        => '%%my_user_node_enfant_id%%',
                        'parent'    => '%%my_user_node_parent_id%%',
                        'title'     => __('greffon enfant', 'tify'),
                        'cb'        => '',
                        'args'      => [],
                        'cap'       => 'edit_users',
                        'show'      => true,
                        'position'  => 1,
                        'helpers'   => []   
                    ]
                ]
            ] 
        ];
    }
}
```

## Déclaration ponctuelle d'une boite à onglets

Permet de déclarer une boîte à onglets. 

```php
<?php
use tiFy\Core\Taboox\Taboox;

add_action('tify_taboox_register_box', 'my_tify_taboox_register_box');
function my_tify_taboox_register_box()
{
    Taboox::registerBox(
        /**
         * @var string $hookname
         * @see \WP_Screen::$id|\get_current_screen()->id
         */
        'page',
        /**
         * @var array $attrs {
         *      Attributs de configuration de la boîte à onglets
         * 
         *      @var string $id Identifiant unique de qualification de la boîte à onglets 
         *      @var string $title Intitulé de la boîte à onglets
         *      @var string $title Intitulé de la boîte à onglets
         *      @var string $title Intitulé de la boîte à onglets
         * }
         */
        [
            'id'            => '%%my_page_box_id%%',
            'title'         => __('Réglages des options de page', 'tify'),
            'object_type'   => 'post_type',
            'object_name'   => 'page'
        ]
    );
}
```

## Déclaration ponctuelle d'un greffon

Permet de déclarer un greffon de boîte à onglets. 
Si la boîte à onglet d'accroche n'existe pas celle-ci sera créée automatiquement avec les attributs de configuration par défaut.

```php
<?php
use tiFy\Core\Taboox\Taboox;

add_action('tify_taboox_register_node', 'my_tify_taboox_register_node');
function my_tify_taboox_register_node()
{
    Taboox::registerNode(
        /**
         * @var string $hookname
         * @see \WP_Screen::$id|\get_current_screen()->id
         */
        'page',
        /**
         * @var array $attrs {
         *      Liste des attributs de configuration d'un greffon 
         * 
         *      @var string $id Identifiant du greffon.
         *      @var string $title Titre du greffon.
         *      @var string $cb Fonction ou méthode ou classe de rappel d'affichage du greffon.
         *      @var mixed $args Liste des arguments passé à la fonction, la méthode ou la classe de rappel.
         *      @var string $parent Identifiant du greffon parent.
         *      @var string $cap Habilitation d'accès au greffon.
         *      @var bool $show Affichage/Masquage du greffon.
         *      @var int $position Ordre d'affichage du greffon.
         *      @var string $object_type post_type|taxonomy|user|options
         *      @var string $object_name (post_type: page|post|custom_post_type; taxonomy: category|tag|custom_taxonomy; options: general|writing|reading|medias|permalink|tify_options|custom_menu_slug; user: edit|profile)
         *      @var string|string[] $helpers Liste des classes de rappel des méthodes d'aide à la saisie. Chaine de caractères séparés par de virgules|Tableau indexé.
         * }
         */
        [
            'id'        => '%%my_page_node_id%%',
            'parent'    => '',
            'title'     => __('greffon de page', 'tify'),
            'cb'        => '',
            'args'      => [],
            'cap'       => 'edit_pages',
            'show'      => true,
            'position'  => 1,
            'helpers'   => []   
        ]
    );
}
```