<?php
namespace tiFy\Core\Taboox\Option\MiscLinks\Admin;

use tiFy\Deprecated\Deprecated;

class MiscLinks extends \tiFy\Core\Taboox\Options\MiscLinks\Admin\MiscLinks
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\MiscLinks\Admin\MiscLinks', '1.2.472', '\tiFy\Core\Taboox\Options\MiscLinks\Admin\MiscLinks');
    }
}