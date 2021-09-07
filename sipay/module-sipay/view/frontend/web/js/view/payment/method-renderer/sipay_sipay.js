define(
  [
    'ko',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'mage/translate',
    'mage/url',
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/customer-email-validator',
    'Magento_Checkout/js/model/shipping-save-processor/default',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/payment/method-list',
    'domReady!'
  ],
  function (
    ko,
    Component,
    quote,
    customer,
    $t,
    url,
    $,
    additionalValidators,
    loginValidator,
    shippingSaveProcessor,
    setBillingAddress,
    totals,
    paymentList
  ) {
    'use strict';

    return Component.extend({
      amount: null,
      processedRedirect: false,
      pwall_client: null,
      pwall_checkout: null,
      defaults: {
        template: 'Sipay_Sipay/payment/form'
      },
      initialize: function () {
        this._super().observe(["totals", "amount"]);
        this.loadStylesInHead();
        this.totalsUpdated();
      },
      totalsUpdated: function () {
        this.totals(quote.totals());
        if(this.amount !== totals.getSegment('grand_total').value){
          this.totals(quote.totals());
          this.amount = totals.getSegment('grand_total').value;
          if (this.pwall_checkout !== null) {
            this.pwall_checkout.amount(this.amount);
          }
          this.log("TOTALS UPDATED", this.totals(), this.amount);
        }
      },
      afterRender: function () {
        this.log("HAS RENDERED");

        this.addObservables();
        this.initializePWallClient();
      },
      addObservables: function () {
        quote.totals.subscribe(this.totalsUpdated.bind(this));
        if (paymentList().length > 1){
          quote.paymentMethod.subscribe(this.isSelectedPaymentMethod.bind(this));
        }
      },
      isSelectedPaymentMethod: function () {
        this.pwall_checkout.isSelected(this.isCheckedPaymentMethod());
      },
      initializePWallClient: function () {
        if (this.pwall_client === null) {
          this.pwall_client = new PWall(this.config().environment, true);
        }
        if (this.pwall_checkout === null) {
          this.pwall_checkout = this.pwall_client.checkout();
        }
        this.pwall_checkout
          .appendTo("#sipay-app")
          .backendUrl(this.config().backend_url)
          .validateForm(function () { return true; })
          .on("beforeValidation", this.checkQuote.bind(this))
          .on("paymentOk", this.redirectToCheckoutSuccess)
          .on("validationFunc", this.isValidPaymentMethod.bind(this))
          .submitButton("button[data-bind*='placeOrder']")
          .isSelected(this.isCheckedPaymentMethod())
          .setTags(this.getTagsFromQuote())
          .amount(this.amount)
          .currency(this.totals().base_currency_code)
          .groupId(customer.isLoggedIn() ? customer.customerData.group_id : 0)
      },
      checkAndSaveShippingInformation: function (value) {
        if (quote.shippingMethod()) {
          shippingSaveProcessor.saveShippingInformation();
        }else{
          setBillingAddress();
        }
      },
      checkQuote: function () {
        if (quote.billingAddress() && quote.billingAddress().canUseForBilling()) {
          // check customer
          var payload = {};
          if (customer.isLoggedIn()) {
            payload.isLoggedIn = true;
            payload.email = customer.getDetails('email');
          } else {
            payload.isLoggedIn = false;
            payload.email = quote.guestEmail;
          }
          $.ajax({ url: this.config().check_url, async: false, dataType: "json", data: payload, timeout: 30000 })
            .done(function (data) {
              if (data.success == "true") {
                this.checkAndSaveShippingInformation();
                this.log("QUOTE OK");
              } else {
                this.log("QUOTE KO");
              }
            }.bind(this));
        } else {
          return this.log("Billing address not valid yet");
        };
      },
      isValidPaymentMethod: function () {
        this.checkAndSaveShippingInformation();
        return this.isCheckedPaymentMethod() && additionalValidators.validate() && loginValidator.validate();
      },
      isCheckedPaymentMethod: function () {
        var checked_payment = quote.paymentMethod() ? quote.paymentMethod().method : null;
        return checked_payment === this.getCode();
      },
      redirectToCheckoutSuccess: function () {
        var redirectUrl = url.build("checkout/onepage/success", {});
        window.location.href = redirectUrl;
      },
      //GETTERS AND CONFIG
      config: function () {
        return window.checkoutConfig.payment[this.getCode()];
      },
      getCode: function () {
        return 'sipay_sipay';
      },
      getSubTitle: function () {
        return '';
      },
      log: function () {
        if (this.config().debug !== "0") {
          var args = Array.prototype.slice.call(arguments, 0);
          args.unshift("[SIPAY DEBUG]");
          console.log.apply(console, args);
        }
      },
      getCSShref: function () {
        return this.config().environment_url + "pwall_app/css/app.css";
      },
      loadStylesInHead: function () {
        // css include
        var l = document.createElement("link");
        l.rel = "stylesheet";
        l.href = this.getCSShref();
        $("head").append(l);
      },
      /**
       * Place order.
       */
      placeOrder: function (data, event) {
        var self = this;

        if (event) {
          event.preventDefault();
        }
        return false;
      },
      getTagsFromQuote: function () {
        var has_virtual = false;
        var has_novirtual = false;
        $.each(quote.getItems(), function (index, item) {
          if (item.product_type == "virtual") {
            has_virtual = true;
          } else {
            has_novirtual = true;
          }
        });
        if (has_virtual && has_novirtual) {
          return "mixto";
        } else if (has_virtual) {
          return "digital";
        } else {
          return "fisico";
        }
      }
    });
  }
);
