<?php

namespace tiFy\Core\Medias;

use League\Glide\Urls\UrlBuilderFactory;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Signatures\SignatureException;
use Symfony\Component\Finder\Finder;
use tiFy\App\Traits\App as TraitsApp;
use tiFy\Core\Route\Route;

class Glide
{
    use TraitsApp;

    /**
     * @var string
     */
    private $BaseUrl = '/shop.pixvert.fr';

    /**
     * Url vers l'image de remplacement
     * @var null|string
     */
    private $HolderImageUrl = null;

    /**
     * Clé de signature privée de requête de récupération des images
     * @var string
     */
    private $SignKey = NONCE_KEY;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        // Déclenchement des événements
        $this->appAddAction('tify_route_register');
    }

    /**
     * Déclaration de la route d'affichage de l'image
     *
     * @return void
     */
    public function tify_route_register()
    {
        Route::register(
            'pixvert-medias',
            [
                'path' => '/img/{path:.+}',
                'cb'   => function ($path) {
                    try {
                        SignatureFactory::create($this->_getSignKey())
                            ->validateRequest(
                                $this->getBaseUrl() . '/img/' . $path,
                                $_GET
                            );
                    } catch (SignatureException $e) {
                        // @todo rendu de l'image de remplacement par défaut
                        exit;
                    }
                    $server = ServerFactory::create([
                        'source'   => PIXVERT_RESOURCES . '/medias',
                        'cache'    => ABSPATH . 'wp-content/uploads/cache'
                    ]);

                    try {
                        $server->outputImage($path, $_GET);
                    } catch (FileNotFoundException $e) {
                        // @todo rendu de l'image de remplacement par défaut
                        exit;
                    }
                    exit;
                }
            ]
        );
    }

    /**
     * Récupération de la clé de signature de requête de récupération des images
     *
     * @return string
     */
    private function _getSignKey()
    {
        return $this->SignKey;
    }

    /**
     * Chemin absolu vers le répertoire la racine des fichiers médias
     *
     * @return string
     */
    public function getAbspath()
    {
        return PIXVERT_RESOURCES . '/medias';
    }

    /**
     * Normalisation du chemin vers un repertoire ou vers un fichier
     *
     * @param string $path
     *
     * @return string
     */
    public function normalizePath($path)
    {
        return '/' . ltrim(rtrim($path, '/'), '/');
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->normalizePath($this->BaseUrl);
    }

    /**
     * Url vers l'image de remplacement par défaut
     *
     * @return string
     */
    public function getHolderImageUrl($args = [])
    {
        if (!is_null($this->HolderImageUrl)) :
            return $this->HolderImageUrl;
        endif;

        $urlBuilder = UrlBuilderFactory::create(
            $this->getBaseUrl() . '/img',
            $this->_getSignKey()
        );

        return $this->HolderImageUrl = $urlBuilder->getUrl(
            '/common/holder.png',
            array_merge(
                $args
            )
        );
    }

    /**
     * Url vers l'image représentative d'un produit
     *
     * @param string $pathname Chemin du fichier image
     * @param \Pixvert\Pattern\Common\ItemInterface string $item_controller
     *
     * @return string
     */
    public function getImageUrl($pathname = '', $args = [])
    {
        if (!$pathname || !file_exists($pathname)) :
            return $this->getHolderImageUrl($args);
        endif;

        try{
            $file = new GlideSplFileInfo($pathname, $this->getAbspath());
        } catch (\UnexpectedValueException $e) {
            return $this->getHolderImageUrl($args);
        }

        $urlBuilder = UrlBuilderFactory::create(
            $this->getBaseUrl() . '/img',
            $this->_getSignKey()
        );

        try {
            $url = $urlBuilder->getUrl(
                $file->getRelativePath() . '/' .  $file->getFilename(),
                $args
            );
        } catch (\InvalidArgumentException $e) {
            $url = $this->getHolderImageUrl($args);
        }

        return $url;
    }

    /**
     * Récupération du chemin vers un fichier
     *
     * @param string $filename Nom du fichier à retrouver
     * @param string $dirs Liste des répertoires à inspecter par ordre de priorité
     * @param string $regex Recherche du nom. ex. images seules : '/^%s(.jpg|.jpeg|.png)?$/'
     *
     * @return string
     */
    public function findPathname($filename, $dirs = [], $regex = '%s')
    {
        // Répertoire par défaut
        $dirs[] = $this->getAbspath() . '/common';

        // Test d'existance de la ressource
        $_dirs = [];
        foreach($dirs as $dir) :
            if (is_dir($dir)) :
                $_dirs[] = $dir;
            endif;
        endforeach;

        if (empty($_dirs)) :
            return '';
        endif;

        $finder = new Finder();
        $finder
            ->in($_dirs)
            ->depth('== 0')
            ->files()
            ->name(sprintf($regex, $filename));

        foreach ($finder as $file) :
            return $file->getPathname();
        endforeach;

        return '';
    }

    /**
     * PERSONNALISATION
     */
    /**
     * Récupération de l'identifiant de qualification de la filiale
     *
     * @return string
     */
    private function _getCompany()
    {
        return get_site_option('_company_pxv_id', '00A');
    }

    /**
     * Suppression du préfixe de filiale d'un identifiant pixvert
     *
     * @return string
     */
    private function _sanitizeCompany($pxv_id)
    {
        if ($trim = ltrim($pxv_id, $this->_getCompany() . '-')) :
            return $trim;
        endif;

        return $pxv_id;
    }

    /**
     * Chemin absolu vers le répertoire des fichiers d'import
     *
     * @return string
     */
    public function getImportPath()
    {
        return PIXVERT_RESOURCES . '/transaction/in';
    }

    /**
     * Chemin absolu vers le répertoire des fichiers d'export
     *
     * @return string
     */
    public function getExportPath()
    {
        return PIXVERT_RESOURCES . '/transaction/out';
    }

    /**
     * Récupération de la liste des chemins réels disponibles
     *
     * @param \Pixvert\Pattern\Common\ItemInterface $item_controller
     *
     * @return array
     */
    public function getRealPathList(\Pixvert\Pattern\Common\ItemInterface $item_controller)
    {
        if (!$base_dirs = (array)$item_controller->getBaseDirs()) :
            return [];
        endif;

        $realpath = [];
        foreach ($base_dirs as $k => $dir) :
            if ($path = $this->getRealPath($dir, $item_controller)) :
                $realpath[] = $path;
            endif;
        endforeach;

        return $realpath;
    }

    /**
     * Récupération du chemin réel vers répertoire de stockage de fichier
     * @internal Remplace les occurences {company}|{school}|{shop}|{student_class}}|{student} par leur identifiant de correspondance
     *
     * @param string $dir Chemin relatif vers un sous-repertoire de dossier de stockage des médias
     * @param \Pixvert\Pattern\Common\ItemInterface $item_controller
     *
     * @return string
     */
    public function getRealPath($dir, \Pixvert\Pattern\Common\ItemInterface $item_controller)
    {
        if (preg_match("#\{([a-z_]+)\}#", $dir)) :
            $dir = preg_replace_callback(
                "#\{([a-z_]+)\}#",
                function ($matches) use ($item_controller) {
                    switch ($matches[1]) :
                        case 'company' :
                            return $this->_getCompany();
                            break;

                        case 'school' :
                            return ($pxv_id = $item_controller->getSchoolPxvId())
                                ? $this->_sanitizeCompany($pxv_id)
                                : $matches[0];
                            break;

                        case 'shop' :
                            return ($pxv_id = $item_controller->getShopPxvId())
                                ? $this->_sanitizeCompany($pxv_id)
                                : $matches[0];
                            break;

                        case 'student_class' :
                            return ($pxv_id = $item_controller->getStudentClassPxvId())
                                ? $this->_sanitizeCompany($pxv_id)
                                : $matches[0];
                            break;

                        case 'student' :
                            return ($pxv_id = $item_controller->getStudentPxvId())
                                ? $this->_sanitizeCompany($pxv_id)
                                : $matches[0];
                            break;

                        default :
                            return $matches[0];
                            break;
                    endswitch;
                },
                $dir
            );
        endif;

        if (!preg_match("#\{([a-z_]+)\}#", $dir)) :
            return $this->getAbspath() . $this->normalizePath($dir);
        endif;

        return '';
    }
}