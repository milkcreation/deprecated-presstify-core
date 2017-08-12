<?php
namespace tiFy\Abstracts;

abstract class Nodes
{
    /**
     * Récupération de greffons personnalisés
     */
    final public function customs( $nodes = array(), $args = array() )
    {
        $ids = array();
        foreach( (array) $nodes as $node ) :
            if( ! isset( $node['id'] ) )
                continue;
            if( ! in_array( $node['id'], $ids ) ) :
                array_push( $ids, $node['id'] );
            endif;
        endforeach;
        
        if( $methods = preg_grep( '/^(.*)_node/', get_class_methods( $this ) ) ) :
            foreach( $methods as $method ) :
                preg_match( '/^(.*)_node/', $method, $matches );
                if( ! isset( $matches[1] ) || in_array( $matches[1], array( 'term', 'post') ) || in_array( $matches[1], $ids ) ) :
                    continue;
                endif;
                array_push( $ids, $matches[1] );
                $nodes[] = array( 'id' => $matches[1] );
            endforeach;
        endif;

        array_walk(
            $nodes,
            array( $this, 'parseCustom' ),
            $args
        );

        return $nodes;
    }    
    
    /**
     * Traitement des attributs d'un greffon personnalisé 
     */
    final public function parseCustom( &$node, $key, $args )
    {
        $_node = array();
        
        $_node['id'] = isset( $node['id'] ) ? esc_attr( $node['id'] ) : uniqid(); 
        $_node['parent'] = isset( $node['parent'] ) ? esc_attr( $node['parent'] ) : ''; 
        
        if( $matches = preg_grep( '/^node_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $attr = preg_replace( '/^node_/', '', $method );                       
                $_node[$attr] = call_user_func( array( $this, 'node_'. $attr ), $node, $args ); 
            endforeach;
        endif;
        
        if( $matches = preg_grep( '/^'. $_node['id'] .'_node_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $attr = preg_replace( '/^'. $_node['id'] .'_node_/', '', $method );                       
                $_node[$attr] = call_user_func( array( $this, $_node['id'] .'_node_'. $attr ), $node, $args ); 
            endforeach;
        endif;
                
        $node = $_node; 
    }
    
    /**
     * Récupération de greffons depuis une liste de termes lié à une taxonomie
     * @param $args array
     * @see wp-includes/taxonomy.php - get_terms( $args );
     */
    final public function terms( $args = array() )
    {
        $terms = get_terms( $args );

        array_walk(            
            $terms,
            array( $this, 'parseTerm' ),
            $args
        );
        return $terms;
    }
    
    /**
     * Traitement des attributs d'un greffon de terme lié à une taxonomie  
     */
    final public function parseTerm( &$term, $key, $args = array() )
    {
        $_term = array();
                
        $_term['id']         = $term->term_id; 
        $_term['parent']     = $term->parent; 
        
        if( $matches = preg_grep( '/^term_node_(.*)/', get_class_methods( $this ) ) ) :
            foreach( $matches as $method ) :
                $attr = preg_replace( '/^term_node_/', '', $method );                       
                $_term[$attr] = call_user_func( array( $this, 'term_node_'. $attr ), $term, $args ); 
            endforeach;
        endif;
                
        $term =  $_term; 
    }
    
    /**
     * Attribut "parent" d'un greffon de terme lié à une taxonomie
     */
    public function term_node_parent( $term, $args = array() )
    {
        return ! $term->parent ? '' : ( ( isset( $args['child_of'] ) && ( $args['child_of'] == $term->parent ) ) ? '' : $term->parent );
    }
    
    /**
     * Attribut "has_children" d'un greffon de term lié à une taxonomie
     */
    public function term_node_has_children( $term, $args = array())
    {
        return \get_term_children( $term->term_id, $term->taxonomy ) ? true : false;
    }
    
    /**
     * (Exemple de personnalisation) Attribut "content" d'un greffon de terme lié à une taxonomie
     */
    public function term_node_content( $term, $args = array() )
    {
        return $term->name;
    }
}