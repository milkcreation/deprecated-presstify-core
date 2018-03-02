<?php
namespace tiFy\Inherits\Importer;

use tiFy\Deprecated\Deprecated;

abstract class tiFyDb extends \tiFy\Lib\Importer\tiFyDb
{
    public function __construct($inputdata = [], $attrs = [])
    {
        parent::__construct($inputdata, $attrs);
        Deprecated::add('function', '\tiFy\Inherits\Importer\tiFyDb', '1.0.420', '\tiFy\Lib\Importer\tiFyDb');
    }
}