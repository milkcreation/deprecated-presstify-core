<?php
/**
 * Menu de navigation par catégorie
 * Peut être utilisé comme menu de remplacement (fallback_cb) si l'emplacement de menu n'est pas affecté
 * ex:
    wp_nav_menu(
        array(
            'theme_location'    => primary-navigation-menu,
            'container'         => 'nav',
            'container_class'   => 'PrimaryNavigation',
            'menu_class'        => 'PrimaryNavigation-Menu',
            
            'fallback_cb'       => 'tify_nav_menu_category',
            'taxonomy'          => 'product_cat',                          
        )
    );
 * @see wp_list_categories 
 */
function tify_nav_menu_category( $args = array() )
{
    return \tiFy\Components\NavMenu\GeneralTemplate::categoryNavMenu( $args );
}