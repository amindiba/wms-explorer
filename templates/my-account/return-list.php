<?php
/**
 * قالب: لیست درخواست‌های مرجوعی در حساب کاربری
 * @var array $requests
 * @var array $eligible_orders
 * @var array $settings
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wms-return-requests" style="direction:rtl;text-align:right;">
	<h2>درخواست‌های مرجوعی من</h2>

	<?php if ( empty( $requests ) ) : ?>
		<p>شما هیچ درخواست مرجوعی ندارید.</p>
	<?php else : ?>
		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table responsive">
			<thead>
				<tr>
					<th>شماره RMA</th>
					<th>سفارش</th>
					<th>وضعیت</th>
					<th>دلیل</th>
					<th>تاریخ</th>
					<th>عملیات</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $requests as $req ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $req->rma_number ); ?></strong></td>
						<td><a href="<?php echo esc_url( wc_get_endpoint_url( 'view-order', $req->order_id ) ); ?>">#<?php echo esc_html( $req->order_id ); ?></a></td>
						<td>
							<span class="wms-status wms-status-<?php echo esc_attr( $req->status ); ?>">
								<?php
								$status_labels = array(
									'pending'   => 'در انتظار',
									'approved'  => 'تایید شده',
									'rejected'  => 'رد شده',
									'refunded'  => 'بازپرداخت شده',
									'cancelled' => 'لغو شده',
								);
								echo esc_html( $status_labels[ $req->status ] ?? $req->status );
								?>
							</span>
						</td>
						<td><?php echo esc_html( $req->return_reason ); ?></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $req->created_at ) ) ); ?></td>
						<td>
							<?php if ( 'pending' === $req->status ) : ?>
								<button type="button" class="button wms-cancel-return" data-request-id="<?php echo esc_attr( $req->id ); ?>">
									لغو
								</button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $eligible_orders ) ) : ?>
		<h2>درخواست مرجوعی جدید</h2>
		<form method="post" class="wms-return-form" id="wms-return-form">
			<?php wp_nonce_field( 'wms_submit_return', 'wms_return_nonce' ); ?>

			<p>
				<label for="wms-order-select">انتخاب سفارش:</label>
				<select name="order_id" id="wms-order-select" required>
					<option value="">یک سفارش انتخاب کنید...</option>
					<?php foreach ( $eligible_orders as $el ) : ?>
						<option value="<?php echo esc_attr( $el['order']->get_id() ); ?>">
							#<?php echo esc_html( $el['order']->get_id() ); ?>
							— <?php echo esc_html( wc_format_datetime( $el['order']->get_date_created() ) ); ?>
							(<?php echo wc_price( $el['order']->get_total() ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<div id="wms-order-items" style="display:none;">
				<h3>اقلام قابل مرجوعی:</h3>
				<table class="shop_table">
					<thead>
						<tr>
							<th><input type="checkbox" id="wms-select-all" /></th>
							<th>محصول</th>
							<th>تعداد موجود</th>
							<th>تعداد مرجوعی</th>
						</tr>
					</thead>
					<tbody id="wms-items-body"></tbody>
				</table>
			</div>

			<p>
				<label for="wms-return-reason">دلیل مرجوعی:</label>
				<select name="return_reason" id="wms-return-reason" required>
					<option value="">یک دلیل انتخاب کنید...</option>
					<?php foreach ( WMS_Helpers::get_return_reasons() as $label ) : ?>
						<option value="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="wms-resolution-type">نوع درخواست:</label>
				<select name="resolution_type" id="wms-resolution-type" required>
					<option value="refund">بازپرداخت</option>
					<?php if ( ! empty( $settings['enable_exchanges'] ) ) : ?>
						<option value="exchange">مبادله کالا</option>
					<?php endif; ?>
				</select>
			</p>

			<p>
				<label for="wms-return-notes">توضیحات تکمیلی:</label>
				<textarea name="return_notes" id="wms-return-notes" rows="4" placeholder="مشکل را شرح دهید..."></textarea>
			</p>

			<p>
				<button type="submit" class="woocommerce-button button" id="wms-submit-return">
					<?php echo esc_html( $settings['return_button_text'] ?? 'ارسال درخواست مرجوعی' ); ?>
				</button>
			</p>
		</form>
	<?php endif; ?>
</div>
