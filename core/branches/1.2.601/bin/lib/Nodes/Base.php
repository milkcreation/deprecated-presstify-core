<?php
namespace tiFy\Lib\Nodes;

abstract class Base
{
    /**
     * Liste des méthodes de surchage des greffons
     * @var string[]
     */
    private $Methods     = [];

    /**
     * Définition des méthodes de surchage des greffons par ordre d'exectution
     * @var string[]
     */
    public $MethodsMap = [];

    /**
     * CONTROLEURS
     */
    /**
     * Définition de la liste des méthodes de surchage des greffons
     *
     * @param string $type custom|term|post
     *
     * @return array
     */
    final protected function setMethods($type)
    {

        if (!$matches = preg_grep("#^{$type}_node_(.*)#", get_class_methods($this))) :
            return;
        else :
            $methods = [];
            foreach($matches as $match) :
                $methods[] = preg_replace("#^{$type}_node_#", '', $match);
            endforeach;
        endif;

        // Liste des méthodes triées
        if ($this->MethodsMap) :
            $order = array_flip($this->MethodsMap);
            $end = count($order); $ordered_methods = [];
            foreach($methods as $k => $v) :
                if (isset($order[$v])) :
                    $ordered_methods[$order[$v]] = $v;
                else :
                    $ordered_methods[$end++] = $v;
                endif;
            endforeach;
            ksort($ordered_methods);
            $this->Methods = $ordered_methods;

        // Liste des méthodes brutes
        else :
            $this->Methods = $methods;
        endif;
    }

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
            if (!isset($node['id'])) :
                continue;
            endif;
            if (!in_array($node['id'], $ids)) :
                array_push($ids, $node['id']);
            endif;
        endforeach;

        $this->setMethods('custom');

        array_walk($nodes, [$this, 'parseCustomNode'], $extras);

        return $nodes;
    }

    /**
     * Traitement des attributs d'un greffon personnalisé
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param int $key Clé d'index du greffon
     * @param array $extras Liste des arguments globaux complémentaires
     *
     * @return array
     */
    final public function parseCustomNode(&$node, $key, $extras = [])
    {
        $node['id'] = isset($node['id']) ? esc_attr($node['id']) : uniqid();
        $node['parent'] = isset($node['parent']) ? esc_attr($node['parent']) : '';

        foreach($this->Methods as $method) :
            $node[$method] = call_user_func_array([$this, 'custom_node_'. $method], [&$node, $extras]);
        endforeach;
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

        $this->setMethods('term');

        array_walk($terms, [$this, 'parseTermNode'], compact('query_args', 'extras'));

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
    final public function parseTermNode(&$term, $key, $attrs = [])
    {
        $node = [];
        $node['id']        = $term->term_id;
        $node['parent']    = $term->parent;
        $node['current']   = (isset($attrs['extras']['selected']) && ($term->term_id == $attrs['extras']['selected'])) ? 1 : 0;
        $node['is_ancestor']  = (isset($attrs['extras']['selected']) && \term_is_ancestor_of($term->term_id, (int)$attrs['extras']['selected'], $term->taxonomy)) ? 1 : 0;
        $node['has_children'] = \get_term_children($term->term_id, $term->taxonomy) ? 1 : 0;

        foreach($this->Methods as $method) :
            $node[$method] = call_user_func_array([$this, 'term_node_'. $method], [&$node, $term, $attrs['query_args'], $attrs['extras']]);
        endforeach;

        $term = $node;
    }

    /**
     * SURCHAGE
     */
    /**
     * Attribut "parent" du greffon de terme lié à une taxonomie
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function custom_node_parent(&$node, $extras = [])
    {
        return !$node['parent'] ? '' : $node['parent'];
    }

    /**
     * Attribut "parent" du greffon de terme lié à une taxonomie
     *
     * @param array $node Liste des attributs de configuration du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Liste des arguments de configuration globaux
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
     * @param array $node Liste des attributs de configuration du greffon
     * @param obj $term Attributs du terme courant
     * @param array $query_args Argument de requête de récupération des termes de taxonomie
     * @param array $extras Liste des arguments de configuration globaux
     *
     * @return string
     */
    public function term_node_content(&$node, $term, $query_args = [], $extras = [])
    {
        return $term->name;
    }
}