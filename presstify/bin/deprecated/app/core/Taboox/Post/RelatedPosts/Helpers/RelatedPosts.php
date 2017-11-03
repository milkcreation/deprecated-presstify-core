<?php
namespace tiFy\Core\Taboox\Post\RelatedPosts\Helpers;

class RelatedPosts extends \tiFy\Core\Taboox\PostType\RelatedPosts\Helpers\RelatedPosts
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\RelatedPosts\Helpers\RelatedPosts', '1.2.472', '\tiFy\Core\Taboox\PostType\RelatedPosts\Helpers\RelatedPosts');
    }
}