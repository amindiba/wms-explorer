<?php
/**
 * قالب: داشبورد مدیریت درخواست‌ها (UI حرفه‌ای)
 */
defined( 'ABSPATH' ) || exit;

$status_labels = array(
	'pending'   => 'در انتظار',
	'approved'  => 'تایید شده',
	'rejected'  => 'رد شده',
	'refunded'  => 'بازپرداخت شده',
	'cancelled' => 'لغو شده',
);

$status_colors = array(
	'pending'   => 'pending',
	'approved'  => 'approved',
	'rejected'  => 'rejected',
	'refunded'  => 'refunded',
	'cancelled' => 'cancelled',
);
?>

<div class="wms-wrap">
	<h1>داشبورد مرجوعی‌ها</h1>

	<div class="wms-header-bar">
		<div class="wms-total">کل درخواست‌ها: <strong><?php echo esc_html( $total ); ?></strong></div>
	</div>

	<!-- فیلتر وضعیت -->
	<div class="wms-status-tabs">
		<a href="?page=wms-returns" class="wms-status-tab <?php echo empty( $status_filter ) ? 'active' : ''; ?>">
			همه
			<span class="count"><?php echo esc_html( $total ); ?></span>
		</a>
		<?php foreach ( $status_labels as $s_key => $s_label ) :
			$count = isset( $status_counts[ $s_key ] ) ? $status_counts[ $s_key ]->count : 0;
			?>
			<a href="?page=wms-returns&status=<?php echo esc_attr( $s_key ); ?>"
			   class="wms-status-tab <?php echo $status_filter === $s_key ? 'active' : ''; ?>">
				<?php echo esc_html( $s_label ); ?>
				<span class="count"><?php echo esc_html( $count ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<?php if ( empty( $requests ) ) : ?>
		<div class="wms-empty">
			<div class="wms-empty-icon">📋</div>
			<p>هیچ درخواست مرجوعی یافت نشد.</p>
		</div>
	<?php else : ?>
		<div class="wms-table-wrap">
			<table class="wms-table">
				<thead>
					<tr>
						<th>RMA</th>
						<th>سفارش</th>
						<th>مشتری</th>
						<th>دلیل</th>
						<th>نوع</th>
						<th>مبلغ</th>
						<th>وضعیت</th>
						<th>تاریخ</th>
						<th>عملیات</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $requests as $req ) : ?>
						<tr>
							<td class="wms-col-rma"><?php echo esc_html( $req->rma_number ); ?></td>
							<td class="wms-col-order">
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $req->order_id . '&action=edit' ) ); ?>">
									#<?php echo esc_html( $req->order_id ); ?>
								</a>
							</td>
							<td><?php echo esc_html( $req->customer_id ? WMS_Helpers::get_customer_name( $req->customer_id ) : $req->customer_email ); ?></td>
							<td><?php echo esc_html( mb_strimwidth( $req->return_reason, 0, 30, '...' ) ); ?></td>
							<td><?php echo esc_html( 'refund' === $req->resolution_type ? 'بازپرداخت' : 'مبادله' ); ?></td>
							<td class="wms-col-amount"><?php echo wc_price( $req->refund_amount ); ?></td>
							<td>
								<select class="wms-status-select" data-id="<?php echo esc_attr( $req->id ); ?>">
									<?php foreach ( array( 'pending', 'approved', 'rejected', 'refunded', 'cancelled' ) as $s ) : ?>
										<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $req->status, $s ); ?>>
											<?php echo esc_html( $status_labels[ $s ] ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="wms-col-date"><?php echo esc_html( date_i18n( 'Y/m/d', strtotime( $req->created_at ) ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $req->id . '&action=edit' ) ); ?>" class="wms-btn-view">
									مشاهده
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="wms-pagination">
				<?php
				echo wp_kses_post( paginate_links( array(
					'base'    => add_query_arg( 'paged', '%#%' ),
					'format'  => '',
					'current' => $page,
					'total'   => $total_pages,
					'prev_text' => '→',
					'next_text' => '←',
				) ) );
				?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
