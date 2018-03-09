<?php

namespace tiFy\Core\Forms;

use tiFy\App\Traits\App as TraitsApp;
use tiFy\Core\Control\Control;
use tiFy\Core\Forms\Form\Form;
use tiFy\Core\Forms\Form\Field;

class Factory
{
    use TraitsApp;

    /**
     * Classe de rappel du Formulaire
     * @var Form
     */
    private $Form;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification du formulaire
     * @param array $attrs Attribut de configuration du formulaire
     *
     * @return void
     */
    public function __construct($id, $attrs = [])
    {
        // Traitement des attributs de formulaire
        $attrs = $this->setAttrs($attrs);

        // Initialisation du formulaire associé
        $this->Form = new Form($id, $attrs);

        // Déclenchement des événements
        $this->appAddAction('tify_form_loaded');
    }

    /**
     * A l'issue du chargement complet de la liste des formulaires déclarés
     *
     * @return void
     */
    final public function tify_form_loaded()
    {
        Control::enqueue_scripts('Notices');

        Forms::setCurrent($this);
        $this->Form->handle()->proceed();
        Forms::resetCurrent();
    }

    /**
     * Récupération de la classe de rappel du formulaire
     *
     * @return Form
     */
    final public function getForm()
    {
        return $this->Form;
    }

    /**
     * Récupération d'un champ.
     *
     * @param string $field_slug Identifiant de qualification du champ
     *
     * @return Field
     */
    final public function getField($field_slug)
    {
        return $this->getForm()->getField($field_slug);
    }

    /**
     * Execution des surcharges d'événements.
     * @internal La méthode de surcharge de la classe on_{callback} doit exister.
     * @see \tiFy\Core\Forms\Form\Callbacks
     *
     * @param string $callback Identifiant de qualification de la méthode de rappel. ex. handle_successfully.
     *
     * @return callable
     */
    final public function call($callback, $args = [])
    {
        if (method_exists($this, 'on_' . $callback)) :
            return call_user_func_array([$this, 'on_' . $callback], $args);
        endif;

        return \__return_null();
    }

    /**
     * Traitement des variables de requête au moment de la soumission du formulaire.
     * @internal La méthode de surcharge de la classe parse_query_var_{field_slug} doit exister.
     *
     * @param string $field_slug Identifiant de qualification du champ
     * @param mixed $value Valeur du champs
     *
     * @return callable
     */
    final public function parseQueryVar($field_slug, $value)
    {
        if (method_exists($this, 'parse_query_var_' . $field_slug)) :
            return call_user_func([$this, 'parse_query_var_' . $field_slug], $value);
        endif;

        return call_user_func([$this, 'parse_query_vars'], $field_slug, $value);
    }

    /**
     * Vérification d'intégrité des variables de requête au moment de la soumission du formulaire.
     *
     * @param Field $field_obj Classe de rappel du champ à vérifier.
     * @param mixed $errors Liste des erreurs relative à la vérification préalable.
     *
     * @return callable
     */
    final public function checkQueryVar($field_obj, $errors)
    {
        if (method_exists($this, 'check_query_var_' . $field_obj->getSlug())) :
            return call_user_func([$this, 'check_query_var_' . $field_obj->getSlug()], $errors, $field_obj);
        endif;

        return call_user_func([$this, 'check_query_vars'], $errors, $field_obj);
    }

    /**
     * Définition des attributs de formulaire
     *
     * @param array Liste des attributs de configuration du formulaire.
     *
     * @return array
     */
    private function setAttrs($attrs)
    {
        /**
         * @var array $addons
         * @var array $buttons
         * @var array $fields
         * @var array $notices
         * @var array $options
         */
        $pieces = ['addons', 'buttons', 'fields', 'notices', 'options'];

        foreach ($pieces as $piece) :
            if (!empty($attrs[$piece])) :
                ${$piece} = $attrs[$piece];
            else :
                ${$piece} = [];
            endif;
            unset($attrs[$piece]);
        endforeach;

        // Globaux
        if ($matches = preg_grep('/^set_form_(.*)/', get_class_methods($this))) :
            foreach ($matches as $method) :
                $attr = preg_replace('/^set_form_/', '', $method);
                if (in_array($attr, $pieces)) {
                    continue;
                }

                $args = isset($attrs[$attr]) ? $attrs[$attr] : null;
                $attrs[$attr] = call_user_func([$this, $method], $args);
            endforeach;
        endif;

        // Addons
        $addons = $this->setAddons($addons);

        // Boutons     
        $buttons = $this->setButtons($buttons);

        // Champs
        $fields = $this->setFields($fields);

        // Notices @todo

        // Options @todo

        $attrs += compact($pieces);

        return $attrs;
    }

