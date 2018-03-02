<?php
namespace tiFy\Core\Cache\Minify;

class ScriptsDependencies extends \WP_Dependencies
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
            'head'   => [
                'src'     => [],
                'deps'    => [],
                'handles' => []
            ],
            'footer' => [
                'src'     => [],
                'deps'    => [],
                'handles' => []
            ]
        ];
        $this->concat = wp_parse_args($this->concat, $defaults );
    }

    /**
     * Ajout d'une concaténation
     */
    protected function addConcat($handle, $src, $deps = [], $group = 0)
    {
        $g = (0 === $group) ? 'head' : 'footer';
        // Sources
        array_push($this->concat[$g]['src'], '//' . trim($src, '/'));
        // Accroches
        array_push($this->concat[$g]['handles'], $handle);
        // Dépendances
        foreach ($deps as $dep) :
            array_push($this->concat[$g]['deps'], $dep);
        endforeach;
    }

    /**
     *
     */
    public function do_item($handle, $group = false)
    {
        global $wp_scripts;

        if (!parent::do_item($handle)) :
            return false;
        endif;

        if (0 === $group && $this->groups[$handle] > 0) :
            $this->in_footer[] = $handle;
            return false;
        endif;

        if (false === $group && in_array($handle, $this->in_footer, true)) :
            $this->in_footer = array_diff($this->in_footer, (array)$handle);
        endif;

        $src = $this->registered[$handle]->src;

        if (preg_match('#' . preg_quote(site_url()) . '#', $src)) :
            $src = preg_replace('#' . preg_quote(site_url()) . '#', '', $src);
        endif;

        if (!file_exists(ABSPATH . $src)) :
            return false;
        endif;

        // @TODO à inclure dans la minification actuellement envoie en entête
        $wp_scripts->print_extra_script($handle);
        $wp_scripts->dequeue($handle);

        $this->addConcat($handle, $src, $this->registered[$handle]->deps, $group);

        return true;
    }

    /**
     *
     */
    public function set_group($handle, $recursion, $group = false)
    {
        if ($this->registered[$handle]->args === 1) :
            $grp = 1;
        else :
            $grp = (int)$this->get_data($handle, 'group');
        endif;

        if (false !== $group && $grp > $group) :
            $grp = $group;
        endif;

        return parent::set_group($handle, $recursion, $grp);
    }
}