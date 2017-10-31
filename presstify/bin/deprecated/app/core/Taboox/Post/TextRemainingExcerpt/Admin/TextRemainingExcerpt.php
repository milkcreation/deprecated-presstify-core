<?php
namespace tiFy\Core\Taboox\Post\TextRemainingExcerpt\Admin;

use tiFy\Deprecated\Deprecated;

class TextRemainingExcerpt extends \tiFy\Core\Taboox\PostType\TextRemainingExcerpt\Admin\TextRemainingExcerpt
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\TextRemainingExcerpt\Admin\TextRemainingExcerpt', '1.2.472', '\tiFy\Core\Taboox\PostType\TextRemainingExcerpt\Admin\TextRemainingExcerpt');
    }
}