<?php
namespace tiFy\Lib\Walkers;

abstract class Tabs extends Base
{            
    /**
     * Iterateur d'affichage
     * 
     * @param array $items
     * @param int $depth
     * @param string $parent
     *
     * @return string
     */
    public function walk($items = array(), $depth = 0, $parent = '')
    {
        $output = "";
        
        // Onglets de navigation
        $opened = false;          
        foreach ( $items as $item ) : 
            if ( $parent !== $item['parent'] )
                continue;
            
            if ( ! $opened ) :
                $output .= $this->start_nav_items( null, $depth, $parent ); 
                $opened = true;
            endif;
            
            $output .= $this->start_nav_item( $item, $depth, $parent );
            $output .= $this->nav_item( $item, $depth, $parent );            
            $output .= $this->end_nav_item( $item, $depth, $parent );
            
            $prevDepth = $depth;
        endforeach;
        if( $opened ) :
            $output .= $this->end_nav_items( null, $depth, $parent );
        endif;
        
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
     * Ouverture d'une liste d'éléments de navigation
     */
    final public function start_nav_items( $item = null, $depth = 0, $parent = '' )
    {
        return is_callable( $item && array( $this, 'start_nav_items_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'start_nav_items_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_start_nav_items' ), $item, $depth, $parent );
    }
    
    /**
     * Fermeture d'une liste d'éléments de navigation
     */
    final public function end_nav_items( $item = null, $depth = 0, $parent = '' )
    {
        return is_callable( $item && array( $this, 'end_nav_items_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'end_nav_items_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_end_nav_items' ), $item, $depth, $parent );
    }
    
    /**
     * Ouverture d'un élement de navigation
     */
    final public function start_nav_item( $item, $depth = 0, $parent = '' )
    {
        return is_callable( array( $this, 'start_nav_item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'start_nav_item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_start_nav_item' ), $item, $depth, $parent );
    }
    
    /**
     * Fermeture d'un élement de navigation
     */
    final public function end_nav_item( $item, $depth = 0, $parent = '' )
    {
        return is_callable( array( $this, 'end_nav_item_'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'end_nav_item_'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_end_nav_item' ), $item, $depth, $parent );
    }
    
    /**
     * Contenu d'un élément de navigation
     */
    final public function nav_item( $item, $depth, $parent )
    {
        return is_callable( array( $this, 'nav_item'. $item['id'] ) ) ? 
            call_user_func( array( $this, 'nav_item'. $item['id'] ), $item, $depth, $parent ) :
            call_user_func( array( $this, 'default_nav_item' ), $item, $depth, $parent );
    }    
    
    /**
     * Ouverture par défaut d'une liste d'éléments de navigation
     */
    public function default_start_nav_items( $item = null, $depth = 0, $parent = '' )
    {
        switch( $depth ) :
            case 0 :
                $class = 'nav nav-stacked';
                break;
            case 1 :
                $class = 'nav nav-tabs';
                break;
            case 2 :
                $class = 'nav nav-pills';
                break;
        endswitch;
        
        return $this->getIndent( $depth ) ."<ul class=\"{$class} tiFyTabs-navItems tiFyTabs-navItems--depth{$depth}\" role=\"tablist\">\n";
    }
    
    /**
     * Fermeture par défaut d'une liste d'éléments de navigation
     */
    public function default_end_nav_items( $item = null, $depth = 0, $parent = '')
    {
        return $this->getIndent( $depth ) ."</ul>\n";
    }
    
    /**
     * Ouverture par défaut d'un élement de navigation
     */
    public function default_start_nav_item( $item, $depth = 0, $parent = '' )
    {        
        $output  = "";
        $output .= $this->getIndent( $depth ) ."\t<li class=\"tiFyTabs-navItem tiFyTabs-navItem--depth{$depth}\" role=\"presentation\">\n";
        $output .= $this->getIndent( $depth ) ."\t\t<a href=\"#{$item['id']}\" aria-controls=\"{$item['id']}\" role=\"tab\" data-toggle=\"tab\">\n";
        
        return $output;
    }
    
    /**
     * Fermeture par défaut d'un élement de navigation
     */
    public function default_end_nav_item( $item, $depth = 0, $parent = '' )
    {
        $output  = "";
        $output .= $this->getIndent( $depth ) ."\t\t</a>\n";
        $output .= $this->getIndent( $depth ) ."\t</li>\n";
        
        return $output;
    }
    
    /**
     * Contenu par défaut d'un élément de navigation
     */
    public function default_nav_item( $item, $depth = 0, $parent = '' )
    {
        return ! empty( $item['title'] ) ? $item['title'] : '';
    }
    
    /**
     * Ouverture par défaut d'une liste de contenus d'éléments
     */
    public function default_start_content_items( $item = null, $depth = 0, $parent = '' )
    {
        return $this->getIndent( $depth ) ."<div class=\"tab-content tiFyTabs-contentItems tiFyTabs-contentItems--depth{$depth}\">\n";
    }
        
    /**
     * Ouverture par défaut d'un contenu d'élement
     */
    public function default_start_content_item( $item, $depth = 0, $parent = '' )
    {          
        return $this->getIndent( $depth ) ."<div role=\"tabpanel\" class=\"tab-pane tiFyTabs-contentItem tiFyTabs-contentItem--depth{$depth}\" id=\"{$item['id']}\">\n";
    }
}