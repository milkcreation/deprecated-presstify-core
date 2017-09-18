<?php
namespace tiFy\Components\ArchiveFilters;

class ArchiveFilters extends \tiFy\Environment\Component
{
    /**
     * Liste des actions à déclencher
     */
    protected $tFyAppActions                = array(
        'wp_loaded',
        'wp_enqueue_scripts'
    );     
    
    /**
     * Liste des Filtres à déclencher
     */ 
    protected $CallFilters                = array(
        'posts_clauses'
    );
    
    /**
     * Ordres de priorité d'exécution des filtres
     */
    protected $CallFiltersPriorityMap    = array(
        'posts_clauses'    => 99
    );
    
    /**
     * Nombre d'arguments autorisé lors de l'appel des filtres
     */
    protected $CallFiltersArgsMap        = array(
        'posts_clauses' => 2
    );
    
    /**
     * Environnements permis
     */ 
    private static $AllowedObj                 =  array(
        'post_type', 'taxonomy'    
    );
    
    /**
     * Liste des filtres déclarés
     */
    private static $Filters                = array();
    
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
        
        // Traitement de la configuration
        foreach( (array) self::tFyAppConfig() as $obj => $attrs ) :
            foreach( (array) $attrs as $obj_type => $args ) :
                self::Register( $obj_type, $obj, $args );
            endforeach;
        endforeach;    
    }
    
    /**
     * DECLENCHEURS
     */
    /**
     * Conditions de requête personnalisés
     */
    final public function posts_clauses( $pieces, $query )
    {    
        // Bypass
        if( ! $query->is_main_query() )
            return $pieces;    
        
        if( ! isset( $_REQUEST['_tyaf']['submit'] ) ) :
            return $pieces;
        else :
            list( $obj, $obj_type ) = self::getContext();
        endif;
        
        // Vérification de l'environnement
        if( ! in_array( $obj, self::$AllowedObj ) )
            return $pieces;
        switch( $obj ) :
            case 'taxonomy' :
                if( ! taxonomy_exists( $obj_type ) )
                    return $pieces;
                if( ! $query->is_tax( $obj_type ) )
                    return $pieces;
                break;
            case 'post_type' :
                if( ! post_type_exists( $obj_type ) )
                    return $pieces;
                if( ! $query->is_post_type_archive( $obj_type ) )
                    return $pieces;
                break;
        endswitch;
        
        if( empty( self::$Filters[$obj][$obj_type]['nodes'] ) )
            return $pieces;
        
        $nodes = self::$Filters[$obj][$obj_type]['nodes'];
                    
        global $wpdb;
        extract( $pieces );

        $distinct = "DISTINCT";
        
        foreach( (array) $nodes as $node_id => $attrs ) :
            if( ! $selected = self::getSelected( $node_id ) )
                continue;
            $_selected = join( ',', $selected );
            
            switch( $attrs['data_type'] ) :
                case 'post_meta':                
                    $join .= " INNER JOIN $wpdb->postmeta as {$node_id}_postmeta ON ($wpdb->posts.ID = {$node_id}_postmeta.post_id AND {$node_id}_postmeta.meta_key = '{$node_id}')";
                    $where .= " AND {$node_id}_postmeta.meta_value IN ( $_selected )";
                    break;
                case 'term' :
                    $join .= " INNER JOIN $wpdb->term_relationships as {$node_id}_relationships ON ($wpdb->posts.ID = {$node_id}_relationships.object_id)";
                    $join .= " INNER JOIN $wpdb->term_taxonomy as {$node_id}_taxonomy ON ({$node_id}_taxonomy.term_taxonomy_id = {$node_id}_relationships.term_taxonomy_id)";
                    $where .= " AND {$node_id}_taxonomy.term_id IN ( $_selected )";
                    break;
            endswitch;    
        endforeach;

        // Empêche l'execution multiple du filtre
        if( $query->is_main_query() )
            \remove_filter( current_filter(), __METHOD__ );
        
        return compact( array_keys( $pieces ) );
    }
        
    /**
     * Après le chargement complet de Wordpress
     */
    final public function wp_loaded()
    {
        do_action_ref_array( 'tify_archive_filters_register', array( $this ) );            
    }
    
    /**
     * Mise en file des scripts
     */
    final public function wp_enqueue_scripts()
    {
        if( self::Has() )
            wp_enqueue_script( 'tiFyComponentsArchiveFilters', self::tFyAppUrl() . '/ArchiveFilters.js', array( 'jquery' ), '160607', true );
    }
    
    /**
     * CONTROLEURS 
     */
    /**
     *
     */
    private static function getSelected( $id )
    {
        $selected = array();
        
        // Récupération des éléments de requête
        if( ! empty( $_REQUEST['_tyaf'][$id] ) ) :
            if( is_string( $_REQUEST['_tyaf'][$id] ) ) :
                $selected = array( $_REQUEST['_tyaf'][$id] );
            else :
                $selected = $_REQUEST['_tyaf'][$id];
            endif;
        endif;    
        
        // Récupération des éléments de contexte
        if( is_tax( $id ) )
            $selected[] = get_queried_object_id();
                
        return $selected;
    }
    
    /**
     *
     */
    private static function getContext()
    {
        $obj = null; $obj_type = null;
        
        foreach( (array) self::$Filters as $_obj => $obj_types ) :
            foreach( (array) $obj_types as $_obj_type => $args ) :
                if( is_post_type_archive( $_obj_type ) ) :                    
                    $obj         = $_obj;      
                    $obj_type     = $_obj_type;
                    break 2;
                elseif( is_tax( $_obj_type ) ) :
                    $obj         = $_obj;      
                    $obj_type     = $_obj_type;
                    break 2;
                endif;
            endforeach;
        endforeach;

        if( ( ! $obj || ! $obj_type ) && isset( $_REQUEST['_tyaf']['submit'] ) )
            list( $obj, $obj_type ) = preg_split( '/:/', $_REQUEST['_tyaf']['submit'], 2 );
        
        return array( $obj, $obj_type );
    }
    
    /**
     * Déclaration d'une interface de filtrage
     */
    public static function Register( $obj_type = 'post', $obj = 'post_type', $args = array() )
    {
        // Bypass
        if( ! in_array( $obj, self::$AllowedObj ) )
            return;
        
        if( ! isset( self::$Filters[$obj] ) )
            self::$Filters[$obj] = array();
        if( ! isset( self::$Filters[$obj][$obj_type] ) )
            self::$Filters[$obj][$obj_type] = array();
        
        if( isset( $args['nodes'] ) ) :
            $nodes = $args['nodes']; 
            unset( $args['nodes'] );
            self::$Filters[$obj][$obj_type]['nodes'] = array();
        endif;
        
        // Définition des options
        $defaults = array( 
            'before'            => '',
            'after'             => '',
            'form_id'           => '',
            'form_class'        => '',
            'form_method'       => 'get',
            'form_action'       => '',
            'form_attrs'        => array(),
            'walker'            => "\\tiFy\\Components\\ArchiveFilters\\Walker"
        );    
        self::$Filters[$obj][$obj_type]['options'] = wp_parse_args( $args, $defaults );
    
        /// Déclaration des éléments de filtrage
        foreach( (array) $nodes as $node_id => $attrs ) :
            self::$Filters[$obj][$obj_type]['nodes'][$node_id] = self::RegisterNode( $node_id, $attrs, $obj_type, $obj );
        endforeach;    
    }
    
    /**
     * Déclaration d'un élément de filtrage
     */
    public static function RegisterNode( $node_id, $args = array(), $obj_type = 'post', $obj = 'post_type' )
    {        
        if( ! isset( self::$Filters[$obj][$obj_type] ) )
            self::Register( $obj_type, $obj );
        
        // Traitement des arguments    
        $defaults = array(
            'id'                    => 'tify-archive-filter-'. $node_id,
            'class'                 => 'tify-archive-filter',
            'before'                => '',
            'after'                 => '',
            'title'                 => '',
            'data_type'             => '',
            'elements'              => array(),
            'walker'                => self::$Filters[$obj][$obj_type]['options']['walker'],
            'single'                => true,
            
        );        
        return $args = wp_parse_args( $args, $defaults );    
    }
    
    /**
     * Vérification d'existance
     */
    public static function Has()
    {
        list( $obj, $obj_type ) = self::getContext();

        if( ! $obj || ! $obj_type )
            return;
        
        return isset( self::$Filters[$obj][$obj_type] );
    }
    
    /**
     * 
     */
    public static function Display( $echo = true )
    {    
        if( ! self::Has() )
            return;
        
        // Récupération du contexte
        list( $obj, $obj_type ) = self::getContext();

        // Bypass        
        if( ! $nodes = self::$Filters[$obj][$obj_type]['nodes'] )
            return;    
        
        $options = self::$Filters[$obj][$obj_type]['options'];
        
        $output  = "";
        $output .= "<div id=\"tiFy_ArchiveFilter_{$obj}_{$obj_type}\" class=\"tiFy_ArchiveFilter\">";
        $output .= "\t<form method=\"{$options['form_method']}\" action=\"{$options['form_action']}\">\n";
        if( $nodes ) :
            $output .= "\t\t<ul class=\"tiFy_ArchiveFilter-items\">";
            foreach( (array) $nodes as $id => $args ) :
                $walker = new $args['walker'];
                if( $args['data_type'] === 'term' ) :
                    $walker->db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );
                elseif( $args['data_type'] === 'post_meta' ) :
                    $walker->db_fields = array( 'parent' => 'parent', 'id' => 'meta_value' );
                endif;
                
                // Définition de la liste d'éléments selectionnable
                $max_depth = -1;
                if( empty( $args['elements'] ) ) :
                    if( $args['data_type'] === 'term' ) :
                        $elements     = get_terms( array( 'taxonomy' => $id, 'get' => 'all' ) );
                        $max_depth    = 0;
                    endif;
                else :
                    foreach( $args['elements'] as $k => $el ) :
                        $elements[$k] = (object) $el;
                    endforeach;
                endif;                
                
                // Définition des éléments selectionné
                $args['selected'] = self::getSelected( $id );        

                $output .= "\t\t\t<li>\n";
                if( $args['before'] )
                    $output .= "\t\t\t\t". $args['before'];
                if( $args['title'] )
                    $output .= "\t\t\t\t<h3>". $args['title'] ."</h3>";                
                $output .= "\t\t\t\t\t<ul id=\"{$args['id']}\" class=\"{$args['class']}\">\n";
                $output .= call_user_func_array( array( $walker, 'walk' ), array( $elements, $max_depth, $args ) );
                $output .= "\t\t\t\t\t</ul>\n";
                if( $args['after'] )
                    $output .= "\t\t\t\t". $args['after'];
                $output .= "\t\t\t</li>\n";
            endforeach;
            $output .= "\t\t</ul>\n";
        endif;
        
        $output .= "\t\t<button type=\"submit\" name=\"_tyaf[submit]\" value=\"{$obj}:{$obj_type}\" >". __( 'Rechercher', 'tify' ) ."</button>";
        $output .= "\t</form>\n";
        $output .= "</div>";
                
        if( $echo )
            echo $output;
        
        return $output;
    }
}