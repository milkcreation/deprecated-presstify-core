<?php
namespace tiFy\Abstracts;

abstract class TreeMenuWalker
{
    /**
     * Liste de éléments
     */
    protected $Items        = array();
    
    /**
     * Niveau de départ de l'intentation
     */
    protected $StartIndent  = "";
    
    /**
     * CONTROLEURS
     */
    /**
     * Traitement des éléments
     */
    final public function parseItems( $items = array() )
    {
        foreach( $items as $item ) :
            $this->Items[$item['id']] = $item;
        endforeach;
    }
    
    /**
     * Récupération d'un élément de menu
     */
    final public function getItem( $id )
    {
        if( isset( $this->Items[$id] ) )
            return $this->Items[$id];
    }
    
    /**
     * Récupération d'attribut d'un élément de menu
     */
    final public function getItemAttr( $id, $attr = 'id', $defaults = '' )
    {
        if( ! $attrs = $this->getItem( $id ) )
            return $defaults;
        
        if( isset( $attrs[$attr] ) ) :
            return $attrs[$attr];
        else :
            return $defaults;
        endif;
    }
    
    /**
     * Récupération de l'indentation
     */
    final public function getIndent( $depth = 0 )
    {
       return $this->StartIndent . str_repeat( "\t", $depth ); 
    }
            
    /**
     * Iterateur d'affichage de menu
     * 
     * @param array $items
     * @param int $depth
     * @param string $parent
     * @return string
     */
    final public function walk( $items = array(), $depth = 0, $parent = '' )
    {         
        if( ! $this->Items )
            $this->parseItems( $items );
        $output = "";
        $prevDepth = 0;

        if ( ! $depth && ! $prevDepth ) 
            $output .= $this->open_menu();            
            
        foreach ( $items as $item ) : 
            if ( $parent !== $item['parent'] )
                continue;
            
            if ( $prevDepth < $depth ) 
                $output .= $this->open_submenu( $item, $depth, $parent );
         
           $output .= $this->open_item( $item, $depth, $parent ) . $this->item( $item, $depth, $parent );
     
           $prevDepth = $depth;
     
           $output .= $this->walk( $items, ($depth + 1), $item['id'] );
        endforeach;
         
        if ( ( $prevDepth == $depth ) && ( $prevDepth != 0 ) ) :
            $output .= $this->close_submenu( $item, $depth, $parent ) . $this->close_item( $item, $depth, $parent );
        elseif ( $prevDepth == $depth ) :
            $output .= $this->close_menu();
        else :
            $output .= $this->close_item( $item, $depth, $parent );
        endif;
         
        return $output;         
    }
    
    /**
     * Ouverture d'un élément
     */
    final public function open_item( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'open_item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'open_item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'open_item_default' ), $item, $depth, $parent );
    }
    
    /**
     * Fermeture d'un élément
     */
    final public function close_item( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'close_item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'close_item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'close_item_default' ), $item, $depth, $parent );
    }
    
    /**
     * Ouverture d'un sous-menu
     */
    final public function open_submenu( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'open_submenu_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'open_submenu_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'open_submenu_default' ), $item, $depth, $parent );
    }
    
    /**
     * Fermeture d'un sous-menu
     */
    final public function close_submenu( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'close_submenu_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'close_submenu_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'close_submenu_default' ), $item, $depth, $parent );
    }
    
    /**
     * Rendu d'un élément
     */
    final public function item( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'item_default' ), $item, $depth, $parent );
    }    
    
    /**
     * Ouverture du menu
     */
    public function open_menu()
    {
        return $this->getIndent( 0 ) ."<ul class=\"tiFyTreeMenu-items tiFyTreeMenu-items--open\">\n";
    }
    
    /**
     * Fermeture du menu
     */
    public function close_menu()
    {
        return $this->getIndent( 0 ) ."</ul>\n";
    }
    
    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default( $item, $depth = 0, $parent = '' )
    {        
        return $this->getIndent( $depth ) ."\t<li class=\"tiFyTreeMenu-item TreeMenu-item--{$depth}\">\n";
    }
    
    /**
     * Fermeture par défaut d'un élément
     */
    public function close_item_default( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."\t</li>\n";
    }
    
    /**
     * Ouverture par défaut d'un sous-menu
     */
    public function open_submenu_default( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."\t\t<ul class=\"tiFyTreeMenu-items TreeMenu-items--{$depth}\">\n";
    }
        
    /**
     * Fermeture par défaut d'un sous-menu
     */
    public function close_submenu_default( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."\t\t</ul>\n";
    }
    
    /**
     * Rendu par défaut d'un élément
     */
    public function item_default( $item, $depth = 0, $parent = '' )
    {
        return ! empty( $item['content'] ) ? $item['content'] : '';
    } 
}