<?php
defined( 'ABSPATH' ) || exit;

class WMS_Restocking {

	public function restock_return_items( $request_id ) {
		global $wpdb;
		$items = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wms_return_items WHERE request_id = %d AND resolution = 'refund'", $request_id
		) );
		foreach ( $items as $item ) {
			$product = wc_get_product( $item->variation_id ?: $item->product_id );
			if ( ! $product || ! $product->managing_stock() ) {
				continue;
			}
			$product->set_stock_quantity( $product->get_stock_quantity() + $item->quantity );
			$product->save();
			WMS_Logger::log_return_action( $request_id, 'restocked', array(
				'product_id' => $item->product_id, 'quantity' => $item->quantity,
			) );
		}
	}

	public function deplete_exchange_stock( $request_id ) {
		global $wpdb;
		$items = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}wms_return_items WHERE request_id = %d AND resolution = 'exchange'", $request_id
		) );
		foreach ( $items as $item ) {
			if ( ! $item->exchange_to_product_id ) {
				continue;
			}
			$product = wc_get_product( $item->exchange_to_product_id );
			if ( ! $product || ! $product->managing_stock() ) {
				continue;
			}
			$product->set_stock_quantity( max( 0, $product->get_stock_quantity() - $item->quantity ) );
			$product->save();
		}
	}
}
