// Form in pop-up dialog
CRM.$(function ($) {
    'use strict';
    $(".wrapi-action").on('crmPopupFormSuccess', CRM.refreshParent);
});

// Send action in AJAX
CRM.$(function ($) {
    'use strict';
    $(".wrapi-ajax-action").click(function (event) {

        // Button clicked
        let button = this;

        event.preventDefault();

        // Send AJAX request, expect JSON return
        $.getJSON(button.href, {}, function () {
            CRM.refreshParent(button);
        });
    });
});
