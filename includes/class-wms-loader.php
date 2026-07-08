<?php
defined( 'ABSPATH' ) || exit;

class WMS_Loader {

	private $actions = array();
	private $filters = array();

	public function run() {
		$this->load_dependencies();
		$this->register_hooks();
	}

	private function load_dependencies() {
		$files = array(
			'includes/utils/class-wms-helpers.php',
			'includes/utils/class-wms-logger.php',
			'includes/database/class-wms-db-schema.php',
			'includes/database/class-wms-db-migrations.php',
			'includes/core/class-wms-return-handler.php',
			'includes/core/class-wms-eligibility-checker.php',
			'includes/core/class-wms-rma-manager.php',
			'includes/core/class-wms-restocking.php',
			'includes/post-types/class-wms-return-request.php',
			'includes/admin/class-wms-admin.php',
			'includes/admin/class-wms-admin-dashboard.php',
			'includes/admin/class-wms-settings.php',
			'includes/frontend/class-wms-frontend.php',
			'includes/frontend/class-wms-guest-portal.php',
			'includes/frontend/class-wms-shortcodes.php',
		);
		foreach ( $files as $file ) {
			$path = WMS_PLUGIN_DIR . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}
	}

	private function register_hooks() {
		// Post types.
		$return_request = new WMS_Return_Request();
		add_action( 'init', array( $return_request, 'register_post_type' ) );
		add_action( 'init', array( $return_request, 'register_statuses' ) );

		// Admin.
		$admin = new WMS_Admin();
		add_action( 'admin_menu', array( $admin, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_assets' ) );

		// Dashboard AJAX.
		$dashboard = new WMS_Admin_Dashboard();
		add_action( 'wp_ajax_wms_update_status', array( $dashboard, 'ajax_update_status' ) );

		// Settings.
		$settings = new WMS_Settings();
		add_action( 'admin_init', array( $settings, 'register_settings' ) );

		// Frontend.
		$frontend = new WMS_Frontend();
		add_filter( 'woocommerce_account_menu_items', array( $frontend, 'add_return_tab' ) );
		add_action( 'init', array( $frontend, 'add_return_endpoint' ) );
		add_action( 'woocommerce_account_return-requests_endpoint', array( $frontend, 'render_return_page' ) );
		add_action( 'wp_enqueue_scripts', array( $frontend, 'enqueue_assets' ) );

		// Guest portal.
		$guest = new WMS_Guest_Portal();
		add_action( 'init', array( $guest, 'add_guest_endpoint' ) );
		add_action( 'woocommerce_account_guest-return_endpoint', array( $guest, 'render_guest_form' ) );

		// Shortcodes.
		$shortcodes = new WMS_Shortcodes();
		add_shortcode( 'wms_return_form', array( $shortcodes, 'render_return_form' ) );

		// AJAX.
		add_action( 'wp_ajax_wms_submit_return', array( $frontend, 'ajax_submit_return' ) );
		add_action( 'wp_ajax_nopriv_wms_submit_return', array( $frontend, 'ajax_submit_return' ) );
		add_action( 'wp_ajax_wms_submit_guest_return', array( $guest, 'ajax_submit_guest_return' ) );
		add_action( 'wp_ajax_wms_cancel_return', array( $frontend, 'ajax_cancel_return' ) );
	}
}
