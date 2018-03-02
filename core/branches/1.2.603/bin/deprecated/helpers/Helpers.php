<?php
use tiFy\Deprecated\Deprecated;

/**
 * @version 1.2.391
 */
/**
 * Vérification d'affichage du formulaire de contact sur une page
 */
function tify_contact_form_is($post = 0)
{
    Deprecated::addFunction(__FUNCTION__, '1.2.391', 'tify_set_contactform_is');
    return tify_set_contactform_is($post);
}

/**
 * Récupération de la identifiant de la page d'affichage du formulaire de contact
 */
function tify_contact_form_hookpage($default = 0)
{
    Deprecated::addFunction(__FUNCTION__, '1.2.391', 'tify_set_contactform_hook_id');
    return tify_set_contactform_get($default);
}

/**
 * Affichage du formulaire de contact
 */
function tify_contact_form_display()
{
    Deprecated::addFunction(__FUNCTION__, '1.2.391', 'tify_set_contactform_display');
    return tify_set_contactform_display();
}

/**
 * @version 1.0.371
 */
/**
 * Définition dynamique de paramètre
 */
function tify_params_set($type, $param, $value, $merge = true)
{
    Deprecated::addFunction(__FUNCTION__, '1.0.371');
    exit;
}

/**
 * @version 1.0.323
 */
function tify_admin_register($id, $args = array())
{
    _deprecated_function(__FUNCTION__, '0.9.9.161008', 'tify_template_register');
    exit();
}

function tify_front_register($id, $args = array())
{
    _deprecated_function(__FUNCTION__, '0.9.9.161008', 'tify_template_register');
    exit();
}

function tify_video_toggle($target = null, $args = array())
{
    _deprecated_function(__FUNCTION__, '1.0.323', 'tify_video_modal_toggle');
    
    if (! isset($args['target']))
        $args['target'] = $target;
    
    $args['video'] = $args['attr'];
    
    return tify_video_modal_toggle($args, (isset($args['echo']) ? $args['echo'] : true));
}

function tify_modal_video_toggle($args = array(), $echo = true)
{
    _deprecated_function(__FUNCTION__, '1.0.323', 'tify_video_modal_toggle');
    
    return tify_video_modal_toggle($args, $echo);
}

function tify_modal_video($args = array(), $echo = true)
{
    _deprecated_function(__FUNCTION__, '1.0.323', 'tify_video_modal');
    
    return tify_video_modal($args, $echo);
}

/**
 *
 * @version 0.2.151228
 */
function tify_require()
{
    $replacement = 'tify_require_lib';
    _deprecated_function(__FUNCTION__, '0.2.151228', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}

/**
 *
 * @version 0.2.151209
 */
function mktzr_breadcrumb()
{
    $replacement = 'tify_breadcrumb';
    _deprecated_function(__FUNCTION__, '0.2.151209', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}

/**
 *
 * @version 0.2.151207
 */
function mktzr_paginate()
{
    $replacement = 'tify_pagination';
    _deprecated_function(__FUNCTION__, '0.2.151207', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}

/**
 *
 * @version 0.2.151204
 */
function tify_db_query()
{
    $replacement = 'tify_query';
    _deprecated_function(__FUNCTION__, '0.2.151204', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}

function tify_db_field()
{
    $replacement = 'tify_query_field';
    _deprecated_function(__FUNCTION__, '0.2.151204', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}

function tify_db_meta()
{
    $replacement = 'tify_query_meta';
    _deprecated_function(__FUNCTION__, '0.2.151204', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}

function tify_db_adjacent()
{
    $replacement = 'tify_query_get_adjacent';
    _deprecated_function(__FUNCTION__, '0.2.151204', $replacement);
    
    call_user_func_array($replacement, func_get_args());
}
