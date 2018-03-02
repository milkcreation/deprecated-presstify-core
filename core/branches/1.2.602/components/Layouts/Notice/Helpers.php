<?php

use tiFy\Core\Layout\Layout;

/**
 * Affichage d'un message de notification
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 *
 *      @var string $container_id ID HTML du conteneur de l'élément.
 *      @var string $container_class Classes HTML du conteneur de l'élément.
 *      @var string $text Texte de notification. défaut 'Lorem ipsum dolor site amet'.
 *      @var string $dismissible Bouton de masquage de la notification
 *      @var string $type Type de notification info|warning|success|error. défaut info.
 * }
 * @return void
 */
function tify_layout_notice($attrs = [])
{
    echo (string)Layout::Notice($attrs);
}