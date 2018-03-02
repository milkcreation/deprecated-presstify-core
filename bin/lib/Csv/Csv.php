<?php
/**
 *  USAGE : 
 *            
    $Csv = \tiFy\Lib\Csv\Csv::getResults(
        array(
            'filename'      => ABSPATH .'/example.csv'
            'delimiter'     => ';',
            'query_args'    => array(
                'paged'         => 1,
                'per_page'      => -1   
            ),            
            'columns'       => array( 'lastname', 'firstname', 'email' ),
            'orderby'       => array(
                'lastname'      => 'ASC'
            ),
            'search'        => array(
                array(
                    'term'      => '@domain.ltd',
                    'cols'      => array( 'email' )    
                ),
                array(
                    'term'      => 'john',
                    'cols'      => array()
                ),
            )               
        ); 
    );
    
    // Récupération la liste des éléments 
    $items = $Csv->getItems();
    
    // Nombre total de resultats
    $total_items = $Csv->getTotalItems();
    
    // Nombre de résultats
    $total_pages = $Csv->getTotalPages(); 
    
    // Nombre d'éléments trouvés
    $found_items = $Csv->getFoundItems(); 
 */
namespace tiFy\Lib\Csv;

use League\Csv\Reader;
use ForceUTF8\Encoding;
//use \League\Csv\Writer;

class Csv
{    
    /**
     * Chemin vers le fichier de données à traiter
     */
    public $Filename;
    
    /**
     * Propriétés Csv
     * @var array
     */
    public $Properties              = array(
        /// Délimiteur de champs
        'delimiter'                     => ',',
        /// Caractère d'encadrement 
        'enclosure'                     => '"',
        /// Caractère de protection
        'escape'                        => '\\'
    );
    
    /**
     * Arguments de requête
     * @var array
     */
    public $QueryArgs               = array(
        // Page courante
        'paged'                         => 1,    
        // Nombre d'éléments par page
        'per_page'                      => -1
    );
    
    /**
     * Arguments de trie
     */
    public $OrderBy                 = array();
    
    /**
     * Arguments de recherche
     * @var array
     */
    public $SearchArgs              = array();
    
    /**
     * Cartographie des colonnes
     * ex array( 'ID', 'title', 'description' );
     */
    public $Columns                 = array();
    
    /**
     * Types de fichier autorisés
     */
    protected $AllowedMimeType      = array( 'csv', 'txt' );
    
    /**
     * Doublons
     */
    protected $Duplicates           = array(); 
    
    /**
     * Trie des données
     */
    protected $Sorts                = array(); 
    
    /**
     * Filtres de données
     */
    protected $Filters              = array();
    
    /**
     * Relation de filtrage des données
     */
    protected $FiltersRelation      = 'OR'; 
    
    /**
     * Nombre d'éléments trouvés
     */
    protected $FoundItems           = 0;
    
    /**
     * Nombre d'éléments total
     */
    protected $TotalItems           = 0;
    
    /**
     * Nombre de page total
     */
    protected $TotalPages           = 0;   
    
    /**
     * Liste des éléments
     */
    protected $Items                = [];
    
