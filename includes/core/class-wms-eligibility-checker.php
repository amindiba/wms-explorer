<?php
defined( 'ABSPATH' ) || exit;

class WMS_Eligibility_Checker {

	public function check_order_eligibility( $order_id, $product_ids = array() ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', 'سفارش نامعتبر است.' );
		}

		$settings = get_option( 'wms_general_settings', array() );

		if ( empty( $settings['enable_returns'] ) ) {
			return new WP_Error( 'returns_disabled', 'در حال حاضر مرجوعی غیرفعال است.' );
		}

		$window_days = absint( $settings['return_window_days'] ?? 30 );
		if ( $window_days > 0 ) {
			$order_date = $order->get_date_completed() ?: $order->get_date_created();
			if ( $order_date ) {
				$days_since = (int) ( ( current_time( 'timestamp' ) - $order_date->getTimestamp() ) / DAY_IN_SECONDS );
				if ( $days_since > $window_days ) {
					return new WP_Error( 'window_expired', "مهلت مرجوعی تمام شده است ({$window_days} روز)." );
				}
			}
		}

		$allowed_statuses = array( 'completed', 'processing' );
		if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
			return new WP_Error( 'invalid_status', 'این سفارش در وضعیت فعلی قابل مرجوعی نیست.' );
		}

		$items = $order->get_items();
		if ( ! empty( $product_ids ) ) {
			$items = array_filter( $items, function ( $item ) use ( $product_ids ) {
				return in_array( $item->get_product_id(), $product_ids, true );
			} );
		}

		if ( empty( $items ) ) {
			return new WP_Error( 'no_items', 'هیچ آیتم واجد شرایطی برای مرجوعی یافت نشد.' );
		}

		$eligible = array();
		foreach ( $items as $item ) {
			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();
			$product      = wc_get_product( $variation_id ?: $product_id );
			if ( ! $product ) {
				continue;
			}

			$product_returns = get_post_meta( $product_id, '_wms_returns_enabled', true );
			if ( 'no' === $product_returns ) {
				continue;
			}

			$already_returned = $this->get_already_returned_quantity( $item->get_id() );
			$available        = $item->get_quantity() - $already_returned;

			if ( $available > 0 ) {
				$eligible[] = array(
					'order_item_id' => $item->get_id(),
					'product_id'    => $product_id,
					'variation_id'  => $variation_id,
					'product_name'  => $item->get_name(),
					'quantity'      => $available,
					'line_total'    => (float) $item->get_total(),
				);
			}
		}

		if ( empty( $eligible ) ) {
			return new WP_Error( 'all_returned', 'همه اقلام این سفارش قبلا مرجوعی داده شده‌اند.' );
		}

		return $eligible;
	}

	private function get_already_returned_quantity( $order_item_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COALESCE(SUM(ri.quantity), 0)
			FROM {$wpdb->prefix}wms_return_items ri
			INNER JOIN {$wpdb->prefix}wms_return_requests rr ON ri.request_id = rr.id
			WHERE ri.order_item_id = %d AND rr.status IN ('pending', 'approved')",
			$order_item_id
		) );
	}

	public function check_auto_approve_eligibility( $customer_id ) {
		$rules = get_option( 'wms_auto_approve_rules', array() );
		if ( empty( $rules['enabled'] ) || ! $customer_id ) {
			return false;
		}
		$max_count = absint( $rules['max_return_count'] ?? 0 );
		if ( $max_count > 0 ) {
			$count = $this->get_customer_return_count( $customer_id );
			if ( $count >= $max_count ) {
				return false;
			}
		}
		return true;
	}

	private function get_customer_return_count( $customer_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wms_return_requests WHERE customer_id = %d", $customer_id
		) );
	}
}
