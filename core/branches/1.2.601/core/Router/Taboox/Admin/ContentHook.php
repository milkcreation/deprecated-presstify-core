<?php
namespace tiFy\Core\Router\Taboox\Admin;

use tiFy\Core\Router\Router;

class ContentHook extends \tiFy\Core\Taboox\Options\Admin
{
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    public function admin_init()
    {
        foreach ((array)Router::getList() as $id => $inst) :
            if(! $option_name = $inst->getOptionName())
                continue;
            \register_setting($this->page, $inst->getOptionName());
        endforeach;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Formulaire de saisie
     */
    public function form()
    {
?>
<table class="form-table">
    <tbody>
    <?php foreach ((array)Router::getList() as $id => $inst) : ?>
        <tr>
            <th><?php echo $inst->getTitle(); ?></th>
            <td>
            <?php
            if ($dropdown = \wp_dropdown_pages(
                    [
                        'name'             => $inst->getOptionName(),
                        'post_type'        => $inst->getObjectName(),
                        'selected'         => $inst->getSelected(),
                        'sort_column'      => $inst->getAttr('listorder'),
                        'show_option_none' => $inst->getAttr('show_option_none'),
                        'echo'             => 0
                    ]
                )
            ) :
                echo $dropdown;
            else :
                _e('Aucune page publiée sur ce site.', 'tify');
            endif;
            ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
    }
}