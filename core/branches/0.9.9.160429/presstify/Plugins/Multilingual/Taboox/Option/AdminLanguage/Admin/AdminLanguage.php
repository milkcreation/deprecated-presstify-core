<?php


class SwitcherMenu extends tiFy\Core\Taboox\Admin
{
	/* = ARGUMENTS = */
	public 	// Configuration
	$name = 'tify_multilingual_adminlang',
	// Référence
	$master;

	/* = FORMULAIRE DE SAISIE = */
	public function form( $args = array() ){
		$languages = get_available_languages();
		$translations = wp_get_available_translations();
	?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php _e( 'Choix de la langue de l\'interface d\'administration', 'tify' );?></th>
				<td>
					<?php
						$locale = get_locale();
						if ( ! in_array( $locale, $languages ) )
							$locale = '';
						wp_dropdown_languages( 
							array(								
								'id'           => $this->name,
								'name'         => $this->name,
								'selected'     => $this->value ? ( is_array( $this->value )? current( array_keys( $this->value ) ) : $this->value ): $locale,
								'languages'    => $languages,
								'translations' => $translations,
								'show_available_translations' => ( ! is_multisite() || is_super_admin() ) && wp_can_install_language_pack(),
							) 
						);
					?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	}
}