<?php
$loader = new \Psr4ClassLoader;
$loader->addNamespace( 'Theme', __DIR__.'/inc' );
$loader->register();
new Thematizer\Autoload;