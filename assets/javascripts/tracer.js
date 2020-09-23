(function ($, STUDIP) {
    'use strict'

    STUDIP.ContactTracer = {
        updateRegisteredCount: function() {
            $.ajax({
                url: STUDIP.URLHelper.getURL('plugins.php/contacttracer/coursetracer/get_registered_count/' +
                    $('#date-qr-code').data('date-id')),
                success: (json) => {
                    $('#registered-counter div.sidebar-widget-content').html(json.text)
                    setTimeout(STUDIP.ContactTracer.updateRegisteredCount, 30000)
                }
            })
        }
    };

    STUDIP.ready(function () {
        STUDIP.ContactTracer.updateRegisteredCount()
    });

}(jQuery, STUDIP));
