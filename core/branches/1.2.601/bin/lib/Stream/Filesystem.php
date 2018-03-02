<?php

namespace tiFy\Lib\Stream;

class Filesystem extends \League\Flysystem\Filesystem
{
    /**
     * Récupération du répertoire racine
     *
     * @return string
     */
    public function getRoot()
    {
        return $this->getAdapter()->getPathPrefix();
    }

    /**
     * Parcours d'un répertoire et récupération du détail de chaque élément.
     *
     * @param  string $directory
     * @param  bool   $recursive
     *
     * @return \SplFileInfo[]
     */
    public function listFilesInfos($directory = null, $recursive = false)
    {
        $directory = ltrim($directory, $this->getRoot());

        if ($items = $this->listContents($directory, $recursive)) :
            $filesinfos = [];
            foreach ($items as $infos) :
                $filename = $this->getRoot() . $infos['path'];
                $filesinfos[] = new \SplFileInfo($filename);
            endforeach;

            return $filesinfos;
        endif;
    }

    /**
     *
     */
    public function limit($offset = 0, $per_page = -1)
    {
        return new \LimitIterator($iterator, $offset, $per_page);
    }
}