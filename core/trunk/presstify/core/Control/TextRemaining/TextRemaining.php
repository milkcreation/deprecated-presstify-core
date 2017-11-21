<?php
/**
 * Zone de texte limitée
 * 
 * @see http://www.w3schools.com/tags/tag_textarea.asp -> attributs possibles pour le selecteur textarea
 * @see http://www.w3schools.com/jsref/dom_obj_text.asp -> attributs possibles pour le selecteur input
 */

namespace tiFy\Core\Control\TextRemaining;

use tiFy\Lib\Chars;

class TextRemaining extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de qualification de la classe
     * @var string
     */
    protected $ID = 'text_remaining';
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     * 
     * @return void
     */
    final public function init()
    {
        wp_register_style('tify_control-text_remaining', self::tFyAppAssetsUrl('TextRemaining.css', get_class()), array(), '141213');
        wp_register_script('tify_control-text_remaining', self::tFyAppAssetsUrl('TextRemaining.js', get_class()), array('jquery'), '141213', true);
        wp_localize_script('tify_control-text_remaining', 'tifyTextRemaining',
            array(
                    'plural'    => __('caractères restants', 'tify'),
                    'singular'  => __('caractère restant', 'tify'),
                    'none'      => __('Aucun caractère restant', 'tify')
            )
        );
    }
    
    /**
     * Mise en file des scripts
     * 
     * @return void
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_style('tify_control-text_remaining');
        wp_enqueue_script('tify_control-text_remaining');
    }
    
    /**
     * Affichage du controleur
     * 
     * @param array $args {
     *      Attributs d'affichage du controleur
     *      
     *      @param string $id Identifiant de qualification.
     *      @param string $container_id Id HTML du conteneur du controleur.
     *      @param string $feedback_area Id HTML du conteneur d'affichage des informations de saisie.
     *      @param string $name Nom du champ d'enregistrement
     *      @param string $selector Type de selecteur. textarea (défaut)|input.
     *      @param string $value Valeur du champ de saisie.
     *      @param array $attrs Attributs HTML du champ.
     *      @param int $length Nombre maximum de caractères attendus. 150 par défaut.
     *      @param bool $maxlength Activation de l'arrêt de la saisie en cas de dépassement. true par défaut.
     *  }
     *  @param bool $echo Activation de l'affichage
     * 
     * @return string
     */
    public static function display( $args = array(), $echo = true )
    {
        static $instance = 0;
        $instance++;
        
        $defaults = array(
            'id'                    => 'tify_control_text_remaining-'. $instance,
            'container_id'          => 'tify_control_text_remaining-container-'. $instance,
            'feedback_area'         => '#tify_control_text_remaining-feedback-'. $instance,
            'name'                  => 'tify_control_text_remaining-'. $instance,
            'selector'              => 'textarea',    // textarea (default) // @TODO | input 
            'value'                 => '',
            'value_filter'          => true,
            'attrs'                 => array(),
            'length'                => 150,
            'maxlength'             => true     // Stop la saisie en cas de dépassement
        );    
        $args = wp_parse_args( $args, $defaults );
        extract($args);

        if ($value_filter) :
            $value = nl2br($value);
            $value = Chars::br2nl($value);
            $value = wp_unslash($value);
        endif;

        $output = "";
        $output .= "<div id=\"{$container_id}\" class=\"tify_control_text_remaining-container\">\n";
        switch( $selector ) :
            default :
            case 'textarea' :                    
                $output .= "\t<textarea id=\"{$id}\" data-tify_control=\"text_remaining\" data-feedback_area=\"{$feedback_area}\"";
                if( $name )
                    $output .= " name=\"{$name}\"";
                if( $maxlength )
                    $output .= " maxlength=\"{$length}\"";
                if( $attrs )
                    foreach( $attrs as $iattr => $vattr )
                        $output .= " {$iattr}=\"{$vattr}\"";
                $output .= ">". $value ."</textarea>\n";
                $output .= "\t<span id=\"" . str_replace('#', '', $feedback_area) . "\" class=\"feedback_area\" data-max-length=\"{$length}\" data-length=\"". strlen( $value ) ."\"></span>\n";
                break;
            case 'input' :                    
                $output .= "\t<input id=\"{$id}\" data-tify_control=\"text_remaining\" data-feedback_area=\"{$feedback_area}\"";
                if( $name )
                    $output .= " name=\"{$name}\"";
                if( $maxlength )
                    $output .= " maxlength=\"{$length}\"";
                if( $attrs )
                    foreach( $attrs as $iattr => $vattr )
                        $output .= " {$iattr}=\"{$vattr}\"";
                $output .= " value=\"". $value ."\">\n";
                $output .= "\t<span id=\"" . str_replace('#', '', $feedback_area) . "\" class=\"feedback_area\" data-max-length=\"{$length}\" data-length=\"". strlen( $value ) ."\"></span>\n";
                break;
        endswitch;
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
    
        return $output;
    }
}