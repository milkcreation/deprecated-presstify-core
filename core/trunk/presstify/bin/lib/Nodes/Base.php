<?php
namespace tiFy\Lib\Nodes;

abstract class Base
{
    /**
     * Liste des méthodes de surchage des greffons. Définie par le système
     */
    private $MethodsMap     = [];

    /**
     * Ordre d'éxecution des méthodes de surchage des greffons
     */
    public $MethodsMapOrder = [];

    /**
     * Définition de la liste des méthodes de surchage des greffons issue des termes d'un taxonomie
     *
     * @return array
     */
    final protected function setTermMethodMap()
    {
        if (! $matches = preg_grep( '/^term_node_(.*)/', get_class_methods($this))) :
            return;
        else :
            $methods = [];
            foreach($matches as $match) :
                $methods[] = preg_replace('/^term_node_/', '', $match);
            endforeach;
        endif;

        // Liste des méthodes triées
        if ($this->MethodsMapOrder) :
            $order = array_flip($this->MethodsMapOrder);
            $end = count($order); $ordered_methods = [];
            foreach($methods as $k => $v) :
                if (isset($order[$v])) :
                    $ordered_methods[$order[$v]] = $v;
                else :
                    $ordered_methods[$end++] = $v;
                endif;
            endforeach;
            ksort($ordered_methods);
            $this->MethodsMap = $ordered_methods;
        // Liste des méthodes brutes
        else :
            $this->MethodsMap = $methods;
        endif;
    }

    /**
     * Récupération de greffons personnalisés
     */
    final public function customs($nodes = array(), $args = array())
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
    final public function parseCustom(&$node, $key, $args)
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
                
        $node = \wp_parse_args($_node, $node);
    }
    
    /**
     * Récupération de greffons depuis une liste de termes lié à une taxonomie
     *
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @see get_terms()
     * @param array $extras Données complémentaires passées en argument (ex: selected)
     *
     * @return array
     */
    final public function terms($query_args = [], $extras = [])
    {
        $terms = get_terms($query_args);

        $this->setTermMethodMap();

        array_walk(
            $terms,
            [$this, 'parseTerm'],
            compact('query_args', 'extras')
        );
        return $terms;
    }
    
    /**
     * Traitement des attributs d'un greffon de terme lié à une taxonomie
     *
     * @param obj $term
     * @param int $key
     * @param array $attrs {
     *      Paramètres complémentaires
     *
     *      @type array $query_args Argument de requête de récupération des termes de taxonomie
     *      @type array $extras Données complémentaires (ex: selected)
     * }
     *
     * @return void
     */
    final public function parseTerm(&$term, $key, $attrs = [])
    {
        $node = [];
        $node['id']        = $term->term_id;
        $node['parent']    = $term->parent;
        $node['current']   = (isset($attrs['extras']['selected']) && ($term->term_id == $attrs['extras']['selected'])) ? 1 : 0;
        $node['ancestor']  = (isset($attrs['extras']['selected']) && \term_is_ancestor_of($term->term_id, (int)$attrs['extras']['selected'], $term->taxonomy)) ? 1 : 0;

        foreach($this->MethodsMap as $attr) :
            $node[$attr] = call_user_func_array([$this, 'term_node_'. $attr], [&$node, $term, $attrs['query_args'], $attrs['extras']]);
        endforeach;

        $term =  $node;
    }
    
    /**
     * Attribut "parent" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Données complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_parent(&$node, $term, $query_args = [], $extras = [])
    {
        return !$term->parent ? '' : ((isset($query_args['child_of']) && ($query_args['child_of'] == $term->parent)) ? '' : $term->parent);
    }
    
    /**
     * Attribut "has_children" du greffon de term lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Données complémentaires (ex: selected)
     *
     * @return bool
     */
    public function term_node_has_children(&$node, $term, $query_args = [], $extras = [])
    {
        return \get_term_children($term->term_id, $term->taxonomy) ? true : false;
    }
    
    /**
     * Attribut "content" du greffon de terme lié à une taxonomie
     *
     * @param array $node Attributs du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Données complémentaires (ex: selected)
     *
     * @return string
     */
    public function term_node_content(&$node, $term, $query_args = [], $extras = [])
    {
        return $term->name;
    }
}