<?php

use tiFy\Core\Layout\Layout;

/**
 * Fil d'arianne
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_layout_breadcrumb($attrs = [], $echo = true)
{
    $layout = (string)Layout::Breadcrumb($attrs);

    if ($echo) :
        echo $layout;
    else :
        return $layout;
    endif;
}

/**
 * Titre de balise meta
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_layout_meta_title($attrs = [], $echo = true)
{
    $layout = (string)Layout::MetaTitle($attrs);

    if ($echo) :
        echo $layout;
    else :
        return $layout;
    endif;
}

/**
 * Message de notification
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 *
 *      @var string $id Identifiant de qualification du controleur d'affichage.
 *      @var string $container_id ID HTML du conteneur de l'élé@var string $id Identifiant de qualification du controleur d'affichage.ment.
 *      @var string $container_class Classes HTML du conteneur de l'élément.
 *      @var string $text Texte de notification. défaut 'Lorem ipsum dolor site amet'.
 *      @var string $dismissible Bouton de masquage de la notification.
 *      @var string $type Type de notification info|warning|success|error. défaut info.
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_layout_notice($attrs = [], $echo = true)
{
    $layout = (string)Layout::Notice($attrs);

    if ($echo) :
        echo $layout;
    else :
        return $layout;
    endif;
}

/**
 * Balise HTML
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 *
 *      @var string $id Identifiant de qualification du controleur d'affichage.
 *      @var string $tag Balise HTML div|span|a|... défaut div.
 *      @var array $attrs Liste des attributs de balise HTML.
 *      @var string $content Contenu de la balise HTML.
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_layout_tag($attrs = [], $echo = true)
{
    $layout = (string)Layout::Tag($attrs);

    if ($echo) :
        echo $layout;
    else :
        return $layout;
    endif;
}