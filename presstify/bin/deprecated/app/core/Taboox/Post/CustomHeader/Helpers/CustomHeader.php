<?php
namespace tiFy\Core\Taboox\Post\CustomHeader\Helpers;

class CustomHeader extends \tiFy\Core\Taboox\PostType\CustomHeader\Helpers\CustomHeader
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\CustomHeader\Helpers\CustomHeader', '1.2.472', '\tiFy\Core\Taboox\PostType\CustomHeader\Helpers\CustomHeader');
    }
}