<?php

namespace tiFy\Lib\Stream\Local;

class Filesystem extends \tiFy\Lib\Stream\Filesystem
{
    /**
     * CONSTRUCTEUR
     *
     * @param array $config {
     *
     * }
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $defaults = [
        ];
        $config = array_merge($defaults, $config);

        parent::__construct(new Adapter($config['root']));
    }
}