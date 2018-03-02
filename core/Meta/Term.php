<?php
namespace tiFy\Core\Meta;

final class Term
{
	/* = ARGUMENTS = */
	// Liste des meta_keys declarées par taxonomy
	private static $MetaKeys	= array();
	
	// Status unique/multiples des meta_keys declarées par taxonomy
	private static $Single		= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		add_action( 'edited_term', array( $this, 'Save' ), 10, 3 );
	}
	
	/* = DECLARATION = */
	final public static function Register( $taxonomy, $meta_key, $single = false, $sanitize_callback = 'wp_unslash' )
	{
		// Bypass
		if( ! empty( self::$MetaKeys[$taxonomy] ) && in_array( $meta_key, self::$MetaKeys[$taxonomy] ) )
			return;

		self::$MetaKeys[$taxonomy][] 			= $meta_key;
		self::$Single[$taxonomy][$meta_key] 	= $single;

		if ( $sanitize_callback !== '' ) :
			add_filter( "tify_sanitize_meta_term_{$taxonomy}_{$meta_key}", $sanitize_callback );
		endif;
	}
	
	/* = RECUPERATION = */
	final public static function Get( $term_id, $meta_key )
	{
		global $wpdb;
		$query = 	"SELECT meta_id, meta_value".
					" FROM {$wpdb->termmeta}".
					" WHERE 1".
					" AND {$wpdb->termmeta}.term_id = %d".
					" AND {$wpdb->termmeta}.meta_key = %s";
	
		if( $order = get_term_meta( $term_id, '_order_'. $meta_key , true ) )
			$query .= " ORDER BY FIELD( {$wpdb->termmeta}.term_id,". implode( ',', $order ) .")";

		if( ! $metas = $wpdb->get_results( $wpdb->prepare( $query, $term_id, $meta_key ) ) )
			return;
				
		$_metas = array();
		foreach ( (array) $metas as $index => $args ) :
			$_metas[ $args->meta_id ] = maybe_unserialize( $args->meta_value );
		endforeach;

		return $_metas;
	}
	
	/* = VERIFICATION = */
	final public static function IsSingle( $taxonomy, $meta_key )
	{
		return isset( self::$Single[$taxonomy][$meta_key] ) ? self::$Single[$taxonomy][$meta_key] : null;
	}
	
	/* = ENREGISTREMENT = */
	final public function Save( $term_id, $tt_id, $taxonomy )
	{
		// Bypass
		/// Contrôle s'il s'agit d'une routine de sauvegarde automatique.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		/// Contrôle si le script est executé via Ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
			
		// Vérification d'existance de metadonnées déclarées pour la taxonomy	
		if( empty( self::$MetaKeys[$taxonomy] ) )
			return;	
			
		// Récupération des metadonnés en $_POST
		$request = ( isset( $_POST['tify_meta_term'] ) ) ? $_POST['tify_meta_term'] : null;		

		// Variables
		$termmeta		= array();
		$meta_keys 		= self::$MetaKeys[$taxonomy];
		$meta_ids 		= array();
		$meta_exists 	= array();

		foreach( (array) $meta_keys as $meta_key ) :
			// Vérification d'existance de la metadonnées en base
			if( $_meta = self::Get( $term_id, $meta_key ) )
				$meta_exists += $_meta;
	  		
			if( ! isset( $request[$meta_key] ) )
				continue;
				
			// Récupération des meta_ids de metadonnées unique
			if( self::isSingle( $taxonomy, $meta_key ) ) :
				$meta_id = $_meta ? key( $_meta ) : uniqid();
				array_push( $meta_ids, $meta_id );
				$termmeta[$meta_key][$meta_id] = $request[$meta_key];
			// Récupération des meta_ids de metadonnées multiple
			elseif( self::isSingle( $taxonomy, $meta_key ) === false ) :
				$meta_ids += array_keys( $request[$meta_key] );
				$termmeta[$meta_key] = $request[$meta_key];
			endif;
		endforeach;

		// Suppression des metadonnées absente du processus de sauvegarde
		foreach( (array) $meta_exists as $meta_id => $meta_value ) :
			if ( ! in_array( $meta_id, $meta_ids ) ) :
				delete_metadata_by_mid( 'term', $meta_id );
			endif;
		endforeach;

		// Sauvegarde des metadonnées (mise à jour ou ajout)
		foreach( (array) $meta_keys as $meta_key ) :
			if( ! isset( $termmeta[$meta_key] ) )
				continue;
	  												
			$order = array();
			foreach( (array) $termmeta[$meta_key] as $meta_id => $meta_value ) :
				$meta_value = apply_filters( "tify_sanitize_meta_term_{$taxonomy}_{$meta_key}", $meta_value );
			
				if( is_int( $meta_id ) && get_metadata_by_mid( 'term', $meta_id ) ) :
					$_meta_id = $meta_id;
					update_metadata_by_mid( 'term', $meta_id, $meta_value );
				else :
					$_meta_id = add_term_meta( $term_id, $meta_key, $meta_value );
				endif;
				// Récupération de l'ordre des metadonnées multiple
				if( self::isSingle( $taxonomy, $meta_key ) === false )
					$order[] = $_meta_id;
			endforeach;
			
			// Sauvegarde de l'ordre
			if( ! empty( $order ) )
				update_term_meta( $term_id, '_order_'. $meta_key, $order );
		endforeach;

		return $term_id;
	}
}