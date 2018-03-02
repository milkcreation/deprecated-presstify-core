<?php
namespace tiFy\Core\Taboox\Post\GoogleMap\Admin;

use tiFy\Deprecated\Deprecated;

class GoogleMap extends \tiFy\Core\Taboox\PostType\GoogleMap\Admin\GoogleMap
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\GoogleMap\Admin\GoogleMap', '1.2.472', '\tiFy\Core\Taboox\PostType\GoogleMap\Admin\GoogleMap');
    }
}