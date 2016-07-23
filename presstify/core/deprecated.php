<?php
class tiFy_CoreDeprecated{
	/* = ARGUMENTS = */
	private $master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFY $master ){
		$this->master = $master;
	}
}

/** == 0.2.151209 == **/
function mktzr_breadcrumb(){
	$replacement = 'tify_breadcrumb';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );
}

/** == 0.2.151207 == **/
function mktzr_paginate(){
	$replacement = 'tify_pagination';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );
}

/** == 0.2.151204 == **/
function tify_db_query( ){
	$replacement = 'tify_query';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );
}
function tify_db_field( ){
	$replacement = 'tify_query_field';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );
}
function tify_db_meta( ){
	$replacement = 'tify_query_meta';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );
}
function tify_db_adjacent( ){
	$replacement = 'tify_query_get_adjacent';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );
}

/** == 0.2.151201 == **/
function tify_controls_enqueue(){
	$replacement = 'tify_control_enqueue';
	_deprecated_function( __FUNCTION__, '0.2.151201', $replacement );	
	
	call_user_func_array( $replacement, func_get_args() );	
}