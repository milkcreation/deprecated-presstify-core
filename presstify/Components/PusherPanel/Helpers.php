<?php
/* = AFFICHAGE DU PANNEAU PUSHER = */
function tify_pusher_panel_display()
{

	\tiFy\Components\PusherPanel\PusherPanel::display();
}

/* = AJOUT D'UN ELEMENT AU PANNEAU POUSSEUR = */
function tify_pusher_panel_add_node( $node = array() )
{
	\tiFy\Components\PusherPanel\PusherPanel::add_node( $node );
}