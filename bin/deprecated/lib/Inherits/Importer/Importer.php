<?php
namespace tiFy\Inherits\Importer;

use tiFy\Deprecated\Deprecated;

abstract class Importer extends \tiFy\Lib\Importer\Importer
{
    public function __construct($inputdata = [], $attrs = [])
    {
        parent::__construct($inputdata, $attrs);
        Deprecated::add('function', '\tiFy\Inherits\Importer\Importer', '1.0.420', '\tiFy\Lib\Importer\Importer');
    }
}