    /**
     * Définition des surchages de déclaration d'addons
     * @internal La méthode de surcharge de la declaration set_addon_{addon_id} doit exister.
     *
     * @param array $items Liste des addons déclarés dans les attributs de configuration.
     *
     * @return array
     */
    private function setAddons($items = [])
    {
        $slugs = (!empty($items)) ? \array_flip(\array_column($items, 'slug')) : [];

        if ($matches = preg_grep('/^set_addon_(.*)/', get_class_methods($this))) :
            foreach ($matches as $method) :
                $slug = preg_replace('/^set_addon_/', '', $method);

                if (isset($slugs[$slug])) :
                    $k = $slugs[$slug];
                    $attrs = $items[$k];
                else :
                    $k = count($items);
                    $attrs = [];
                endif;

                $items[$slug] = call_user_func([$this, $method], $attrs);
            endforeach;
        endif;

        return $items;
    }

    /**
     * Définition des surchages de déclaration de boutons d'action
     * @internal La méthode de surcharge de la declaration set_button_{button_id} doit exister.
     *
     * @return array
     */
    private function setButtons($items = [])
    {
        $slugs = (!empty($items)) ? array_flip(array_column($items, 'slug')) : [];

        if ($matches = preg_grep('/^set_button_(.*)/', get_class_methods($this))) :
            foreach ($matches as $method) :
                $slug = preg_replace('/^set_button_/', '', $method);

                if (isset($slugs[$slug])) :
                    $k = $slugs[$slug];
                    $attrs = $items[$k];
                else :
                    $k = count($items);
                    $attrs = [];
                endif;

                $items[$slug] = call_user_func([$this, $method], $attrs);
            endforeach;
        endif;

        return $items;
    }

    /**
     * Définition des surchages de déclaration des champs de formulaire
     * @internal La méthode de surcharge de la declaration set_field_{field_slug} doit exister.
     *
     * @return array
     */
    private function setFields($items = [])
    {
        $slugs = (!empty($items)) ? array_flip(array_column($items, 'slug')) : [];

        if ($matches = preg_grep('/^set_field_(.*)/', get_class_methods($this))) :
            foreach ($matches as $method) :
                $slug = preg_replace('/^set_field_/', '', $method);

                if (isset($slugs[$slug])) :
                    $k = $slugs[$slug];
                    $attrs = $items[$k];
                else :
                    $k = count($items);
                    $attrs = [];
                endif;

                $items[$k] = wp_parse_args(['slug' => $slug], call_user_func([$this, $method], $attrs));
            endforeach;
        endif;

        return $items;
    }

    /**
     * Liste des classes HTML du formulaire
     *
     * @param Form $form Classe de rappel du formulaire
     * @param array $classes Liste des classes du formulaire déclarées dans la configuration.
     *
     * @return array
     */
    final public function formClasses($form, $classes)
    {
        return is_callable([$this, 'form_classes'])
            ? call_user_func([$this, 'form_classes'], $form, $classes)
            : $classes;
    }

    /**
     * Balise d'ouverture du conteneur d'un champ
     * @internal La méthode de surcharge de la declaration field_open_{field_slug} doit exister.
     *
     * @param Field $field Classe de rappel du champ.
     * @param string $id ID HTML de la balise du conteneur d'ouverture du champ.
     * @param string $class Class HTML de la balise du conteneur d'ouverture du champ.
     *
     * @return string
     */
    final public function fieldOpen($field, $id, $class)
    {
        return is_callable([$this, 'field_open_' . $field->getSlug()])
            ? call_user_func([$this, 'field_open_' . $field->getSlug()], $field, $id, $class)
            : call_user_func([$this, 'fields_open'], $field, $id, $class);
    }

    /**
     * Balise de fermeture du conteneur d'un champ
     * @internal La méthode de surcharge de la declaration field_close_{field_slug} doit exister.
     *
     * @param Field $field Classe de rappel du champ.
     *
     * @return string
     */
    final public function fieldClose($field)
    {
        return is_callable([$this, 'field_close_' . $field->getSlug()])
            ? call_user_func([$this, 'field_close_' . $field->getSlug()], $field)
            : call_user_func([$this, 'fields_close'], $field);
    }

    /**
     * Libellé de l'affichage d'un champ
     */
    final public function fieldLabel( $field, $input_id, $class, $label, $required )
    {
        return is_callable( array( $this, 'field_label_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_label_'. $field->getSlug() ), $field, $input_id, $class, $label, $required ) :
            call_user_func( array( $this, 'fields_label' ), $field, $input_id, $class, $label, $required );
    }
    
