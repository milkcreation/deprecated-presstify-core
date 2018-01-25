<?php
namespace tiFy\Core\Taboox\Option\CustomHeader\Admin;

use tiFy\Deprecated\Deprecated;

class CustomHeader extends \tiFy\Core\Taboox\Options\CustomHeader\Admin\CustomHeader
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\CustomHeader\Admin\CustomHeader', '1.2.472', '\tiFy\Core\Taboox\Options\CustomHeader\Admin\CustomHeader');
    }
}