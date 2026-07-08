<?php
/**
 * قالب: لیست درخواست‌های مرجوعی (UI حرفه‌ای)
 */
defined( 'ABSPATH' ) || exit;
?>

<div class="wms-container">
	<h2 class="wms-section-title">درخواست‌های مرجوعی من</h2>

	<?php if ( ! empty( $requests ) ) : ?>
		<div class="wms-requests-list">
			<?php foreach ( $requests as $req ) :
				$status_labels = array(
					'pending'   => 'در انتظار',
					'approved'  => 'تایید شده',
					'rejected'  => 'رد شده',
					'refunded'  => 'بازپرداخت شده',
					'cancelled' => 'لغو شده',
				);
				?>
				<div class="wms-request-card">
					<div class="wms-request-header">
						<span class="wms-request-rma"><?php echo esc_html( $req->rma_number ); ?></span>
						<span class="wms-badge wms-badge-<?php echo esc_attr( $req->status ); ?>">
							<?php echo esc_html( $status_labels[ $req->status ] ?? $req->status ); ?>
						</span>
					</div>
					<div class="wms-request-meta">
						<span>سفارش #<?php echo esc_html( $req->order_id ); ?></span>
						<span>•</span>
						<span><?php echo esc_html( $req->return_reason ); ?></span>
						<span>•</span>
						<span><?php echo esc_html( date_i18n( 'Y/m/d', strtotime( $req->created_at ) ) ); ?></span>
					</div>
					<div class="wms-request-footer">
						<span class="wms-col-amount"><?php echo wc_price( $req->refund_amount ); ?></span>
						<?php if ( 'pending' === $req->status ) : ?>
							<button type="button" class="wms-btn wms-btn-danger wms-btn-sm wms-cancel-return" data-request-id="<?php echo esc_attr( $req->id ); ?>">
								لغو درخواست
							</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $eligible_orders ) ) : ?>
		<div class="wms-form-section">
			<h3 class="wms-form-title">درخواست مرجوعی جدید</h3>

			<form method="post" id="wms-return-form">
				<?php wp_nonce_field( 'wms_submit_return', 'wms_return_nonce' ); ?>

				<div class="wms-form-group">
					<label for="wms-order-select">انتخاب سفارش <span class="required">*</span></label>
					<select name="order_id" id="wms-order-select" class="wms-form-control" required>
						<option value="">یک سفارش انتخاب کنید...</option>
						<?php foreach ( $eligible_orders as $el ) : ?>
							<option value="<?php echo esc_attr( $el['order']->get_id() ); ?>">
								#<?php echo esc_html( $el['order']->get_id() ); ?>
								— <?php echo esc_html( wc_format_datetime( $el['order']->get_date_created() ); ?>
								(<?php echo wc_price( $el['order']->get_total() ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div id="wms-items-section" style="display:none;">
					<div class="wms-form-group">
						<label>اقلام قابل مرجوعی</label>
						<table class="wms-items-table">
							<thead>
								<tr>
									<th style="width:40px;"><input type="checkbox" id="wms-select-all" /></th>
									<th>محصول</th>
									<th style="width:80px;">تعداد</th>
									<th style="width:80px;">مرجوعی</th>
								</tr>
							</thead>
							<tbody id="wms-items-body"></tbody>
						</table>
					</div>
				</div>

				<div class="wms-form-group">
					<label for="wms-return-reason">دلیل مرجوعی <span class="required">*</span></label>
					<select name="return_reason" id="wms-return-reason" class="wms-form-control" required>
						<option value="">یک دلیل انتخاب کنید...</option>
						<?php foreach ( WMS_Helpers::get_return_reasons() as $label ) : ?>
							<option value="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="wms-form-group">
					<label for="wms-resolution-type">نوع درخواست <span class="required">*</span></label>
					<select name="resolution_type" id="wms-resolution-type" class="wms-form-control" required>
						<option value="refund">بازپرداخت وجه</option>
						<?php if ( ! empty( $settings['enable_exchanges'] ) ) : ?>
							<option value="exchange">مبادله کالا</option>
						<?php endif; ?>
					</select>
				</div>

				<div class="wms-form-group">
					<label for="wms-return-notes">توضیحات تکمیلی</label>
					<textarea name="return_notes" id="wms-return-notes" class="wms-form-control" rows="3" placeholder="مشکل را شرح دهید..."></textarea>
				</div>

				<button type="submit" class="wms-btn wms-btn-primary" id="wms-submit-return">
					<?php echo esc_html( $settings['return_button_text'] ?? 'ارسال درخواست مرجوعی' ); ?>
				</button>
			</form>
		</div>
	<?php endif; ?>
</div>
