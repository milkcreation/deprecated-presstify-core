<?php
namespace tiFy\Core\Ui\Admin\Templates\Table\Traits;

trait Notices
{
    /// Affichage des messages de notification
    /* VERSION TABLE
     foreach( (array) $this->Notices as $nid => $nattr ) :
        if( ! isset( $_REQUEST[ $nattr['query_arg'] ] ) || ( $_REQUEST[ $nattr['query_arg'] ] !== $nid ) )
            continue;

        add_action( 'admin_notices', function() use( $nattr ){
        ?>
            <div class="notice notice-<?php echo $nattr['notice'];?><?php echo $nattr['dismissible'] ? ' is-dismissible':'';?>">
                <p><?php echo $nattr['message'] ?></p>
            </div>
        <?php

        });
    endforeach;
    */


    /* VERSION FORM
/// Affichage des messages de notification
if ($code = $this->current_notice()) :
    $notice_attrs = $this->Notices[$code];
    add_action('admin_notices', function () use ($notice_attrs) {
        ?>
        <div class="notice notice-<?php echo $notice_attrs['notice']; ?><?php echo $notice_attrs['dismissible'] ? ' is-dismissible' : ''; ?>">
            <p><?php echo $notice_attrs['message'] ?></p>
        </div>
        <?php

    });
endif;
*/
}