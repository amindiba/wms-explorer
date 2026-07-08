<?php
defined( 'ABSPATH' ) || exit;

class WMS_Return_Request {

	const POST_TYPE = 'wms_return';

	public function register_post_type() {
		$labels = array(
			'name'               => 'درخواست‌های مرجوعی',
			'singular_name'      => 'درخواست مرجوعی',
			'menu_name'          => 'مدیریت مرجوعی',
			'all_items'          => 'همه درخواست‌ها',
			'add_new_item'       => 'افزودن درخواست جدید',
			'add_new'            => 'افزودن',
			'new_item'           => 'درخواست جدید',
			'edit_item'          => 'ویرایش درخواست',
			'view_item'          => 'مشاهده درخواست',
			'search_items'       => 'جستجو در درخواست‌ها',
			'not_found'          => 'هیچ درخواستی یافت نشد',
			'not_found_in_trash' => 'درخواستی در زباله‌دان یافت نشد',
		);
		register_post_type( self::POST_TYPE, array(
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_icon'          => 'dashicons-update-alt',
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'supports'           => array( 'title', 'editor', 'comments' ),
			'has_archive'        => false,
			'rewrite'            => false,
			'show_in_rest'       => false,
		) );
	}

	public function register_statuses() {
		$statuses = array(
			'wms-pending-return'   => 'در انتظار بررسی',
			'wms-return-approved'  => 'تایید شده',
			'wms-return-rejected'  => 'رد شده',
			'wms-refund-requested' => 'بازپرداخت درخواست شده',
			'wms-completed'        => 'تکمیل شده',
		);
		foreach ( $statuses as $slug => $label ) {
			register_post_status( $slug, array(
				'label'                     => $label,
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( "{$label} <span class=\"count\">(%s)</span>", "{$label} <span class=\"count\">(%s)</span>", 'wms-explorer' ),
			) );
		}
	}
}
