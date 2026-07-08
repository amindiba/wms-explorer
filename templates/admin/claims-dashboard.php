<?php
/**
 * قالب: داشبورد مدیریت درخواست‌ها
 * @var array  $requests
 * @var int    $total
 * @var int    $total_pages
 * @var int    $page
 * @var string $status_filter
 * @var object $status_counts
 */
defined( 'ABSPATH' ) || exit;

$status_labels = array(
	'pending'   => 'در انتظار',
	'approved'  => 'تایید شده',
	'rejected'  => 'رد شده',
	'refunded'  => 'بازپرداخت شده',
	'cancelled' => 'لغو شده',
);
?>

<div class="wrap" style="direction:rtl;text-align:right;">
	<h1>داشبورد درخواست‌های مرجوعی</h1>

	<ul class="subsubsub">
		<li><a href="?page=wms-dashboard" class="<?php echo empty( $status_filter ) ? 'current' : ''; ?>">همه (<?php echo esc_html( $total ); ?>)</a> |</li>
		<?php foreach ( $status_labels as $s_key => $s_label ) :
			$count = isset( $status_counts[ $s_key ] ) ? $status_counts[ $s_key ]->count : 0;
			?>
			<li><a href="?page=wms-dashboard&status=<?php echo esc_attr( $s_key ); ?>"
				   class="<?php echo $status_filter === $s_key ? 'current' : ''; ?>">
				<?php echo esc_html( $s_label ); ?> (<?php echo esc_html( $count ); ?>)
			</a> |</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( empty( $requests ) ) : ?>
		<p>هیچ درخواست مرجوعی یافت نشد.</p>
	<?php else : ?>
		<table class="widefat striped" id="wms-claims-table">
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
					<tr data-id="<?php echo esc_attr( $req->id ); ?>">
						<td><strong><?php echo esc_html( $req->rma_number ); ?></strong></td>
						<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $req->order_id . '&action=edit' ) ); ?>">#<?php echo esc_html( $req->order_id ); ?></a></td>
						<td><?php echo esc_html( $req->customer_id ? WMS_Helpers::get_customer_name( $req->customer_id ) : $req->customer_email ); ?></td>
						<td><?php echo esc_html( $req->return_reason ); ?></td>
						<td><?php echo esc_html( 'refund' === $req->resolution_type ? 'بازپرداخت' : 'مبادله' ); ?></td>
						<td><?php echo wc_price( $req->refund_amount ); ?></td>
						<td>
							<select class="wms-status-select" data-id="<?php echo esc_attr( $req->id ); ?>" style="min-width:120px;">
								<?php foreach ( array( 'pending', 'approved', 'rejected', 'refunded', 'cancelled' ) as $s ) : ?>
									<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $req->status, $s ); ?>>
										<?php echo esc_html( $status_labels[ $s ] ?? $s ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $req->created_at ) ) ); ?></td>
						<td><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $req->id . '&action=edit' ) ); ?>" class="button button-small">مشاهده</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php echo wp_kses_post( paginate_links( array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'current' => $page,
						'total'   => $total_pages,
					) ) ); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
