<?php
namespace Theme\tiFy\Core\Taboox\PostType\SurveyForm\Helpers;

class SurveyForm extends \tiFy\Core\Taboox\Helpers
{
	/* = ARGUMENTS = */
	/** @see https://codex.wordpress.org/Plugin_API/Action_Reference **/
	// Liste des actions à déclencher
	protected $tFyAppActions				= array(
		'wp'
	);
	
	/* = WP = */
	public function wp()
	{
		// Bypass
		if( ! tify_taboox_content_hook_get( 'current_survey' ) )
			return;
		if( ! tify_taboox_content_hook_is( 'current_survey_page' ) )
			return;
		add_action( 'ehgode_single_post_bottom', array( $this, 'display' ) );
	}
	
	/* = AFFICHAGE DU FORMULAIRE = */
	public function display()
	{
		?>
		<section class="ContactArea Section" id="contact-area">
			<div class="Section-inner">
				<h2 class="Section-title"><span class="Section-titleInner"><?php echo get_the_title( tify_taboox_content_hook_get( 'current_survey' ) ); ?></span></h2>
				<?php tify_form_display( 'CurrentSurveyForm' ); ?>
			</div>
		</section>
		<?php
	}
}