<?php
namespace tiFy\Core\Taboox\Post\RelatedPosts\Admin;

use tiFy\Deprecated\Deprecated;

class RelatedPosts extends \tiFy\Core\Taboox\PostType\RelatedPosts\Admin\RelatedPosts
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\RelatedPosts\Admin\RelatedPosts', '1.2.472', '\tiFy\Core\Taboox\PostType\RelatedPosts\Admin\RelatedPosts');
    }
}