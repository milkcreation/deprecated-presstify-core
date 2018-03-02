<?php
namespace tiFy\Core\Query;

class Injection extends \tiFy\App\Core
{
    /**
     * @var string|int $pageID
     * @access protected
     * @since 1.0.0
     */
    protected $pageID;

    /**
     * @var bool $postFormatSupport
     * @access protected
     * @since 1.0.0
     */
    protected $postFormatSupport;

    /**
     * @var bool $removePageFromLoop
     * @access protected
     * @since 1.0.0
     */
    protected $removePageFromLoop;

    /**
     * @var array $args
     * @access protected
     * @since 1.0.0
     */
    protected $args;

    /**
     * @var string $templatePart
     * @access protected
     * @since 1.0.0
     */
    protected $templatePart;

    /**
     * @var string Chemin vers le template à afficher avant le template original
     * @access protected
     * @since 1.0.0
     */
    protected $beforeTemplatePart;

    /**
     * @var array Arguments à passer au template à afficher avant le template original
     * @access protected
     * @since 1.0.0
     */
    protected $beforeTemplatePartArgs;

    /**
     * @var string Chemin vers le template à afficher après le template original
     * @access protected
     * @since 1.0.0
     */
    protected $afterTemplatePart;

    /**
     * @var array Arguments à passer au template à afficher après le template original
     * @access protected
     * @since 1.0.0
     */
    protected $afterTemplatePartArgs;

    /**
     * @var string Chemin vers le template à afficher avant la boucle
     * @access protected
     * @since 1.0.0
     */
    protected $beforeLoopTemplatePart;

    /**
     * @var array Arguments à passer au template à afficher avant la boucle
     * @access protected
     * @since 1.0.0
     */
    protected $beforeLoopTemplatePartArgs;

    /**
     * @var string Chemin vers le template à afficher après la boucle
     * @access protected
     * @since 1.0.0
     */
    protected $afterLoopTemplatePart;

    /**
     * @var array Arguments à passer au template à afficher après la boucle
     * @access protected
     * @since 1.0.0
     */
    protected $afterLoopTemplatePartArgs;

    /**
     * @var array $mergedArgs
     * @access protected
     * @since 1.0.0
     */
    protected $mergedArgs = [];

    /**
     * @var null|\stdClass $injectorQuery
     * @access protected
     * @since 1.0.0
     */
    protected $injectorQuery = null;

    /**
     * @var int $validatedPageID
     * @access protected
     * @since 1.0.0
     */
    protected $validatedPageID = 0;

    /**
     * Constructor method
     *
     * @param string|int $pageID The ID of the page we would like to target
     * @param string $postFormatSupport Should get_template_part support post format specific template parts
     * @param bool $removePageFromLoop Should the page content be displayed or not
     * @param array $args An array of valid arguments compatible with WP_Query
     * @param string $templatePart The template part which should be used to display posts
     * @parem string $beforeTemplatePart Chemin vers le template à afficher avant le template original
     * @param array $beforeTemplatePartArgs Arguments à passer au template à afficher avant le template original
     * @param string $afterTemplatePart Chemin vers le template à afficher après le template original
     * @param array $afterTemplatePartArgs Arguments à passer au template à afficher après le template original
     * @param string $beforeLoopTemplatePart Chemin vers le template à afficher avant la boucle
     * @param array $beforeLoopTemplatePartArgs Arguments à passer au template à afficher avant la boucle
     * @param string $afterLoopTemplatePart Chemin vers le template à afficher après la boucle
     * @param array $afterLoopTemplatePartArgs Arguments à passer au template à afficher après la boucle
     *
     *
     * @since 1.0.0
     */
    public function __construct(
        $pageID = null,
        $postFormatSupport = false,
        $removePageFromLoop = false,
        $args = [],
        $templatePart = null,
        $beforeTemplatePart = null,
        $beforeTemplatePartArgs = [],
        $afterTemplatePart = null,
        $afterTemplatePartArgs = [],
        $beforeLoopTemplatePart = null,
        $beforeLoopTemplatePartArgs = [],
        $afterLoopTemplatePart = null,
        $afterLoopTemplatePartArgs = []
    )
    {
        $this->pageID = $pageID;
        $this->postFormatSupport = $postFormatSupport;
        $this->removePageFromLoop = $removePageFromLoop;
        $this->args = $args;
        $this->templatePart = $templatePart;
        $this->beforeTemplatePart = $beforeTemplatePart;
        $this->beforeTemplatePartArgs = $beforeTemplatePartArgs;
        $this->afterTemplatePart = $afterTemplatePart;
        $this->afterTemplatePartArgs = $afterTemplatePartArgs;
        $this->beforeLoopTemplatePart = $beforeLoopTemplatePart;
        $this->beforeLoopTemplatePartArgs = $beforeLoopTemplatePartArgs;
        $this->afterLoopTemplatePart = $afterLoopTemplatePart;
        $this->afterLoopTemplatePartArgs = $afterLoopTemplatePartArgs;
        $this->validatePageID();
    }

