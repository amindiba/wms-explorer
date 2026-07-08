<?php
/**
 * قالب: فرم مرجوعی برای یک سفارش خاص
 * @var object $order
 * @var array  $items
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wms-return-form-single" style="direction:rtl;text-align:right;">
	<h2>درخواست مرجوعی سفارش #<?php echo esc_html( $order->get_id() ); ?></h2>

	<form method="post" id="wms-single-return-form">
		<?php wp_nonce_field( 'wms_submit_return', 'wms_return_nonce' ); ?>
		<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>" />

		<table class="shop_table">
			<thead>
				<tr>
					<th><input type="checkbox" id="wms-select-all-single" /></th>
					<th>محصول</th>
					<th>تعداد</th>
					<th>تعداد مرجوعی</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $item ) : ?>
					<tr>
						<td><input type="checkbox" name="return_items[<?php echo esc_attr( $item['order_item_id'] ); ?>][return]" value="1" class="wms-item-check" /></td>
						<td><?php echo esc_html( $item['product_name'] ); ?></td>
						<td><?php echo esc_html( $item['quantity'] ); ?></td>
						<td>
							<input type="number" name="return_items[<?php echo esc_attr( $item['order_item_id'] ); ?>][quantity]"
								   value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1"
								   max="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" disabled />
							<input type="hidden" name="return_items[<?php echo esc_attr( $item['order_item_id'] ); ?>][order_item_id]"
								   value="<?php echo esc_attr( $item['order_item_id'] ); ?>" />
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<p><label>دلیل:</label>
			<select name="return_reason" required>
				<option value="">انتخاب...</option>
				<?php foreach ( WMS_Helpers::get_return_reasons() as $label ) : ?>
					<option value="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p><label>نوع درخواست:</label>
			<select name="resolution_type" required>
				<option value="refund">بازپرداخت</option>
				<option value="exchange">مبادله کالا</option>
			</select>
		</p>

		<p><textarea name="return_notes" rows="3" placeholder="توضیحات تکمیلی..."></textarea></p>

		<button type="submit" class="button">ارسال درخواست مرجوعی</button>
	</form>
</div>
