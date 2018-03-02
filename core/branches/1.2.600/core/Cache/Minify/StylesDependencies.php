<?php
namespace tiFy\Core\Cache\Minify;

class StylesDependencies extends \WP_Dependencies
{
    /**
     * @var array
     */
    protected $concat = [];

    /**
     * CONTROLEURS
     */
    /**
     * Initialisation des concaténations
     */
    protected function initConcat()
    {
        $defaults = [
            'src' 		=> [],
            'deps' 		=> [],
            'handles' 	=> []
        ];
        $this->concat = \wp_parse_args($this->concat, $defaults);
    }

    /**
     * Ajout d'une concaténation
     */
    protected function addConcat($handle, $src, $deps = [])
    {
        // Sources
        array_push($this->concat['src'], '//' . trim($src, '/'));
        // Accroches
        array_push($this->concat['handles'], $handle);
        // Dépendances
        foreach ($deps as $dep) :
            array_push($this->concat['deps'], $dep);
        endforeach;
    }

    /**
     *
     */
    public function do_item($handle)
    {
        global $wp_styles;

        if (!parent::do_item($handle)) :
            return false;
        endif;

        $src = $this->registered[$handle]->src;

        if (preg_match('#' . preg_quote(site_url()) . '#', $src)) :
            $src = preg_replace('#' . preg_quote(site_url('/')) . '#', '', $src);
        endif;

        if (!file_exists(ABSPATH . $src)) :
            return false;
        endif;

        $wp_styles->dequeue($handle);

        $this->addConcat($handle, $src, $this->registered[$handle]->deps);

        return true;
    }
}