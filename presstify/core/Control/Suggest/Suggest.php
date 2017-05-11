<?php
namespace tiFy\Core\Control\Suggest;

use tiFy\Core\Control\Factory;

class Suggest extends Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'suggest';
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de Wordpress
     */
    final public function init()
    {
        wp_register_style( 'tify_control-suggest', self::getUrl( get_class() ) .'/Suggest.css', array( ), '160222' );
        wp_register_script( 'tify_control-suggest', self::getUrl( get_class() ) .'/Suggest.js', array( 'jquery-ui-autocomplete' ), '160222', true );
        wp_localize_script( 
            'tify_control-suggest', 
            'tiFyControlSuggest', 
            array(
                'noResultsFound' => __( 'Aucun resultat trouvé', 'tify' )
            )
        );
        
        add_action( 'wp_ajax_tify_control_suggest_ajax', array( $this, 'ajax' ) );
        add_action( 'wp_ajax_nopriv_tify_control_suggest_ajax', array( $this, 'ajax' ) );
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-suggest' );
        wp_enqueue_script( 'tify_control-suggest' );
    }
    
    
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     * 
     * @param array $args
     * @param string $echo
     * @return string
     */
    public static function display( $args = array(), $echo = true )
    {
        static $instance = 0;
        $instance++;
        
        $defaults = array(
            'id'                    => 'tify_control_suggest-'. $instance,
            'class'                 => '',
            'name'                  => 'tify_control_suggest_term-'. $instance,
            'value'                 => '',
            'attrs'                 => array(),
            'before'                => '',
            'after'                 => '',            
            'placeholder'           => __( 'Votre recherche', 'tify' ),
            'readonly'              => false, 
            
            'select'                => false,
            'selected'              => '',
            
            'button_text'           => '',
            'delete_button_text'    => '',
            
            // Options Autocomplete
            /** @see http://api.jqueryui.com/autocomplete/ **/
            'options'            => array(
                //( isset( $args['id'] ) ) ? '#'.$args['id'] .'_response' : '#tify_control_suggest-'. $instance .'_response',
                'appendTo'        => 'body',
                'minLength'        => 2
            ),
            // Classe de la liste de selection    
            'picker'            => ( isset( $args['id'] ) ) ? ''.$args['id'] .'_picker' : 'tify_control_suggest-'. $instance .'_picker',                
                
            // Arguments passés par la requête
            'ajax_action'        => 'tify_control_suggest_ajax',
            'query_args'        => array(), 
            'elements'            => array( 'title', 'permalink' /*'id', 'thumbnail', 'ico', 'type', 'status'*/ ),
            'extras'            => array()
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        $elements    = htmlentities( json_encode( $elements ) );
        $query_args    = htmlentities( json_encode( $query_args ) );
        $extras        = htmlentities( json_encode( $extras ) );
        $options    = htmlentities( json_encode( $options ) );
        
        $search_before = '<button type="button" class="tify_control_suggest_button tify_control_suggest_search">';
        $search_after = '</button>';
        if( ! $button_text ) :
            $button_text = $search_before . '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve" fill="#000000"><g><rect x="20.2" y="28.4" transform="matrix(0.7071 0.7071 -0.7071 0.7071 30.809 -12.7615)" width="21.3" height="4.7"/><path d="M4.6,4.6c-6.1,6.1-6.1,15.9,0,22s15.9,6.1,22,0s6.1-15.9,0-22S10.6-1.5,4.6,4.6z M23.2,23.4   c-4.2,4.2-11.1,4.2-15.3,0s-4.2-11.1,0-15.3s11.1-4.2,15.3,0S27.4,19.2,23.2,23.4z"/></g></svg>' . $search_after;
        else :
            $button_text = $search_before . $button_text .$search_after;
        endif;
        if( $select ) :
            if( $value ) :
                $class .= ' selected';
                $readonly = true;
            endif;
            $delete_button_before = '<button type="button" class="tify_control_suggest_button tify_control_suggest_delete">';
            $delete_button_after = '</button>';
            if( ! $delete_button_text ) :
                $delete_button_text = $delete_button_before . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 357 357"><polygon points="312.13 71.6 285.4 44.88 178.5 151.78 71.6 44.88 44.88 71.6 151.78 178.5 44.88 285.4 71.6 312.13 178.5 205.22 285.4 312.13 312.13 285.4 205.22 178.5 312.13 71.6"/></svg>' . $delete_button_after;
            else :
                $delete_button_text = $delete_button_before . $delete_button_text .$delete_button_after;
            endif;
            $button_text .= $delete_button_text;
        endif;
        
        $field_name = $select ? '' : "name=\"{$name}\"";
        $output  = "";
        $output .= "<div id=\"{$id}\" class=\"tify_control_suggest". ( $class ? ' '. $class : '' ) ."\"";
        $output .= "data-tify_control_suggest=\"{$ajax_action}\" data-select=\"".(int)$select."\" data-elements=\"{$elements}\" data-query_args=\"{$query_args}\" data-extras=\"{$extras}\" data-options=\"{$options}\" data-picker=\"{$picker}\"";
        foreach( (array) $attrs as $k => $v )
            $output .= " {$k}=\"{$v}\"";
        $output .= ">\n";
        $output .= $before;
        $output .= "\t<input type=\"text\" {$field_name} placeholder=\"{$placeholder}\" autocomplete=\"off\"". ( $readonly ? ' readonly' : '' ) ." value=\"" . ( $select ? $selected : $value ) . "\">\n";
        $output .= $button_text;
        $output .= "\t<div class=\"tify_spinner\"><span></span></div>\n";
        $output .= "\t<div id=\"{$id}_response\" class=\"tify_control_suggest_response\"></div>\n";
        if( $select )
            $output .= "\t<input type=\"hidden\" class=\"tify_control_suggest_select_value\" name=\"{$name}\" value=\"{$value}\">";
        $output .= "";
        $output .= $after;
        $output .= "</div>\n";
        
        if( $echo )
            echo $output;
    
        return $output;        
    }
    
    /**
     * Rendu de l'autocomplete
     */
    final public static function itemRender( $args = array() )
    {
        $output  = "";
        $output .= "<a href=\"". ( ! empty( $args['permalink'] ) ? $args['permalink'] : '#' )."\" class=\"". ( isset( $args['ico'] ) ? 'has_ico' : '' )."\">\n";
        unset( $args['permalink'] );
        foreach( $args as $key => $value )
            $output .= "\t<span class=\"{$key}\">{$value}</span>\n";
        $output .= "</a>\n";
    
        return $output;
    }
    
    /**
     *  Récupération de la reponse via Ajax
     */
    final public function ajax()
    {
        // Arguments par defaut à passer en $_POST
        $args = array(
            'term'                => '',
            'elements'            => array(),
            'query_args'        => array(),
            'extras'            => array()
        );
        extract( $args );        
            
        // Traitement des arguments de requête
        ///
        if( isset( $_POST['term'] ) )
            $term = $_POST['term'];
        
        ///
        if( ! empty( $_POST['elements'] ) && is_array( $_POST['elements'] ) )
            $elements = $_POST['elements'];
        
        /// Arguments de requête WP_QUERY
        $query_args['posts_per_page'] =    -1;
        if( isset( $_POST['query_args'] ) && is_array( $_POST['query_args'] ) )
            $query_args = $_POST['query_args'];
        if( ! isset( $query_args['post_type'] ) )
            $query_args['post_type'] = 'any';
        $query_args['s'] = $term;

        // Récupération des posts
        $query_post = new \WP_Query;
        $posts = $query_post->query( $query_args );
        
        // Valeur de retour par défaut
        $response = array();
        while( $query_post->have_posts() ) : $query_post->the_post();
            // Données requises
            $label             = get_the_title();
            $value             = get_the_ID();
                
            // Données de rendu
            if( in_array( 'id', $elements ) )
                $id         = get_the_ID();
            if( in_array( 'title', $elements ) )
                $title        = get_the_title();
            if( in_array( 'permalink', $elements ) )
                $permalink    = get_the_permalink();
            if( in_array( 'thumbnail', $elements ) )
                $thumbnail     = get_the_post_thumbnail( null, 'thumbnail', false );
            if( in_array( 'ico', $elements ) )
                $ico         = get_the_post_thumbnail( null, array(50,50), false );
            if( in_array( 'type', $elements ) )
                $type         = get_post_type_object( get_post_type() )->label;
            if( in_array( 'status', $elements ) )
                $status     = get_post_status_object( get_post_status() )->label;
                
            // Génération du rendu
            $render = call_user_func( "\\tiFy\\Core\\Control\\Suggest\\Suggest::itemRender", compact( $elements ) );
 
            // Valeur de retour
            $response[] = compact( 'label', 'value', 'render', $elements );
        endwhile; 
        wp_reset_query();
            
        wp_send_json( $response );
    }
}