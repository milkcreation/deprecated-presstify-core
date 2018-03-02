<?php
namespace tiFy\Core\Taboox\Post\RichExcerpt\Admin;

use tiFy\Deprecated\Deprecated;

class RichExcerpt extends \tiFy\Core\Taboox\PostType\RichExcerpt\Admin\RichExcerpt
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\RichExcerpt\Admin\RichExcerpt', '1.2.472', '\tiFy\Core\Taboox\PostType\RichExcerpt\Admin\RichExcerpt');
    }
}