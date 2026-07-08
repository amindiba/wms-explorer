<?php
defined( 'ABSPATH' ) || exit;

class WMS_Admin {

	public function add_menu() {
		// منوی اصلی
		add_menu_page(
			'مدیریت مرجوعی',
			'مدیریت مرجوعی',
			'manage_woocommerce',
			'wms-returns',
			array( $this, 'render_dashboard' ),
			'dashicons-cart',
			56
		);

		// زیرمجموعه‌ها
		add_submenu_page(
			'wms-returns',
			'داشبورد مرجوعی‌ها',
			'داشبورد',
			'manage_woocommerce',
			'wms-returns',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'wms-returns',
			'تنظیمات مرجوعی',
			'تنظیمات',
			'manage_woocommerce',
			'wms-settings',
			array( $this, 'render_settings' )
		);
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'wms-' ) === false ) {
			return;
		}

		// رنگ متمایز منوی مرجوعی
		$color  = '#2271b1';
		$pending = WMS_Return_Handler::get_pending_count();
		?>
		<style>
			/* رنگ پس‌زمینه آیکون منو */
			#adminmenu .toplevel_page_wms-returns .wp-menu-image {
				background-color: <?php echo $color; ?> !important;
				border-radius: 4px;
			}
			#adminmenu .toplevel_page_wms-returns .wp-menu-image::before {
				color: #fff !important;
			}
			#adminmenu .toplevel_page_wms-returns:hover .wp-menu-image,
			#adminmenu .toplevel_page_wms-returns.current .wp-menu-image {
				background-color: #135e96 !important;
			}
			#adminmenu .toplevel_page_wms-returns .wp-submenu-head {
				background-color: <?php echo $color; ?> !important;
				color: #fff !important;
			}
			<?php if ( $pending > 0 ) : ?>
			/* نشان تعداد درخواست‌های در انتظار */
			#adminmenu .toplevel_page_wms-returns .wp-menu-name::after {
				content: "<?php echo esc_attr( $pending ); ?>";
				background: #d63638;
				color: #fff;
				border-radius: 50%;
				padding: 1px 6px;
				font-size: 11px;
				margin-right: 6px;
				vertical-align: middle;
				font-weight: 700;
				line-height: 1.6;
			}
			<?php endif; ?>
		</style>
		<?php

		wp_enqueue_style( 'wms-admin', WMS_PLUGIN_URL . 'assets/css/admin/admin.css', array(), WMS_VERSION );
		wp_enqueue_script( 'wms-admin', WMS_PLUGIN_URL . 'assets/js/admin/admin.js', array( 'jquery' ), WMS_VERSION, true );
		wp_localize_script( 'wms-admin', 'wmsAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wms_admin_nonce' ),
			'i18n'    => array(
				'confirmApprove' => 'آیا از تایید این مرجوعی مطمئن هستید؟',
				'confirmReject'  => 'آیا از رد این مرجوعی مطمئن هستید؟',
				'statusUpdated'  => 'وضعیت به‌روزرسانی شد.',
				'error'          => 'خطایی رخ داد.',
			),
		) );
	}

	public function render_dashboard() {
		$dashboard = new WMS_Admin_Dashboard();
		$dashboard->render_dashboard();
	}

	public function render_settings() {
		$settings = new WMS_Settings();
		$settings->render_page();
	}
}
