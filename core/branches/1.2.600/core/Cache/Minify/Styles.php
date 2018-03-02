<?php
namespace tiFy\Core\Cache\Minify;

use tiFy\tiFy;

class Styles extends StylesDependencies
{
    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
	public function __construct()
    {
        add_action('wp_print_styles', [$this, 'wp_print_styles'], 100);
	}

    /**
     * DECLENCHEURS
     */
    /**
     * Ecriture des styles de la page
     */
    final public function wp_print_styles()
    {
        global $wp_styles;

        if (is_admin()) :
            return;
        endif;

        $this->initConcat();

        if (!empty($wp_styles->registered)) :
            foreach ((array)$wp_styles->registered as $r) :
                $this->add($r->handle, $r->src, $r->deps, $r->ver, $r->args);

                if (!empty($r->extra)) :
                    foreach ((array) $r->extra as $k => $v) :
                        $this->add_data($r->handle, $k, $v);
                    endforeach;
                endif;

                if(! in_array($r->handle, $wp_styles->queue)) :
                    continue;
                endif;

                $this->enqueue($r->handle);
            endforeach;
        endif;

        $this->do_items();

        if (!empty($this->concat['src'])) :
            $this->concat['deps'] = array_diff($this->concat['deps'], $this->concat['handles']);
            $wp_styles->add(
                'minifyCss',
                site_url('/wp-content/mu-plugins/presstify/core/Cache/Minify/min/') . '?f=' . join(',', array_map(create_function('$src', 'return trim( $src, "/" );'), $this->concat['src'])), $this->concat['deps']
            );
            $wp_styles->enqueue('minifyCss');
        endif;
    }
}