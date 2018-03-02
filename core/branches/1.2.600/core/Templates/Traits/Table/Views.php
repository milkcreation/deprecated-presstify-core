<?php 
namespace tiFy\Core\Templates\Traits\Table;

trait Views
{
    /*** === Cartographie des vues filtrées === ***/
    public function parseViews( $views = array() )
    {    
        foreach( $views as &$attrs ) :
            if( ! is_string( $attrs ) ) :
                $attrs = $this->parseView( $attrs );
            endif;
        endforeach;
        
        return $views;
    }
    
    /** === Traitement des arguments d'une vue filtrée == **/
    public function parseView( $args = array() )
    {
        static $index;
        
        $defaults = array(
            'base_uri'              => $this->getConfig( 'base_url' ),
            'label'                 => sprintf( __( 'Filtre #%d', 'tify' ), $index++ ),    
            'title'                 => '',
            'class'                 => '',
            'link_attrs'            => array(),
            'current'               => false,
            'hide_empty'            => false,
            'count'                 => 0,    
            'add_query_args'        => false,
            'remove_query_args'     => false,                        
            'count_query_args'      => false
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        if( ! $base_uri )
            $base_uri = set_url_scheme( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                    
        if( $hide_empty && ! $count )
            return;
        
        // Traitement des arguments
        /// Traitement de l'url de requête
        $parsed_request_uri = parse_url( $_SERVER['REQUEST_URI'] );
        if( isset( $parsed_request_uri['query'] ) ) :
            parse_str( $parsed_request_uri['query'], $request_query_args );
        else :
            $request_query_args = array();
        endif;
        
        /// Traitement de l'url de la vue
        $parsed_base_uri = parse_url( $base_uri );    
        if( isset( $parsed_base_uri['query'] ) ) :
            parse_str( $parsed_base_uri['query'], $base_query_args );
        else: 
            $base_query_args = array();
        endif;
            
        /// Traitement des arguments de requête à ajouter
        if( ! empty( $add_query_args ) ) :
            $base_query_args = wp_parse_args( $add_query_args, $base_query_args );
        endif;
        
        /// Traitement des argument de requête à supprimer
        if( empty( $remove_query_args ) ) :
            $remove_query_args = array();
        elseif( $remove_query_args === true ) :
            $remove_query_args = array();
        elseif( is_string( $remove_query_args ) ) :
            $remove_query_args = array( $remove_query_args );
        endif;        
        array_push( $remove_query_args, 'action', 'action2', 'filter_action' );        
        foreach( $remove_query_args as $key ) :
            unset( $base_query_args[$key] );
        endforeach;
                
        /// Traitement du lien    
        $href = esc_url( add_query_arg( $base_query_args, $parsed_base_uri['scheme'] .'://'. $parsed_base_uri['host'] . $parsed_base_uri['path'] ) );
        
        /// Vérifie si le lien est actif                
        if( ! is_null( $current ) ) :
            if( ! empty( $add_query_args ) && is_array( $base_query_args ) && is_array( $request_query_args ) && ! @array_diff_assoc( $base_query_args, $request_query_args ) ) :
                $current = true;
            elseif( empty( $add_query_args ) && is_array( $request_query_args ) && ! @array_diff( $request_query_args, $base_query_args ) ) :
                $current = true;
            endif;    
        endif;
        
        if( $current ) :
            $this->QueryArgs = wp_parse_args( $add_query_args, $this->QueryArgs );
        endif;
        
        /// Définition de l'intitulé
        if( is_array( $label ) && isset( $label['singular'] ) && isset( $label['plural'] )  ) :
            $text = _n( $label['singular'], $label['plural'], $count );
        else :
            $text = (string) $label; 
        endif;
        
        $output  = "";
        $output .= "<a href=\"{$href}\"";
        $output .= " class=\"". ( $current ? 'current' : '' ) ."". ( ! empty( $class ) ? ' '. $class : '' ) ."\"";
        if( ! empty( $link_attrs ) ) :
            foreach( $link_attrs as $i => $j ) :
                $output .= " {$i}=\"{$j}\"";
            endforeach;
        endif;
        if( $title )
            $output .= " title=\"{$title}\"";
        $output .= ">{$text}";    
        $output .= " <span class=\"count\">({$count})</span>";
        $output .= "</a>";    
        
        return $output;
    }
}