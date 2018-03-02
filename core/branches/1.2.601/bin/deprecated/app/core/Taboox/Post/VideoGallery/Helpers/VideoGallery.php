<?php
namespace tiFy\Core\Taboox\Post\VideoGallery\Helpers;

class VideoGallery extends \tiFy\Core\Taboox\PostType\VideoGallery\Helpers\VideoGallery
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\VideoGallery\Helpers\VideoGallery', '1.2.472', '\tiFy\Core\Taboox\PostType\VideoGallery\Helpers\VideoGallery');
    }
}