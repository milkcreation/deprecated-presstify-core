<?php
namespace tiFy\Lib;

class Csv
{    
    /* = ARGUMENTS = */
    // CONFIGURATION
    /// Type de fichier autorisé 
    private $AllowedMimeType = array( 'csv', 'txt' );
    
    // OPTIONS
    /// Délimiteur de champs
    private $Delimiter      = ",";
    
    /// Caractère d'encadrement 
    private $Enclosure      = "\"";
    
    /// Caractère de protection
    private $Escape         = "\\";
    
    /// Page courante
    private $Paged          = 1;
    
    /// Nombre d'éléments par page
    private $PerPage        = 20;
    
    /// Encodage des caractères
    private $Charset        = 'utf8';
    
    /// Cartographie des colonnes d'éléments de données
    /// ex array( 'ID', 'title', 'description' );
    private $MapColumn      = array();
    
    // PARAMETRES
    /// Chemin vers le fichier de données
    private $Filename;
    
    /// Nombre total d'éléments de données
    private $TotalItems     = 0;
    
    /// Nombre total de page d'éléments de données
    private $TotalPages     = 0;
    
    /* = CONSTRUCTEUR = */
    public function __construct( $filename = null, $options = array() )
    {
        if( $filename ) :
            $this->setFilename( $filename );
        endif;
        
        foreach( $options as $option_name => $option_value ) :
            $this->setOption( $option_name, $option_value );                
        endforeach;
    }
    
    /* = PARAMETRAGES = */
    /** == Définition du fichier de données == **/
    final public function setFilename( $filename )
    {
        if( file_exists( $filename ) ) :
            $this->Filename = $filename;
        endif;
    }
    
    /** == Définition d'une option == **/
    final public function setOption( $name, $value = '' )
    {
        if( ! in_array( $name, array( 'delimiter', 'enclosure', 'escape', 'paged', 'per_page', 'charset', 'map_column' ) ) )
            return;
        
        $Name = explode( '_', $name );            
        $Name = implode( array_map( 'ucfirst', $Name ) );
        $this->{$Name} = $value;            
    }
    
    /** == Définition du nombre total d'éléments == **/
    final public function setTotalItems( $total_items )
    {
        $this->TotalItems = (int) $total_items;
    }
    
    /** == Définition du nombre total de page == **/
    final public function setTotalPages( $total_pages )
    {
        $this->TotalPages = (int) $total_pages;
    }
    
    /* = OPTIONS = */        
    /** == Récupération du délimiteur de champs == **/
    public function getDelimiter()
    {
        return $this->Delimiter;
    }
    
    /** == Récupération du caractère d'encadrement == **/
    public function getEnclosure()
    {
        return $this->Enclosure;
    }
    
    /** == Récupération du caractère de protection == **/
    public function getEscape()
    {
        return $this->Escape;
    }
    
    /** == Récupération du caractère de protection == **/
    public function getCharset()
    {
        return $this->Charset;
    }
    
    /** == Récupération de la page courante == **/
    public function getPaged()
    {
        return $this->Paged;
    }
    
    /** == Récupération du nombre d'éléments par page == **/
    public function getPerPage()
    {
        return $this->PerPage;
    }
    
    /* = PARAMETRES = */
    /** == Récupération du fichier de données == **/
    public function getFilename()
    {
        return $this->Filename;
    }
    
    /** == Récupération du nombre total d'éléments de données == **/
    public function getTotalItems()
    {
        return $this->TotalItems;
    }
    
    /** == Récupération du nombre total de page d'éléments de données == **/
    public function getTotalPages()
    {
        return $this->TotalPages;
    }
    
    /** == Récupération du nombre total de page d'éléments de données == **/
    public function getMapColumn( $index )
    {
        if( isset( $this->MapColumn[$index] ) )
            return $this->MapColumn[$index];
        
        return $index;
    }
        
    /* = CONTROLEURS = */  
    /** == Vérifie si le type de fichier est autorisé à être traité == **/
    public function isAllowedMimeType(){}
    
    /** == Encode la chaîne de caractères d'une ligne de données == **/
    public function encode( $str )
    {
        if( $this->getCharset() === 'utf8' )
            $str = utf8_encode( $str );
        
        return $str;
    }
    
    /** == Récupération des éléments de donnée du fichier == **/
    public function getItems()
    {
        /**
         * http://stackoverflow.com/questions/32184933/solved-remove-bom-%C3%AF-from-imported-csv-file
         * http://stackoverflow.com/questions/4348802/how-can-i-output-a-utf-8-csv-in-php-that-excel-will-read-properly
         */         
        /*
        // SOLUTION 1
        function removeBomUtf8($s){
          if(substr($s,0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
               return substr($s,3);
           }else{
               return $s;
           }
        }
        // SOLUTION 2
        $fileContent = file_get_contents( $this->filename );
        $fileContent = mb_convert_encoding( $fileContent, "UTF-8" );
        $lines = explode("\n", $fileContent);
        */
        
        // SOLUTION 3
        $lines = file( $this->Filename );
        
        /// Pagination
        $total_items = count( $lines );
        $this->setTotalItems( $total_items );
                
        $paged = $this->getPaged();        
        $per_page = $this->getPerPage();

        $total_pages = ceil( $total_items/$per_page ); 
        $this->setTotalPages( $total_pages );
        
        $offset = ( $paged - 1 )*$per_page;       
        $limit = $offset+$per_page;        
        if( $limit > $total_items ) : 
            $limit = $total;
        endif;        

        $datas = array();
        for( $i = $offset; $i < $limit; $i++ ) :
            $s = $lines[$i];
            // Eviter les erreurs de BOM
            $s = ( substr( $s, 0, 3 ) == chr( hexdec( 'EF' ) ) . chr( hexdec( 'BB' ) ) . chr( hexdec( 'BF' ) ) ) ? substr( $s, 3 ) : $s;
            $s = $this->encode( $s );
            $datas[] = str_getcsv( $s, $this->getDelimiter(), $this->getEnclosure(), $this->getEscape() );
        endfor;
        
        $items = array();
        foreach( $datas as $i => $row ) :
            //$items[$i] = new \stdClass();
            foreach( $row as $k => $value ) :
                $attr = $this->getMapColumn( $k );
                $items[$i]->{$attr} = $value;    
            endforeach;
        endforeach;
        
        return $items;
    }
}