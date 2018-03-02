<?php
namespace tiFy;

use tiFy\tiFy;

class Helpers
{
    public function __construct()
    {
        require tiFy::$AbsDir . '/bin/helpers/Components.php';
        require tiFy::$AbsDir . '/bin/helpers/Core.php';
        require tiFy::$AbsDir . '/bin/helpers/Lib.php';
    }
}