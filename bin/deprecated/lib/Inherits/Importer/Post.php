<?php
namespace tiFy\Inherits\Importer;

use tiFy\Deprecated\Deprecated;

abstract class Post extends \tiFy\Lib\Importer\Post
{
    public function __construct($inputdata = [], $attrs = [])
    {
        parent::__construct($inputdata, $attrs);
        Deprecated::add('function', '\tiFy\Inherits\Importer\Post', '1.0.420', '\tiFy\Lib\Importer\Post');
    }
}