    /**
     * Public method init()
     *
     * The init method will be use to initialize our pre_get_posts action
     *
     * @since 1.0.0
     */
    public function init()
    {
        // Initialise our pre_get_posts action
        add_action('pre_get_posts', [$this, 'preGetPosts']);
    }

    /**
     * Private method validatePageID()
     *
     * Validates the page ID passed
     *
     * @since 1.0.0
     */
    private function validatePageID()
    {
        $validatedPageID = filter_var($this->pageID, FILTER_VALIDATE_INT);
        $this->validatedPageID = $validatedPageID;
    }

    /**
     * Private method mergedArgs()
     *
     * Merge the default args with the user passed args
     *
     * @since 1.0.0
     */
    private function mergedArgs()
    {
        // Set default arguments
        if (get_query_var('paged')) :
            $currentPage = get_query_var('paged');
        elseif (get_query_var('page')) :
            $currentPage = get_query_var('page');
        else :
            $currentPage = 1;
        endif;
        $default = [
            'suppress_filters'    => true,
            'ignore_sticky_posts' => 1,
            'paged'               => $currentPage,
            'posts_per_page'      => get_option('posts_per_page'), // Set posts per page here to set the LIMIT clause etc
            'nopaging'            => false
        ];
        $mergedArgs = wp_parse_args((array)$this->args, $default);
        $this->mergedArgs = $mergedArgs;
    }

    /**
     * Public method preGetPosts()
     *
     * This is the callback method which will be hooked to the
     * pre_get_posts action hook. This method will be used to alter
     * the main query on the page specified by ID.
     *
     * @param \stdClass WP_Query The query object passed by reference
     *
     * @since 1.0.0
     */
    public function preGetPosts(\WP_Query $q)
    {
        /**
         * Bypass
         */
        if ($q->get('page_id') === get_option('page_on_front', 0))
            return;

        if (!is_admin() // Only target the front end
            && $q->is_main_query() // Only target the main query
            && $q->is_page(filter_var($this->validatedPageID, FILTER_VALIDATE_INT)) // Only target our specified page
        ) :
            // Remove the pre_get_posts action to avoid unexpected issues
            remove_action(current_action(), [$this, __METHOD__]);

            // METHODS:
            // Initiale our mergedArgs() method
            $this->mergedArgs();
            // Initiale our custom query method
            $this->injectorQuery();

            /**
             * We need to alter a couple of things here in order for this to work
             * - Set posts_per_page to the user set value in order for the query to
             *   to properly calculate the $max_num_pages property for pagination
             * - Set the $found_posts property of the main query to the $found_posts
             *   property of our custom query we will be using to inject posts
             * - Set the LIMIT clause to the SQL query. By default, on pages, `is_singular`
             *   returns true on pages which removes the LIMIT clause from the SQL query.
             *   We need the LIMIT clause because an empty limit clause inhibits the calculation
             *   of the $max_num_pages property which we need for pagination
             */
            if ($this->mergedArgs['posts_per_page']
                && true !== $this->mergedArgs['nopaging']
            ) :
                $q->set('posts_per_page', $this->mergedArgs['posts_per_page']);
            elseif (true === $this->mergedArgs['nopaging']) :
                $q->set('posts_per_page', -1);
            endif;

            // FILTERS:
            add_filter('found_posts', [$this, 'foundPosts'], PHP_INT_MAX, 2);
            add_filter('post_limits', [$this, 'postLimits'], PHP_INT_MAX, 2);

            // ACTIONS:
            /**
             * We can now add all our actions that we will be using to inject our custom
             * posts into the main query. We will not be altering the main query or the
             * main query's $posts property as we would like to keep full integrity of the
             * $post, $posts globals as well as $wp_query->post. For this reason we will use
             * post injection
             */
            add_action('loop_start', [$this, 'loopStart'], 1);
            add_action('loop_end', [$this, 'loopEnd'], 1);
        endif;
    }

