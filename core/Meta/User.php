<?php
namespace tiFy\Core\Meta;

final class User
{
	/* = ARGUMENTS = */
	// Liste des meta_keys declarées
	private static $MetaKeys	= array();
	
	// Status unique/multiples des meta_keys declarées
	private static $Single		= array();
	
	/* = CONSTRUCTEUR = */
	public function __construct()
	{
		add_action( 'profile_update', array( $this, 'Save' ) );
		add_action( 'user_register', array( $this, 'Save' ) );
	}
	
	/* = DECLARATION = */
	final public static function Register( $meta_key, $single = false, $sanitize_callback = 'wp_unslash' )
	{
		// Bypass
		if( ! empty( self::$MetaKeys ) && in_array( $meta_key, self::$MetaKeys ) )
			return;
	
		self::$MetaKeys[] 			= $meta_key;
		self::$Single[$meta_key] 	= $single;

		if ( $sanitize_callback !== '' ) :
			add_filter( "tify_sanitize_meta_user_{$meta_key}", $sanitize_callback );
		endif;
	}
	
	/* = RECUPERATION = */
	final public static function Get( $user_id, $meta_key )
	{
		global $wpdb;
		$query = 	"SELECT umeta_id as meta_id, meta_value".
					" FROM {$wpdb->usermeta}".
					" WHERE 1".
					" AND {$wpdb->usermeta}.user_id = %d".
					" AND {$wpdb->usermeta}.meta_key = %s";
	
		if( $order = get_user_meta( $user_id, '_order_'. $meta_key , true ) )
			$query .= " ORDER BY FIELD( {$wpdb->usermeta}.user_id,". implode( ',', $order ) .")";

		if( ! $metas = $wpdb->get_results( $wpdb->prepare( $query, $user_id, $meta_key ) ) )
			return;
				
		$_metas = array();
		foreach ( (array) $metas as $index => $args ) :
			$_metas[ $args->meta_id ] = maybe_unserialize( $args->meta_value );
		endforeach;
	
		return $_metas;
	}
	
	/* = VERIFICATION = */
	final public static function IsSingle( $meta_key )
	{
		return isset( self::$Single[$meta_key] ) ? self::$Single[$meta_key] : null;
	}
	
	/* = ENREGISTREMENT = */
	final public function Save( $user_id )
	{
		// Bypass
		/// Contrôle s'il s'agit d'une routine de sauvegarde automatique.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;
		/// Contrôle si le script est executé via Ajax.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;
		
		// Vérification d'existance de metadonnées utilisateurs déclarées	
		if( empty( self::$MetaKeys ) )
			return;	
			
		// Récupération des metadonnés en $_POST
		$request = ( isset( $_POST['tify_meta_user'] ) ) ? $_POST['tify_meta_user'] : null;		

		// Variables
		$usermeta		= array();
		$meta_keys 		= self::$MetaKeys;
		$meta_ids 		= array();
		$meta_exists 	= array();		
	
		foreach( (array) $meta_keys as $meta_key ) :
			
			// Vérification d'existance de la metadonnées en base
			if( $_meta = self::Get( $user_id, $meta_key ) )
				$meta_exists += $_meta;
	  		
			if( ! isset( $request[$meta_key] ) )
				continue;

			// Récupération des meta_ids de metadonnées unique
			if( self::isSingle( $meta_key ) ) :
				$meta_id = $_meta ? key( $_meta ) : uniqid();
				array_push( $meta_ids, $meta_id );
				$usermeta[$meta_key][$meta_id] = $request[$meta_key];
			// Récupération des meta_ids de metadonnées multiple
			elseif( self::isSingle( $meta_key ) === false ) :
				$meta_ids += array_keys( $request[$meta_key] );
				$usermeta[$meta_key] = $request[$meta_key];
			endif;
		endforeach;

		// Suppression des metadonnées absente du processus de sauvegarde
		foreach( (array) $meta_exists as $meta_id => $meta_value ) :
			if ( ! in_array( $meta_id, $meta_ids ) ) :
				delete_metadata_by_mid( 'user', $meta_id );
			endif;
		endforeach;

		// Sauvegarde des metadonnées (mise à jour ou ajout)
		foreach( (array) $meta_keys as $meta_key ) :
			if( ! isset( $usermeta[$meta_key] ) )
				continue;
	  												
			$order = array();
			foreach( (array) $usermeta[$meta_key] as $meta_id => $meta_value ) :
				$meta_value = apply_filters( "tify_sanitize_meta_user_{$meta_key}", $meta_value );
			
				if( is_int( $meta_id ) && get_metadata_by_mid( 'user', $meta_id ) ) :
					$_meta_id = $meta_id;
					update_metadata_by_mid( 'user', $meta_id, $meta_value );
				else :
					$_meta_id = add_user_meta( $user_id, $meta_key, $meta_value );
				endif;
				// Récupération de l'ordre des metadonnées multiple
				if( self::isSingle( $meta_key ) === false )
					$order[] = $_meta_id;
			endforeach;
			
			// Sauvegarde de l'ordre
			if( ! empty( $order ) )
				update_user_meta( $user_id, '_order_'. $meta_key, $order );
		endforeach;

		return $user_id;
	}
}