    /**
     * CONSTRUCTEUR
     * @param array $options
     */
    public function __construct($options = [])
    {
        foreach( $options as $option_name => $option_value ) :
            switch( $option_name ) :
                case 'filename' :
                    $this->setFilename( $option_value );
                    break;
                case 'delimiter' :
                case 'enclosure' :
                case 'escape' :
                    $this->setProperty( $option_name, $option_value );   
                    break;
                case 'query_args' :
                    foreach( $option_value as $query_arg => $value ) :
                        $this->setQueryArg( $query_arg, $value ); 
                    endforeach;
                    break;
                case 'columns' :
                    $this->Columns = $option_value;
                    break;
                case 'orderby' :
                    $this->OrderBy = $option_value;
                    break;    
                case 'search' :
                    $this->SearchArgs = $option_value;
                    break;               
            endswitch;
        endforeach;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Définition du fichier de données
     */
    final public function setFilename( $filename )
    {
        if( file_exists( $filename ) ) :
            $this->Filename = $filename;
        endif;
    }
    
    /**
     * Récupération du fichier de données
     */
    public function getFilename()
    {
        return $this->Filename;
    }
    
    /**
     * Définition de propriété Csv
     */
    final public function setProperty( $prop, $value = '' )
    {
        if( ! in_array( $prop, array( 'delimiter', 'enclosure', 'escape' ) ) )
            return;

        $this->Properties[$prop] = $value;            
    }
    
    /**
     * Récupération de propriété Csv
     */
    public function getProperty( $prop, $default = '' )
    {
        // Bypass
        if( ! in_array( $prop, array( 'delimiter', 'enclosure', 'escape' ) ) )
            return $default;
        
        if( isset( $this->Properties[$prop] ) )
            return $this->Properties[$prop];
        
        return $defaut;    
    }
    
    /**
     * Définition d'un argument de requête
     */
    final public function setQueryArg( $arg, $value = '' )
    {
        $this->QueryArgs[$arg] = $value;            
    }
    
    /**
     * Récupération d'un argument de requête
     */
    public function getQueryArg( $arg, $default = '' )
    {
        if( isset( $this->QueryArgs[$arg] ) )
            return $this->QueryArgs[$arg];
        
        return $defaut;
    }
    
    /**
     * Définition du nombre total d'éléments
     */
    final public function setFoundItems( $found_items )
    {
        $this->FoundItems = (int) $found_items;
    }
    
    /**
     * Récupération du nombre d'éléments trouvés
     */
    public function getFoundItems()
    {
        return $this->FoundItems;
    }    
    
    /**
     * Définition du nombre total d'éléments
     */
    final public function setTotalItems( $total_items )
    {
        $this->TotalItems = (int) $total_items;
    }
    
    /**
     * Récupération du nombre total d'éléments
     */
    public function getTotalItems()
    {
        return $this->TotalItems;
    }
    
    /**
     * Définition du nombre total de page
     */
    final public function setTotalPages( $total_pages )
    {
        $this->TotalPages = (int) $total_pages;
    }
    
    /**
     * Récupération du nombre total de page
     */
    public function getTotalPages()
    {
        return $this->TotalPages;           
    }
    
    /**
     * Récupération des éléments
     */
    public function getItems()
    {
        return $this->Items;
    }
    
    /**
     * Récupération des colonnes
     */
    public function getColumns()
    {
       return ! empty( $this->Columns ) ? $this->Columns : 0;
    }
    
    /**
     * Récupération de l'index d'une colonne
     */
    final public function getColumnIndex( $column )
    {
        if( ( $index = array_search( $column, $this->Columns ) ) && is_numeric( $index ) ) :
            return $index;
        endif;
        
        return null;
    }
    
    /**
     * Définition de l'ordre de trie
     */
    final public function setSorts()
    {
        if( ! $this->OrderBy )
            return;         

        foreach( (array) $this->OrderBy as $key => $value ) :
            $key = ( is_numeric( $key ) ) ? $key : $this->getColumnIndex( $key );
            if( ! is_numeric( $key ) )
                continue;
            $this->Sorts[$key] = in_array( strtoupper( $value ), array( 'ASC', 'DESC' ) ) ? strtoupper( $value ) : 'ASC';             
        endforeach;
        
        return $this->Sorts;
    }
    
    /**
     * Méthode de rappel de trie des données
     */
    final public function searchSortCallback( $rowA, $rowB )
    {
        foreach( $this->Sorts as $col => $order  ) : 
            switch( $order ) :
                case 'ASC' :
                    return strcasecmp( $rowA[$col], $rowB[$col] );
                    break;
                case 'DESC' :
                    return strcasecmp( $rowB[$col], $rowA[$col] );
                    break;
            endswitch;
        endforeach;
    }
    
    /**
     * Définition du filtrage
     */
    final public function setFilters( $csvObj )
    {
        if( ! $this->SearchArgs )
            return;
                     
        $clone = clone $csvObj;        
        $count = count( $clone->fetchOne() );            
            
        foreach( $this->SearchArgs as $key => $f ) :
            if( ! is_numeric( $key ) && ( $key === 'relation' ) && ( in_array( strtoupper( $f ), array( 'OR', 'AND' ) ) ) ) :
                $this->FiltersRelation = strtoupper( $f );
            endif;    
            if( empty( $f['term'] ) )
                continue;
            $term = $f['term'];
            
            $exact = ! empty( $f['exact'] ) ? true : false;
            
            if( empty( $f['columns'] ) ) :
                $columns = range( 0, ( $count-1 ), 1 );
            elseif( is_string( $f['columns'] ) ) :
                $columns = array_map( 'trim', explode( ',', $f['columns'] ) ) ;
            elseif( is_array( $f['columns'] ) ) :
                $columns = $f['columns'];
            endif;
            $filters = array();
            foreach( $columns as $c ) :
                if( ! is_numeric( $c ) ) :
                    $c = $this->getColumnIndex( $c );
                endif;
                
                              
                $this->Filters[] = array(
                    'col'   => (int) $c,
                    'exact' => $exact,
                    'term'  => $term
                );
            endforeach;            
        endforeach;

        return $this->Filters;
    }
    
    /**
     * Méthode de rappel du filtrage
     */
    final public function searchFilterCallback( $row )
    {
        $has = array();
        foreach( $this->Filters as $f ) :
            $regex = $f['exact'] ? '^'. $f['term'] .'$' : $f['term'];

            if( preg_match( '/'. $regex .'/i', $row[$f['col']] ) ) :                
                $has[$f['col']] = 1;
            else :
                $has[$f['col']] = 0;
            endif;
        endforeach;
        
        switch( $this->FiltersRelation ) :
            default :
            case 'OR' :
                if( in_array( 1, $has ) )
                    return true;
                break;
            case 'AND' :
                if( ! in_array( 0, $has ) )
                    return true;
                break;
        endswitch;        
        
        return false;
    }
    
    /**
     * Méthode de rappel de filtrage des doublons
     * @todo
     */
    final public function duplicateFilterCallback( $row )
    {        
        if( ! in_array( $row[0], $this->Duplicates ) ) :
            array_push( $this->Duplicates, $row[0] );           
            return true;                    
        endif;
    } 
                
    /**
     * Récupération d'éléments
     */
    final public static function getResults( $args = array() )
    {
        $csv = new Static( $args );

        // Traitement global du fichier csv
        $reader = Reader::createFromFileObject( new \SplFileObject( $csv->getFilename() ) );
        
        // Définition des propriétés csv
        $reader
            ->setDelimiter( $csv->getProperty( 'delimiter', ',' ) )
            ->setEnclosure( $csv->getProperty( 'enclosure', '"' ) )
            ->setEscape( $csv->getProperty( 'escape', '\\' ) )
            ->setOffset( 0 )
            ->setLimit( -1 );
            
        // Traitement des arguments de requête
        /// Flag de filtrage des données
        $filtered = false;        
        
        /// Recherche
        if( $csv->setFilters( $reader ) ) :
            $filtered = true;        
            $reader->addFilter( array( $csv, 'searchFilterCallback' ) );          
        endif;
            
        /// Trie des éléments
        if( $csv->setSorts( $reader ) ) :
           $reader->addSortBy( array( $csv, 'searchSortCallback' ) ); 
        endif;
                       
        // Traitement des données filtrées
        if( $filtered ) :
            $counter = clone $reader;
            $rows = $counter->fetchAll();
            // Définition du nombre total d'éléments
            $total_items = count( $rows );
            $csv->setTotalItems( $total_items );
            
        // Traitement des données non filtrées    
        else :
            // Définition du nombre total d'éléments
            $total_items = $reader->each( function($row){ return true;});
            $csv->setTotalItems( $total_items );
        endif;
        
        // Définition des attributs de pagination
        $per_page = $csv->getQueryArg( 'per_page', -1 );
        $paged = $csv->getQueryArg( 'paged', 1 );
        $offset = ( $per_page > -1 ) ? ( ( $paged - 1 ) * $per_page ) : 0; 
        $total_pages = ( $per_page > -1 ) ? ceil( $total_items / $per_page ) : 1;  
        $csv->setTotalPages( $total_pages );  
        
        // Pagination
        $reader->setOffset( $offset );
        $reader->setLimit( $per_page );
               
        // Récupération des résultats
        $results = $reader->fetchAssoc( 
            $csv->getColumns(), 
            function($row, $rowOffset) {
                return array_map( function( $str ){ return Encoding::toUTF8( $str ); }, $row );
            } 
        );    
        $csv->Items = iterator_to_array( $results );
        
        // Définition du nombre d'élément trouvés pour la requête
        $found_items = count( $csv->Items );
        $csv->setFoundItems( $found_items );
        
        return $csv;
    }
    
    /**
     * Récupération d'une ligne de donnée du fichier
     */
    final public static function getRow( $offset = 0, $args = array() )
    {
        $csv = new Static( $args );
        
        // Traitement global du fichier csv
        $reader = Reader::createFromFileObject( new \SplFileObject( $csv->getFilename() ) );
        
        // Définition des propriétés csv
        $reader
            ->setDelimiter( $csv->getProperty( 'delimiter', ',' ) )
            ->setEnclosure( $csv->getProperty( 'enclosure', '"' ) )
            ->setEscape( $csv->getProperty( 'escape', '\\' ) )
            ->setOffset( $offset )
            ->setLimit( 1 );            

        // Récupération des résultats
        $results = $reader->fetchAssoc( 
            $csv->getColumns(), 
            function($row, $rowOffset) {
                return array_map( function( $str ){ return Encoding::toUTF8( $str ); }, $row );
            } 
        );
        $csv->Items = iterator_to_array( $results );
        
        // Définition du nombre d'élément trouvés pour la requête
        $found_items = count( $csv->Items );
        $csv->setFoundItems( $found_items );
        $csv->setTotalItems( $found_items );
        $csv->setTotalPages( 1 );
        
        return $csv;
    } 
    
    /**
     * Récupération des éléments de donnée du fichier
     * @todo
     
    public function getCol( $col = 0, $distinct = true )
    {      
        // Traitement global du fichier csv
        $csv = Reader::createFromFileObject( new \SplFileObject( $this->getFilename() ) );
        // Définition des propriétés du csv
        $csv
            ->setDelimiter( $this->getProperty( 'delimiter', ',' ) )
            ->setEnclosure( $this->getProperty( 'enclosure', '"' ) )
            ->setEscape( $this->getProperty( 'escape', '\\' ) );
               
        // Traitement des arguments de requête           
        $per_page = $this->getQueryArg( 'per_page', -1 );
        $paged = $this->getQueryArg( 'paged', 1 );
        $offset = ( $per_page > -1 ) ? ( ( $paged - 1 ) * $per_page ) : 0;    
        $filtered = false;
        
        // Doublons
        if( ! $distinct ) :
            $filtered = true; $this->setTotalItems( 0 );
            $csv->addFilter( array( $this, 'duplicateFilterCallback' ) );
            $total_items = $this->getTotalItems(); 
        endif;        
        
        // Recherche
        if( $this->setFilters( $csv ) ) :
            $filtered = true; $this->setTotalItems( 0 );
            $csv->addFilter( array( $this, 'searchFilterCallback' ) );
            $total_items = $this->getTotalItems();                 
        endif;  
        
        if( ! $filtered ) :
            // Compte le nombre total d'éléments trouvés
            $total_items = $csv->each( function($row){ return true;});
            $this->setTotalItems( $total_items );
        endif;
                
        // Pagination
        $csv
            ->setOffset( $offset )
            ->setLimit( $per_page );
        
        // Trie des éléments
        if( $this->setSorts( $csv ) ) :
           $csv->addSortBy( array( $this, 'searchSortCallback' ) ); 
        endif;
        
        // Définition du nombre total de page
        $total_pages = ( $per_page > -1 ) ? ceil( $total_items / $per_page ) : 1;  
        $this->setTotalPages( $total_pages );  
  
        // Récupération des résultats
        $colname = ( isset( $this->Columns[$col] ) ) ? $this->Columns[$col] : $col;
        $results = $csv->fetchAssoc( 
            array( $colname ), 
            function($row){ 
                return (object) array_map( 'utf8_encode', $row );
            } 
        );

        // Formatage et retour des résultats
        $this->Items = iterator_to_array( $results, true );
        
        return $this->Items;
    }*/
}