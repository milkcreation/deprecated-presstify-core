<?php
/**
 * @Overrideable
 */
namespace tiFy\Components\NavMenu; 

class Factory
{
    /**
     *  Entrées de menu
     */   
    protected static $Items;
    
    /**
     * CONTROLEURS
     */
    /**
     * Ajout d'une entrée de menu
     * 
     * @param array $attrs
     * @return array
     */
    final public static function add( $attrs = array() )
    {
        $defaults = array(
            'id'            => '',
            'parent'        => '',
            'title'         => '',
            'content'       => '',
            'capability'    => 'read',
            'cb'            => '', 
            'order'         => null
        );
        $attrs = wp_parse_args( $attrs, $defaults );
        
        return self::$Items[$attrs['id']] = $attrs;
    }
    
    /**
     * Traitement d'une entrée de menu
     * @todo
     * @param array $item
     */
    final protected static function parseItem( $item )
    {        
        if ( null === $order ) :
            self::$Items[] = $attrs;
        elseif ( isset( self::$Items[ "{$order}" ] ) ) :
            $order = $order + substr( base_convert( md5( $slug . $title ), 16, 10 ) , -5 ) * 0.00001;
            self::$Items[ "{$order}" ] = $attrs;
        else :
            self::$Items[ $order ] = $attrs;
        endif;        
    }
    
    /**
     * Affichage du menu
     * 
     * @param array $args
     * @param string $echo
     * @return string
     */
    final public static function display( $args = array(), $echo = true )
    {        
        $defaults = array(
            'title'     => '',
            'walker'    => ''
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args );
        
        if( $walker ) :
            $Walker = new $walker;
        else :
            $Walker = new Static;
        endif;
            
        $output = $Walker->output( self::$Items, 0 );
        
        if( $echo )
            echo $output;
        
        return $output;
    }
    
    /**
     * Iterateur d'affichage de menu
     * 
     * @param array $items
     * @param int $depth
     * @param string $parent
     * @return string
     */
    final protected function walk( $items, $depth, $parent = '' )
    {         
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
        return "<ul>\n";
    }
    
    /**
     * Fermeture du menu
     */
    public function close_menu()
    {
        return "</ul>\n";
    }
    
    /**
     * Ouverture par défaut d'un élément
     */
    public function open_item_default( $item, $depth, $parent )
    {
        return "<li>";
    }
    
    /**
     * Fermeture par défaut d'un élément
     */
    public function close_item_default( $item, $depth, $parent )
    {
        return "</li>\n";
    }
    
    /**
     * Ouverture par défaut d'un sous-menu
     */
    public function open_submenu_default( $item, $depth, $parent )
    {
        return "<ul>\n";
    }
        
    /**
     * Fermeture par défaut d'un sous-menu
     */
    public function close_submenu_default( $item, $depth, $parent )
    {
        return "</ul>\n";
    }
    
    /**
     * Rendu par défaut d'un élément
     */
    public function item_default( $item, $depth, $parent )
    {
        return $item['title'];
    } 
}