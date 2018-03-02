<?php

namespace tiFy\Lib\Walkers;

abstract class Base
{
    /**
     * Liste de éléments
     * @var array
     */
    protected $Items = [];

    /**
     * Liste des attributs par défaut d'un élément
     * @var array
     */
    protected $ItemDefaults = [
        'class'   => '',
        'parent'  => '',
        'content' => '',
    ];

    /**
     * Element courant
     * @var mixed
     */
    protected $Current = null;

    /**
     * Niveau de départ de l'indentation
     * @var string Chaine de caractére d'occurence "\t"
     */
    protected $Pad = "";

    /**
     * Attributs de configuration
     * @var array
     */
    protected $Attrs = [];

    /**
     * Ordonnancement
     * @var bool|string (append|prepend)
     */
    protected $Sort = 'append';

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
    final public function setItemList($items = [])
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

        if (($key !== false) && isset($this->Items[$key])) :
            return $this->Items[$key];
        endif;
    }

    /**
     * Récupération d'attribut d'un élément de menu
     *
     * @param string $id Identifiant de qualification de l'élément
     * @param string $name Identifiant de qualification de l'attribut à récupéré
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    final public function getItemAttr($id, $name = 'id', $default = '')
    {
        if (!$attrs = $this->getItem($id)) :
            return $default;
        endif;

        if (isset($attrs[$name])) :
            return $attrs[$name];
        else :
            return $default;
        endif;
    }

    /**
     * Définition de l'élément courant
     *
     * @param string $id Identifiant de qualification de l'élément courant à définir
     *
     * @return string
     */
    final public function setItemCurrent($id)
    {
        $this->Current = $id;
    }

    /**
     * Vérification si un élément est l'élément courant
     *
     * @param string $id Identifiant de qualification de l'élément à vérifier
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
     * @param string $id Identifiant de qualification de l'élément à vérifier
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
     * @param string $id Identifiant de qualification de l'élément à vérifier
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
     * @param int $depth Niveau de profondeur de l'indentation
     *
     * @return string
     */
    final public function getIndent($depth = 0)
    {
        return $this->Pad . str_repeat("\t", $depth);
    }

    /**
     * Récupération de la liste des attributs de configuration
     *
     * @return array
     */
    public function getAttrList()
    {
        return $this->Attrs;
    }

    /**
     * Vérification d'existance d'un attribut de configuration
     *
     * @param string $name Identifiant de qualification de l'attribut
     *
     * @return bool
     */
    public function issetAttr($name)
    {
        return isset($this->Attrs[$name]);
    }

    /**
     * Récupération d'un attribut de configuration
     *
     * @param string $name Identifiant de qualification de l'attribut
     * @param mixed $default Valeur de retour par défaut
     *
     * @return mixed
     */
    public function getAttr($name, $default = '')
    {
        if (!$this->issetAttr($name)) :
            return $default;
        endif;

        return $this->Attrs[$name];
    }

    /**
     * Ouverture d'une liste de contenu d'éléments
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    final protected function start_content_items($item = null, $depth = 0, $parent = '')
    {
        return is_callable($item && [$this, 'start_content_items_' . $item['id']]) ?
            call_user_func([$this, 'start_content_items_' . $item['id']], $item, $depth, $parent) :
            call_user_func([$this, 'default_start_content_items'], $item, $depth, $parent);
    }

    /**
     * Fermeture d'une liste de contenu d'éléments
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    final protected function end_content_items($item = null, $depth = 0, $parent = '')
    {
        return is_callable($item && [$this, 'end_content_items_' . $item['id']]) ?
            call_user_func([$this, 'end_content_items_' . $item['id']], $item, $depth, $parent) :
            call_user_func([$this, 'default_end_content_items'], $item, $depth, $parent);
    }

    /**
     * Ouverture d'un contenu d'élement
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    final protected function start_content_item($item = null, $depth = 0, $parent = '')
    {
        return is_callable([$this, 'start_content_item_' . $item['id']]) ?
            call_user_func([$this, 'start_content_item_' . $item['id']], $item, $depth, $parent) :
            call_user_func([$this, 'default_start_content_item'], $item, $depth, $parent);
    }

    /**
     * Fermeture d'un contenu d'élement
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    final protected function end_content_item($item = null, $depth = 0, $parent = '')
    {
        return is_callable([$this, 'end_content_item_' . $item['id']]) ?
            call_user_func([$this, 'end_content_item_' . $item['id']], $item, $depth, $parent) :
            call_user_func([$this, 'default_end_content_item'], $item, $depth, $parent);
    }

    /**
     * Rendu d'un contenu d'élément
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    final protected function content_item($item = null, $depth = 0, $parent = '')
    {
        return is_callable([$this, 'content_item' . $item['id']]) ?
            call_user_func([$this, 'content_item' . $item['id']], $item, $depth, $parent) :
            call_user_func([$this, 'default_content_item'], $item, $depth, $parent);
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
     * @param $items Liste des éléments à traiter
     * @param $depth Niveau de profondeur courant
     * @param $parent Identifiant de qualification de l'élément parent courant
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

            if (!$opened) :
                $output .= $this->start_content_items($item, $depth, $parent);
                $opened = true;
            endif;

            $output .= $this->start_content_item($item, $depth, $parent);
            $output .= $this->content_item($item, $depth, $parent);
            $output .= $this->walk($items, ($depth + 1), $item['id']);
            $output .= $this->end_content_item($item, $depth, $parent);

            $prevDepth = $depth;
        endforeach;

        if ($opened) :
            $output .= $this->end_content_items(null, $depth, $parent);
        endif;

        return $output;
    }

    /**
     * Ordonnancement
     *
     * @param $items Liste des éléments à traiter
     * @param $depth Niveau de profondeur courant
     * @param $parent Identifiant de qualification de l'élément parent courant
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
        $max = max($positions);
        $min = ($positions);
        $count = count($positions);
        $i = 1;
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
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function getItemClass($item = null, $depth = 0, $parent = '')
    {
        // Bypass
        if (!$item) :
            return '';
        endif;

        $classes = [];
        $classes[] = 'tiFyWalker-contentItem';
        $classes[] = "tiFyWalker-contentItem--depth{$depth}";
        if (!empty($item['class'])) :
            $classes[] = $item['class'];
        endif;

        return implode(' ', $classes);
    }

    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_start_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "<div class=\"tiFyWalker-contentItems tiFyWalker-contentItems--depth{$depth}\">\n";
    }

    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_end_content_items($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "</div>\n";
    }

    /**
     * Ouverture par défaut d'un contenu d'élement
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_start_content_item($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "<div class=\"" . $this->getItemClass($item, $depth,
                $parent) . "\" id=\"tiFyWalker-contentItem--{$item['id']}\">\n";
    }

    /**
     * Fermeture par défaut d'un contenu d'élement
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_end_content_item($item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent($depth) . "</div>\n";
    }

    /**
     * Rendu par défaut d'un contenu d'élément
     *
     * @param array $item Attribut de configuration de l'élément
     * @param int $depth Niveau de profondeur courant
     * @param string $parent Identifiant de qualification de l'élément parent courant
     *
     * @return string
     */
    public function default_content_item($item = null, $depth = 0, $parent = '')
    {
        return !empty($item['content']) ? $item['content'] : '';
    }
}