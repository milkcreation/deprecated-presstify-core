<?php
namespace tiFy\Core\Db;

final class Parse
{
    /**
     * Object base de données
     * @var unknown
     */
    protected    $Db;
    
    //
    protected    $MetaQuery        = null;
    
    // 
    protected    $MetaClauses     = array();
    
    /* = CONSTRUCTEUR = */    
    public function __construct( Factory $Db )
    {
        $this->Db = $Db;            
    }
    
    /** == Traitements des arguments de requête == **/
    final public function query_vars( $vars, $defaults = null )
    {
        if( is_null( $defaults ) )
            $defaults = array(
                'item__in'        => '',
                'item__not_in'    => '',
                's'                => '',
                'meta_query'    => array(),
                'per_page'         => -1,
                'paged'         => 1,
                'order'         => 'DESC',
                'orderby'         => $this->Db->Primary
            );
        $vars =  wp_parse_args( $vars, $defaults );    
        
        // Gestion des requêtes de métadonnées
        if( ! empty( $vars['meta_query'] ) && $this->Db->hasMeta() ) :    
            $this->MetaQuery = new Meta_Query( $this->Db, $vars['meta_query'] );            

            $this->MetaClauses = $this->MetaQuery->get_sql(
                $this->Db->MetaType,
                $this->Db->Name,
                $this->Db->Primary,
                null
            );
        endif;

        // Retro-Compatibilité
        if( ! empty( $vars['include'] ) ) :
            $vars['item__in'] = $vars['include'];
            unset( $vars['include'] );
        endif;
        if( ! empty( $vars['exclude'] ) ) :
            $vars['item__not_in'] = $vars['exclude'];
            unset( $vars['exclude'] );
        endif;    
            
        return $vars;    
    }
    
    /** == Traitement de la clause JOIN == **/
    final public function clause_join()
    {
        $join = array();
        
        // Traitement des conditions relatives au metadonnées
        if( ! empty( $this->MetaClauses['join'] ) ) :
            $join[] = trim( $this->MetaClauses['join'] );    
        endif;
        
        if( ! empty( $join ) )
            return " ". implode( ' ', $join );
    }
    
    /** == Traitement de la clause WHERE == **/
    final public function clause_where( $vars )
    {
        $where = array();
        $clause = "WHERE 1";
        
        // Traitement des conditions relatives aux colonnes de la table principale
        if( $cols = $this->validate( $vars ) ) :    
            foreach( (array) $cols as $col_name => $value ) :            
                /* Gestion des alias
                 if( ( $value === 'any' ) && isset( $this->col_{$col}['any'] ) )
                    $value = $this->col_{$col}['any'];*/
                
                if( is_string( $value ) ) :
                    $where[] = "AND {$this->Db->Name}.{$col_name} = '{$value}'";
                elseif( is_bool( $value ) &&  $value ) :
                    $where[] = "AND {$this->Db->Name}.{$col_name}";
                elseif( is_bool( $value ) &&  ! $value ) :
                    $where[] = "AND ! {$this->Db->Name}.{$col_name}";
                elseif( is_numeric( $value ) ) :
                    $where[] = "AND {$this->Db->Name}.{$col_name} = {$value}";
                elseif( is_array( $value ) ) :
                    $where[] = "AND {$this->Db->Name}.{$col_name} ". $this->clause_where_compare_value( $value );  
                elseif( is_null( $value ) ) :
                    $where[] = "AND {$this->Db->Name}.{$col_name} IS NULL";    
                endif;            
            endforeach;
        endif;
        
        // Traitement des conditions relatives au metadonnées
        if( ! empty( $this->MetaClauses['where'] ) ) :
            $where[] = trim( $this->MetaClauses['where'] );    
        endif;        
        
        return $clause ." ". implode( ' ', $where );
    }
    
    /**
     * 
     */
    final public function clause_where_compare_value( $col_value )
    {       
        if( array_key_exists( 'value', $col_value ) ) :
            $value = $col_value['value'];
            $compare = isset( $col_value['compare'] ) ? $col_value['compare'] : '';
            
            if ( in_array( $compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ) ) ) :
                if ( ! is_array( $value ) ) :
                    $value = preg_split( '/[,\s]+/', $value );
                endif;
            else :
                $value = trim( $value );
            endif;

            switch ( $compare ) :
                case 'IN' :
                case 'NOT IN' :
                    $compare_string = '(' . substr( str_repeat( ',%s', count( $value ) ), 1 ) . ')';
                    $where = $this->Db->sql()->prepare( $compare_string, $value );
                    break;

                case 'BETWEEN' :
                case 'NOT BETWEEN' :
                    $value = array_slice( $value, 0, 2 );
                    $where = $this->Db->sql()->prepare( '%s AND %s', $value );
                    break;

                case 'LIKE' :
                case 'NOT LIKE' :
                    $mvalue = '%' . $this->Db->sql()->esc_like( $value ) . '%';
                    $where = $this->Db->sql()->prepare( '%s', $value );
                    break;

