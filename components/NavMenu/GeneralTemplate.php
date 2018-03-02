<?php
namespace tiFy\Components\NavMenu;

class GeneralTemplate
{
    /**
     * Menu de navigation par catégorie
     * Peut être utilisé comme menu de remplacement (fallback_cb) pour un emplacement de menu non affecté.
     * @see wp_list_categories
     */
    public static function categoryNavMenu( $args = array() )
    {
    	$defaults = array(
    	    'child_of'             => 0,
    	    'current_category'     => 0,
    	    'depth'                => 0,
    	    'echo'                 => true,
    	    'exclude'              => null,
    	    'exclude_tree'         => null,
    	    'feed'                 => null,
    	    'feed_image'           => null,
    	    'feed_type'            => null,
    	    'hide_empty'           => 1,
    	    'hide_title_if_empty'  => false,
    	    'hierarchical'         => true,
    	    'order'                => 'ASC',
    	    'order_by'             => 'ID',
    	    'separator'            => '',
    	    'show_count'           => 0,
    	    'show_option_all'      => '',
    	    'show_option_none'     => '',
    	    'taxonomy'             => 'category',
    	    'title_li'             => '',
            'use_desc_for_title'   => 0,
    
    		'menu_id'      => '',
    		'menu_class'   => 'menu',
    		'container'    => 'div',		
    		'link_before'  => '',
    		'link_after'   => '',
    		'before'       => '<ul>',
    		'after'        => '</ul>',
    		'item_spacing' => 'discard',
    		'walker'       => '',
    	);
    	$args = wp_parse_args( $args, $defaults );
    	
    	if ( ! in_array( $args['item_spacing'], array( 'preserve', 'discard' ) ) ) :
    		$args['item_spacing'] = $defaults['item_spacing'];
    	endif;
    
    	if ( 'preserve' === $args['item_spacing'] ) :
    		$t = "\t";
    		$n = "\n";
    	else :
    		$t = '';
    		$n = '';
    	endif;
    	$menu = '';
    
    	$list_args = array_diff_key( $args, array_flip( array( 'menu_id', 'menu_class', 'container', 'link_before', 'link_after', 'before', 'after', 'item_spacing', 'walker' ) ) );
    	$list_args['echo'] = false;
    	$list_args['title_li'] = '';
    	$menu .= wp_list_categories( $list_args );
    
    	$container = sanitize_text_field( $args['container'] );
    
    	if ( empty( $container ) ) :
    		$container = 'div';
    	endif;
    
        if ( $menu ) :
    		if ( isset( $args['fallback_cb'] ) &&
    			'tify_nav_menu_category' === $args['fallback_cb'] &&
    			'ul' !== $container ) :
    			$args['before'] = "<ul";
                if ( ! empty( $args['menu_id'] ) ) :
            		$args['before'] .= ' id="' . esc_attr( $args['menu_id'] ) . '"';
            	endif;
            
            	if ( ! empty( $args['menu_class'] ) ) :
            		$args['before'] .= ' class="' . esc_attr( $args['menu_class'] ) . '"';
            	endif;
    						
    			$args['before'] .= ">{$n}";
    			
    			
    			$args['after'] = '</ul>';
    		endif;
    
    		$menu = $args['before'] . $menu . $args['after'];
    	endif;
    	
    	$attrs = '';
    	if ( ! empty( $args['container_id'] ) ) :
    		$attrs .= ' id="' . esc_attr( $args['container_id'] ) . '"';
    	endif;
    
    	if ( ! empty( $args['container_class'] ) ) :
    		$attrs .= ' class="' . esc_attr( $args['container_class'] ) . '"';
    	endif;
    
    	$menu = "<{$container}{$attrs}>" . $menu . "</{$container}>{$n}";
    
    	if ( $args['echo'] )
    		echo $menu;
    
    	return $menu;  
    }
}