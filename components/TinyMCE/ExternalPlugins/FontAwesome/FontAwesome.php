<?php

namespace tiFy\Components\TinyMCE\ExternalPlugins\FontAwesome;

use tiFy\Lib\File;
use \tiFy\Components\TinyMCE\TinyMCE;

class FontAwesome extends \tiFy\App
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
        TinyMCE::registerExternalPlugin('fontawesome', self::tFyAppUrl() . '/plugin.js');

        // Déclaration des événements
        $this->appAddAction('init');
        $this->appAddAction('admin_init');
        $this->appAddAction('admin_enqueue_scripts');
        $this->appAddAction('admin_head');
        $this->appAddAction('admin_print_styles');
        $this->appAddAction('wp_enqueue_scripts');
        $this->appAddAction('wp_head');
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
            'hookname'     => 'font-awesome',
            // Url vers la police css
            'css'          => \tify_style_get_src('font-awesome', 'dev'),
            // Numero de version pour la mise en file d'appel la police de caractères
            'version'      => \tify_style_get_attr('font-awesome', 'version'),
            // Dépendance pour la mise en file d'appel la police de caractères
            'dependencies' => [],
            // Préfixe des classes de la police de caractères
            'prefix'       => 'fa',
            // Famille de la police
            'font-family'  => 'fontAwesome',
            // Suffixe de la classe du bouton de l'éditeur (doit être contenu dans la police)
            'button'       => 'flag',
            // Infobulle du bouton et titre de la boîte de dialogue
            'title'        => __('Police de caractères fontAwesome', 'tify'),
            // Nombre d'éléments par ligne
            'cols'         => 32,
        ];
        // Déclaration des scripts
        wp_register_style($this->options['hookname'], $this->options['css'], $this->options['dependencies'],
            $this->options['version']);
        wp_register_style('tinymce-fontawesome', self::tFyAppUrl() . '/plugin.css', [], '20141219');

        // Récupération des glyphs
        $css = File::getContents($this->options['css']);
        preg_match_all('/.fa-(.*):before\s*\{\s*content\:\s*"(.*)"(;?|)\s*\}\s*/', $css, $matches);
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
        wp_enqueue_style('tinymce-fontawesome');
    }

    /** == Personnalisation des scripts de l'entête == **/
    final public function admin_head()
    {
        ?>
        <script type="text/javascript">/* <![CDATA[ */
            var fontAwesomeChars = <?php echo $this->get_css_glyphs();?>,
                tinymceFontAwesomel10n = {'title': '<?php echo $this->options['title'];?>'};
            /* ]]> */</script><?php
    }

    /** == Personnalisation des styles de l'entête == **/
    final public function admin_print_styles()
    {
        ?>
        <style type="text/css">i.mce-i-fontawesome:before {
            content: "<?php echo $this->glyphs[$this->options['button']];?>";
        }

        i.mce-i-fontawesome:before, .mce-grid a.fontawesome {
            font-family: <?php echo $this->options['font-family'];?> !important;
        }</style><?php
    }

    /** ==  Mise en file des scripts == **/
    final public function wp_enqueue_scripts()
    {
        wp_enqueue_style('font-awesome');
    }

    /** == Personnalisation des scripts de l'entête == **/
    final public function wp_head()
    {
        ?>
        <style type="text/css">.fontawesome {
            font-family: '<?php echo $this->options['font-family'];?>';
            font-style: normal;
        }</style><?php
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
            $return .= "'$class':'" . html_entity_decode(preg_replace('/' . preg_quote('\\') . '/', '&#x', $content),
                    ENT_NOQUOTES, 'UTF-8') . "',";
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