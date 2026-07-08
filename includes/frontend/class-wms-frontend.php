<?php
defined( 'ABSPATH' ) || exit;

class WMS_Frontend {

	public function add_return_tab( $items ) {
		$logout = $items['customer-logout'];
		unset( $items['customer-logout'] );
		$items['return-requests'] = 'مرجوعی‌های من';
		$items['customer-logout'] = $logout;
		return $items;
	}

	public function add_return_endpoint() {
		add_rewrite_endpoint( 'return-requests', EP_ROOT | EP_PAGES );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'wms-frontend', WMS_PLUGIN_URL . 'assets/css/frontend/frontend.css', array(), WMS_VERSION );
		wp_enqueue_script( 'wms-frontend', WMS_PLUGIN_URL . 'assets/js/frontend/frontend.js', array( 'jquery' ), WMS_VERSION, true );
		wp_localize_script( 'wms-frontend', 'wmsFrontend', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wms_return_nonce' ),
		) );
	}

	public function render_return_page() {
		$customer_id = get_current_user_id();
		if ( ! $customer_id ) {
			return;
		}

		$handler  = new WMS_Return_Handler();
		$requests = $handler->get_requests_for_customer( $customer_id );

		$settings       = get_option( 'wms_general_settings', array() );
		$eligible_orders = $this->get_eligible_orders( $customer_id );

		include WMS_PLUGIN_DIR . 'templates/my-account/return-list.php';
	}

	private function get_eligible_orders( $customer_id ) {
		$orders = wc_get_orders( array(
			'customer_id' => $customer_id,
			'status'      => array( 'wc-completed', 'wc-processing' ),
			'limit'       => 20,
			'orderby'     => 'date',
			'order'       => 'DESC',
		) );
		$eligible = array();
		$checker  = new WMS_Eligibility_Checker();
		foreach ( $orders as $order ) {
			$result = $checker->check_order_eligibility( $order->get_id() );
			if ( ! is_wp_error( $result ) ) {
				$eligible[] = array( 'order' => $order, 'items' => $result );
			}
		}
		return $eligible;
	}

	public function ajax_submit_return() {
		check_ajax_referer( 'wms_return_nonce', 'nonce' );
		$customer_id = get_current_user_id();
		if ( ! $customer_id ) {
			wp_send_json_error( array( 'message' => 'برای ارسال درخواست باید وارد شوید.' ) );
		}

		$order_id        = absint( $_POST['order_id'] ?? 0 );
		$return_reason   = sanitize_text_field( wp_unslash( $_POST['return_reason'] ?? '' ) );
		$return_notes    = sanitize_textarea_field( wp_unslash( $_POST['return_notes'] ?? '' ) );
		$resolution_type = sanitize_text_field( wp_unslash( $_POST['resolution_type'] ?? 'refund' ) );

		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_customer_id() !== $customer_id ) {
			wp_send_json_error( array( 'message' => 'سفارش نامعتبر است.' ) );
		}

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
			'customer_id'     => $customer_id,
			'customer_email'  => $order->get_billing_email(),
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

	public function ajax_cancel_return() {
		check_ajax_referer( 'wms_return_nonce', 'nonce' );
		$customer_id = get_current_user_id();
		if ( ! $customer_id ) {
			wp_send_json_error( array( 'message' => 'برای لغو درخواست باید وارد شوید.' ) );
		}
		$request_id = absint( $_POST['request_id'] ?? 0 );
		if ( ! $request_id ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر است.' ) );
		}
		$handler = new WMS_Return_Handler();
		$request = $handler->get_request( $request_id );
		if ( ! $request || absint( $request->customer_id ) !== $customer_id ) {
			wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز.' ) );
		}
		if ( 'pending' !== $request->status ) {
			wp_send_json_error( array( 'message' => 'فقط درخواست‌های در انتظار قابل لغو هستند.' ) );
		}
		$result = $handler->update_status( $request_id, 'cancelled', 0 );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'درخواست مرجوعی لغو شد.' ) );
	}
}