                // EXISTS with a value is interpreted as '='.
                case 'EXISTS' :
                    $compare = '=';
                    $where = $this->Db->sql()->prepare( '%s', $value );
                    break;

                // 'value' is ignored for NOT EXISTS.
                case 'NOT EXISTS' :
                    $where = '';
                    break;
                    
                default :
                    $where = $this->Db->sql()->prepare( '%s', $value );
                    break;
            endswitch;
        else :
            $compare = 'IN';
            $where = "('". implode( "', '", $col_value ) ."')";
        endif;  
        
        return "{$compare} {$where}";
    }
    
    
    /** == Traitement de la recherche de term == **/
    final public function clause_search( $terms = '' )
    {        
        if( empty( $terms ) || ! $this->Db->hasSearch() )
            return;
        
        $like = '%' . $this->Db->sql()->esc_like( $terms ) . '%';
        $search_query = array();
        foreach( (array) $this->Db->SearchColNames as $col_name )
            $search_query[] = $this->Db->Name .".{$col_name} LIKE '{$like}'";

        if( $search_query )
            return " AND (". join( " OR ", $search_query ) .")";
    }
    
    /** == Traitement de la clause ITEM__IN == **/
    public function clause__in( $ids )
    {
        // Bypass
        if( ! $ids )
            return;
        
        if( ! is_array( $ids ) )
            $ids = array( $ids );
        
        $__in =  implode( ',', array_map( 'absint', $ids ) );
        
        return " AND ". $this->Db->Name .".". $this->Db->Primary ." IN ({$__in})";
    }
    
    /** == Traitement de la clause ITEM__NOT_IN == **/
    public function clause__not_in( $ids )
    {
        // Bypass
        if( ! $ids )
            return;
        
        if( ! is_array( $ids ) )
            $ids = array( $ids );
        
        $__not_in = implode(',',  array_map( 'absint', $ids ) );
        
        return " AND ". $this->Db->Name .".". $this->Db->Primary ." NOT IN ({$__not_in})";
    }
    
    /** == Traitement de la clause ORDER == **/
    public function clause_order( $orderby, $order = 'DESC' )
    {
        $orderby_array = array();
        if ( is_array( $orderby ) ) :
            foreach ( $orderby as $_orderby => $order ) :
                $orderby = addslashes_gpc( urldecode( $_orderby ) );
                $parsed  = $this->parse_orderby( $orderby );

                if ( ! $parsed )
                    continue;

                $orderby_array[] = $parsed . ' ' . $this->parse_order( $order );
            endforeach;
            $orderby = implode( ', ', $orderby_array );

        else :
            $orderby = urldecode( $orderby );
            $orderby = addslashes_gpc( $orderby );

            foreach ( explode( ' ', $orderby ) as $i => $orderby ) :
                $parsed = $this->parse_orderby( $orderby );
                if ( ! $parsed )
                    continue;

                $orderby_array[] = $parsed;
            endforeach;
            $orderby = implode( ' ' . $order . ', ', $orderby_array );

            if ( empty( $orderby ) ) :
                $orderby = "$this->Db->Name . $this->Db->Primary " . $order;
            elseif ( ! empty( $order ) ) :
                $orderby .= " {$order}";
            endif;
        endif;
        
        return " ORDER BY ". $orderby;
    }
    
    /**
     * 
     * @param unknown $orderby
     */
    public function parse_orderby( $orderby )
    {
       if( ( $orderby === 'meta_value' ) &&  $this->MetaQuery ) :        
            $clauses = $this->MetaQuery->get_clauses();
            $primary_meta_query = reset( $clauses );
            $orderby_clause = "CAST({$primary_meta_query['alias']}.meta_value AS {$primary_meta_query['cast']})";        
        elseif( $orderby = $this->Db->isCol( $orderby ) ) :
            $orderby_clause = $this->Db->Name .".{$orderby}";
        else :
            $orderby_clause = $this->Db->Name . $this->Db->Primary;
        endif;
        
        return $orderby_clause;        
    }
    
    /**
     * 
     */
    public function parse_order( $order = null ) 
    {
        if ( ! is_string( $order ) || empty( $order ) ) :
            return 'DESC';
        endif;

        if ( 'ASC' === strtoupper( $order ) ) :
            return 'ASC';
        else :
            return 'DESC';
        endif;
    }
    
    
    /** == Traitement de la clause GROUPBY == **/
    public function clause_group_by()
    {
        if( $this->MetaClauses )
            return "GROUP BY {$this->Db->Name}.{$this->Db->Primary}";
    }
    
    
    /** == Vérification des arguments de requête == **/
    final public function validate( $vars )
    {
        $_vars = array();
        foreach( $vars as $col_name => $value ) :
            if( ! $col_name = $this->Db->isCol( $col_name )  )
                continue;
            /** @todo : Typage des valeurs  ! any cf parse_conditions **/
            $_vars[$col_name] = $value;            
        endforeach;
                
        return $_vars;        
    }
}
