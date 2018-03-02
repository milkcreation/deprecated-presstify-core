<?php
namespace tiFy\Components\HookArchive;

use tiFy\Core\Taboox\Taboox;
use tiFy\Components\Breadcrumb\Breadcrumb;

final class Taxonomy extends Factory
{
    /* = ARGUMENTS = */


    /* = CONSTRUCTEUR = */
    public function __construct($args = [])
    {
        parent::__construct($args);

        add_action('registered_taxonomy', [$this, 'registered_taxonomy'], 10, 3);
        add_action('init', [$this, 'init'], 9999);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes'], 99);
        add_action('edit_form_top', [$this, 'edit_form_top'], 10);
        add_filter('term_link', [$this, 'term_link'], 10, 3);
        add_filter('post_type_link', [$this, 'post_type_link'], 10, 4);

        add_filter('tify_breadcrumb_is_single', [$this, 'tify_breadcrumb_is_single']);
        add_filter('tify_breadcrumb_is_tax', [$this, 'tify_breadcrumb_is_tax']);
        add_filter('tify_seo_title_is_tax', [$this, 'tify_seo_title_is_tax']);
        add_filter('tify_seo_desc_is_tax', [$this, 'tify_seo_desc_is_tax']);
        add_action('tify_taboox_register_node', [$this, 'tify_taboox_register_node']);
    }

    /* = ACTIONS = */
    /** == Déclaration de la taxonomie == **/
    final public function registered_taxonomy($taxonomy, $object_type, $args)
    {
        if ($this->Archive !== $taxonomy) {
            return;
        }

        global $wp_rewrite;

        $RegisteredTaxonomy = [];

        foreach ((array)$this->GetHooks() as $hook) :
            if (!$hook['term'] || !$hook['id']) {
                continue;
            }
            if (!$term = get_term($hook['term'])) {
                continue;
            }

            $RegisteredTaxonomy[$hook['id']][] = $term->slug;
        endforeach;

        foreach ((array)$RegisteredTaxonomy as $hook_id => $slugs) :
            $archive_slug = (string)$this->GetArchiveSlug($hook_id);
            $_slugs = join(',', $slugs);
            add_rewrite_rule("{$archive_slug}/?$", "index.php?{$taxonomy}={$_slugs}&tify_hook_id={$hook_id}", 'top');
            add_rewrite_rule("{$archive_slug}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$",
                "index.php?{$taxonomy}={$_slugs}&tify_hook_id={$hook_id}" . '&paged=$matches[1]', 'top');
        endforeach;

        // Empêche l'execution multiple de l'action
        remove_action('registered_taxonomy', [$this, 'registered_taxonomy']);
    }

    /** == == **/
    final public function init()
    {
        if ($this->Options['rewrite']) :
            global $wp_post_types;

            foreach ((array)$this->Options['permalink'] as $post_type) :
                if (isset($wp_post_types[$post_type])) :
                    call_user_func([$this, 'registered_post_type_for_taxonomy'], $post_type,
                        $wp_post_types[$post_type]);
                elseif (!has_action('registered_post_type', [$this, 'registered_post_type_for_taxonomy'])) :
                    add_action('registered_post_type', [$this, 'registered_post_type_for_taxonomy'], 10, 2);
                endif;
            endforeach;
        endif;
    }

    /** == == **/
    final public function registered_post_type_for_taxonomy($post_type, $args)
    {
        if (!is_object_in_taxonomy($post_type, $this->Archive)) {
            return;
        }

        // Affichage du contenu seul
        foreach ((array)$this->GetHooks() as $hook) :
            if (!$hook['term'] || !$hook['permalink'] || (!$term = get_term($hook['term']))) {
                continue;
            }
            if (is_array($hook['permalink']) && !in_array($post_type, $hook['permalink'])) {
                continue;
            }

            $archive_slug = (string)$this->GetArchiveSlug($hook['id']);

            if ($args->hierarchical) {
                add_rewrite_rule($archive_slug . "/(.+?)/?$",
                    "index.php?post_type={$post_type}&tify_hook_id={$hook['id']}" . '&pagename=$matches[1]', 'top');
            } else {
                add_rewrite_rule($archive_slug . "/([^/]+)/?$",
                    "index.php?post_type={$post_type}&tify_hook_id={$hook['id']}" . '&name=$matches[1]', 'top');
            }
        endforeach;
    }

