<?php
namespace tiFy\Plugins\Minify;

class Styles extends \WP_Dependencies {
	// WP_Dependencies
	public $registered = array();
	public $queue = array();
	public $to_do = array();
	public $done = array();
	public $args = array();
	public $groups = array();
	public $group = 0;
	// tiFy_Minify_Styles
	public $wp_styles;
	public $uri;
	public $concat = array();

	/**
	 * Initialisation de la classe
	 */
	function __construct( $args = array() ){
		extract( $args );

		foreach( array( 'wp_styles', 'uri' ) as $arg )
			if( isset( ${$arg} ) )
				$this->{$arg} = ${$arg};

				add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ), 100 );
	}

	/**
	 *
	 */
	function parse_concat(){
		$defaults = array(
				'src' 		=> array(),
				'deps' 		=> array(),
				'handles' 	=> array()
		);
		$this->concat = wp_parse_args( $this->concat, $defaults );
	}

	/**
	 *
	 */
	function add_concat( $handle, $src, $deps = array() ){
		// Sources
		array_push( $this->concat['src'], '//' .trim( $src, '/' ) );
		// Accroches
		array_push( $this->concat['handles'], $handle );
		// Dépendances
		foreach( $deps as $dep )
			array_push( $this->concat['deps'], $dep );
	}

	/**
	 *
	 */
	function wp_print_styles(){
		if( is_admin() )
			return;

			$this->parse_concat();

			foreach( $this->wp_styles->registered as $reg ) :
			$this->add( $reg->handle, $reg->src, $reg->deps, $reg->ver, $reg->args );
			foreach( (array)  $reg->extra as $key => $value )
				$this->add_data( $reg->handle, $key, $value );

				if( ! in_array( $reg->handle, $this->wp_styles->queue ) )
					continue;

					$this->enqueue( $reg->handle );
					endforeach;

					$this->do_items( );
					if( ! empty( $this->concat['src'] ) ) :
					$this->concat['deps'] = array_diff( $this->concat['deps'], $this->concat['handles'] );
					$this->wp_styles->add( 'minify', $this->uri .'/min/f='. join( ',', array_map( create_function( '$src', 'return trim( $src, "/" );' ), $this->concat['src'] ) ), $this->concat['deps'] );
					$this->wp_styles->enqueue( 'minify' );
					endif;

					// Génération de la sortie
					//var_dump( Minify::combine( $this->concat['src'] ) ); exit;
	}

	/**
	 *
	 */
	public function do_item( $handle ) {
		if ( !parent::do_item($handle) )
			return false;

			$src = $this->registered[$handle]->src;

			if( preg_match( '#'. preg_quote( site_url() ) .'#', $src ) )
				$src = preg_replace( '#'. preg_quote( site_url() ) .'#', '', $src );

				if( ! file_exists( ABSPATH.$src ) )
					return false;

					$this->wp_styles->dequeue( $handle );

					$this->add_concat( $handle, $src, $this->registered[$handle]->deps );

					return true;
	}
}