# Controleur de champ

## Récupération avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Field\Field;

$input_text = Field::Text([]);
?>
```

## Affichage du champ

### Méthode 1 - avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Field\Field;

echo Field::Text([]);
?>
```

### Méthode 2 - avec la méthode "display" de l'intanciateur global

```php
<?php
use tiFy\Core\Field\Field;

echo Field::display('Text', []);
?>
```

### Méthode 3 - avec la méthode "display" du controleur de champ

```php
<?php
use tiFy\Core\Field\Text\Text;

Text::display([]);
?>
```

### Méthode 4 - avec la fonction d'aide à la saisie (déconseillé hors des fichiers de templates)

> Format de nommage de la fonction d'appel : tify_field_{{lower_id}}.
> Le {{lower_id}} est composé par le nom de déclaration du controleur en minuscule, chaque majuscule étant remplacée par des séparateurs "_" (à l'exception de la première)

```php
<?php
tify_field_select_js([]);
?>
```

## Initialisation des scripts

### Méthode 1 - avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Field\Field;

add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    Field::enqueue('Text', []);
}

?>
```

### Méthode 2 - avec la méthode "enqueue_scripts" du controleur de champ

```php
<?php
use tiFy\Core\Field\Text\Text;

add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    Text::enqueue_scripts([]);
}

?>
```

### Méthode 3 - avec la fonction d'aide à la saisie (déconseillé)

```php
<?php
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    tify_field_enqueue_scripts('Text', []);
}

?>
```

### Méthode 4 - avec la fonction d'aide à la saisie et le {{lower_id}} (déprécié)

```php
<?php
add_action('wp_enqueue_scripts', 'my_enqueue_scripts');

function my_enqueue_scripts()
{
    tify_field_enqueue_scripts('modal', []);
}

?>
```

## Appel de méthode d'affichage complémentaire

### Méthode 1 - avec l'intanciateur global (recommandé)

```php
<?php
use tiFy\Core\Field\Field;

echo Field::call('Text', 'trigger', []);
?>
```

### Méthode 2 - depuis la méthode global (alternative)

```php
<?php
use tiFy\Core\Field\Text\Text;

Text::trigger('trigger', [], true);
?>
```

### Méthode 3 - depuis une fonction d'aide à la saisie déclarée (déconseillé hors des fichiers de templates)

> Format de nommage de la fonction d'appel : tify_field_{{lower_id}}.
> Le {{lower_id}} est composé par le nom de déclaration du controleur en minuscule, chaque majuscule étant remplacée par des séparateurs "_" (à l'exception de la première)

```php
<?php
tify_field_modal_trigger();
?>
```

## Liste des controleurs d'affichage natifs

### Natifs 

#### Button

Bouton d'action

#### Checkbox

Case à cocher

#### DatetimeJs

Selecteur de date et heure JS

#### File

Champ de téléversement de fichier

#### Hidden

Champ caché

#### Label

Libelé de champ

#### Number

Champ de selection de valeur numérique

#### NumberJs

Champ de selection de valeur numérique enrichi

#### Password

Champ de saisie de mot de passe

#### Radio

Champ de selection bouton radio

#### Repeater

Répétiteur de champs

#### Select

Liste de selection

#### SelectJs

Liste de selection enrichie

#### Submit

Soumission de formulaire

#### Text

Champ texte de saisie libre

#### Textarea

Zone de texte de saisie libre

#### ToggleSwitch

Bouton de bascule on/off