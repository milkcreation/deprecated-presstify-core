<?php
namespace tiFy\Core\Taboox\Post\Fileshare\Helpers;

class Fileshare extends \tiFy\Core\Taboox\PostType\Fileshare\Helpers\Fileshare
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\Fileshare\Helpers\Fileshare', '1.2.472', '\tiFy\Core\Taboox\PostType\Fileshare\Helpers\Fileshare');
    }
}