    /** == Nettoyage des metaboxe == **/
    final public function add_meta_boxes()
    {
        foreach ((array)$this->GetHooks() as $hook) :
            if (!$hook['edit']) {
                continue;
            }
            foreach ((array)$hook['permalink'] as $post_type) :
                remove_meta_box('tagsdiv-' . $this->Archive, $post_type, 'side');
                remove_meta_box($this->Archive . 'div', $post_type, 'side');
            endforeach;
        endforeach;
    }

    /** == Affichage d'un message d'avertissement lors de l'édition du contenu d'accroche == **/
    final public function edit_form_top($post)
    {
        // Vérification de correspondance
        foreach ((array)$this->GetHooks() as $hook) :
            if ($post->post_type !== $hook['post_type']) :
                continue;
            elseif ((int)$post->ID !== $hook['id']) :
                continue;
            elseif ($term = get_term($hook['term'])) :
                break;
            endif;
        endforeach;

        // Bypass
        if (empty($term) || is_wp_error($term)) {
            return;
        }

        $label = $term->name;

        echo "<div class=\"notice notice-info inline\">\n" . "\t<p>Vous éditez actuellement la page d'affichage des \"{$label}\".</p>\n" . "</div>";
    }

    /** == == **/
    final public function term_link($termlink, $term, $taxonomy)
    {
        if ($taxonomy !== $this->Archive) {
            return $termlink;
        }

        $hook_id = 0;
        foreach ((array)$this->GetHooks() as $hook) :
            if (((int)$hook['term'] !== $term->term_id)) {
                continue;
            }
            $hook_id = $hook['id'];
            $hook_post_type = $hook['post_type'];
            break;
        endforeach;

        if (empty($hook_id) || empty($hook_post_type)) {
            return $termlink;
        }

        $archive_slug = (string)$this->GetArchiveSlug($hook_id);

        return site_url($archive_slug);
    }

    /** == == **/
    final public function post_type_link($post_link, $post, $leavename, $sample)
    {
        // Bypass
        if (!$this->Options['rewrite']) {
            return $post_link;
        }

        if (!$post->post_name) {
            return $post_link;
        }

        if (!is_object_in_taxonomy($post->post_type, $this->Archive)) {
            return $post_link;
        }

        $terms = wp_get_post_terms($post->ID, $this->Archive, ['fields' => 'ids']);
        if (is_wp_error($terms)) {
            return $post_link;
        }

        $hook_id = 0;
        foreach ((array)$this->GetHooks() as $hook) :
            if (!in_array($hook['term'], $terms) || !$hook['permalink'] || (!$term = get_term($hook['term']))) {
                continue;
            }
            if (is_array($hook['permalink']) && !in_array($post->post_type, $hook['permalink'])) {
                continue;
            }

            $permalink_term = (int)get_post_meta($post->ID, '_tify_hookarchive_term_permalink', true);
            if ($permalink_term < 0) {
                continue;
            } elseif (($permalink_term > 0) && ($permalink_term !== (int)$hook['term'])) {
                continue;
            }

            $hook_id = $hook['id'];
            $hook_post_type = $hook['post_type'];
            break;
        endforeach;

        if (empty($hook_id) || empty($hook_post_type)) {
            return $post_link;
        }

        $archive_slug = (string)$this->GetArchiveSlug($hook_id);

        return site_url($archive_slug . '/' . $post->post_name);
    }