    /**
     * Public method injectorQuery
     *
     * This will be the method which will handle our custom
     * query which will be used to
     * - return the posts that should be injected into the main
     *   query according to the arguments passed
     * - alter the $found_posts property of the main query to make
     *   pagination work
     *
     * @link https://codex.wordpress.org/Class_Reference/WP_Query
     * @since 1.0.0
     * @return \stdClass $this->injectorQuery
     */
    public function injectorQuery()
    {
        //Define our custom query
        $injectorQuery = new \WP_Query($this->mergedArgs);

        // Update the thumbnail cache
        update_post_thumbnail_cache($injectorQuery);

        $this->injectorQuery = $injectorQuery;

        return $this->injectorQuery;
    }

    /**
     * Public callback method foundPosts()
     *
     * We need to set found_posts in the main query to the $found_posts
     * property of the custom query in order for the main query to correctly
     * calculate $max_num_pages for pagination
     *
     * @param string $found_posts Passed by reference by the filter
     * @param stdClass \WP_Query Sq The current query object passed by refence
     *
     * @since 1.0.0
     * @return $found_posts
     */
    public function foundPosts($found_posts, \WP_Query $q)
    {
        if (!$q->is_main_query())
            return $found_posts;

        remove_filter(current_filter(), [$this, __METHOD__]);

        // Make sure that $this->injectorQuery actually have a value and is not null
        if ($this->injectorQuery instanceof \WP_Query
            && 0 != $this->injectorQuery->found_posts
        )
            return $found_posts = $this->injectorQuery->found_posts;

        return $found_posts;
    }

    /**
     * Public callback method postLimits()
     *
     * We need to set the LIMIT clause as it it is removed on pages due to
     * is_singular returning true. Witout the limit clause, $max_num_pages stays
     * set 0 which avoids pagination.
     *
     * We will also leave the offset part of the LIMIT cluase to 0 to avoid paged
     * pages returning 404's
     *
     * @param string $limits Passed by reference in the filter
     *
     * @since 1.0.0
     * @return $limits
     */
    public function postLimits($limits, \WP_Query $q)
    {
        if (!$q->is_main_query())
            return $limits;

        $posts_per_page = (int)$this->mergedArgs['posts_per_page'];
        if ($posts_per_page
            && -1 != $posts_per_page // Make sure that posts_per_page is not set to return all posts
            && true !== $this->mergedArgs['nopaging'] // Make sure that nopaging is not set to true
        ) :
            $limits = "LIMIT 0, $posts_per_page"; // Leave offset at 0 to avoid 404 on paged pages
        endif;

        return $limits;
    }

    /**
     * Public callback method loopStart()
     *
     * Callback function which will be hooked to the loop_start action hook
     *
     * @param \stdClass \WP_Query $q Query object passed by reference
     *
     * @since 1.0.0
     */
    public function loopStart(\WP_Query $q)
    {
        /**
         * Although we run this action inside our preGetPosts methods and
         * and inside a main query check, we need to redo the check here aswell
         * because failing to do so sets our div in the custom query output as well
         */

        if (!$q->is_main_query())
            return;

        /**
         * Add inline style to hide the page content from the loop
         * whenever $removePageFromLoop is set to true. You can
         * alternatively alter the page template in a child theme by removing
         * everything inside the loop, but keeping the loop
         * Example of how your loop should look like:
         *     while ( have_posts() ) {
         *     the_post();
         *         // Add nothing here
         *     }
         */
        if (true === $this->removePageFromLoop)
            echo '<div style="display:none">';
    }

