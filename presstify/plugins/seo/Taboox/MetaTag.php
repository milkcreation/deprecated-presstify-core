<?php
use tiFy\Taboox\Admin;

class tiFy_SEO_PostMetaTag_Taboox extends Admin
{
	/* = ARGUMENTS = */			
	private $master;
	
	/* = CONSTRUCTEUR = */
	public function __construct( tiFy_SEO $master )
	{
		parent::__construct();
		$this->master = $master;				
	}
	
	/* = CHARGEMENT DE LA PAGE = */
	public function current_screen( $current_screen )
	{
		// Déclaration des metadonnées à enregistrer
		register_post_meta( $current_screen->id, '_tify_seo_meta' );		
	}
	
	/* = MISE EN FILE DES SCRIPTS = */
	public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'text_remaining' );	
		wp_enqueue_style( 'tify_seo_metatag-admin', $this->Url ."/MetaTag.css", array(), '150323' );
		wp_enqueue_script( 'tify_seo_metatag-admin', $this->Url ."/MetaTag.js", array( 'jquery' ), '150323', true );
	}
	
	/* = FORMULAIRE DE SAISIE = */
	public function form( $post )
	{
		$value = wp_parse_args( ( ( $_value = get_post_meta( $post->ID, '_tify_seo_meta', true ) ) ? $_value : array() ), array( 'title' => '', 'url' => '', 'desc' => '' ) );
			
		// Valeurs originales
		/// Titre		
		$original_title = esc_attr( $post->post_title ). ( $this->master->append_blogname ? $this->master->sep . get_bloginfo( 'name' ) : '' ) . ( $this->master->append_sitedesc ? $this->master->sep . get_bloginfo( 'description', 'display' ) : '' );
		/// Url
		list( $permalink, $post_name ) = get_sample_permalink( $post->ID );
		$original_url 	= str_replace( array( '%pagename%', '%postname%' ), $post_name, urldecode( $permalink ) );
		/// Description
		$original_desc = get_bloginfo( 'name' ) .'&nbsp;|&nbsp;'. get_bloginfo( 'description' );
		if( $post->post_excerpt )
			$original_desc = tify_excerpt( strip_tags( html_entity_decode( $post->post_excerpt ) ), array( 'max' => 156 ) );
		elseif( $post->post_content )
			$original_desc = tify_excerpt( strip_tags( html_entity_decode( $post->post_content ) ), array( 'max' => 156 ) );
	?>
		<div id="tify_seo_taboox_meta">
			<div id="tify_seo_meta-preview">
				<?php $title = $value['title'] ? esc_attr( $value['title'] ) : $original_title;?>
				<span id="tify_seo_meta_title-preview" data-original="<?php echo $original_title;?>"><?php echo $title;?></span>

				<?php $url = $value['url'] ? esc_url( $value['url'] ) : $original_url;?>
				<span id="tify_seo_meta_url-preview" data-original="<?php echo $original_url;?>"><?php echo $url;?></span>
				
				<?php $desc = $value['desc'] ? esc_attr( tify_excerpt( $value['desc'] ) ) : strip_tags( html_entity_decode( $original_desc ) );?>
				<p id="tify_seo_meta_desc-preview" data-original="<?php echo $original_desc;?>"><?php echo $desc;?></p>
			</div>
			<h3><?php _e( 'Personnalisation', 'tify' );?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Balise titre', 'tify' );?><br>
						</th>
						<td>
							<input type="text" id="tify_seo_meta_title" data-fill_out="#tify_seo_meta_title-preview" name="tify_post_meta[single][_tify_seo_meta][title]" placeholder="<?php echo $original_title;?>" value="<?php echo $value['title'];?>" autocomplete="off">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Balise description', 'tify' );?><br>
						</th>
						<td>
							<?php tify_control_text_remaining( 
									array( 
										'container_id'	=> 'tify_seo_meta_desc-wrapper', 
										'id'			=> 'tify_seo_meta_desc', 
										'name' 			=> 'tify_post_meta[single][_tify_seo_meta][desc]',
										'value'			=> $value['desc'],
										'length' 		=> 156,
										'maxlength'		=> true,
										'attrs' 		=> array( 
											'data-fill_out' => '#tify_seo_meta_desc-preview',
											'placeholder'	=> $original_desc
										) 
									) 
								);?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Url canonique', 'tify' );?><br>
							<em style="font-size:0.8em;color:#999;"><?php _e( 'Utilisateur avancé', 'tify' );?></em>
						</th>
						<td>
							<input type="text" id="tify_seo_meta_url" data-fill_out="#tify_seo_meta_url-preview" name="tify_post_meta[single][_tify_seo_meta][url]" placeholder="<?php echo $original_url;?>" value="<?php echo $value['url'];?>" autocomplete="off">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php
	}
}