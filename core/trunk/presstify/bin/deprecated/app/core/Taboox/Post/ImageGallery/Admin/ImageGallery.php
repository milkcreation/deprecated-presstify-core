<?php
namespace tiFy\Core\Taboox\Post\ImageGallery\Admin;

use tiFy\Deprecated\Deprecated;

class ImageGallery extends \tiFy\Core\Taboox\PostType\ImageGallery\Admin\ImageGallery
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\ImageGallery\Admin\ImageGallery', '1.2.472', '\tiFy\Core\Taboox\PostType\ImageGallery\Admin\ImageGallery');
    }
}