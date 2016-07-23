<?php
function check_for_csv_and_overload( $served, $result, $request, $wp_rest_server ) {
	if ( ! isset( $_GET['_csv'] ) ) {
		return $served;
	}
	if ( empty( $result->data ) ) {
		return $served;	
	}
	$file = 'report.csv';
	header( "Content-Type: ;charset=utf-8" );
	header( "Content-Disposition: attachment;filename=\"$file\"" );
	header( "Pragma: no-cache" );
	header( "Expires: 0" );
	$csv = fopen('php://output', 'w');
	$done = false;
	foreach( $result->data as $post ) {
		
		// Do first csv column row
		if ( ! $done ) {
			$cols = jt_add_column_headers( $post );
			fputcsv( $csv, $cols );
			$done = true;
		}
		$values = array();
		// Get some column values
		foreach ( $post as $column => $val ) {
			if ( ! in_array( $column, array(
				'_links',
				'guid',
				'content',
			) ) )	{
				$values[] = jt_assign_value( $val );
			}
		}
		// Get associated links
		if ( isset( $post['_links'] ) ) {
			foreach ( $post['_links'] as $column => $col_value ) {
				if ( in_array( $column, array(
					'self',
					'collection',
					'author',
					'replies',
					'version-history',
					'http://v2.wp-api.org/attachment',
				) ) )	{
					continue;
				}
				foreach ( $col_value as $val ) {
					if ( isset( $val['href'] ) ) {
						$values[] = $val['href'];
					}
				}
			}
		}
		// and update the csv row
		fputcsv( $csv, $values );
	}
	// Download it
	fclose( $csv );
	exit();
}
add_filter( 'rest_pre_serve_request', 'check_for_csv_and_overload', 10, 4 );
function jt_add_column_headers( $post ) {
	$cols = array();
	foreach ( array_keys( (array) $post ) as $column ) {
		if ( ! in_array( $column, array(
			'_links',
			'guid',
			'content',
		) ) )	{
			$cols[] = $column;
		}
	}
	if ( isset( $post['_links'] ) ) {
		foreach ( $post['_links'] as $column => $col_value ) {
			if ( in_array( $column, array(
				'self',
				'collection',
				'author',
				'replies',
				'version-history',
				'http://v2.wp-api.org/attachment',
			) ) )	{
				continue;
			}
			foreach ( $col_value as $val ) {
				if ( isset( $val['href'] ) ) {
					$cols[] = isset( $val['taxonomy'] ) ? $val['taxonomy'] : $val['href'];
				}
			}
		}
	}
	error_log( '$cols: '. print_r( $cols, true ) );
	return $cols;
}
function jt_assign_value( $value ) {
	if ( isset( $value['rendered'] ) ) {
		$value = $value['rendered'];
	} elseif ( is_scalar( $value ) ) {
		$value = $value;
	} else {
		$value = 'needs-parsing';
	}
	return $value;
}