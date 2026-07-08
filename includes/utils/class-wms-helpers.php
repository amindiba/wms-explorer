<?php
defined( 'ABSPATH' ) || exit;

class WMS_Helpers {

	public static function get_option( $option_name, $default = '' ) {
		$settings = get_option( 'wms_general_settings', array() );
		return isset( $settings[ $option_name ] ) ? $settings[ $option_name ] : $default;
	}

	public static function update_option( $option_name, $value ) {
		$settings                  = get_option( 'wms_general_settings', array() );
		$settings[ $option_name ] = $value;
		update_option( 'wms_general_settings', $settings );
	}

	public static function get_return_reasons() {
		return get_option( 'wms_return_reasons', array() );
	}

	public static function format_amount( $amount, $currency = '' ) {
		if ( empty( $currency ) ) {
			$currency = get_woocommerce_currency();
		}
		return wc_price( $amount, array( 'currency' => $currency ) );
	}

	public static function get_customer_name( $customer_id ) {
		if ( ! $customer_id ) {
			return '';
		}
		$user = get_user_by( 'id', $customer_id );
		return $user ? $user->display_name : '';
	}

	public static function get_guest_return_url( $order_id, $email ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return '';
		}
		return add_query_arg(
			array(
				'order' => $order_id,
				'email' => rawurlencode( $email ),
				'key'   => $order->get_order_key(),
			),
			wc_get_account_endpoint_url( 'guest-return' )
		);
	}
}
