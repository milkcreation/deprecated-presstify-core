<?php

namespace tiFy\Core\Query\Controller;

use Illuminate\Support\Collection;
use tiFy\App\Traits\App as TraitsApp;

abstract class AbstractPostList extends Collection
{
    use TraitsApp;

    /**
     * CONSTRUCTEUR
     *
     * @param PostItemInterface[] $items
     *
     * @return void
     */
    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}