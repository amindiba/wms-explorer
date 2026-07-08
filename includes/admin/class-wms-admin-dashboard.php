<?php
defined( 'ABSPATH' ) || exit;

class WMS_Admin_Dashboard {

	public function render_dashboard() {
		global $wpdb;

		if ( isset( $_POST['wms_update_status_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wms_update_status_nonce'] ?? '' ) ), 'wms_update_status' ) ) {
			$request_id = absint( $_POST['request_id'] ?? 0 );
			$new_status = sanitize_text_field( wp_unslash( $_POST['new_status'] ?? '' ) );
			if ( $request_id && $new_status ) {
				$handler = new WMS_Return_Handler();
				$handler->update_status( $request_id, $new_status, get_current_user_id() );
				echo '<div class="notice notice-success" style="direction:rtl;text-align:right;"><p>وضعیت به‌روزرسانی شد.</p></div>';
			}
		}

		$page         = max( 1, absint( $_GET['paged'] ?? 1 ) );
		$per_page     = 20;
		$offset       = ( $page - 1 ) * $per_page;
		$status_filter = sanitize_text_field( wp_unslash( $_GET['status'] ?? '' ) );

		$where = '1=1';
		$args  = array();
		if ( $status_filter ) {
			$where     .= ' AND rr.status = %s';
			$args[]     = $status_filter;
		}

		$total_q = "SELECT COUNT(*) FROM {$wpdb->prefix}wms_return_requests rr WHERE {$where}";
		$total   = $args ? (int) $wpdb->get_var( $wpdb->prepare( $total_q, ...$args ) ) : (int) $wpdb->get_var( $total_q );

		$data_q  = "SELECT rr.* FROM {$wpdb->prefix}wms_return_requests rr WHERE {$where} ORDER BY rr.created_at DESC LIMIT %d OFFSET %d";
		$q_args  = array_merge( $args, array( $per_page, $offset ) );
		$requests    = $wpdb->get_results( $wpdb->prepare( $data_q, ...$q_args ) );
		$total_pages = ceil( $total / $per_page );

		$status_counts = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$wpdb->prefix}wms_return_requests GROUP BY status",
			OBJECT_K
		);

		include WMS_PLUGIN_DIR . 'templates/admin/claims-dashboard.php';
	}

	public function ajax_update_status() {
		check_ajax_referer( 'wms_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز.' ) );
		}
		$request_id = absint( $_POST['request_id'] ?? 0 );
		$new_status = sanitize_text_field( wp_unslash( $_POST['new_status'] ?? '' ) );
		if ( ! $request_id || ! $new_status ) {
			wp_send_json_error( array( 'message' => 'پارامترها نامعتبر هستند.' ) );
		}
		$handler = new WMS_Return_Handler();
		$result  = $handler->update_status( $request_id, $new_status, get_current_user_id() );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'وضعیت به‌روزرسانی شد.' ) );
	}
}
