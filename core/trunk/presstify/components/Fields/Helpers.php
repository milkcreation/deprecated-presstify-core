<?php

use tiFy\Core\Field\Field;

/**
 * Bouton
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_button($attrs = [], $echo = true)
{
    $field = (string)Field::Button($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 * Case à coché
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_checkbox($attrs = [], $echo = true)
{
    $field = (string)Field::Checkbox($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 * Selecteur de date et heure JS
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_datetime_js($attrs = [], $echo = true)
{
    $field = (string)Field::DatetimeJs($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 * Champ de téléversement de fichier
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_file($attrs = [], $echo = true)
{
    $field = (string)Field::File($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_hidden($attrs = [], $echo = true)
{
    $field = (string)Field::Hidden($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_label($attrs = [], $echo = true)
{
    $field = (string)Field::Label($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_number($attrs = [], $echo = true)
{
    $field = (string)Field::Number($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_number_js($attrs = [], $echo = true)
{
    $field = (string)Field::NumberJs($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_password($attrs = [], $echo = true)
{
    $field = (string)Field::Password($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_radio($attrs = [], $echo = true)
{
    $field = (string)Field::Radio($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_repeater($attrs = [], $echo = true)
{
    $field = (string)Field::Repeater($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_select($attrs = [], $echo = true)
{
    $field = (string)Field::Select($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_select_js($attrs = [], $echo = true)
{
    $field = (string)Field::SelectJs($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_submit($attrs = [], $echo = true)
{
    $field = (string)Field::Submit($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_text($attrs = [], $echo = true)
{
    $field = (string)Field::Text($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_textarea($attrs = [], $echo = true)
{
    $field = (string)Field::Textarea($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}

/**
 *
 *
 * @param array $attrs {
 *      Liste des attributs de configuration
 * }
 * @param bool $echo Activation de l'affichage. défaut true.
 *
 * @return string
 */
function tify_field_toggle_switch($attrs = [], $echo = true)
{
    $field = (string)Field::ToggleSwitch($attrs);

    if ($echo) :
        echo $field;
    else :
        return $field;
    endif;
}