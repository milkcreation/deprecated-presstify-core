<?php
namespace tiFy\Inherits\Importer;

use tiFy\Deprecated\Deprecated;

abstract class Taxonomy extends \tiFy\Lib\Importer\Taxonomy
{
    public function __construct($inputdata = [], $attrs = [])
    {
        parent::__construct($inputdata, $attrs);
        Deprecated::add('function', '\tiFy\Inherits\Importer\Taxonomy', '1.0.420', '\tiFy\Lib\Importer\Taxonomy');
    }
}