<?php
namespace tiFy\Core\Taboox\Option\Slideshow\Helpers;

class Slideshow extends \tiFy\Core\Taboox\Options\Slideshow\Helpers\Slideshow
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\Slideshow\Helpers\Slideshow', '1.2.472', '\tiFy\Core\Taboox\Options\Slideshow\Helpers\Slideshow');
    }
}