<?php
namespace tiFy\Core\Cache\Minify;

class Scripts extends ScriptsDependencies
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        add_action('wp_print_scripts', [$this, 'wp_print_scripts'], 100);
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Ecriture des scripts de la page
     */
    final public function wp_print_scripts()
    {
        global $wp_scripts;

        if (is_admin()) :
            return;
        endif;

        $this->initConcat();

		foreach ($wp_scripts->registered as $r) :
			$this->add($r->handle, $r->src, $r->deps, $r->ver, $r->args);
		
			foreach ((array) $r->extra as $key => $value) :
				$this->add_data($r->handle, $key, $value);
            endforeach;

			if (! in_array($r->handle, $wp_scripts->queue)) :
				continue;
            endif;
			$this->enqueue($r->handle);
		endforeach;

        $this->do_items(false, 0);
        if (!empty($this->concat['head']['src'])) :
            $this->concat['head']['deps'] = array_diff($this->concat['head']['deps'], $this->concat['head']['handles']);
            $wp_scripts->add(
                'minifyJs-header',
                site_url(
                    '/wp-content/mu-plugins/presstify/core/Cache/Minify/min/') . '?f=' .
                    join(',',
                        array_map(
                            function ($src) {
                                return trim($src, "/");
                            },
                            $this->concat['head']['src']
                        )
                    ),
                    $this->concat['head']['deps']
                );
            $wp_scripts->enqueue('minifyJs-header');
        endif;

        $this->do_items(false, 1);
        if (!empty($this->concat['footer']['src'])) :
            $this->concat['footer']['deps'] = array_diff($this->concat['footer']['deps'], $this->concat['footer']['handles']);
            $wp_scripts->add(
                'minifyJs-footer',
                site_url(
                    '/wp-content/mu-plugins/presstify/core/Cache/Minify/min/') . '?f=' .
                    join(',',
                        array_map(
                            function ($src) {
                                return trim($src, "/");
                            },
                            $this->concat['footer']['src']
                        )
                    ),
                    $this->concat['footer']['deps']
                );
            $wp_scripts->add_data('minifyJs-footer', 'group', 1);
            $wp_scripts->enqueue('minifyJs-footer');
        endif;
	}
}