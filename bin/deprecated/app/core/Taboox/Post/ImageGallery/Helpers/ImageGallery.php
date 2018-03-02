<?php
namespace tiFy\Core\Taboox\Post\ImageGallery\Helpers;

class ImageGallery extends \tiFy\Core\Taboox\PostType\ImageGallery\Helpers\ImageGallery
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\ImageGallery\Helpers\ImageGallery', '1.2.472', '\tiFy\Core\Taboox\PostType\ImageGallery\Helpers\ImageGallery');
    }
}