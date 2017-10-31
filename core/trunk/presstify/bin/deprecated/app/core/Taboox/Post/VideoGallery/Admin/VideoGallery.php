<?php
namespace tiFy\Core\Taboox\Post\VideoGallery\Admin;

use tiFy\Deprecated\Deprecated;

class VideoGallery extends \tiFy\Core\Taboox\PostType\VideoGallery\Admin\VideoGallery
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\VideoGallery\Admin\VideoGallery', '1.2.472', '\tiFy\Core\Taboox\PostType\VideoGallery\Admin\VideoGallery');
    }
}