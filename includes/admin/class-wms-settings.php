<?php
defined( 'ABSPATH' ) || exit;

class WMS_Settings {

	private $tabs = array();

	public function __construct() {
		$this->tabs = array(
			'general'    => 'عمومی',
			'reasons'    => 'دلایل مرجوعی',
			'approval'   => 'قوانین تایید',
			'emails'     => 'ایمیل‌ها',
			'appearance' => 'ظاهر',
		);
	}

	public function register_settings() {
		register_setting( 'wms_general_settings', 'wms_general_settings', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_general' ),
		) );
		register_setting( 'wms_return_reasons', 'wms_return_reasons', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_reasons' ),
		) );
		register_setting( 'wms_auto_approve_rules', 'wms_auto_approve_rules', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_approval' ),
		) );
		register_setting( 'wms_email_settings', 'wms_email_settings', array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize_email' ),
		) );
	}

	public function sanitize_general( $input ) {
		return array(
			'return_window_days'     => absint( $input['return_window_days'] ?? 30 ),
			'enable_returns'         => ! empty( $input['enable_returns'] ),
			'enable_exchanges'       => ! empty( $input['enable_exchanges'] ),
			'enable_store_credit'    => ! empty( $input['enable_store_credit'] ),
			'rma_prefix'             => sanitize_text_field( $input['rma_prefix'] ?? 'RMA' ),
			'guest_returns'          => ! empty( $input['guest_returns'] ),
			'require_attachment'     => ! empty( $input['require_attachment'] ),
			'max_attachments'        => absint( $input['max_attachments'] ?? 5 ),
			'return_button_text'     => sanitize_text_field( $input['return_button_text'] ?? '' ),
			'hide_button_after_days' => absint( $input['hide_button_after_days'] ?? 0 ),
			'custom_notes'           => sanitize_textarea_field( $input['custom_notes'] ?? '' ),
		);
	}

	public function sanitize_reasons( $input ) {
		$sanitized = array();
		foreach ( $input as $key => $value ) {
			$sanitized[ sanitize_key( $key ) ] = sanitize_text_field( $value );
		}
		return $sanitized;
	}

	public function sanitize_approval( $input ) {
		return array(
			'enabled'          => ! empty( $input['enabled'] ),
			'max_order_value'  => floatval( $input['max_order_value'] ?? 0 ),
			'max_return_count' => absint( $input['max_return_count'] ?? 0 ),
			'allowed_reasons'  => array_map( 'sanitize_text_field', $input['allowed_reasons'] ?? array() ),
		);
	}

	public function sanitize_email( $input ) {
		return array(
			'admin_email'   => sanitize_email( $input['admin_email'] ?? '' ),
			'from_name'     => sanitize_text_field( $input['from_name'] ?? '' ),
			'from_email'    => sanitize_email( $input['from_email'] ?? '' ),
			'enable_emails' => ! empty( $input['enable_emails'] ),
		);
	}

	public function render_page() {
		$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'general' ) );
		?>
		<div class="wrap" style="direction:rtl;text-align:right;">
			<h1>تنظیمات مدیریت مرجوعی</h1>
			<nav class="nav-tab-wrapper">
				<?php foreach ( $this->tabs as $key => $label ) : ?>
					<a href="?page=wms-settings&tab=<?php echo esc_attr( $key ); ?>"
					   class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<div class="tab-content" style="margin-top:20px;">
				<?php
				switch ( $active_tab ) {
					case 'general':
						$this->tab_general();
						break;
					case 'reasons':
						$this->tab_reasons();
						break;
					case 'approval':
						$this->tab_approval();
						break;
					case 'emails':
						$this->tab_emails();
						break;
					case 'appearance':
						$this->tab_appearance();
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	private function tab_general() {
		$s = get_option( 'wms_general_settings', array() );
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wms_general_settings' ); ?>
			<table class="form-table">
				<tr><th><label for="enable_returns">فعال‌سازی مرجوعی</label></th>
				<td><input type="checkbox" name="wms_general_settings[enable_returns]" value="1" <?php checked( ! empty( $s['enable_returns'] ) ); ?> id="enable_returns" /></td></tr>
				<tr><th><label for="return_window_days">مدت زمان مرجوعی (روز)</label></th>
				<td><input type="number" name="wms_general_settings[return_window_days]" value="<?php echo esc_attr( $s['return_window_days'] ?? 30 ); ?>" min="0" max="365" id="return_window_days" class="small-text" />
				<p class="description">تعداد روزهای پس از تحویل که مرجوعی امکان‌پذیر است. 0 = نامحدود.</p></td></tr>
				<tr><th><label for="enable_exchanges">فعال‌سازی مبادله کالا</label></th>
				<td><input type="checkbox" name="wms_general_settings[enable_exchanges]" value="1" <?php checked( ! empty( $s['enable_exchanges'] ) ); ?> id="enable_exchanges" /></td></tr>
				<tr><th><label for="guest_returns">مرجوعی مهمان</label></th>
				<td><input type="checkbox" name="wms_general_settings[guest_returns]" value="1" <?php checked( ! empty( $s['guest_returns'] ) ); ?> id="guest_returns" /></td></tr>
				<tr><th><label for="rma_prefix">پیشوند شماره RMA</label></th>
				<td><input type="text" name="wms_general_settings[rma_prefix]" value="<?php echo esc_attr( $s['rma_prefix'] ?? 'RMA' ); ?>" id="rma_prefix" class="regular-text" /></td></tr>
				<tr><th><label for="max_attachments">حداکثر پیوست</label></th>
				<td><input type="number" name="wms_general_settings[max_attachments]" value="<?php echo esc_attr( $s['max_attachments'] ?? 5 ); ?>" min="0" max="10" id="max_attachments" class="small-text" /></td></tr>
			</table>
			<?php submit_button( 'ذخیره تنظیمات' ); ?>
		</form>
		<?php
	}

	private function tab_reasons() {
		$reasons = get_option( 'wms_return_reasons', array() );
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wms_return_reasons' ); ?>
			<table class="widefat striped">
				<thead><tr><th>کلید</th><th>برچسب</th></tr></thead>
				<tbody>
					<?php foreach ( $reasons as $key => $label ) : ?>
						<tr>
							<td><input type="text" name="wms_return_reasons[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( 'ذخیره دلایل' ); ?>
		</form>
		<?php
	}

	private function tab_approval() {
		$r = get_option( 'wms_auto_approve_rules', array() );
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wms_auto_approve_rules' ); ?>
			<table class="form-table">
				<tr><th>تایید خودکار</th>
				<td><input type="checkbox" name="wms_auto_approve_rules[enabled]" value="1" <?php checked( ! empty( $r['enabled'] ) ); ?> />
				<p class="description">مراحل مرجوعی مطابق قوانین زیر به صورت خودکار تایید شوند.</p></td></tr>
				<tr><th><label for="max_order_value">حداکثر مبلغ سفارش</label></th>
				<td><input type="number" name="wms_auto_approve_rules[max_order_value]" value="<?php echo esc_attr( $r['max_order_value'] ?? 0 ); ?>" min="0" step="0.01" id="max_order_value" class="regular-text" />
				<p class="description">تایید خودکار مرجوعی سفارشات تا این مبلغ. 0 = بدون محدودیت.</p></td></tr>
				<tr><th><label for="max_return_count">حداکثر تعداد مرجوعی هر مشتری</label></th>
				<td><input type="number" name="wms_auto_approve_rules[max_return_count]" value="<?php echo esc_attr( $r['max_return_count'] ?? 0 ); ?>" min="0" id="max_return_count" class="small-text" /></td></tr>
			</table>
			<?php submit_button( 'ذخیره قوانین' ); ?>
		</form>
		<?php
	}

	private function tab_emails() {
		$e = get_option( 'wms_email_settings', array() );
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wms_email_settings' ); ?>
			<table class="form-table">
				<tr><th>فعال‌سازی ایمیل‌ها</th>
				<td><input type="checkbox" name="wms_email_settings[enable_emails]" value="1" <?php checked( ! empty( $e['enable_emails'] ) ); ?> /></td></tr>
				<tr><th><label for="admin_email">ایمیل مدیر</label></th>
				<td><input type="email" name="wms_email_settings[admin_email]" value="<?php echo esc_attr( $e['admin_email'] ?? '' ); ?>" id="admin_email" class="regular-text" /></td></tr>
				<tr><th><label for="from_name">نام فرستنده</label></th>
				<td><input type="text" name="wms_email_settings[from_name]" value="<?php echo esc_attr( $e['from_name'] ?? '' ); ?>" id="from_name" class="regular-text" /></td></tr>
				<tr><th><label for="from_email">ایمیل فرستنده</label></th>
				<td><input type="email" name="wms_email_settings[from_email]" value="<?php echo esc_attr( $e['from_email'] ?? '' ); ?>" id="from_email" class="regular-text" /></td></tr>
			</table>
			<?php submit_button( 'ذخیره ایمیل‌ها' ); ?>
		</form>
		<?php
	}

	private function tab_appearance() {
		$s = get_option( 'wms_general_settings', array() );
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'wms_general_settings' ); ?>
			<table class="form-table">
				<tr><th><label for="return_button_text">متن دکمه مرجوعی</label></th>
				<td><input type="text" name="wms_general_settings[return_button_text]" value="<?php echo esc_attr( $s['return_button_text'] ?? '' ); ?>" id="return_button_text" class="regular-text" /></td></tr>
				<tr><th><label for="hide_button_after_days">مخفی کردن دکمه پس از (روز)</label></th>
				<td><input type="number" name="wms_general_settings[hide_button_after_days]" value="<?php echo esc_attr( $s['hide_button_after_days'] ?? 0 ); ?>" min="0" id="hide_button_after_days" class="small-text" />
				<p class="description">0 = همیشه نمایش داده شود.</p></td></tr>
				<tr><th><label for="custom_notes">یادداشت سفارشی به مشتری</label></th>
				<td><textarea name="wms_general_settings[custom_notes]" id="custom_notes" rows="4" class="large-text"><?php echo esc_textarea( $s['custom_notes'] ?? '' ); ?></textarea>
				<p class="description">هنگام لغو مرجوعی به مشتری نمایش داده می‌شود.</p></td></tr>
			</table>
			<?php submit_button( 'ذخیره ظاهر' ); ?>
		</form>
		<?php
	}
}
