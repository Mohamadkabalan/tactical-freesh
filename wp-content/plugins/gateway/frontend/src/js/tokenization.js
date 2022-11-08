// TODO: Create webpack config or task runner to mini/uglify this.
jQuery(function ($) {
    $(document).ready(function () {
        /** @see tokenization-form.js */
        var $saved_payment_methods = $('ul.woocommerce-SavedPaymentMethods');

        $saved_payment_methods.each(function () {
            $(this).wc_tokenization_form();
        });
    });
});
