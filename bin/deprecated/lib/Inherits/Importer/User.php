<?php
namespace tiFy\Inherits\Importer;

use tiFy\Deprecated\Deprecated;

abstract class User extends \tiFy\Lib\Importer\User
{
    public function __construct($inputdata = [], $attrs = [])
    {
        parent::__construct($inputdata, $attrs);
        Deprecated::add('function', '\tiFy\Inherits\Importer\User', '1.0.420', '\tiFy\Lib\Importer\User');
    }
}