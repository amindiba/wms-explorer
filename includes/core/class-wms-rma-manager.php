<?php
defined( 'ABSPATH' ) || exit;

class WMS_RMA_Manager {

	public static function generate_rma_number() {
		$settings = get_option( 'wms_general_settings', array() );
		$prefix   = $settings['rma_prefix'] ?? 'RMA';
		$year     = gmdate( 'Y' );
		$counter  = get_option( 'wms_rma_counter', 0 );
		$counter  = absint( $counter ) + 1;
		update_option( 'wms_rma_counter', $counter );
		return sprintf( '%s-%s-%04d', $prefix, $year, $counter );
	}

	public static function parse_rma_number( $rma_number ) {
		if ( preg_match( '/^([A-Z]+)-(\d{4})-(\d{4})$/', $rma_number, $matches ) ) {
			return array(
				'prefix'   => $matches[1],
				'year'     => $matches[2],
				'sequence' => (int) $matches[3],
			);
		}
		return false;
	}
}
