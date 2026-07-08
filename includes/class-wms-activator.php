<?php
defined( 'ABSPATH' ) || exit;

class WMS_Activator {

	public static function activate() {
		self::create_tables();
		self::set_default_options();
		flush_rewrite_rules();
	}

	private static function create_tables() {
		require_once WMS_PLUGIN_DIR . 'includes/database/class-wms-db-schema.php';
		WMS_DB_Schema::create_tables();
	}

	private static function set_default_options() {
		if ( false === get_option( 'wms_general_settings' ) ) {
			update_option( 'wms_general_settings', array(
				'return_window_days'     => 30,
				'enable_returns'         => true,
				'enable_exchanges'       => true,
				'enable_store_credit'    => false,
				'rma_prefix'             => 'RMA',
				'guest_returns'          => true,
				'require_attachment'     => false,
				'max_attachments'        => 5,
				'return_button_text'     => 'درخواست مرجوعی',
				'hide_button_after_days' => 0,
				'custom_notes'           => '',
			) );
		}

		if ( false === get_option( 'wms_return_reasons' ) ) {
			update_option( 'wms_return_reasons', array(
				'defective'    => 'معیوب / آسیب‌دیده',
				'wrong_item'   => 'کالای اشتباه ارسال شده',
				'not_as_desc'  => 'مطابق توضیحات نیست',
				'changed_mind' => 'نظرم عوض شده',
				'better_price' => 'قیمت بهتر پیدا کردم',
				'late_delivery' => 'تاخیر در ارسال',
				'other'        => 'سایر',
			) );
		}

		if ( false === get_option( 'wms_email_settings' ) ) {
			update_option( 'wms_email_settings', array(
				'admin_email'   => get_option( 'admin_email' ),
				'from_name'     => get_bloginfo( 'name' ),
				'from_email'    => get_option( 'woocommerce_email_from_address' ),
				'enable_emails' => true,
			) );
		}

		if ( false === get_option( 'wms_auto_approve_rules' ) ) {
			update_option( 'wms_auto_approve_rules', array(
				'enabled'          => false,
				'max_order_value'  => 0,
				'max_return_count' => 0,
				'allowed_reasons'  => array(),
			) );
		}

		if ( false === get_option( 'wms_rma_counter' ) ) {
			update_option( 'wms_rma_counter', 0 );
		}

		if ( false === get_option( 'wms_db_version' ) ) {
			update_option( 'wms_db_version', WMS_DB_VERSION );
		}
	}
}
