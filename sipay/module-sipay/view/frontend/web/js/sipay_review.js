define(
  [
    'mage/translate',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'domReady!'
  ],
  function (
    $t,
    url,
    messageList
  ) {
    'use strict';
      var initPaymentReview = function (config, element) {
        const client = new PWall(config.enviroment, true);
        var checkout = client.checkout();

        checkout.backendUrl(config.backendUrl)
        .appendTo(element)
        .on("paymentOk", redirectToCheckoutSuccess)
        .on("validationFunc", function(){return true;})
        .setTags("fisico")
        .isSelected(true)
        .validateForm(function(){return true;})
        .currency(config.currency)
        .groupId(config.customer_id)
        .amount(config.amount)

        function redirectToCheckoutSuccess() {
          var redirectUrl = url.build("checkout/onepage/success", {});
          window.location.href = redirectUrl;
        }
    }
    return initPaymentReview;
  });