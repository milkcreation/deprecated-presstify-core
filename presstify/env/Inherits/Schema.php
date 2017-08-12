<?php
namespace tiFy\Environment\Inherits;

use \tiFy\tiFy;
use \tiFy\Core\Params;

class Schema
{
    /**
     * Définition du schema 
     */
    protected function initSchema()
    {
        $dirname    = self::getDirname() .'/config/';
        $schema     = array();
        $class      = get_called_class();
        
        // Récupération du paramétrage natif
        $_dir = @ opendir( $dirname );
        if( $_dir ) :
            while ( ( $file = readdir( $_dir ) ) !== false ) :
                if ( substr( $file, 0, 1 ) == '.' )
                        continue;
                $basename = basename( $file, ".yml" );
                if( $basename !== 'schema' )
                    continue;
                
                $schema += \tiFy\Core\Params::_parseFilename( "{$dirname}/{$file}", array(), 'yml', array( 'eval' => true ) );
            endwhile;
            closedir( $_dir );
        endif;
        
        // Récupération du paramétrage personnalisé
        if( ! empty( tiFy::$Params[$this->SubDir][self::$__tFyAppClassName[$class]]['schema'] ) ) :
            $schema = wp_parse_args( tiFy::$Params[$this->SubDir][self::$__tFyAppClassName[$class]]['schema'], $schema ); 
        elseif( preg_match( '/'. preg_quote( $this->Namespace, '\\' ) .'/', self::$__tFyAppClassName[$class] )  && ! empty( tiFy::$Params[$this->SubDir][self::$__tFyAppShortName[$class]]['schema'] ) ) :
            $schema = wp_parse_args( tiFy::$Params[$this->SubDir][self::$__tFyAppShortName[$class]]['schema'], $schema ); 
        endif;
    
        // Traitement du parametrage
        foreach( (array) $schema as $id => $entity ) :
            /// Classe de rappel des données en base
            if( isset( $entity['Db'] ) ) :
                \tiFy\Core\Db\Db::Register( $id, $entity['Db'] );
            endif;
            
            /// Classe de rappel des intitulés
            \tiFy\Core\Labels\Labels::Register( $id, ( isset( $entity['Labels'] ) ? $entity['Labels'] : array() ) );
            
            /// Gabarits de l'interface d'administration
            if( isset( $entity['Admin'] ) ) :
                foreach( (array) $entity['Admin'] as $i => $tpl ) :
                    if( ! isset( $tpl['db'] ) )
                        $tpl['db'] = $id;
                    if( ! isset( $tpl['labels'] ) )
                        $tpl['labels'] = $id;
                        
                    \tiFy\Core\Templates\Templates::Register( $i, $tpl, 'admin' );
                endforeach;
            endif;
            
            /// Gabarits de l'interface utilisateur
            if( isset( $entity['Front'] ) ) :
                foreach( (array) $entity['Front'] as $i => $tpl ) :
                    if( ! isset( $tpl['db'] ) )
                        $tpl['db'] = $id;
                    if( ! isset( $tpl['labels'] ) )
                        $tpl['labels'] = $id;
                    
                    \tiFy\Core\Templates\Templates::Register( $i, $tpl, 'front' );
                endforeach;
            endif;          
        endforeach;
    }
}