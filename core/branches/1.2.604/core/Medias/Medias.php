<?php
namespace tiFy\Core\Medias;

class Medias extends \tiFy\App\Core
{
    /**
     * CONSTRUCTEUR
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Chargement des controleurs
        new Download;

        // Déclaration des événements de déclenchement
        $this->appAddFilter('wp_get_attachment_url', null, 10, 2);
        $this->appAddFilter('get_attached_file', null, 10, 2);
        $this->appAddFilter('wp_calculate_image_srcset', null, 10, 5);
    }
        
    /**
     * DECLENCHEURS
     */
    /**
     * Modification de l'url d'accès à un attachment uploadé avec l'import tiFy
     * @see \tiFy\Statics\Media::import()
     * @see \wp_get_attachment_url()
     * 
     * @param string $file Url d'accès original
     * @param int $attachment_id ID de l'attachment
     * 
     * @return string
     */
    public function wp_get_attachment_url($url, $post_id)
    {
        // Bypass
        if (! $metadata = \get_post_meta($post_id, '_wp_attachment_metadata', true))
            return $url;
        if (! isset($metadata['upload_dir']))
            return $url;

        if ($file = get_post_meta($post_id, '_wp_attached_file', true)) :
            $url = $metadata['upload_dir']['baseurl'] . "/$file";
        else :
            $url = get_the_guid($post_id);
        endif;

        if (is_ssl() && ! is_admin() && 'wp-login.php' !== $GLOBALS['pagenow']) :
            $url = set_url_scheme($url);
        endif;
        
        return $url;
    }
    
    /**
     * Modification du chemin d'accès vers un attachment uploadé avec l'import tiFy
     * @see \tiFy\Statics\Media::import()
     * @see \get_attached_file()
     * 
     * @param string $file Chemin d'accès original
     * @param int $attachment_id ID de l'attachment
     * 
     * @return string
     */
    public function get_attached_file($file, $attachment_id)
    {
        // Bypass
        if (!$metadata = \get_post_meta($attachment_id, '_wp_attachment_metadata', true)) :
            return $file;
        endif;
        if (!isset($metadata['upload_dir'])) :
            return $file;
        endif;
        
        $file = get_post_meta($attachment_id, '_wp_attached_file', true );
        $file = "{$metadata['upload_dir']['basedir']}/{$file}";
        
        return $file;
    }
    
    /**
     * Calcul des sources images inclus dans l'attribut 'srcset'
     * @see \wp_calculate_image_srcset()
     * 
     * @param array $sources
     * @param array $size_array Tableau de valeurs de la largeur et la hauteur en pixels (dans cet ordre).
     * @param string $image_src Chemin 'src' de l'image.
     * @param array $image_meta Liste des métadonnées de l'image retournées par 'wp_get_attachment_metadata()'.
     * @param int $attachment_id ID de l'attachment.
     * 
     * @return array
     */
    public function wp_calculate_image_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id)
    {
        // Bypass
        if (!$metadata = \get_post_meta($attachment_id, '_wp_attachment_metadata', true)) :
            return $sources;
        endif;
        if (!isset($metadata['upload_dir'])) :
            return $sources;
        endif;
        
        foreach($sources as &$attrs) :
            $attrs['url'] = $metadata['upload_dir']['url'] . '/' . basename($attrs['url']);
        endforeach; 
        
        return $sources;
    }
}