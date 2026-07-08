<?php
defined( 'ABSPATH' ) || exit;

class WMS_DB_Schema {

	private static function get_charset_collate() {
		global $wpdb;
		return $wpdb->get_charset_collate();
	}

	public static function create_tables() {
		global $wpdb;
		$charset = self::get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tbl = $wpdb->prefix . 'wms_return_requests';
		dbDelta( "CREATE TABLE {$tbl} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			rma_number VARCHAR(30) NOT NULL,
			order_id BIGINT UNSIGNED NOT NULL,
			customer_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			customer_email VARCHAR(255) NOT NULL DEFAULT '',
			status VARCHAR(30) NOT NULL DEFAULT 'pending',
			return_reason TEXT NOT NULL,
			return_notes TEXT NOT NULL,
			resolution_type VARCHAR(20) NOT NULL DEFAULT 'refund',
			refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
			restocking_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
			exchange_order_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			approved_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
			approved_at DATETIME DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY rma_number (rma_number),
			KEY order_id (order_id),
			KEY customer_id (customer_id),
			KEY status (status)
		) {$charset};" );

		$tbl2 = $wpdb->prefix . 'wms_return_items';
		dbDelta( "CREATE TABLE {$tbl2} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			request_id BIGINT UNSIGNED NOT NULL,
			order_item_id BIGINT UNSIGNED NOT NULL,
			product_id BIGINT UNSIGNED NOT NULL,
			variation_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			quantity INT UNSIGNED NOT NULL DEFAULT 1,
			refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
			resolution VARCHAR(20) NOT NULL DEFAULT 'refund',
			exchange_to_product_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (id),
			KEY request_id (request_id),
			KEY product_id (product_id)
		) {$charset};" );

		$tbl3 = $wpdb->prefix . 'wms_return_attachments';
		dbDelta( "CREATE TABLE {$tbl3} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			request_id BIGINT UNSIGNED NOT NULL,
			file_path VARCHAR(500) NOT NULL,
			file_type VARCHAR(50) NOT NULL DEFAULT '',
			PRIMARY KEY (id),
			KEY request_id (request_id)
		) {$charset};" );

		$tbl4 = $wpdb->prefix . 'wms_return_notes';
		dbDelta( "CREATE TABLE {$tbl4} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			request_id BIGINT UNSIGNED NOT NULL,
			author_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			author_type VARCHAR(10) NOT NULL DEFAULT 'system',
			note TEXT NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY request_id (request_id)
		) {$charset};" );

		$tbl5 = $wpdb->prefix . 'wms_store_credit';
		dbDelta( "CREATE TABLE {$tbl5} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			customer_id BIGINT UNSIGNED NOT NULL,
			balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
			currency VARCHAR(3) NOT NULL DEFAULT 'IRR',
			PRIMARY KEY (id),
			UNIQUE KEY customer_id (customer_id)
		) {$charset};" );

		$tbl6 = $wpdb->prefix . 'wms_store_credit_logs';
		dbDelta( "CREATE TABLE {$tbl6} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			customer_id BIGINT UNSIGNED NOT NULL,
			amount DECIMAL(12,2) NOT NULL,
			type VARCHAR(20) NOT NULL,
			reference_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY customer_id (customer_id)
		) {$charset};" );

		update_option( 'wms_db_version', WMS_DB_VERSION );
	}

	public static function drop_tables() {
		global $wpdb;
		$tables = array(
			$wpdb->prefix . 'wms_return_requests',
			$wpdb->prefix . 'wms_return_items',
			$wpdb->prefix . 'wms_return_attachments',
			$wpdb->prefix . 'wms_return_notes',
			$wpdb->prefix . 'wms_store_credit',
			$wpdb->prefix . 'wms_store_credit_logs',
		);
		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}
	}
}
