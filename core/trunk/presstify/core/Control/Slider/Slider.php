<?php
/**
 * @Overrideable
 */
namespace tiFy\Core\Control\Slider;

class Slider extends \tiFy\Core\Control\Factory
{
    /**
     * Identifiant de la classe
     */
    protected $ID = 'slider';
    
    /**
     * Instance courante
     */
    protected static $Instance;
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
               
        // Incrémentation de l'instance courante
        self::$Instance++;
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     */
    final public function init()
    {
        wp_register_style('tify_control-slider', self::tFyAppAssetsUrl('Slider.css', get_class()), array(), 170215 );
        wp_register_script('tify_control-slider', self::tFyAppAssetsUrl('Slider.js', get_class()), array( 'tify-slideshow' ), 170215, true);
    }
    
    /**
     * Mise en file des scripts
     */
    final public function enqueue_scripts()
    {
        wp_enqueue_style( 'tify_control-slider' );
        wp_enqueue_script( 'tify_control-slider' );
    }
       
    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     */
    final public static function display( $args = array(), $echo = true )
    {
        $defaults = array(
            'id'        => 'tiFyControl-slider'. self::$Instance,
            'class'     => '',
            'slides'    => array(),
            'options'   => array(
                'ratio'     => '16:9'
            )
        );
        $args = wp_parse_args( $args, $defaults );
                
        $output = static::output( $args );      
        
        if( $echo )
            echo $output;

        return $output;
    }   
    
    /**
     * Traitement des slides
     */
    protected static function parseSlides( $slides )
    {
        $slides = (array) $slides;
        $defaults = array(
            'src'       => '',
            'alt'       => '',
            'url'       => '',
            'title'     => '',
            'caption'   => ''
        );

        foreach( $slides as &$slide ) :
            if( is_string( $slide ) ) :
                $slide['url'] = $slide;
            endif;
            $slide = wp_parse_args( $slide, $defaults );
        endforeach;

        return $slides;
    }
    
    /**
     * Traitement des options
     */
    protected static function parseOptions( $options )
    {
        $options = (array) $options;
        $defaults = array(
            'ratio'         => '1:1',
            'arrow_nav'     => false,
            'tab_nav'       => false,
            'progressbar'   => false
        );
        $options = wp_parse_args( $options, $defaults );
        
        // Calcul du ratio
        if( !empty( $options['ratio'] ) ) :
            list( $w, $h ) = preg_split( '/:/', $options['ratio'] );
            $options['ratio'] = ceil( 100/$w * $h ) .'%';
        endif;
        
        return $options;
    }    
        
