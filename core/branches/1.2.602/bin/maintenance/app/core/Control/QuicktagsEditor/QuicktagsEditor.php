<?php
/**
 * @name QuicktagsEditor
 * @desc Controleur d'affichage d'éditeur de quicktags
 * @package presstiFy
 * @namespace tiFy\Core\Control\QuicktagsEditor
 * @version 1.1
 * @subpackage Core
 * @since 1.2.502
 *
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Core\Control\QuicktagsEditor;

/**
 * @Overrideable \App\Core\Control\QuicktagsEditor\QuicktagsEditor
 *
 * <?php
 * namespace \App\Core\Control\QuicktagsEditor
 *
 * class QuicktagsEditor extends \tiFy\Core\Control\QuicktagsEditor\QuicktagsEditor
 * {
 *
 * }
 */

class QuicktagsEditor extends \tiFy\Core\Control\Factory
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation globale
     *
     * @return void
     */
    protected function init()
    {
        // Déclaration des scripts
        \wp_register_style(
            'tify_control-quicktags_editor',
            self::tFyAppAssetsUrl('QuicktagsEditor.css', get_class()),
            ['font-awesome'],
            141212
        );
        \wp_register_script(
            'tify_control-quicktags_editor',
            self::tFyAppAssetsUrl('QuicktagsEditor.js', get_class()),
            ['jquery', 'quicktags'],
            141212,
            true
        );
    }

    /**
     * Mise en file des scripts
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        \wp_enqueue_style( 'tify_control-quicktags_editor' );
        \wp_enqueue_script( 'tify_control-quicktags_editor' );
    }

    /**
     * CONTROLEURS
     */
    /**
     * Affichage
     *
     * @param array $attrs Liste des attributs de configuration
     *
     * @return string
     */
    protected function display($attrs = [])
    {
        // Traitement des attributs de configuration
        $defaults = [
            'id'          => 'tify_control_quicktags_editor-' . $this->getId(),
            'class'       => '',
            'name'        => 'tify_control_quicktags_editor-' . $this->getId(),
            'value'       => '',
            'preview'     => false,
            'editor'      => 'textarea', // textarea | input
            // Liste des boutons natifs, mettre à null pour désactiver tous les boutons natifs
            'buttons'     => [
                'link',
                'strong',
                'code',
                'del',
                'fullscreen',
                'em',
                'li',
                'img',
                'ol',
                'block',
                'ins',
                'more',
                'ul',
                'spell',
                'close'
            ],
            /**
             * Liste de boutons personnalisés
             * @see https://codex.wordpress.org/Quicktags_API
             *    array(
             *        array(
             *            id, display, arg1, arg2, access_key, title, priority
             *        ),
             *        array(
             *            id, display, arg1, arg2, access_key, title, priority
             *        )
             *    )
             *
             *    // Exemple de personnalisation
             * tify_control_quicktags_editor(
             * array(
             * 'buttons'        => null,
             * 'add_buttons'    => array(
             * array( '_strong', __( 'Gras', 'tify' ), '<strong>', '</strong>', 'b', __( 'Texte en gras', 'tify' ), 10 ),
             * array( '_em', __( 'Italique', 'tify' ), '<em>', '</em>', 'i', __( 'Texte en italique', 'tify' ), 20 ),
             * array( '_ins', __( 'Souligné', 'tify' ), '<ins>', '</ins>', 's', __( 'Soulignement du texte', 'tify' ), 30 ),
             * array( '_del', __( 'Barré', 'tify' ), '<del>', '</del>', 'd', __( 'Texte barré', 'tify' ), 40 ),
             * array( '_ul', __( 'Liste', 'tify' ), '<ul>\n', '</ul>\n\n', 'u', __( 'Liste', 'tify' ), 50 ),
             * array( '_li', __( 'Puce de liste', 'tify' ), '\t<li>', '</li>\n\n', 'l', __( 'Puce de liste', 'tify' ), 60 ),
             * array( '_ol', __( 'Numéro de liste', 'tify' ), '\t<ol>', '</ol>\n\n', 'o', __( 'Numéro de liste', 'tify' ), 70 ),
             * array( '_block', __( 'Citation', 'tify' ), '\n\n<blockquote>', '</blockquote>\n\n', 'q', __( 'Citation', 'tify' ), 80 ),
             * array( '_code', __( 'Code', 'tify' ), '<code>', '</code>', 'c', __( 'Code', 'tify' ), 90 )
             * )
             * )
             * )
             */
            'add_buttons' => []
        ];
        $attrs    = wp_parse_args( $attrs, $defaults );

        // Traitement des boutons natifs
        if ( is_null( $attrs['buttons'] ) ) :
            $attrs['buttons'] = ' ';
        elseif ( ! empty( $attrs['buttons'] ) && is_array( $attrs['buttons'] ) ) :
            if ( $attrs['editor'] === 'input' ) {
                $attrs['buttons'] = array_diff( $attrs['buttons'],
                    [ 'fullscreen', 'li', 'img', 'ol', 'block', 'more', 'ul' ] );
            }

            $attrs['buttons'] = implode( ',', $attrs['buttons'] );
        endif;

        // Traitement des boutons personnalisés
        /*if( ! empty( $attrs['add_buttons'] ) && is_array( $attrs['add_buttons'] ) ) :
            foreach( $attrs['add_buttons'] as $param )
        endif;*/

        extract( $attrs );

        $output = "";
        $output .= "<div id=\"tify_control_quicktags_editor-wrapper-" . $this->getId() . "\" class=\"tify_control_quicktags_editor-wrapper\">\n";
        if ( $editor === 'textarea' ) {
            $output .= "\t<textarea id=\"{$id}\" name=\"{$name}\" class=\"tify_control_quicktags_editor\">{$value}</textarea>\n";
        } elseif ( $editor === 'input' ) {
            $output .= "\t<input id=\"{$id}\" name=\"{$name}\" class=\"tify_control_quicktags_editor\" value=\"{$value}\"/>\n";
        }
        $output .= "</div>\n";

        $attrs['instance'] = $this->getId();

        add_action( ( is_admin() ? 'admin_print_footer_scripts' : 'wp_footer' ), function () use ( $attrs ) {
            if ( ! wp_script_is( 'quicktags' ) ) {
                return;
            }
            ?>
            <script type="text/javascript">/* <![CDATA[ */
                var tify_QtagsEd<?php echo $attrs['instance'];?> = quicktags({
                    id:      '<?php echo $attrs['id'];?>',
                    buttons: '<?php echo $attrs['buttons'];?>'
                });

                <?php if( ! empty( $attrs['add_buttons'] ) ) :?>
                <?php foreach( $attrs['add_buttons'] as $i => $param ) :?>
                QTags.addButton('<?php echo $param[0];?>', '<?php echo $param[1];?>', '<?php echo $param[2];?>',
                    '<?php echo $param[3];?>', '<?php echo $param[4];?>', '<?php echo $param[5];?>',
                    '<?php echo $param[6];?>', tify_QtagsEd<?php echo $attrs['instance'];?>.id);
                <?php endforeach;?>
                <?php endif;
                /*jQuery.each( edButtons, function( u, v ){
                    if( ( v !== undefined ) && ( v.instance === tify_QtagsEd<?php echo $attrs['id'];?>.id ) )
                        console.log( v );
                });*/ ?>
                /* ]]> */</script><?php
            /**
             *    // Exemple de personnalisation du comportement d'un bouton
             *    // -> Modification de l'intitulé de la fenêtre de saisie du bouton lien
             *    QTags.LinkButton.prototype.callback = function( e, c, ed, defaultValue ) {
             *        var URL, t = this;
             *        if ( ! defaultValue )
             *            defaultValue = 'http://';
             *        if ( t.isOpen(ed) === false ) {
             *            URL = prompt( "Saisissez l'adresse du site", defaultValue );
             *            if ( URL ) {
             *                t.tagStart = '<a href="' + URL + '">';
             *                QTags.TagButton.prototype.callback.call(t, e, c, ed);
             *            }
             *        } else {
             *            QTags.TagButton.prototype.callback.call(t, e, c, ed);
             *        }
             *    }
             */
        }, 99 );

        echo $output;
    }
}