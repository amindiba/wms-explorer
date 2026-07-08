<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/database/class-wms-db-schema.php';
WMS_DB_Schema::drop_tables();

delete_option( 'wms_general_settings' );
delete_option( 'wms_return_reasons' );
delete_option( 'wms_email_settings' );
delete_option( 'wms_auto_approve_rules' );
delete_option( 'wms_rma_counter' );
delete_option( 'wms_db_version' );
