<?php
/**
 * Plugin Name:       مدیریت مرجوعی و بازپرداخت ووکامرس
 * Plugin URI:        https://zoroo.ir
 * Description:       سیستم جامع مدیریت مرجوعی، بازپرداخت و مبادله کالا در ووکامرس — پورتال مشتری، شماره RMA، تایید سفارشی و اعلان‌ها
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            تیم زورو
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wms-explorer
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * @package WMS_Explorer
 */

defined( 'ABSPATH' ) || exit;

define( 'WMS_VERSION', '1.0.0' );
define( 'WMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WMS_DB_VERSION', '1.0.0' );

spl_autoload_register(
	function ( $class ) {
		$prefix = 'WMS_';
		$len    = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}
		$relative = substr( $class, $len );
		$slug     = strtolower( str_replace( '_', '-', $relative ) );
		$paths    = array(
			WMS_PLUGIN_DIR . 'includes/class-wms-' . $slug . '.php',
			WMS_PLUGIN_DIR . 'includes/' . $slug . '.php',
		);
		$subdir_map = array(
			'admin'                => 'includes/admin/',
			'admin-dashboard'      => 'includes/admin/',
			'settings'             => 'includes/admin/',
			'frontend'             => 'includes/frontend/',
			'guest-portal'         => 'includes/frontend/',
			'shortcodes'           => 'includes/frontend/',
			'return-handler'       => 'includes/core/',
			'eligibility-checker'  => 'includes/core/',
			'rma-manager'          => 'includes/core/',
			'restocking'           => 'includes/core/',
			'return-request'       => 'includes/post-types/',
			'db-schema'            => 'includes/database/',
			'db-migrations'        => 'includes/database/',
			'helpers'              => 'includes/utils/',
			'logger'               => 'includes/utils/',
		);
		if ( isset( $subdir_map[ $slug ] ) ) {
			array_unshift( $paths, WMS_PLUGIN_DIR . $subdir_map[ $slug ] . 'class-wms-' . $slug . '.php' );
		}
		foreach ( $paths as $file ) {
			if ( file_exists( $file ) ) {
				require $file;
				return;
			}
		}
	}
);

function wms_explorer_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wms_explorer_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

function wms_explorer_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error" style="direction:rtl;text-align:right;">
		<p>افزونه مدیریت مرجوعی نیاز به نصب و فعال بودن ووکامرس دارد.</p>
	</div>
	<?php
}

function wms_explorer_init() {
	if ( ! wms_explorer_check_woocommerce() ) {
		return;
	}
	$loader = new WMS_Loader();
	$loader->run();
}
add_action( 'plugins_loaded', 'wms_explorer_init' );

function wms_explorer_activate() {
	require_once WMS_PLUGIN_DIR . 'includes/class-wms-activator.php';
	WMS_Activator::activate();
}
register_activation_hook( __FILE__, 'wms_explorer_activate' );

function wms_explorer_deactivate() {
	require_once WMS_PLUGIN_DIR . 'includes/class-wms-deactivator.php';
	WMS_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'wms_explorer_deactivate' );
