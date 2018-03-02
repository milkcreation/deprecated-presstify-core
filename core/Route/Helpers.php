<?php

/**
 * @name Helpers
 * @desc Fonctions d'aide à la saisie du gestionnaire de routage de page.
 * @package presstiFy
 * @version 1.1
 * @subpackage Core
 * @since 1.2.596
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

use tiFy\Core\Route\Route;

/**
 * Vérifie la correspondance du nom de qualification d'une route existante avec la valeur soumise.
 *
 * @param string $name Identifiant de qualification de la route à vérifier
 *
 * @return bool
 */
function tify_route_exists($name)
{
    return Route::exists($name);
}

/**
 * Récupération de l'url d'une route déclarée
 *
 * @param string $name Identifiant de qualification de la route
 * @param array $replacements Arguments de remplacement
 *
 * @return string
 */
function tify_route_url($name, array $replacements = [])
{
    return Route::url($name, $replacements);
}

/**
 * Redirection de page vers une route déclarée.
 *
 * @param string $name Identifiant de qualification de la route
 * @param array $args Liste arguments passés en variable de requête dans l'url
 * @param int $status_code Code de redirection. @see https://fr.wikipedia.org/wiki/Liste_des_codes_HTTP
 *
 * @return void
 */
function tify_route_redirect($name, array $args = [], $status_code = 301)
{
    Route::redirect($name, $args, $status_code);
}

/**
 * Récupération du nom de qualification de la route courante à afficher.
 *
 * @return string
 */
function tify_route_current_name()
{
    return Route::currentName();
}

/**
 * Récupération des arguments de requête passés dans la route courante.
 *
 * @return array
 */
function tify_route_current_args()
{
    Route::currentArgs();
}

/**
 * Vérifie si la page d'affichage courante correspond à une route déclarée
 *
 * @return bool
 */
function tify_route_has_current()
{
    Route::hasCurrent();
}

/**
 * Vérifie de correspondance du nom de qualification la route courante avec la valeur soumise.
 *
 * @param string $name Identifiant de qualification de la route à vérifier
 *
 * @return bool
 */
function tify_route_is_current($name)
{
    return Route::isCurrent($name);
}
