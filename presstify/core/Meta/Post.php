<?php
namespace tiFy\Core\Meta;

final class Post
{
	/* = ARGUMENTS = */
	// Liste des meta_keys declarées par type de post
	private static $MetaKeys	= array();
	
	// Status unique/multiples des meta_keys declarées par type de post
	private static $Single		= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		add_action( 'save_post', array( $this, 'Save' ), 10, 2 );
	}
	
	/* = DECLARATION = */
	final public static function Register( $post_type, $meta_key, $single = false, $sanitize_callback = 'wp_unslash' )
	{
		// Bypass
		if( ! empty( self::$MetaKeys[$post_type] ) && in_array( $meta_key, self::$MetaKeys[$post_type] ) )
			return;

		self::$MetaKeys[$post_type][] 			= $meta_key;
		self::$Single[$post_type][$meta_key] 	= $single;

		if ( $sanitize_callback !== '' ) :
			add_filter( "tify_sanitize_meta_post_{$post_type}_{$meta_key}", $sanitize_callback );
		endif;
	}
	
	/* = RECUPERATION = */
	final public static function Get( $post_id, $meta_key )
	{
		global $wpdb;
		$query = 	"SELECT meta_id, meta_value".
					" FROM {$wpdb->postmeta}".
					" WHERE 1".
					" AND {$wpdb->postmeta}.post_id = %d".
					" AND {$wpdb->postmeta}.meta_key = %s";
	
		if( $order = get_post_meta( $post_id, '_order_'. $meta_key , true ) )
			$query .= " ORDER BY FIELD( {$wpdb->postmeta}.meta_id,". implode( ',', $order ) .")";

		if( ! $metas = $wpdb->get_results( $wpdb->prepare( $query, $post_id, $meta_key ) ) )
			return;
				
		$_metas = array();
		foreach ( (array) $metas as $index => $args ) :
			$_metas[ $args->meta_id ] = maybe_unserialize( $args->meta_value );
		endforeach;

		return $_metas;
	}
	
	/* = VERIFICATION = */
	final public static function IsSingle( $post_type, $meta_key )
	{
		return isset( self::$Single[$post_type][$meta_key] ) ? self::$Single[$post_type][$meta_key] : null;
	}
	
	/* = ENREGISTREMENT = */
	final public function Save( $post_id, $post )
	{
		// Bypass
		/// Contrôle s'il s'agit d'une routine de sauvegarde automatique.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		/// Contrôle si le script est executé via Ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		/// Contrôle si le requête contenant l'indication de type de post est définie 		
		if( ! isset( $_POST['post_type'] ) )
			return;
					
		// Contrôle des permissions d'édition de l'utilisateur courant
	  	if ( ( 'page' === $_POST['post_type'] ) && ! current_user_can( 'edit_page', $post_id ) )
	  		return;
	  	if( ( 'page' !== $_POST['post_type'] ) && ! current_user_can( 'edit_post', $post_id ) )
			return;
		
		// Vérification de l'existance du post	
		if( ( ! $post = get_post( $post_id ) ) )
			return;
		
		$post_type = $post->post_type;
		
		// Vérification d'existance de metadonnées déclarées pour le type de post
		if( empty( self::$MetaKeys[$post_type] ) )
			return;
		
		// Récupération des metadonnés en $_POST
		$request = ( isset( $_POST['tify_meta_post'] ) ) ? $_POST['tify_meta_post'] : null;		

		// Variables
		$postmeta		= array();
		$meta_keys 		= self::$MetaKeys[$post_type];
		$meta_ids 		= array();
		$meta_exists 	= array();

		foreach( (array) $meta_keys as $meta_key ) :
			// Vérification d'existance de la metadonnées en base
			if( $_meta = self::Get( $post_id, $meta_key ) )
				$meta_exists += $_meta;
	  		
			if( ! isset( $request[$meta_key] ) )
				continue;
				
			// Récupération des meta_ids de metadonnées unique
			if( self::isSingle( $post_type, $meta_key ) ) :
				$meta_id = $_meta ? key( $_meta ) : uniqid();
				array_push( $meta_ids, $meta_id );
				$postmeta[$meta_key][$meta_id] = $request[$meta_key];
			// Récupération des meta_ids de metadonnées multiple
			elseif( self::isSingle( $post_type, $meta_key ) === false ) :
				$meta_ids += array_keys( $request[$meta_key] );
				$postmeta[$meta_key] = $request[$meta_key];
			endif;
		endforeach;

		// Suppression des metadonnées absente du processus de sauvegarde
		foreach( (array) $meta_exists as $meta_id => $meta_value ) :
			if ( ! in_array( $meta_id, $meta_ids ) ) :
				delete_metadata_by_mid( 'post', $meta_id );
			endif;
		endforeach;

		// Sauvegarde des metadonnées (mise à jour ou ajout)
		foreach( (array) $meta_keys as $meta_key ) :
			if( ! isset( $postmeta[$meta_key] ) )
				continue;
	  												
			$order = array();
			foreach( (array) $postmeta[$meta_key] as $meta_id => $meta_value ) :
				$meta_value = apply_filters( "tify_sanitize_meta_post_{$post_type}_{$meta_key}", $meta_value );

				if( is_int( $meta_id ) && get_post_meta_by_id( $meta_id ) ) :
					$_meta_id = $meta_id;
					update_metadata_by_mid( 'post', $meta_id, $meta_value );
				else :
					$_meta_id = add_post_meta( $post_id, $meta_key, $meta_value );
				endif;
				// Récupération de l'ordre des metadonnées multiple
				if( self::isSingle( $post_type, $meta_key ) === false )
					$order[] = $_meta_id;
			endforeach;
			
			// Sauvegarde de l'ordre
			if( ! empty( $order ) )
				update_post_meta( $post_id, '_order_'. $meta_key, $order );
		endforeach;

		return $post;
	}
}