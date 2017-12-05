<?php
namespace tiFy\Core\Taboox\Post;

use tiFy\Deprecated\Deprecated;

abstract class Admin extends \tiFy\Core\Taboox\PostType\Admin
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\PostType\Admin', '1.2.472', '\tiFy\Core\Taboox\PostType\Admin');
    }
}