    /**
     * Pré-affichage du contenu d'un champ
     */
    final public function fieldBefore( $field, $before )
    {
        return is_callable( array( $this, 'field_before_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_before_'. $field->getSlug() ), $field, $before ) :
            call_user_func( array( $this, 'fields_before' ), $field, $before );
    }

    /**
     * Post-affichage du contenu d'un champ
     */
    final public function fieldAfter( $field, $after )
    {
        return is_callable( array( $this, 'field_after_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_after_'. $field->getSlug() ), $field, $after ) :
            call_user_func( array( $this, 'fields_after' ), $field, $after );
    }
    
    /**
     * Liste des classes HTML du contenu d'un champ
     */
    final public function fieldClasses( $field, $classes )
    {
        return is_callable( array( $this, 'field_classes_'. $field->getSlug() ) ) ? 
            call_user_func( array( $this, 'field_classes_'. $field->getSlug() ), $field, $classes ) :
            call_user_func( array( $this, 'fields_classes' ), $field, $classes );
    }
    
    /**
     * Ouverture de l'affichage d'un bouton
     */
    final public function buttonOpen( $button, $id, $class )
    {
        return is_callable( array( $this, 'button_open_'. $button->getID() ) ) ? 
            call_user_func( array( $this, 'button_open_'. $button->getID() ), $button, $id, $class ) :
            call_user_func( array( $this, 'buttons_open' ), $button, $id, $class );
    }
    
    /**
     * Fermeture de l'affichage d'un bouton
     */
    final public function buttonClose($button )
    {
        return is_callable( array( $this, 'button_close_'. $button->getID() ) ) ? 
            call_user_func( array( $this, 'button_close_'. $button->getID() ), $button ) :
            call_user_func( array( $this, 'buttons_close' ), $button );
    }
    
    /**
     * Liste des classes HTML d'un bouton
     */
    final public function buttonClasses( $button, $classes )
    {
        return is_callable( array( $this, 'button_classes_'. $button->getID() ) ) ? 
            call_user_func( array( $this, 'button_classes_'. $button->getID() ), $button, $classes ) :
            call_user_func( array( $this, 'buttons_classes' ), $button, $classes );
    }

    /**
     * Traitement par défaut des variables de requête au moment de la soumission
     */
    public function parse_query_vars( $field_slug, $value )
    {
        return $value;
    }
    
    /**
     * Vérification par défaut de l'intégrité des variables de requêtes
     */
    public function check_query_vars( $errors, $field_obj )
    {
        return $errors;
    }

    /**
     * Affichage du formulaire
     */
    public function display( $echo = false )
    {
        $output = $this->getForm()->display();
        if( $echo )
            echo $output;
        
        return $output;
    }
    
    /**
     * Liste des classes HTML d'un formulaire
     * 
     * @see \tiFy\Core\Forms\Form
     */
    public function form_classes( $form, $classes )
    {
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un champ
     * 
     * @see \tiFy\Core\Forms\Form\Field
     */
    public function fields_open( $field, $id, $class )
    {
        if( ! $field->typeSupport( 'wrapper' ) )
            return;

        return "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"{$class}\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un champ
     * 
     * @see \tiFy\Core\Forms\Form\Field
     */
    public function fields_close( $field )
    {
        if( ! $field->typeSupport( 'wrapper' ) )
            return;

        return "</div>\n";
    }
    
    /**
     * Libellé par défault de l'affichage d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function fields_label( $field, $input_id, $class, $label, $required )
    {
        return "<label for=\"{$input_id}\" class=\"{$class}\">{$label}{$required}</label>\n";
    }
    
    /**
     * Pré-affichage par défaut du contenu d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function fields_before( $field, $before )
    {
        return $before;
    }
    
    /**
     * Post-affichage par défaut du contenu d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function fields_after( $field, $after )
    {
        return $after;
    }
    
    /**
     * Liste des classes HTML du contenu d'un champ
     * 
     * @see \tiFy\Core\Forms\FieldTypes\Factory
     */
    public function fields_classes( $field, $classes )
    {
        return $classes;
    }
    
    /**
     * Ouverture par défaut de l'affichage d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function buttons_open( $button, $id, $class )
    {
        return "<div". ( $id ? " id=\"{$id}\"" : "" ) ." class=\"{$class}\">\n";
    }
    
    /**
     * Fermeture par défaut de l'affichage d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function buttons_close( $button )
    {
        return "</div>\n";
    }
    
    /**
     * Liste des classes HTML d'un bouton
     * 
     * @see \tiFy\Core\Forms\Buttons\Factory
     */
    public function buttons_classes( $button, $classes )
    {
        return $classes;
    }    
}