<?php
namespace tiFy\Core\Taboox\Post\TaxonomySelect\Admin;

use tiFy\Deprecated\Deprecated;

class TaxonomySelect extends \tiFy\Core\Taboox\PostType\TaxonomySelect\Admin\TaxonomySelect
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        Deprecated::addFunction('\tiFy\Core\Taboox\Post\TaxonomySelect\Admin\TaxonomySelect', '1.2.472', '\tiFy\Core\Taboox\PostType\TaxonomySelect\Admin\TaxonomySelect');
    }
}