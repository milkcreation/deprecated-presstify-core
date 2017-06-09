<?php
namespace
{
	/** == == **/
	/** == Déclaration == **/
	function tify_admin_register( $id, $args = array() )
	{
		_deprecated_function( __FUNCTION__, '0.9.9.161008', 'tify_template_register' );
		exit;
	}

	/** == Déclaration == **/
	function tify_front_register( $id, $args = array() )
	{
		_deprecated_function( __FUNCTION__, '0.9.9.161008', 'tify_template_register' );
		exit;
	}
	
	/** == 1.0.323 == **/
	function tify_video_toggle( $target = null, $args = array() )
	{
		_deprecated_function( __FUNCTION__, '1.0.323', 'tify_video_modal_toggle' );	
		
		if( ! isset( $args['target'] ) )
			$args['target'] = $target;
		
		$args['video'] = $args['attr'];
		
		return tify_video_modal_toggle( $args, ( isset( $args['echo'] ) ? $args['echo'] : true ) );
	}
	
	/** == 1.0.323 == **/
	function tify_modal_video_toggle( $args = array(), $echo = true )
	{
	    _deprecated_function( __FUNCTION__, '1.0.323', 'tify_video_modal_toggle' );
		
		return tify_video_modal_toggle( $args, $echo );
	}
	
	/** == 1.0.323 == **/
	function tify_modal_video( $args = array(), $echo = true  )
	{
	     _deprecated_function( __FUNCTION__, '1.0.323', 'tify_video_modal' );
	     
	     return tify_video_modal( $args, $echo );
	}
	
	/** == 0.2.151228 == **/
	function tify_require(){
		$replacement = 'tify_require_lib';
		_deprecated_function( __FUNCTION__, '0.2.151228', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
	
	/** == 0.2.151209 == **/
	function mktzr_breadcrumb(){
		$replacement = 'tify_breadcrumb';
		_deprecated_function( __FUNCTION__, '0.2.151209', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
	
	/** == 0.2.151207 == **/
	function mktzr_paginate(){
		$replacement = 'tify_pagination';
		_deprecated_function( __FUNCTION__, '0.2.151207', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
	
	/** == 0.2.151204 == **/
	function tify_db_query( ){
		$replacement = 'tify_query';
		_deprecated_function( __FUNCTION__, '0.2.151204', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
	function tify_db_field( ){
		$replacement = 'tify_query_field';
		_deprecated_function( __FUNCTION__, '0.2.151204', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
	function tify_db_meta( ){
		$replacement = 'tify_query_meta';
		_deprecated_function( __FUNCTION__, '0.2.151204', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
	function tify_db_adjacent( ){
		$replacement = 'tify_query_get_adjacent';
		_deprecated_function( __FUNCTION__, '0.2.151204', $replacement );	
		
		call_user_func_array( $replacement, func_get_args() );
	}
}