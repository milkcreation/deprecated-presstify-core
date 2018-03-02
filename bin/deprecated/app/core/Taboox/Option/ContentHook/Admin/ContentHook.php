<?php
namespace tiFy\Core\Taboox\Option\ContentHook\Admin;

use tiFy\Deprecated\Deprecated;

class ContentHook extends \tiFy\Core\Taboox\Options\ContentHook\Admin\ContentHook
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\ContentHook\Admin\ContentHook', '1.2.472', '\tiFy\Core\Taboox\Options\ContentHook\Admin\ContentHook');
    }
}