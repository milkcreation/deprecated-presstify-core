<?php
namespace tiFy\Inherits;

use tiFy\Deprecated\Deprecated;

class Csv extends \tiFy\Lib\Csv\Csv
{
    public function __construct($options = [])
    {
        parent::__construct($options);
        Deprecated::add('function', '\tiFy\Inherits\Csv', '1.0.420', '\tiFy\Lib\Csv\Csv');
    }
}