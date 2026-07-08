<?php
/**
 * قالب: فرم مرجوعی سفارش خاص (UI حرفه‌ای)
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wms-container">
	<div class="wms-form-section">
		<h2 class="wms-form-title">درخواست مرجوعی سفارش #<?php echo esc_html( $order->get_id() ); ?></h2>

		<form method="post" id="wms-single-return-form">
			<?php wp_nonce_field( 'wms_submit_return', 'wms_return_nonce' ); ?>
			<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>" />

			<div class="wms-form-group">
				<label>اقلام قابل مرجوعی</label>
				<table class="wms-items-table">
					<thead>
						<tr>
							<th style="width:40px;"><input type="checkbox" id="wms-select-all-single" /></th>
							<th>محصول</th>
							<th style="width:80px;">تعداد</th>
							<th style="width:80px;">مرجوعی</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $item ) : ?>
							<tr>
								<td>
									<input type="checkbox" name="return_items[<?php echo esc_attr( $item['order_item_id'] ); ?>][return]"
										   value="1" class="wms-item-check" />
								</td>
								<td><?php echo esc_html( $item['product_name'] ); ?></td>
								<td><?php echo esc_html( $item['quantity'] ); ?></td>
								<td>
									<input type="number" name="return_items[<?php echo esc_attr( $item['order_item_id'] ); ?>][quantity]"
										   value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1"
										   max="<?php echo esc_attr( $item['quantity'] ); ?>" disabled />
									<input type="hidden" name="return_items[<?php echo esc_attr( $item['order_item_id'] ); ?>][order_item_id]"
										   value="<?php echo esc_attr( $item['order_item_id'] ); ?>" />
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="wms-form-group">
				<label>دلیل مرجوعی <span class="required">*</span></label>
				<select name="return_reason" class="wms-form-control" required>
					<option value="">انتخاب...</option>
					<?php foreach ( WMS_Helpers::get_return_reasons() as $label ) : ?>
						<option value="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="wms-form-group">
				<label>نوع درخواست <span class="required">*</span></label>
				<select name="resolution_type" class="wms-form-control" required>
					<option value="refund">بازپرداخت وجه</option>
					<option value="exchange">مبادله کالا</option>
				</select>
			</div>

			<div class="wms-form-group">
				<label>توضیحات تکمیلی</label>
				<textarea name="return_notes" class="wms-form-control" rows="3" placeholder="توضیحات..."></textarea>
			</div>

			<button type="submit" class="wms-btn wms-btn-primary">ارسال درخواست مرجوعی</button>
		</form>
	</div>
</div>
