<?php
defined( 'ABSPATH' ) || exit;

class WMS_Deactivator {
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
