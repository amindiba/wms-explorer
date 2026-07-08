/**
 * WMS Explorer — Admin JavaScript
 */
(function ($) {
    'use strict';

    // Inline status update from claims dashboard.
    $(document).on('change', '.wms-status-select', function () {
        var $select = $(this);
        var requestId = $select.data('id');
        var newStatus = $select.val();

        $.post(wmsAdmin.ajaxUrl, {
            action: 'wms_update_status',
            nonce: wmsAdmin.nonce,
            request_id: requestId,
            new_status: newStatus
        }, function (response) {
            if (response.success) {
                $select.addClass('wms-updated');
                setTimeout(function () { $select.removeClass('wms-updated'); }, 1000);
            } else {
                alert(response.data.message || wmsAdmin.i18n.error);
            }
        }).fail(function () {
            alert(wmsAdmin.i18n.error);
        });
    });

})(jQuery);
