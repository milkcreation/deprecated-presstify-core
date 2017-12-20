<?php
/**
 * @see https://github.com/Nicolab/php-ftp-client
 */

namespace tiFy\Core\Ui\Admin\Templates\FtpBrowser;

use tiFy\Lib\Stream\Ftp\Filesystem as Stream;

class FtpBrowser extends \tiFy\Core\Ui\Admin\Templates\Browser\Browser
{
    /**
     * Classe de rappel du système de fichier
     * @return \tiFy\Lib\Stream\Ftp\Filesystem
     */
    protected $Stream = null;

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification
     * @param array $attrs Attributs de configuration
     *
     * @return void
     */
    public function __construct($id = null, $attrs = [])
    {
        parent::__construct($id, $attrs);

        // Définition de la liste des paramètres autorisés
        $this->setAllowedParamList(
            [
                'root',
                'host',
                'username',
                'password',
                'passive',
                'port'
            ]
        );

        // Définition de la liste des paramètres par défaut
        $this->setDefaultParam(
            'root',
            '/'
        );
        $this->setDefaultParam(
            'host',
            '127.0.0.1'
        );
        $this->setDefaultParam(
            'username',
            'anonymous'
        );
        $this->setDefaultParam(
            'password',
            'anonymous'
        );
        $this->setDefaultParam(
            'passive',
            true
        );
        $this->setDefaultParam(
            'port',
            21
        );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Récupération de la classe de rappel du système de fichier
     *
     * @return \tiFy\Lib\Stream\Ftp\Filesystem
     */
    public function getStream()
    {
        if (!$this->Stream) :
            // Connection au FTP
            $this->Stream = new Stream(
                [
                    'host'     => $this->getParam('host'),
                    'username' => $this->getParam('username'),
                    'password' => $this->getParam('password'),

                    /** optional config settings */
                    'port'     => $this->getParam('port'),
                    'root'     => $this->getParam('root'),
                    'passive'  => $this->getParam('passive'),
                    'timeout'  => 30
                ]
            );
        endif;

        return $this->Stream;
    }

    /**
     * Récupération de la liste informations des fichiers/dossiers/liens pour un chemin donné
     *
     * @param string $path Chemin complet vers le répertoire à traiter
     * @param int $offset Indice du premier élément à traiter
     * @param int $per_page Nombre d'éléments à traiter
     *
     * @return \tiFy\Lib\Stream\Ftp\SplFileInfo[]
     */
    public function getFilesInfos($path = '/', $offset = 0, $per_page = -1)
    {
        // Récupération des infos fichier du repertoire
        if ($filesinfos = $this->getStream()->listFilesInfos($path)) :
            // Trie des éléments
            // @todo Créer un itérateur de trie
            // @see Symfony\Component\Finder\Iterator\SortableIterator
            uasort($filesinfos, function ($a, $b) {
                if ($a->isDir() && $b->isFile()) :
                    return -1;
                elseif ($a->isFile() && $b->isDir()) :
                    return 1;
                endif;

                return strcmp($a->getRealpath(), $b->getRealpath());
            });

            return $filesinfos;
        endif;
    }

    /**
     * Récupération de la liste informations d'un fichier/dossier/lien d'un répertoire
     *
     * @param string $filename Chemin vers le fichier/dossier/lien
     *
     * @return \tiFy\Lib\Stream\Ftp\SplFileInfo
     */
    public function getFileInfos($filename)
    {
        return;
    }
}