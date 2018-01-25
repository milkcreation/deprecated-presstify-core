<?php
namespace tiFy\Core\Taboox\Option\ContentHook\Helpers;

class ContentHook extends \tiFy\Core\Taboox\Options\ContentHook\Helpers\ContentHook
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\ContentHook\Helpers\ContentHook', '1.2.472', '\tiFy\Core\Taboox\Options\ContentHook\Helpers\ContentHook');
    }
}