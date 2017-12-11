<?php
namespace tiFy\Core\Taboox\Post\TaxonomyDropdown\Admin;

use tiFy\Deprecated\Deprecated;

class TaxonomyDropdown extends \tiFy\Core\Taboox\PostType\TaxonomyDropdown\Admin\TaxonomyDropdown
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\TaxonomyDropdown\Admin\TaxonomyDropdown', '1.2.472', '\tiFy\Core\Taboox\PostType\TaxonomyDropdown\Admin\TaxonomyDropdown');
    }
}