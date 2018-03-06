<?php

namespace tiFy\Core\Medias;

use Symfony\Component\Finder\SplFileInfo as SfSplFileInfo;

class GlideSplFileInfo extends SfSplFileInfo
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    public function __construct($file, $abspath)
    {
        if (!preg_match("#^" . preg_quote($abspath, '/') . "#", $file)) :
            throw new \UnexpectedValueException(__('Le fichier ne fait pas partie du repertoire de stockage des ressources', 'theme'));
        endif;

        $rel_pathname = preg_replace("#^" . preg_quote($abspath, '/') . "#", '', $file);

        parent::__construct($file, dirname($rel_pathname), $rel_pathname);
    }
}