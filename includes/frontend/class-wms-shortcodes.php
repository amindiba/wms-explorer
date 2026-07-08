<?php
defined( 'ABSPATH' ) || exit;

class WMS_Shortcodes {

	public function render_return_form( $atts ) {
		$atts = shortcode_atts( array( 'order_id' => 0 ), $atts, 'wms_return_form' );
		if ( ! is_user_logged_in() ) {
			return '<p style="direction:rtl;text-align:right;">برای درخواست مرجوعی باید وارد شوید.</p>';
		}
		$customer_id = get_current_user_id();
		$handler     = new WMS_Return_Handler();
		$requests    = $handler->get_requests_for_customer( $customer_id );
		$settings    = get_option( 'wms_general_settings', array() );
		$eligible_orders = array();

		if ( $atts['order_id'] ) {
			$checker = new WMS_Eligibility_Checker();
			$result  = $checker->check_order_eligibility( absint( $atts['order_id'] ) );
			if ( is_wp_error( $result ) ) {
				return '<p class="wms-error" style="direction:rtl;">' . esc_html( $result->get_error_message() ) . '</p>';
			}
			$order = wc_get_order( $atts['order_id'] );
			$items = $result;
			ob_start();
			include WMS_PLUGIN_DIR . 'templates/my-account/return-request-form.php';
			return ob_get_clean();
		}

		$orders = wc_get_orders( array(
			'customer_id' => $customer_id,
			'status'      => array( 'wc-completed', 'wc-processing' ),
			'limit'       => 20,
		) );
		$checker = new WMS_Eligibility_Checker();
		foreach ( $orders as $order ) {
			$result = $checker->check_order_eligibility( $order->get_id() );
			if ( ! is_wp_error( $result ) ) {
				$eligible_orders[] = array( 'order' => $order, 'items' => $result );
			}
		}

		ob_start();
		include WMS_PLUGIN_DIR . 'templates/my-account/return-list.php';
		return ob_get_clean();
	}
}
