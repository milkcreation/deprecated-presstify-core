<?php
namespace tiFy\Lib\Walkers;

abstract class Base
{
    /**
     * Liste de éléments
     * @var array
     */
    protected $Items        = [];

    /**
     * Liste des attributs par défaut d'un élément
     * @var array
     */
    protected $ItemDefaults = [
        'class'         => '',
        'parent'        => '',
        'content'       => ''
    ];

    /**
     * Element courant
     * @var mixed
     */
    protected $Current      = null;

    /**
     * Niveau de départ de l'indentation
     */
    protected $StartIndent  = "";

    /**
     * Attributs de configuration
     */
    protected $Attrs        = [];

    /**
     * Ordonnancement
     * @var bool|string (append|prepend)
     */
    protected $Sort         = 'append';

    /**
     * CONTROLEURS
     */
    /**
     * Définition et traitement des éléments
     *
     * @param array[] $items Liste des attributs de configuration des éléments
     *
     * @return void|array
     */
    final public function setItemList($items = array())
    {
        foreach ($items as $item) :
            $this->Items[] = \wp_parse_args($item, $this->ItemDefaults);
        endforeach;

        return $this->Items;
    }

    /**
     * Récupération de la liste des éléments
     *
     * @return array
     */
    final public function getItemList()
    {
        return $this->Items;
    }

    /**
     * Récupération d'un élément
     *
     * @param string $id Identifiant de qualification de l'élément
     *
     * @return array
     */
    final public function getItem($id)
    {
        $key = array_search($id, array_column($this->Items, 'id'));

        if (($key!== false) && isset($this->Items[$key])) :
            return $this->Items[$key];
        endif;
    }
    
    /**
     * Récupération d'attribut d'un élément de menu
     */
    final public function getItemAttr($id, $attr = 'id', $defaults = '')
    {
        if (! $attrs = $this->getItem($id)) :
            return $defaults;
        endif;
        
        if (isset($attrs[$attr])) :
            return $attrs[$attr];
        else :
            return $defaults;
        endif;
    }

    /**
     * Définition de l'élément courant
     *
     * @param string $id Identifiant de qualification de l'élément
     *
     * @return string
     */
    final public function setItemCurrent($id)
    {
        $this->Current = $id;
    }

    /**
     * Vérification si l'élément est courant
     *
     * @param string $id Identifiant de qualification de l'élément
     *
     * @return bool
     */
    final public function isItemCurrent($id)
    {
        if (!is_null($this->Current)) :
            return ($this->Current === $id);
        else :
            return $this->getItemAttr($id, 'current', false);
        endif;
    }

    /**
     * Vérification si l'élément a un parent
     *
     * @param string $id Identifiant de qualification de l'élément courant
     *
     * @return bool
     */
    final public function hasItemParent($id)
    {
        return $this->getItemAttr($id, 'parent', false) ? true : false;
    }

    /**
     * Vérification si l'élément a au moins un enfant
     *
     * @param string $id Identifiant de qualification de l'élément courant
     *
     * @return bool
     */
    final public function hasItemChild($id)
    {
        return array_search($id, array_column($this->Items, 'parent'));
    }
    
    /**
     * Récupération de l'indentation
     *
     * @return string
     */
    final public function getIndent($depth = 0)
    {
       return $this->StartIndent . str_repeat("\t", $depth);
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
     * SURCHARGE
     */
    /**
     * Affichage de la sortie
     *
     * @param array $items Liste de éléments
     *
     * @return string
     */
    public static function output($items = null, $attrs = [])
    {
        $instance = new static;
        $items = $items ? $instance->setItemList($items) : $instance->Items;
        $instance->Attrs = $attrs;

        return $instance->walk($items, 0, '');
    }

    /**
     * Itérateur d'affichage
     * 
     * @param array $items
     * @param int $depth
     * @param string $parent
     *
     * @return string
     */
    public function walk($items = [], $depth = 0, $parent = '')
    {
        $output = "";

        // Ordonnancement des éléments
        $sorted = $this->sort($items, $depth, $parent);

        // Contenus des onglets
        $opened = false;
        foreach ($sorted as $item) :
            if ($parent !== $item['parent']) :
                continue;
            endif;

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
     * Ordonnancement
     *
     * @param array $items
     * @param int $depth
     * @param string $parent
     *
     * @return array
     */
    public function sort($items = [], $depth = 0, $parent = '')
    {
        $positions = [];

        // Extraction des données de position des éléments courants
        foreach ($items as $k => $item) :
            if ($parent !== $item['parent']) :
                continue;
            endif;
            $positions[$k] = isset($item['position']) ? $item['position'] : null;
        endforeach;

        // Bypass - Aucun élément à traiter
        if (empty($positions)) :
            return [];
        endif;

        // Récupération des informations de position
        $max = max($positions); $min = ($positions); $count = count($positions); $i = 1;
        $sorted = [];

        //
        foreach ($positions as $k => $position) :
            if (is_null($position)) :
                switch ($this->Sort) :
                    default :
                    case 'append' :
                        $position = $max++;
                        break;
                    case 'prepend' :
                        $position = $min++ - $count;
                        break;
                endswitch;
            endif;

            if (isset($sorted[$position])) :
                switch ($this->Sort) :
                    default :
                    case 'append' :
                        $position = (float)$position . "." . $i++;
                        break;
                    case 'prepend' :
                        $position = (float)($position - 1) . "." . (99999 - ($count + $i++));
                        break;
                endswitch;
            endif;

            $sorted[$position] = $items[$k];
        endforeach;

        ksort($sorted);

        return $sorted;
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