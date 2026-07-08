<?php
defined( 'ABSPATH' ) || exit;

class WMS_Guest_Portal {

	public function add_guest_endpoint() {
		add_rewrite_endpoint( 'guest-return', EP_ROOT | EP_PAGES );
	}

	public function render_guest_form() {
		$settings = get_option( 'wms_general_settings', array() );
		if ( empty( $settings['guest_returns'] ) ) {
			echo '<p style="direction:rtl;text-align:right;">مرجوعی مهمان غیرفعال است.</p>';
			return;
		}

		$order_id = absint( $_GET['order'] ?? 0 );
		$email    = sanitize_email( wp_unslash( $_GET['email'] ?? '' ) );
		$key      = sanitize_text_field( wp_unslash( $_GET['key'] ?? '' ) );

		$authenticated = false;
		$order         = null;
		$eligible_items = array();

		if ( $order_id && $email && $key ) {
			$order = wc_get_order( $order_id );
			if ( $order && $order->get_order_key() === $key && $order->get_billing_email() === $email ) {
				$authenticated = true;
				$checker       = new WMS_Eligibility_Checker();
				$result        = $checker->check_order_eligibility( $order_id );
				if ( ! is_wp_error( $result ) ) {
					$eligible_items = $result;
				}
			}
		}

		include WMS_PLUGIN_DIR . 'templates/my-account/guest-return-form.php';
	}

	public function ajax_submit_guest_return() {
		$order_id = absint( $_POST['order_id'] ?? 0 );
		$email    = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$key      = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

		if ( ! $order_id || ! $email || ! $key ) {
			wp_send_json_error( array( 'message' => 'فیلدهای ضروری وجود ندارند.' ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_order_key() !== $key || $order->get_billing_email() !== $email ) {
			wp_send_json_error( array( 'message' => 'اطلاعات سفارش نامعتبر است.' ) );
		}

		$return_reason   = sanitize_text_field( wp_unslash( $_POST['return_reason'] ?? '' ) );
		$return_notes    = sanitize_textarea_field( wp_unslash( $_POST['return_notes'] ?? '' ) );
		$resolution_type = sanitize_text_field( wp_unslash( $_POST['resolution_type'] ?? 'refund' ) );

		$items     = array();
		$raw_items = json_decode( sanitize_text_field( wp_unslash( $_POST['items'] ?? '[]' ) ), true );
		if ( is_array( $raw_items ) ) {
			foreach ( $raw_items as $item_data ) {
				$order_item = $order->get_item( absint( $item_data['order_item_id'] ?? 0 ) );
				if ( ! $order_item ) {
					continue;
				}
				$qty    = absint( $item_data['quantity'] ?? 1 );
				$amount = (float) $order_item->get_total() * ( $qty / $order_item->get_quantity() );
				$items[] = array(
					'order_item_id' => absint( $item_data['order_item_id'] ),
					'product_id'    => $order_item->get_product_id(),
					'variation_id'  => $order_item->get_variation_id(),
					'quantity'      => $qty,
					'refund_amount' => $amount,
					'resolution'    => $resolution_type,
				);
			}
		}

		if ( empty( $items ) ) {
			wp_send_json_error( array( 'message' => 'هیچ آیتمی انتخاب نشده.' ) );
		}

		$handler = new WMS_Return_Handler();
		$result  = $handler->create_return_request( array(
			'order_id'        => $order_id,
			'customer_id'     => 0,
			'customer_email'  => $email,
			'return_reason'   => $return_reason,
			'return_notes'    => $return_notes,
			'resolution_type' => $resolution_type,
			'items'           => $items,
		) );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'درخواست مرجوعی با موفقیت ثبت شد.' ) );
	}
}
