<?php
/**
 * Fichier d'exemple de configuration
 */
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