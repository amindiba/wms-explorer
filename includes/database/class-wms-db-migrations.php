<?php
defined( 'ABSPATH' ) || exit;

class WMS_DB_Migrations {

	private static $migrations = array(
		'1.0.0' => 'migrate_to_1_0_0',
	);

	public static function run_migrations() {
		$current_version = get_option( 'wms_db_version', '0.0.0' );
		foreach ( self::$migrations as $version => $method ) {
			if ( version_compare( $current_version, $version, '<' ) ) {
				if ( method_exists( __CLASS__, $method ) ) {
					self::$method();
				}
				update_option( 'wms_db_version', $version );
			}
		}
	}

	private static function migrate_to_1_0_0() {
		require_once WMS_PLUGIN_DIR . 'includes/database/class-wms-db-schema.php';
		WMS_DB_Schema::create_tables();
	}
}
