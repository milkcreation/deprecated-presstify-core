<?php
namespace tiFy\Core\Taboox\Option\RelatedPosts\Admin;

use tiFy\Deprecated\Deprecated;

class RelatedPosts extends \tiFy\Core\Taboox\Options\RelatedPosts\Admin\RelatedPosts
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\RelatedPosts\Admin\RelatedPosts', '1.2.472', '\tiFy\Core\Taboox\Options\RelatedPosts\Admin\RelatedPosts');
    }
}