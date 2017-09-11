<?php
/**
 * @var string $context Contexte d'expédition du message. confirmation|notification
 *
 * @var string $subject Sujet du mail
 *
 * @var array $fields[] {
 *      Tableau de correspondances des champs du formulaires
 *
 *      @param array $slug Identifiant qualificatif du champs {
 *          Attributs du champ
 *
 *          @param string $label Intitulé du champs
 *          @param string $value Valeur saisie dans le champs
 *      }
 * }
 */
?>
<table cellpadding="0" cellspacing="10" border="0" align="center">
    <tbody>
        <tr>
            <td width="600" valign="top" colspan="2">
            <?php
                printf(
                    __('Nouvelle demande sur le site %1$s, <a href="%2$s">%2$s<a>', 'tify'),
                    get_bloginfo('name'),
                    esc_url(get_bloginfo('url'))
                );
            ?>
            </td>
        </tr>
        <tr>
            <td width="600" valign="top" colspan="2">
                <h3><?php echo $subject;?></h3>
            </td>
        </tr>
        <?php foreach ((array) $fields as $slug => $attrs) :?>
        <tr>
        <?php if ($attrs['label']) :?>
            <td width="200" valign="top"><?php echo $attrs['label'];?></td>
            <td width="400" valign="top"><?php echo $attrs['value'];?></td>
        <?php else :?>
            <td colspan="2" width="600" valign="top"><?php echo $attrs['value'];?></td>
        <?php endif;?>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>