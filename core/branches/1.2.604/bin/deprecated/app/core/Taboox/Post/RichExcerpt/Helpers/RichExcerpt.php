<?php
namespace tiFy\Core\Taboox\Post\RichExcerpt\Helpers;

class RichExcerpt extends \tiFy\Core\Taboox\PostType\RichExcerpt\Helpers\RichExcerpt
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\RichExcerpt\Helpers\RichExcerpt', '1.2.472', '\tiFy\Core\Taboox\PostType\RichExcerpt\Helpers\RichExcerpt');
    }
}