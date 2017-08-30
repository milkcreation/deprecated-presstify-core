<?php
namespace tiFy\Components\CustomColumns;

abstract class Factory extends \tiFy\App\Factory
{
    /* = ARGUMENTS = */
    // Instance    
    private static $Instance    = 0;
    
    // Attributs
    private $Attrs              = array();
        
    /* = CONSTRUCTEUR = */
    public function __construct( $args = array() )
    {
        parent::__construct();
        
        if( empty( $args['env'] ) || empty( $args['type'] ) )
            return;
        
        self::$Instance++;
        
        // Définition de la configuration
        $defaults = array(
            'sortable'    => false,
            'position'    => 0,    
            'title'        => '',
            'column'    => 'tiFyColumn-'. self::$Instance
        );
        
        if( is_callable( array( $this, 'getDefaults' ) ) )
            $defaults = wp_parse_args( (array) call_user_func( array( $this, 'getDefaults' ) ), $defaults );
                
        $this->Attrs = wp_parse_args( $args, $defaults );
        
        // Initialisation de la vue courante
        add_filter( "manage_edit-{$args['type']}_columns", array( $this, '_Header' ) );
    
        switch( $args['env'] ) :
            case 'post_type' :
                add_action( "manage_{$args['type']}_posts_custom_column", array( $this, '_Content' ), 25, 2 );
                break;
            case 'taxonomy' :
                add_filter( "manage_{$args['type']}_custom_column", array( $this, '_Content' ), 25, 3 );
                break;
        endswitch;
    }
    
    /* = DECLENCHEURS = */
    /** == Initialisation de l'interface d'administration == **/
    public function admin_init(){}
    
    /** == Chargement de la page courante == **/
    public function current_screen( $current_screen ){}
    
    /** == Mise en file des scripts de l'interface d'administration == **/
    public function admin_enqueue_scripts(){}
        
    /* = CONTROLEURS = */
    /** == Récupération de paramètre == **/
    final public function getAttrs( $index = null )
    {
        if( ! $index ) :
            return $this->Attrs;
        elseif( isset( $this->Attrs[$index] ) ) :
            return $this->Attrs[$index];
        endif;
    }
    
        
    /** == Déclaration de la colonne == **/
    final public function _Header( $columns )
    {    
        if( ! empty( $this->Attrs['position'] ) ) :
            $newcolumns = array(); $n = 0;
            foreach( $columns as $key => $column ) :
                if( $n === (int) $this->Attrs['position'] ) 
                    $newcolumns[$this->Attrs['column']] = $this->Attrs['title'];
                $newcolumns[$key] = $column;
                $n++;                
            endforeach;
            $columns = $newcolumns;
        else :
            $columns[$this->Attrs['column']] = $this->Attrs['title'];
        endif;

        return $columns;
    }
    
    /** == == **/
    final public function _Content()
    {
        switch( $this->Attrs['env'] ) :
            case 'post_type':
                $column_name    = func_get_arg( 0 );
                // Bypass
                if( $column_name !== $this->Attrs['column'] )
                    return;
            break;
            case 'taxonomy':
                $output            = func_get_arg( 0 );
                $column_name    = func_get_arg( 1 );
                // Bypass
                if( $column_name !== $this->Attrs['column'] )
                    return $output;
            break;
        endswitch;
                
        call_user_func_array( array( $this, 'content' ), func_get_args() );
    }

    /** == Récupération des arguments par défaut == **/
    public function getDefaults()
    {
        return array();    
    }

    /** == Affichage des données de la colonne == **/
    /*public function content()
    {        
        echo __( 'Pas de données à afficher', 'tify' );
    }*/
}