    /* = FIL D'ARIANE = */
    /* = Page de contenu seul == */
    final public function tify_breadcrumb_is_single($output)
    {
        // Bypass
        if (!$this->Options['rewrite'] || !is_object_in_taxonomy(get_post_type(),
                $this->Archive) || (!$terms = wp_get_post_terms(get_the_ID(), $this->Archive,
                ['fields' => 'ids'])) || is_wp_error($terms)) :
        else :
            foreach ($this->GetHooks() as $hook) :
                if (!in_array($hook['term'], $terms) || !$hook['permalink'] || (!$term = get_term($hook['term']))) {
                    continue;
                }

                if (($hook_id = $hook['id']) && ($term->term_id === (int)get_post_meta(get_the_ID(),
                            '_tify_hookarchive_term_permalink', true))) {
                    break;
                }

                continue;
            endforeach;

            if (!empty($hook_id) && ($post = get_post($hook_id))) :
                $ancestors = "";
                if ($post->post_parent && $post->ancestors) :
                    $parents = (count($post->ancestors) > 1) ? array_reverse($post->ancestors) : $post->ancestors;
                    foreach ($parents as $parent) {
                        $ancestors .= sprintf('<li class="tiFyBreadcrumb-Item"><a href="%1$s" class="tiFyBreadcrumb-ItemLink">%2$s</a></li>',
                            get_permalink($parent), esc_html(wp_strip_all_tags(get_the_title($parent))));
                    }
                endif;

                $Template = $this->appGetContainer('tiFy\Components\Breadcrumb\Template');
                $part = ['name' => esc_html(wp_strip_all_tags(get_the_title()))];

                $term_link = sprintf('<li class="tiFyBreadcrumb-Item"><a href="%1$s" class="tiFyBreadcrumb-ItemLink">%2$s</a></li>',
                    get_term_link($term), get_the_title($hook_id));
                $output = $ancestors . $term_link . $Template::currentRender($part);
            endif;
        endif;

        // Empêche l'execution multiple du filtre
        remove_filter('tify_breadcrumb_is_single', __METHOD__);

        return $output;
    }

    /** == Page de flux == **/
    final public function tify_breadcrumb_is_tax($output)
    {
        if ((get_queried_object()->taxonomy !== $this->Archive) || (!$hook_id = get_query_var('tify_hook_id')) || (!$post = get_post($hook_id))) :

        else:
            $ancestors = "";
            if ($post->post_parent && $post->ancestors) :
                $parents = (count($post->ancestors) > 1) ? array_reverse($post->ancestors) : $post->ancestors;
                foreach ($parents as $parent) {
                    $ancestors .= sprintf('<li class="tiFyBreadcrumb-Item"><a href="%1$s" class="tiFyBreadcrumb-ItemLink">%2$s</a></li>',
                        get_permalink($parent), esc_html(wp_strip_all_tags(get_the_title($parent))));
                }
            endif;

            $Template = $this->appGetContainer('tiFy\Components\Breadcrumb\Template');
            $part = ['name' => esc_html(wp_strip_all_tags(get_the_title($hook_id)))];

            $output = $ancestors . $Template::currentRender($part);
        endif;

        // Empêche l'execution multiple du filtre
        remove_filter('tify_breadcrumb_is_tax', __METHOD__);

        return $output;
    }

    /** == Titre de référencement == **/
    final public function tify_seo_title_is_tax($title)
    {
        if ((get_queried_object()->taxonomy !== $this->Archive) || (!$hook_id = get_query_var('tify_hook_id')) || (!$post = get_post($hook_id))) :
        else :
            if (($seo_meta = get_post_meta($post->ID, '_tify_seo_meta', true)) && !empty($seo_meta['title'])) :
                $title = $seo_meta['title'];
            else :
                $title = $post->post_title;
            endif;
        endif;

        return $title;
    }

    /** == Description de référencement == **/
    final public function tify_seo_desc_is_tax($desc)
    {
        if ((get_queried_object()->taxonomy !== $this->Archive) || (!$hook_id = get_query_var('tify_hook_id')) || (!$post = get_post($hook_id))) :
        elseif (($seo_meta = get_post_meta($post->ID, '_tify_seo_meta', true)) && !empty($seo_meta['desc'])) :
            $desc = $seo_meta['desc'];
        endif;

        return $desc;
    }

    /**
     * Déclaration des greffons de boites à onglets de saisie
     *
     * @return void
     */
    final public function tify_taboox_register_node()
    {
        if (!$hooks = $this->GetHooks()) :
            return;
        endif;

        $post_types = [];

        foreach ($hooks as $hook) :
            if (empty($hook['permalink']) || !is_array($hook['permalink'])) :
                continue;
            endif;

            foreach ($hook['permalink'] as $post_type) :
                if (in_array($post_type, $post_types)) :
                    continue;
                endif;

                array_push($post_types, $post_type);

                Taboox::registerNode($post_type, [
                        'id'       => 'tiFyHooknameTaxonomy',
                        'title'    => get_taxonomy($this->Archive)->label,
                        'cb'       => 'tiFy\Components\HookArchive\Taboox\PostType\TermSelector\Admin\TermSelector',
                        'args'     => [
                            'taxonomy'         => $this->Archive,
                            'selector'         => 'checkbox',
                            'show_option_none' => false
                        ],
                        'position' => 0
                    ]);
            endforeach;
        endforeach;
    }
}