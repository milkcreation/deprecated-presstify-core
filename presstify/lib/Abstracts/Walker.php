<?php
namespace tiFy\Abstracts;

abstract class Walker
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
     * Iterateur d'affichage
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
       
        // Contenus des onglets
        $opened = false;                    
        foreach ( $items as $item ) : 
            if ( $parent !== $item['parent'] )
                continue;
            
            if ( ! $opened ) : 
                $output .= $this->start_content_items( null, $depth, $parent ); 
                $opened = true;
            endif;
            
            $output .= $this->start_content_item( $item, $depth, $parent );
            $output .= $this->walk( $items, ($depth + 1), $item['id'] );
            $output .= $this->content_item( $item, $depth, $parent );
            $output .= $this->end_content_item( $item, $depth, $parent );
            
            $prevDepth = $depth;            
        endforeach;
        if( $opened ) :      
            $output .= $this->end_content_items( null, $depth, $parent );
        endif;
        
        return $output;         
    }
    
    /**
     * Ouverture d'une liste de contenu d'éléments
     */
    final public function start_content_items( $item = null, $depth = 0, $parent = '' )
    {
        return is_callable( $item && array( $this, 'start_content_items_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'start_content_items_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_start_content_items' ), $item, $depth, $parent );
    }
    
    /**
     * Fermeture d'une liste de contenu d'éléments
     */
    final public function end_content_items( $item = null, $depth = 0, $parent = '' )
    {
        return is_callable( $item && array( $this, 'end_content_items_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'end_content_items_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_end_content_items' ), $item, $depth, $parent );
    }
    
    /**
     * Ouverture d'un contenu d'élement
     */
    final public function start_content_item( $item, $depth = 0, $parent = '' )
    {
        return is_callable( array( $this, 'start_content_item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'start_content_item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_start_content_item' ), $item, $depth, $parent );
    }
    
    /**
     * Fermeture d'un contenu d'élement
     */
    final public function end_content_item( $item, $depth = 0, $parent = '' )
    {
        return is_callable( array( $this, 'end_content_item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'end_content_item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_end_content_item' ), $item, $depth, $parent );
    }
    
    /**
     * Rendu d'un contenu d'élément
     */
    final public function content_item( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'content_item'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'content_item'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_content_item' ), $item, $depth, $parent );
    }    
    
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items( $item = null, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."<div class=\"tiFyWalker-contentItems tiFyWalker-contentItems--depth{$depth}\">\n";
    }
    
    /**
     * Fermeture par défaut d'une liste de contenus d'éléments
     */
    public function default_end_content_items( $item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent( $depth ) ."</div>\n";
    }
    
    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item( $item, $depth = 0, $parent = '' )
    {          
        return $this->getIndent( $depth ) ."<div class=\"tiFyWalker-contentItem tiFyWalker-contentItem--depth{$depth}\" id=\"tiFyWalker-contentItem--{$item['id']}\">\n";
    }
    
    /**
     * Fermeture par défaut d'un contenu d'élement
     */
    public function default_end_content_item( $item, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."</div>\n";
    }
    
    /**
     * Rendu par défaut d'un contenu d'élément
     */
    public function default_content_item( $item, $depth = 0, $parent = '' )
    {
        return ! empty( $item['content'] ) ? $item['content'] : '';
    }
}