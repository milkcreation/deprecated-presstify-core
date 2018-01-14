<?php

namespace tiFy\Components\TinyMCE\ExternalPlugins\Dashicons;

use \tiFy\Components\TinyMCE\TinyMCE;

class Dashicons extends \tiFy\App
{
    private $options;

    private $glyphs;

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration du plugin
        TinyMCE::registerExternalPlugin('dashicons', self::tFyAppUrl() . '/plugin.js');

        // Déclaration des événements
        $this->appAddAction('init');
        $this->appAddAction('admin_init');
        $this->appAddAction('admin_enqueue_scripts');
        $this->appAddAction('admin_head');
        $this->appAddAction('admin_print_styles');
        $this->appAddAction('wp_enqueue_scripts');
    }

    /**
     * EVENEMENTS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    final public function init()
    {
        // Déclaration des options
        $this->options = [
            // Nom d'accroche pour la mise en file de la police de caractères
            'hookname'     => 'dashicons',
            // Url vers la police css
            'css'          => includes_url() . 'css/dashicons.css',
            // Numero de version pour la mise en file d'appel la police de caractères
            'version'      => '4.1',
            // Dépendance pour la mise en file d'appel la police de caractères
            'dependencies' => [],
            // Préfixe des classes de la police de caractères
            'prefix'       => 'dashicons',
            // Famille de la police
            'font-family'  => 'dashicons',
            // Suffixe de la classe du bouton de l'éditeur (doit être contenu dans la police)
            'button'       => 'wordpress-alt',
            // Infobulle du bouton et titre de la boîte de dialogue
            'title'        => __('Police de caractères Wordpress', 'tify'),
            // Nombre d'éléments par ligne
            'cols'         => 24,
        ];
        // Déclaration des scripts
        wp_register_style($this->options['hookname'], $this->options['css'], $this->options['dependencies'],
            $this->options['version']);
        wp_register_style('tinymce-dashicons', self::tFyAppUrl() . '/plugin.css', [], '20141219');

        // Récupération des glyphs
        $css_path = tify_get_relative_url($this->options['css']);
        $css = file_get_contents(ABSPATH . $css_path);
        preg_match_all('/.dashicons-(.*):before\s*\{\s*content\:\s*"(.*)"(;?|)\s*\}\s*/', $css, $matches);
        foreach ($matches[1] as $i => $class) {
            $this->glyphs[$class] = $matches[2][$i];
        }
    }

    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    final public function admin_init()
    {
        if ((current_user_can('edit_posts') || current_user_can('edit_pages')) && get_user_option('rich_editing')) {
            add_filter('mce_css', [$this, 'mce_css']);
        }
    }

    /**
     * Mise en file des scripts de l'interface d'administration
     *
     * @return void
     */
    final public function admin_enqueue_scripts()
    {
        wp_enqueue_style($this->options['hookname']);
        wp_enqueue_style('tinymce-dashicons');
    }

    /** == Personnalisation des scripts de l'entête == **/
    final public function admin_head()
    {
        ?>
        <script type="text/javascript">/* <![CDATA[ */
            var dashiconsChars = <?php echo $this->get_css_glyphs();?>,
                tinymceDashiconsl10n = {'title': '<?php echo $this->options['title'];?>'};
            /* ]]> */</script><?php
    }

    /** == Personnalisation des styles de l'entête == **/
    final public function admin_print_styles()
    {
        ?>
        <style type="text/css">i.mce-i-dashicons:before {
            content: "<?php echo $this->glyphs[$this->options['button']];?>";
        }

        i.mce-i-dashicons:before, .mce-grid a.dashicons {
            font-family: <?php echo $this->options['font-family'];?> !important;
        }</style><?php
    }

    /** == Mise en file des scripts == **/
    final public function wp_enqueue_scripts()
    {
        wp_enqueue_style($this->options['hookname']);
    }

    /** == Ajout des styles dans l'éditeur == **/
    final public function mce_css($mce_css)
    {
        return $mce_css .= ', ' . $this->options['css'] . ', ' . self::tFyAppUrl() . '/editor.css';
    }

    /* = CONTROLEUR = */
    /** == Récupération des glyphs depuis le fichier CSS == **/
    public function get_css_glyphs()
    {
        $return = "[";
        $col = 0;
        foreach ((array)$this->glyphs as $class => $content) :
            if (!$col) {
                $return .= "{";
            }
            $return .= "'$class':'" . preg_replace('/' . preg_quote('\\') . '/', '&#x', $content) . "',";
            if (++$col >= $this->options['cols']) :
                $col = 0;
                $return .= "},";
            endif;
        endforeach;
        if ($col) {
            $return .= "}";
        }
        $return .= "]";

        return $return;
    }
}