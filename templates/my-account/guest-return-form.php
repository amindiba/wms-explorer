<?php
/**
 * قالب: فرم مرجوعی مهمان (UI حرفه‌ای)
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wms-container">
	<h2 class="wms-section-title">مرجوعی مهمان</h2>

	<?php if ( ! $authenticated ) : ?>
		<div class="wms-guest-auth">
			<p style="margin-bottom:16px;color:#646970;">اطلاعات سفارش خود را وارد کنید.</p>

			<form method="get">
				<div class="wms-form-group">
					<label for="guest-order-id">شماره سفارش <span class="required">*</span></label>
					<input type="number" name="order" id="guest-order-id" class="wms-form-control" required />
				</div>
				<div class="wms-form-group">
					<label for="guest-email">ایمیل <span class="required">*</span></label>
					<input type="email" name="email" id="guest-email" class="wms-form-control" required />
				</div>
				<button type="submit" class="wms-btn wms-btn-primary" style="width:100%;">جستجوی سفارش</button>
			</form>
		</div>

	<?php elseif ( empty( $eligible_items ) ) : ?>
		<div class="wms-message wms-message-info">
			این سفارش واجد شرایط مرجوعی نیست.
		</div>

	<?php else : ?>
		<div class="wms-message wms-message-info">
			سفارش #<?php echo esc_html( $order->get_id() ); ?> — اقلام قابل مرجوعی را انتخاب کنید.
		</div>

		<div class="wms-form-section">
			<form method="post" id="wms-guest-return-form">
				<?php wp_nonce_field( 'wms_guest_return', 'guest_return_nonce' ); ?>
				<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>" />
				<input type="hidden" name="email" value="<?php echo esc_attr( $email ); ?>" />
				<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>" />

				<div class="wms-form-group">
					<label>اقلام قابل مرجوعی</label>
					<table class="wms-items-table">
						<thead>
							<tr>
								<th style="width:40px;"><input type="checkbox" id="wms-guest-select-all" /></th>
								<th>محصول</th>
								<th style="width:80px;">تعداد</th>
								<th style="width:80px;">مرجوعی</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $eligible_items as $item ) : ?>
								<tr>
									<td>
										<input type="checkbox" class="wms-guest-item-check"
											   name="items[<?php echo esc_attr( $item['order_item_id'] ); ?>][return]" value="1" />
										<input type="hidden" name="items[<?php echo esc_attr( $item['order_item_id'] ); ?>][order_item_id]"
											   value="<?php echo esc_attr( $item['order_item_id'] ); ?>" />
									</td>
									<td><?php echo esc_html( $item['product_name'] ); ?></td>
									<td><?php echo esc_html( $item['quantity'] ); ?></td>
									<td>
										<input type="number" name="items[<?php echo esc_attr( $item['order_item_id'] ); ?>][quantity]"
											   value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1"
											   max="<?php echo esc_attr( $item['quantity'] ); ?>" disabled />
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="wms-form-group">
					<label>دلیل <span class="required">*</span></label>
					<select name="return_reason" class="wms-form-control" required>
						<option value="">انتخاب...</option>
						<?php foreach ( WMS_Helpers::get_return_reasons() as $label ) : ?>
							<option value="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="wms-form-group">
					<label>توضیحات</label>
					<textarea name="return_notes" class="wms-form-control" rows="3" placeholder="توضیحات..."></textarea>
				</div>

				<button type="submit" class="wms-btn wms-btn-primary">ارسال درخواست مرجوعی</button>
			</form>
		</div>
	<?php endif; ?>
</div>
