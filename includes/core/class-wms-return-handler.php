<?php
defined( 'ABSPATH' ) || exit;

class WMS_Return_Handler {

	public function create_return_request( $data ) {
		global $wpdb;

		$required = array( 'order_id', 'return_reason', 'resolution_type', 'items' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error( 'missing_field', "فیلد ضروری وجود ندارد: {$field}" );
			}
		}

		$order = wc_get_order( $data['order_id'] );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', 'سفارش نامعتبر است.' );
		}

		$rma_number = WMS_RMA_Manager::generate_rma_number();

		$total_refund = 0;
		foreach ( $data['items'] as $item ) {
			$total_refund += floatval( $item['refund_amount'] ?? 0 );
		}

		$request_id = $wpdb->insert(
			$wpdb->prefix . 'wms_return_requests',
			array(
				'rma_number'      => $rma_number,
				'order_id'        => absint( $data['order_id'] ),
				'customer_id'     => absint( $data['customer_id'] ?? 0 ),
				'customer_email'  => sanitize_email( $data['customer_email'] ?? '' ),
				'status'          => 'pending',
				'return_reason'   => sanitize_textarea_field( $data['return_reason'] ),
				'return_notes'    => sanitize_textarea_field( $data['return_notes'] ?? '' ),
				'resolution_type' => sanitize_text_field( $data['resolution_type'] ),
				'refund_amount'   => $total_refund,
				'created_at'      => current_time( 'mysql' ),
				'updated_at'      => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s' )
		);

		if ( ! $request_id ) {
			return new WP_Error( 'db_error', 'خطا در ایجاد درخواست مرجوعی.' );
		}

		foreach ( $data['items'] as $item ) {
			$wpdb->insert(
				$wpdb->prefix . 'wms_return_items',
				array(
					'request_id'             => $request_id,
					'order_item_id'          => absint( $item['order_item_id'] ),
					'product_id'             => absint( $item['product_id'] ),
					'variation_id'           => absint( $item['variation_id'] ?? 0 ),
					'quantity'               => absint( $item['quantity'] ?? 1 ),
					'refund_amount'          => floatval( $item['refund_amount'] ?? 0 ),
					'resolution'             => sanitize_text_field( $item['resolution'] ?? 'refund' ),
					'exchange_to_product_id' => absint( $item['exchange_to_product_id'] ?? 0 ),
				),
				array( '%d', '%d', '%d', '%d', '%d', '%f', '%s', '%d' )
			);
		}

		$this->add_note( $request_id, 0, 'system', "درخواست مرجوعی با شماره {$rma_number} ثبت شد." );

		$order->add_order_note( "درخواست مرجوعی ثبت شد (RMA: {$rma_number})" );
		$order->set_status( 'wms-pending-return' );
		$order->save();

		WMS_Logger::log_return_action( $request_id, 'created', array( 'rma' => $rma_number, 'order_id' => $data['order_id'] ) );

		return $request_id;
	}

	public function update_status( $request_id, $new_status, $admin_id = 0 ) {
		global $wpdb;

		$current = $wpdb->get_var( $wpdb->prepare(
			"SELECT status FROM {$wpdb->prefix}wms_return_requests WHERE id = %d", $request_id
		) );

		if ( ! $current ) {
			return new WP_Error( 'not_found', 'درخواست مرجوعی یافت نشد.' );
		}

		$update = array(
			'status'     => $new_status,
			'updated_at' => current_time( 'mysql' ),
		);

		if ( 'approved' === $new_status ) {
			$update['approved_by'] = $admin_id;
			$update['approved_at'] = current_time( 'mysql' );
		}

		$wpdb->update( $wpdb->prefix . 'wms_return_requests', $update, array( 'id' => $request_id ) );

		$this->add_note( $request_id, $admin_id, 'admin', "وضعیت از «{$current}» به «{$new_status}» تغییر کرد." );

		$req = $wpdb->get_row( $wpdb->prepare(
			"SELECT order_id FROM {$wpdb->prefix}wms_return_requests WHERE id = %d", $request_id
		) );

		if ( $req ) {
			$order = wc_get_order( $req->order_id );
			if ( $order ) {
				$map = array(
					'pending'   => 'wms-pending-return',
					'approved'  => 'wms-return-approved',
					'rejected'  => 'wms-return-rejected',
					'refunded'  => 'completed',
					'exchanged' => 'completed',
					'cancelled' => 'cancelled',
				);
				if ( isset( $map[ $new_status ] ) ) {
					$order->set_status( $map[ $new_status ] );
					$order->save();
				}
			}
		}

		WMS_Logger::log_return_action( $request_id, 'status_changed', array( 'from' => $current, 'to' => $new_status ) );

		return true;
	}

	public function add_note( $request_id, $author_id, $author_type, $note ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'wms_return_notes',
			array(
				'request_id'  => $request_id,
				'author_id'   => $author_id,
				'author_type' => $author_type,
				'note'        => $note,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s' )
		);
	}

	public function get_request( $request_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wms_return_requests WHERE id = %d", $request_id
		) );
	}

	public function get_request_items( $request_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wms_return_items WHERE request_id = %d", $request_id
		) );
	}

	public function get_requests_for_order( $order_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wms_return_requests WHERE order_id = %d ORDER BY created_at DESC", $order_id
		) );
	}

	public function get_requests_for_customer( $customer_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wms_return_requests WHERE customer_id = %d ORDER BY created_at DESC", $customer_id
		) );
	}

	public static function get_pending_count() {
		global $wpdb;
		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wms_return_requests WHERE status = 'pending'"
		);
	}
}
