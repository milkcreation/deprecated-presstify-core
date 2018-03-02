<?php
namespace tiFy\Core\Taboox\Option\ColorPalette\Admin;

use tiFy\Deprecated\Deprecated;

class ColorPalette extends \tiFy\Core\Taboox\Options\ColorPalette\Admin\ColorPalette
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Option\ColorPalette\Admin\ColorPalette', '1.2.472', '\tiFy\Core\Taboox\Options\ColorPalette\Admin\ColorPalette');
    }
}