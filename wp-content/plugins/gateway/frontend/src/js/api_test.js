// TODO: Create webpack config or task runner to mini/uglify this.
jQuery(function ($) {
    $(document).ready(function () {
        var data = custom_payment_gateway_ajax_test_api_data;
        var $main = $('[action="options.php"]');
        var $apiKey = $('#custom_payment_gateway\\[api_key\\]', $main);
        var $apiUrl = $('#custom_payment_gateway\\[api_url\\]', $main);
        var messageId = 'custom_payment_gateway_ajax_test_api_message';
        var $message = $('<div/>', {
            id: messageId
        })
        var apiKeyInitialValue = $apiKey.val();
        var post_data = {
            nonce: data.nonce,
            action: data.action,
        };
        $.post(data.ajax_url, post_data, handle);
        $(document).on('click', '#custom_payment_gateway_ajax_test_api_button', function (e) {
            if ($apiKey.val() !== apiKeyInitialValue) {
                post_data.api_key = $apiKey.val();
            }
            post_data.api_url = $apiUrl.val();
            var $spinner = $('<span/>', {
                'class': 'spinner spinner--api-test is-active'
            });
            $spinner.appendTo($(this));
            $.post(data.ajax_url, post_data, handle);
        });

        function handle(response) {
            if (response) {
                response = JSON.parse(response);
                $message.removeClass().addClass(['notice', 'notice-' + response.status, 'is-dismissible']);
                $message.html('<p>' + response.message + '</p>');
                var $spinner = $('.spinner--api-test', $main);
                $spinner.remove();
                if (!$('#' + messageId, $main).length) {
                    $main.prepend($message);
                }
            }
        }
    });
});
