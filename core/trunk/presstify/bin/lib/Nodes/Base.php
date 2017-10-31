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
     * GREFFONS PERSONNALISES
     */
    /**
     * Récupération de greffons personnalisés
     *
     * @param array $nodes Liste des greffons
     * @param array $extras Liste des arguments globaux complémentaires
     *
     * @return array
     */
    final public function customs($nodes = [], $extras = [])
    {
        $ids = [];
        foreach ((array)$nodes as $node) :
            if (!isset($node['id']))
                continue;
            if (!in_array($node['id'], $ids)) :
                array_push($ids, $node['id']);
            endif;
        endforeach;

        if ($methods = preg_grep('/^(.*)_node/', get_class_methods($this))) :
            foreach ($methods as $method) :
                preg_match('/^(.*)_node/', $method, $matches);
                if (!isset($matches[1]) || in_array($matches[1], ['term', 'post']) || in_array($matches[1], $ids)) :
                    continue;
                endif;
                array_push($ids, $matches[1]);
                $nodes[] = ['id' => $matches[1]];
            endforeach;
        endif;

        array_walk($nodes, [$this, 'parseCustom'], $extras);

        return $nodes;
    }

    /**
     * Traitement des attributs d'un greffon personnalisé
     *
     * @param array $attrs Liste des attributs de configuration du greffon
     * @param int $key Clé d'index du greffon
     * @param array $extras Liste des arguments globaux complémentaires
     *
     * @return array
     */
    final public function parseCustom(&$attrs, $key, $args = [])
    {
        $_attrs = [];

        $_attrs['id'] = isset($attrs['id']) ? esc_attr($attrs['id']) : uniqid();
        $_attrs['parent'] = isset($attrs['parent']) ? esc_attr($attrs['parent']) : '';

        if ($matches = preg_grep('/^node_(.*)/', get_class_methods($this))) :
            foreach ($matches as $method) :
                $attr = preg_replace('/^node_/', '', $method);
                $_attrs[$attr] = call_user_func([$this, 'node_' . $attr], $attrs, $args);
            endforeach;
        endif;

        if ($matches = preg_grep('/^' . $_attrs['id'] . '_node_(.*)/', get_class_methods($this))) :
            foreach ($matches as $method) :
                $attr = preg_replace('/^' . $_attrs['id'] . '_node_/', '', $method);
                $_attrs[$attr] = call_user_func([$this, $_attrs['id'] . '_node_' . $attr], $attrs, $args);
            endforeach;
        endif;

        $attrs = \wp_parse_args($_attrs, $attrs);
    }

    /**
     * GREFFONS DE TERME DE TAXONOMY
     */
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

        array_walk($terms, [$this, 'parseTerm'], compact('query_args', 'extras'));

        return $terms;
    }

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
        $node['is_ancestor']  = (isset($attrs['extras']['selected']) && \term_is_ancestor_of($term->term_id, (int)$attrs['extras']['selected'], $term->taxonomy)) ? 1 : 0;
        $node['has_children'] = \get_term_children($term->term_id, $term->taxonomy) ? 1 : 0;

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