# Controleurs d'affichage

## Récupération avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Control\Control;

$modal = Control::Modal([]);
?>
```

## Affichage du controleur

### Méthode 1 - avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Control\Control;

Control::Modal([], true);
?>
```

### Méthode 2 - avec la méthode "display" de l'intanciateur global

```php
<?php
use tiFy\Core\Control\Control;

Control::display('Modal', [], true);
?>
```

### Méthode 3 - avec la méthode "display" du controleur

```php
<?php
use tiFy\Core\Control\Modal\Modal;

Modal::display([]);
?>
```

### Méthode 4 - avec la fonction d'aide à la saisie (déconseillé hors des fichiers de templates)

> Format de nommage de la fonction d'appel : tify_control_{{lower_id}}.
> Le {{lower_id}} est composé par le nom de déclaration du controleur en minuscule, chaque majuscule étant remplacée par des séparateurs "_" (à l'exception de la première)

```php
<?php
tify_control_slick_carousel([]);
?>
```

## Initialisation des scripts

### Méthode 1 - avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Control\Control;

add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    Control::enqueue_scripts('Modal', []);
}

?>
```

### Méthode 2 - avec la méthode "enqueue_scripts" du controleur

```php
<?php
use tiFy\Core\Control\Modal\Modal;

add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    Modal::enqueue_scripts([]);
}

?>
```

### Méthode 3 - avec la fonction d'aide à la saisie (déconseillé)

```php
<?php
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    tify_control_enqueue_scripts('Modal', []);
}

?>
```

### Méthode 4 - avec la fonction d'aide à la saisie et le {{lower_id}} (déprécié)

```php
<?php
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    tify_control_enqueue_scripts('modal', []);
}

?>
```

## Appel de méthode d'affichage complémentaire

### Méthode 1 - avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Control\Control;

Control::call('Modal', 'trigger', [], true);
?>
```

### Méthode 2 - depuis la méthode global (alternative)

```php
<?php
use tiFy\Core\Control\Modal\Modal;

Modal::trigger('trigger', [], true);
?>
```

### Méthode 3 - depuis une fonction d'aide à la saisie déclarée (déconseillé hors des fichiers de templates)

> Format de nommage de la fonction d'appel : tify_control_{{lower_id}}.
> Le {{lower_id}} est composé par le nom de déclaration du controleur en minuscule, chaque majuscule étant remplacée par des séparateurs "_" (à l'exception de la première)

```php
<?php
tify_control_modal_trigger();
?>
```

## Liste des controleurs d'affichage natifs

### Natifs 

#### AccordionMenu

Menu accordéon

***lower_id*** : accordion_menu

#### AdminPanel (maintenance requise)

Interface d'administration 

***lower_id*** : admin_panel

#### Calendar

Calendrier

***lower_id*** : calendar

#### Checkbox (dépreciation > Field)

Case à cocher enrichie

***lower_id*** : checkbox

#### Colorpicker (dépreciation > Field)

Selecteur couleur

***lower_id*** : color_picker

#### CookieNotice

Notiification masquable. L'action de masquage est enregistrée dans un cookie.

***lower_id*** : cookie_notice

#### CryptedData (dépreciation > Field)

Champ de texte crypté avec générateur en option

***lower_id*** : crypted_data

#### CurtainMenu

Menu rideau

***lower_id*** : curtain_menu

#### DropdownColors (dépreciation > Field)

Selecteur de palette de couleur

***lower_id*** : dropdown_colors

#### DropdownGlyphs (dépreciation > Field)

Selecteur de glyph

***lower_id*** : dropdown_glyphs

#### DropdownImages (dépreciation > Field)

Selecteur d'image

***lower_id*** : dropdown_images

#### DropdownMenu

Menu déroulant

***lower_id*** : dropdown_menu

#### Findposts (dépreciation > Field)

Champ doté d'une fenêtre de recherche de post

***lower_id*** : findposts

#### HolderImage

Image de remplacement

***lower_id*** : holder_image

#### ImageLightbox

Fenêtre modale d'affichage d'image

***lower_id*** : image_lightbox

#### MediaFile (dépreciation > Field)

Champ doté d'une fenêtre de selection de fichier dans la médiathèque (interface d'administration uniquement)

***lower_id*** : media_file

#### MediaImage (dépreciation > Field)

Zone image doté d'une fenêtre de selection de fichier dans la médiathèque (interface d'administration uniquement)

***lower_id*** : media_image

#### Modal

Fenêtre modale d'affichage de contenu

***lower_id*** : modal

#### Notices

Affichage de notification masquable

***lower_id*** : notices

#### Progress (maintenance requise)

Jauge de progression

***lower_id*** : progress

#### QuicktagsEditor (maintenance requise)

Editeur quicktags

***lower_id*** : quicktags_editor

#### Repeater (dépreciation en cours > Field)

Liste de champs répétable

***lower_id*** : repeater

#### SlickCarousel

Diaporama de contenu HTML utilisant la bibliothèque slick

***lower_id*** : slick_carousel

#### Slider

Diaporama d'images

***lower_id*** : slider

#### Spinkit

Indicateur de préchargement

***lower_id*** : spinkit

#### Suggest (dépreciation > Field)

Champ de recherche par autocomplétion

***lower_id*** : suggest

#### Table

Tableau responsive CSS (basé sur des div)

***lower_id*** : table

#### Tabs

Interface de boîte à onglet

***lower_id*** : tabs

#### TextRemaining (dépreciation > Field)

Champ de saisie avec limitation du nombre de caractère

***lower_id*** : text_remaining

### Dépréciées

#### Dropdown - dépréciation muette - utiliser tiFy\Core\Field\SelectJs\SelectJs

Selecteur JS

***lower_id*** : dropdown

#### DynamicInputs - dépréciation muette - utiliser tiFy\Core\Control\Repeater\Repeater

Répétiteur de champ

***lower_id*** : dynamic_inputs

#### Switcher - dépréciation muette - utiliser tiFy\Core\Field\ToggleSwitch\ToggleSwitch

Bouton de bascule On/Off

***lower_id*** : switch

#### Token utiliser tiFy\Core\Control\CryptedData\CryptedData

Champ de texte crypté et générateur

***lower_id*** : token

#### Touchtime - dépréciation muette - utiliser tiFy\Core\Field\DatetimeJs\DatetimeJs

Selecteur de date et heure

***lower_id*** : touchtime

### Annexes

#### TakeOverActionLink

Lien d'action de prise de controle de compte utilisateur ou de récupération de compte principal

***lower_id*** : take_over_action_link

#### TakeOverAdminBar (en cours de développement)

Barre d'administration de prise de controle de compte utilisateur

***lower_id*** : take_over_admin_bar

#### TakeOverSwitcherForm

Formulaire de prise de controle de compte utilisateur

***lower_id*** : take_over_switcher_form

#### ContactToggle (évolution)

Affichage des information de contact

***lower_id*** : contact_toggle