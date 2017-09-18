<?php
namespace tiFy\Lib\Walkers;

abstract class Base
{
    /**
     * Liste de éléments
     */
    protected $Items        = array();

    /**
     * Liste des attributs par défaut d'un élément
     */
    protected $ItemDefaults = array(
        'class'         => '',
        'parent'        => '',
        'content'       => ''
    );

    /**
     * Element courant
     * @var mixed
     */
    protected $Current      = null;

    /**
     * Niveau de départ de l'intentation
     */
    protected $StartIndent  = "";

    /**
     * Attributs de configuration
     */
    protected $Attrs        = [];

    /**
     * CONTROLEURS
     */
    /**
     * Définition et traitement des éléments
     */
    final public function setItems($items = array())
    {
        foreach ($items as $item) :
            $this->Items[$item['id']] = \wp_parse_args($item, $this->ItemDefaults);
        endforeach;

        return $this->Items;
    }
    
    /**
     * Récupération d'un élément de menu
     */
    final public function getItem($id)
    {
        if (isset($this->Items[$id]))
            return $this->Items[$id];
    }
    
    /**
     * Récupération d'attribut d'un élément de menu
     */
    final public function getItemAttr($id, $attr = 'id', $defaults = '')
    {
        if (! $attrs = $this->getItem($id))
            return $defaults;
        
        if (isset($attrs[$attr])) :
            return $attrs[$attr];
        else :
            return $defaults;
        endif;
    }

    /**
     * Récupération de la classe HTML d'un élément de menu
     */
    public function getItemClass($item = null, $depth = 0, $parent = '')
    {
        // Bypass
        if(!$item)
            return '';

        $classes = [];
        $classes[] = 'tiFyWalker-contentItem';
        $classes[] = "tiFyWalker-contentItem--depth{$depth}";
        if(! empty($item['class'])) :
            $classes[] = $item['class'];
        endif;

        return implode(' ', $classes);
    }
    
    /**
     * Récupération de l'indentation
     */
    final public function getIndent($depth = 0)
    {
       return $this->StartIndent . str_repeat("\t", $depth);
    }

    /**
     *
     */
    public static function output($items = null, $attrs = [])
    {
        $instance = new static;
        $items = $items ? $instance->setItems($items) : $instance->Items;
        $instance->Attrs = $attrs;

        return $instance->walk($items, 0, '');
    }

    /**
     * Itérateur d'affichage
     * 
     * @param array $items
     * @param int $depth
     * @param string $parent
     * @
     *
     * @return string
     */
    public function walk($items = [], $depth = 0, $parent = '')
    {
        $output = "";
       
        // Contenus des onglets
        $opened = false;
        foreach ($items as $item) :
            if ($parent !== $item['parent'])
                continue;

            if (! $opened) :
                $output .= $this->start_content_items($item, $depth, $parent);
                $opened = true;
            endif;
            
            $output .= $this->start_content_item($item, $depth, $parent);
            $output .= $this->content_item($item, $depth, $parent);
            $output .= $this->walk($items, ($depth + 1), $item['id']);
            $output .= $this->end_content_item($item, $depth, $parent);
            
            $prevDepth = $depth;
        endforeach;

        if($opened) :
            $output .= $this->end_content_items(null, $depth, $parent);
        endif;
        
        return $output;
    }
    
    /**
     * Ouverture d'une liste de contenu d'éléments
     */
    final protected function start_content_items($item = null, $depth = 0, $parent = '')
    {
        return is_callable($item && array($this, 'start_content_items_'. $item['id'])) ?
            call_user_func(array($this, 'start_content_items_'. $item['id']), $item, $depth, $parent) :
            call_user_func(array($this, 'default_start_content_items'), $item, $depth, $parent);
    }
    
    /**
     * Fermeture d'une liste de contenu d'éléments
     */
    final protected function end_content_items($item = null, $depth = 0, $parent = '')
    {
        return is_callable($item && array( $this, 'end_content_items_'. $item['id'])) ?
            call_user_func(array($this, 'end_content_items_'. $item['id']), $item, $depth, $parent) :
            call_user_func(array($this, 'default_end_content_items'), $item, $depth, $parent);
    }
    
    /**
     * Ouverture d'un contenu d'élement
     */
    final protected function start_content_item($item, $depth = 0, $parent = '')
    {
        return is_callable(array( $this, 'start_content_item_'. $item['id'])) ?
            call_user_func(array( $this, 'start_content_item_'. $item['id']), $item, $depth, $parent) :
            call_user_func(array( $this, 'default_start_content_item'), $item, $depth, $parent);
    }
    
    /**
     * Fermeture d'un contenu d'élement
     */
    final protected function end_content_item($item, $depth = 0, $parent = '')
    {
        return is_callable(array($this, 'end_content_item_'. $item['id'])) ?
            call_user_func(array($this, 'end_content_item_'. $item['id']), $item, $depth, $parent) :
            call_user_func(array($this, 'default_end_content_item'), $item, $depth, $parent);
    }
    
    /**
     * Rendu d'un contenu d'élément
     */
    final protected function content_item($item, $depth, $parent)
    {
        return is_callable(array($this, 'content_item'. $item['id'])) ?
            call_user_func(array($this, 'content_item'. $item['id']), $item, $depth, $parent) :
            call_user_func(array($this, 'default_content_item'), $item, $depth, $parent);
    }    
    
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "<div class=\"tiFyWalker-contentItems tiFyWalker-contentItems--depth{$depth}\">\n";
    }
    
    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "</div>\n";
    }
    
    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item($item, $depth = 0, $parent = '')
    {          
        return $this->getIndent($depth) . "<div class=\"" . $this->getItemClass($item, $depth, $parent) . "\" id=\"tiFyWalker-contentItem--{$item['id']}\">\n";
    }
    
    /**
     * Fermeture par défaut d'un contenu d'élement
     */
    public function default_end_content_item($item, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "</div>\n";
    }
    
    /**
     * Rendu par défaut d'un contenu d'élément
     */
    public function default_content_item($item, $depth = 0, $parent = '')
    {
        return ! empty($item['content']) ? $item['content'] : '';
    }
}