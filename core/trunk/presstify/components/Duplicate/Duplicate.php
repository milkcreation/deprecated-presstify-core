<?php
namespace tiFy\Components\Duplicate;

final class Duplicate extends \tiFy\App\Component
{
    /* = ARGUMENTS = */
    // Type de post
    private static $PostType        = array();
    
    // Taxonomy
    private static $Taxonomy        = array();

    /* = CONSTRUCTEUR = */
    public function __construct()
    {
        parent::__construct();
        
        if( $post_type = self::tFyAppConfig( 'post_type' ) ) :        
            foreach( $post_type as $type => $attrs ) :
                self::$PostType[$type] = $this->parseAttrs( $attrs );
            endforeach;
        endif;
        
        if( $taxonomy = self::tFyAppConfig( 'taxonomy' ) ) :      
            foreach( $taxonomy as $type => $attrs ) :
                self::$Taxonomy[$pt] = $this->parseAttrs( $attrs );
            endforeach;
        endif;

        // Chargement des contrôleurs
        new PostType\PostType;
        //new Taxonomy\Taxonomy; 
    }

    /* = CONTROLEURS = */
    /** == Traitement des arguments de duplication == **/
    private function parseAttrs( $attrs = array() )
    {
        $attrs = wp_parse_args(
            $attrs,
            array(
                'row_actions'   => true,
                'blog'          => false,
                'meta'          => false
            )
        );
        
        if( empty( $attrs['blog'] ) ) :
            $attrs['blog'] = array( get_current_blog_id() );
        else :
            $attrs['blog'] = (array) $attrs['blog'];
            // Force le type int des IDs
            $attrs['blog'] = array_map( 'absint', $attrs['blog'] );
            // Dédoublonnage des valeurs
            $attrs['blog'] = array_unique( $attrs['blog'] );
            // Suppression des données vide
            $attrs['blog'] = array_filter( $attrs['blog'] );
        endif;        
        
        return $attrs;
    }
    
    /** == == **/
    final public static function getPostType( $type = null )
    {
        if( ! $type )
            return self::$PostType;
        if( isset( self::$PostType[$type] ) )
            return self::$PostType[$type];            
    }
    
    /** == == **/
    final public static function getTaxonomy( $type = null )
    {
        if( ! $type )
            return self::$Taxonomy;
        if( isset( self::$Taxonomy[$type] ) )
            return self::$Taxonomy[$type]; 
    }
}