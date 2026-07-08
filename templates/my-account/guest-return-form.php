<?php
/**
 * قالب: فرم مرجوعی مهمان
 * @var bool   $authenticated
 * @var object $order
 * @var array  $eligible_items
 * @var string $email
 * @var int    $order_id
 * @var string $key
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wms-guest-return" style="direction:rtl;text-align:right;">
	<h2>مرجوعی مهمان</h2>

	<?php if ( ! $authenticated ) : ?>
		<p>اطلاعات سفارش خود را وارد کنید تا درخواست مرجوعی ثبت کنید.</p>

		<form method="get" class="wms-guest-auth-form">
			<p>
				<label for="guest-order-id">شماره سفارش:</label>
				<input type="number" name="order" id="guest-order-id" required style="width:100%;" />
			</p>
			<p>
				<label for="guest-email">ایمیل:</label>
				<input type="email" name="email" id="guest-email" required style="width:100%;" />
			</p>
			<p><button type="submit" class="button">جستجوی سفارش</button></p>
		</form>

	<?php elseif ( empty( $eligible_items ) ) : ?>
		<p>این سفارش واجد شرایط مرجوعی نیست.</p>

	<?php else : ?>
		<p>سفارش #<?php echo esc_html( $order->get_id() ); ?> — اقلام قابل مرجوعی را انتخاب کنید:</p>

		<form method="post" id="wms-guest-return-form" class="wms-guest-form-wrapper">
			<?php wp_nonce_field( 'wms_guest_return', 'guest_return_nonce' ); ?>
			<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>" />
			<input type="hidden" name="email" value="<?php echo esc_attr( $email ); ?>" />
			<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>" />

			<table class="shop_table">
				<thead>
					<tr>
						<th><input type="checkbox" id="wms-guest-select-all" /></th>
						<th>محصول</th>
						<th>تعداد موجود</th>
						<th>تعداد مرجوعی</th>
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
							<td><input type="number" name="items[<?php echo esc_attr( $item['order_item_id'] ); ?>][quantity]"
									   value="<?php echo esc_attr( $item['quantity'] ); ?>" min="1"
									   max="<?php echo esc_attr( $item['quantity'] ); ?>" class="small-text" disabled /></td>
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

			<p><textarea name="return_notes" rows="3" placeholder="توضیحات..."></textarea></p>

			<button type="submit" class="button">ارسال درخواست مرجوعی</button>
		</form>
	<?php endif; ?>
</div>
