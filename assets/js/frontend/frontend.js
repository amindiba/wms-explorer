/**
 * WMS Explorer — Frontend JavaScript
 */
(function ($) {
    'use strict';

    // Toggle quantity input when item checkbox changes.
    $(document).on('change', '.wms-item-check, .wms-guest-item-check', function () {
        var $row = $(this).closest('tr');
        $row.find('input[type="number"]').prop('disabled', !this.checked);
    });

    // Select all items.
    $(document).on('change', '#wms-select-all, #wms-select-all-single, #wms-guest-select-all', function () {
        var checked = this.checked;
        $(this).closest('table').find('.wms-item-check, .wms-guest-item-check').prop('checked', checked).trigger('change');
    });

    // Cancel return request.
    $(document).on('click', '.wms-cancel-return', function (e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to cancel this return request?')) {
            return;
        }

        var $btn = $(this);
        var requestId = $btn.data('request-id');

        $.post(wmsFrontend.ajaxUrl, {
            action: 'wms_cancel_return',
            nonce: wmsFrontend.nonce,
            request_id: requestId
        }, function (response) {
            if (response.success) {
                $btn.closest('tr').fadeOut(300, function () { $(this).remove(); });
            } else {
                alert(response.data.message || 'Error');
            }
        }).fail(function () {
            alert('Error');
        });
    });

    // AJAX return form submission.
    $(document).on('submit', '#wms-single-return-form, #wms-guest-return-form', function (e) {
        e.preventDefault();
        var $form = $(this);

        var items = [];
        $form.find('tr').each(function () {
            var $row = $(this);
            var $check = $row.find('.wms-item-check, .wms-guest-item-check');
            if ($check.length && $check.is(':checked')) {
                items.push({
                    order_item_id: $row.find('input[name*="order_item_id"]').val(),
                    quantity: $row.find('input[type="number"]').val()
                });
            }
        });

        if (items.length === 0) {
            alert('Please select at least one item.');
            return;
        }

        var isGuest = $form.hasClass('wms-guest-form-wrapper');
        var action = isGuest ? 'wms_submit_guest_return' : 'wms_submit_return';

        $.post(wmsFrontend.ajaxUrl, $.extend({
            action: action,
            nonce: wmsFrontend.nonce,
            order_id: $form.find('input[name="order_id"]').val(),
            return_reason: $form.find('select[name="return_reason"]').val(),
            return_notes: $form.find('textarea[name="return_notes"]').val(),
            resolution_type: $form.find('select[name="resolution_type"]').val(),
            items: JSON.stringify(items)
        }, isGuest ? {
            email: $form.find('input[name="email"]').val(),
            key: $form.find('input[name="key"]').val()
        } : {}), function (response) {
            if (response.success) {
                $form.replaceWith('<div class="woocommerce-message">' + response.data.message + '</div>');
            } else {
                $form.before('<div class="woocommerce-error">' + response.data.message + '</div>');
            }
        }).fail(function () {
            $form.before('<div class="woocommerce-error">An error occurred. Please try again.</div>');
        });
    });

})(jQuery);
