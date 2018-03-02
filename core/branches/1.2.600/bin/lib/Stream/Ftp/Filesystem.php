<?php

namespace tiFy\Lib\Stream\Ftp;

class Filesystem extends \tiFy\Lib\Stream\Filesystem
{
    /**
     * CONSTRUCTEUR
     *
     * @param array $config {
     *      Liste des attributs de configuration
     *
     *      @var string $host Url du serveur FTP. défaut '127.0.0.1'.
     *      @var int $port Port de connection au serveur FTP. défaut 21.
     *      @var string $username Identifiant de connection. défaut 'anonymous'.
     *      @var string $password Mot de passe de connection. défaut 'anonymous'.
     *      @var bool $ssl Activation de la connection sécurisée. défaut false.
     *      @var int $timeout Expiration de la tentative de connection exprimée en secondes. défaut 30.
     *      @var string $root Racine du répertoire de traitement des éléments. défault '/'.
     *      @var bool $passive Activation du mode passif. défaut true.
     *      @var $permPrivate
     *      @var $permPublic
     *      @var $transferMode
     *      @var $systemType
     *      @var $ignorePassiveAddress
     *      @var $recurseManually
     *      @var $utf8
     * }
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $defaults = [
            'host'     => '127.0.0.1',
            'port'     => 21,
            'username' => 'anonymous',
            'password' => 'anonymous',
            'ssl'      => false,
            'timeout'  => 30,
            'root'     => '/',
            'passive'  => true,
        ];
        $config = array_merge($defaults, $config);

        parent::__construct(new Adapter($config));
    }

    /**
     * Parcours d'un répertoire et récupération du détail de chaque élément.
     *
     * @param  string $directory
     * @param  bool   $recursive
     *
     * @return SplFileInfo[]
     */
    public function listFilesInfos($directory = null, $recursive = false)
    {
        $directory = ltrim($directory, $this->getRoot());

        if ($items = $this->listContents($directory, $recursive)) :
            $filesinfos = [];
            foreach ($items as $infos) :
                $filename = $this->getRoot() . $infos['path'];
                $filesinfos[] = new SplFileInfo($filename, $infos);
            endforeach;

            return $filesinfos;
        endif;
    }
}