    /**
     * Sortie
     */
    public static function output( $args = array() )
    {
        extract( $args );
        
        $slides = static::parseSlides( $slides );
        $options = static::parseOptions( $options ); 
        
        $output  = "";
        $output .= "<div id=\"{$id}\" class=\"tiFyControl-slider". ( $class ? ' '.$class : '' )."\" data-tify_control=\"slider\"";
        foreach( $options as $k => $v ) :
            // @see mu-plugins/presstify/assets/lib/tify-slideshow.js
            if( ! in_array( $k, array( 'interval', 'pause', 'transition', 'speed', 'easing' , 'resize', 'bypage' ) ) )
                continue;
            $output .= " data-{$k}=\"{$v}\"";                
        endforeach;
        $output .= ">\n";
        
        // Preview
        $output .= "\t<div class=\"tiFyControl-sliderPreview viewer\">\n"; 
        
        $output .= "\t\t<ul class=\"tiFyControl-sliderPreviewItems roller\">\n";
        foreach( (array) $slides as $slide ) :
            $output .= "\t\t\t<li class=\"tiFyControl-sliderPreviewItem\">\n";            
            // Dimensionneur
            if( $options['ratio'] ) :
                $output .= "\t\t\t\t<span style=\"display:block;content:'';padding-top:{$options['ratio']};\"></span>\n";
            endif;            
            // Lien
            if( ! empty( $slide['url'] ) ) :  
                $output .= "\t\t\t\t<a href=\"{$slide['url']}\" class=\"tiFyControl-sliderPreviewItemLink\"></a>\n";
            endif;
            // Image
            $output .= "\t\t\t\t<figure class=\"tiFyControl-sliderPreviewItemImage\"";
            if( $options['ratio'] ) :
                $output .= " style=\"background-image:url({$slide['src']})\"></figure>\n";
            else :
                $output .= "><img src=\"{$slide['src']}\" alt=\"{$slide['alt']}\" ></figure>\n";
            endif;            
            // Cartouche
            if( ! empty( $slide['title'] ) || ! empty( $slide['caption'] ) ) :
                $output .= "\t\t\t\t<section class=\"tiFyControl-sliderPreviewItemCaption\">\n";
                // Titre
                if( ! empty( $slide['title'] ) ) :
                    $output .= "<h3 class=\"tiFyControl-sliderPreviewItemCaptionTitle\">{$slide['title']}</h3>";
                endif;
                // Légende
                if( ! empty( $slide['caption'] ) ) :
                    $output .= "<div class=\"tiFyControl-sliderPreviewItemCaptionDescription\">{$slide['caption']}</div>";
                endif;
                $output .= "\t\t\t\t</section>\n";
            endif;    
            $output .= "\t\t\t</li>\n";
        endforeach;            
        $output .= "\t\t</ul>\n";
        $output .= "\t</div>\n";
        
        // Navigation fléches suivant/précédent
        if( $options['arrow_nav'] ) :
            $output .= "\t<nav class=\"tiFyControl-sliderArrowNav\">\n";
            $output .= "\t\t<ul class=\"tiFyControl-sliderArrowNavItems\">\n";
            $output .= "\t\t\t<li class=\"tiFyControl-sliderArrowNavItem tiFyControl-sliderArrowNavItem--prev\">\n";
            $output .= "\t\t\t\t<a href=\"#\" class=\"tiFyControl-sliderArrowNavItemLink tiFyControl-sliderArrowNavItemLink--prev\" data-arrownav=\"prev\">&larr;</a>\n";
            $output .= "\t\t\t</li>\n";
            $output .= "\t\t\t<li class=\"tiFyControl-sliderArrowNavItem tiFyControl-sliderArrowNavItem--next\">\n";
            $output .= "\t\t\t\t<a href=\"#\" class=\"tiFyControl-sliderArrowNavItemLink tiFyControl-sliderArrowNavItemLink--next\" data-arrownav=\"next\">&rarr;</a>\n";
            $output .= "\t\t\t</li>\n"; 
            $output .= "\t\t</ul>\n";
            $output .= "\t</nav>\n";
        endif;

        // Navigation tabulation
        if( $options['tab_nav'] ) :
            reset( $slides );
            $output .= "\t<nav class=\"tiFyControl-sliderTabNav\">\n";
            $output .= "\t\t<ul class=\"tiFyControl-sliderTabNavItems\">\n";
            foreach( (array) $slides as $n => $slide ) : $i = $n+1;
                $output .= "\t\t\t<li class=\"tiFyControl-sliderTabNavItem\">\n";
                $output .= "\t\t\t\t<a href=\"#\" class=\"tiFyControl-sliderTabNavItemLink tiFyControl-sliderTabNavItemLink--{$i}\" data-tabnav=\"{$n}\">{$i}</a>\n";
                $output .= "\t\t\t</li>\n";
            endforeach;
            $output .= "\t\t</ul>\n";
            $output .= "\t</nav>\n";
        endif;
        
        // Barre de progression
        if( $options['progressbar'] ) :
            $output .= "\t<div class=\"tiFyControl-sliderProgressbar\"><span></span></div>\n";
        endif;
        
        $output .= "</div>\n";
        
        return $output;
    }
}