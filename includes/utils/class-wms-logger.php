<?php
defined( 'ABSPATH' ) || exit;

class WMS_Logger {

	public static function log( $message, $context = 'wms-explorer' ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->info( $message, array( 'source' => $context ) );
		}
	}

	public static function log_return_action( $request_id, $action, $data = array() ) {
		$message = "درخواست مرجوعی #{$request_id}: {$action}";
		if ( ! empty( $data ) ) {
			$message .= ' | ' . wp_json_encode( $data );
		}
		self::log( $message, 'wms-return-actions' );
	}

	public static function log_refund( $request_id, $amount, $method, $admin_id = 0 ) {
		$message = "بازپرداخت درخواست #{$request_id}: " . wc_price( $amount ) . " از طریق {$method} توسط کاربر #{$admin_id}";
		self::log( $message, 'wms-refunds' );
	}
}