    /**
     * Public callback method loopEnd()
     *
     * Callback function which will be hooked to the loop_end action hook
     *
     * @param \stdClass \WP_Query $q Query object passed by reference
     *
     * @since 1.0.0
     */
    public function loopEnd(\WP_Query $q)
    {
        /**
         * Although we run this action inside our preGetPosts methods and
         * and inside a main query check, we need to redo the check here as well
         * because failing to do so sets our custom query into an infinite loop
         */
        if (!$q->is_main_query())
            return;

        // See the note in the loopStart method
        if (true === $this->removePageFromLoop)
            echo '</div>';

        //Make sure that $this->injectorQuery actually have a value and is not null
        if (!$this->injectorQuery instanceof \WP_Query)
            return;

        // Setup a counter as wee need to run the custom query only once
        static $count = 0;

        /**
         * Only run the custom query on the first run of the loop. Any consecutive
         * runs (like if the user runs the loop again), the custom posts won't show.
         */
        if (0 === (int)$count) :
            // We will now add our custom posts on loop_end
            $this->injectorQuery->rewind_posts();

            // Create our loop
            if ($this->injectorQuery->have_posts()) :
                /**
                 * Fires before the loop to add pagination.
                 *
                 * @since 1.0.0
                 *
                 * @param \stdClass $this ->injectorQuery Current object (passed by reference).
                 */
                do_action('injection_before_loop_pagination', $this->injectorQuery);

                if (!is_null($this->beforeLoopTemplatePart))
                    self::tFyAppGetTemplatePart($this->beforeLoopTemplatePart, null, $this->beforeLoopTemplatePartArgs);

                // Add a static counter for those who need it
                static $counter = 0;

                while ($this->injectorQuery->have_posts()) :
                    $this->injectorQuery->the_post();

                    /**
                     * Fires before get_template_part.
                     *
                     * @since 1.0.0
                     *
                     * @param int $counter (passed by reference).
                     */
                    do_action('injection_counter_before_template_part', $counter);

                    /**
                     * Fires before get_template_part.
                     *
                     * @since 1.0.0
                     *
                     * @param \stdClass $this ->injectorQuery-post Current post object (passed by reference).
                     * @param \stdClass $this ->injectorQuery Current object (passed by reference).
                     */
                    do_action('injection_current_post_and_object', $this->injectorQuery->post, $this->injectorQuery);

                    /**
                     * Load our custom template part as set by the user
                     *
                     * We will also add template support for post formats. If $this->postFormatSupport
                     * is set to true, get_post_format() will be automatically added in get_template part
                     *
                     * If you have a template called content-video.php, you only need to pass 'content'
                     * to $template part and then set $this->postFormatSupport to true in order to load
                     * content-video.php for video post format posts
                     */
                    $part = '';
                    if (true === $this->postFormatSupport)
                        $part = get_post_format($this->injectorQuery->post->ID);

                    if (!is_null($this->beforeTemplatePart))
                        self::tFyAppGetTemplatePart($this->beforeTemplatePart, null, $this->beforeTemplatePartArgs);

                    get_template_part(
                        filter_var($this->templatePart, FILTER_SANITIZE_STRING),
                        $part
                    );

                    if (!is_null($this->afterTemplatePart))
                        self::tFyAppGetTemplatePart($this->afterTemplatePart, null, $this->afterTemplatePartArgs);

                    /**
                     * Fires after get_template_part.
                     *
                     * @since 1.0.0
                     *
                     * @param int $counter (passed by reference).
                     */
                    do_action('injection_counter_after_template_part', $counter);

                    $counter++; //Update the counter
                endwhile;

                wp_reset_postdata();

                if (!is_null($this->afterLoopTemplatePart))
                    self::tFyAppGetTemplatePart($this->afterLoopTemplatePart, null, $this->afterLoopTemplatePartArgs);

                /**
                 * Fires after the loop to add pagination.
                 *
                 * @since 1.0.0
                 *
                 * @param \stdClass $this ->injectorQuery Current object (passed by reference).
                 */
                do_action('injection_after_loop_pagination', $this->injectorQuery);
            endif;
        endif;

        // Update our static counter
        $count++;
    }
}