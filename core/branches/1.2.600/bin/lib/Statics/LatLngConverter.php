<?php
namespace tiFy\Statics;

class LatLngConverter
{
    public static function toDecimal( $value, $type = true ) 
    {
        $decimal = '';
        preg_match( '#^(\d{1,2})°(\d{2})\'(\d{1,2}.\d{2})\"([EeWwNnSs])#', $value, $matches );

        if( count( $matches ) ===  5 ) :
            $degrees = $matches[1];
            $minutes = $matches[2]/60; 
            $seconds = $matches[3]/3600; 
            $direction = ( in_array( $matches[4], array( 'S', 's', 'W', 'w' ) ) ) ? -1 : 1;
             
            $decimal = (float) ($degrees+$minutes+$seconds)*$direction;
        endif;
        
        return $decimal;
    }
}