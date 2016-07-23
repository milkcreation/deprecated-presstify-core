<?php 
function mktzr_forms_cart_cheque_display_form( ){
?>
<form method="post" action="<?php echo add_query_arg( array( 'mktzr_forms_cart' => 'order') ); ?>">
	<input type="submit" value="Je confirme ma commande">
</form>
<?php 
}
