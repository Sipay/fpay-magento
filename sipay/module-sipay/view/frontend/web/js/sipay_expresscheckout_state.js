define(
  [
    'jquery', 
    'mage/url',
    'domReady!'
  ],
  function ($, url) {
    'use strict';
    var sipayExpressCheckoutState = function () {
      this.profiles           = {};
      this.currentProfile     = null;
      this.lastProfile        = null;
      this.config             = null;
      this.element            = null;
      this.elementParent      = null;
      this.hasLoadedStyle     = false;

      this.addProfile = function(config, element){
        this.profiles[config.profile] = {config:config, element: element, elementParent: $(element).parent()};
      }

      this.setCurrentProfile = function(config, element){
        this.addProfile(config, element);
        this.lastProfile = this.currentProfile;
        this.removeFieldset(this.element);
        this.currentProfile = config.profile;
        this.config = config;
        this.element = element;
        this.elementParent = this.profiles[config.profile].elementParent;
        this.initExpressCheckout()
      }

      this.revertProfile = function(){
        if(!this.lastProfile){
          this.currentProfile = null;
          this.config = null;
          this.element = null;
          return;
        }
        this.removeFieldset(this.element);
        this.currentProfile = this.lastProfile;
        this.lastProfile = null;
        this.config = this.profiles[this.currentProfile].config;
        this.element = this.profiles[this.currentProfile].element;
        this.elementParent = this.profiles[this.currentProfile].elementParent;
        this.initExpressCheckout();
      }

      this._init = function(config, element, quote) {
        const client = new PWall(config.enviroment, false);
        var express_checkout = client.expresscheckout();

        express_checkout.backendUrl(config.backendUrl)
        express_checkout.appendTo(element)
        express_checkout.setProfile(config.profile)
        express_checkout.setTags(quote.tags)
        express_checkout.setLogoUrl(config.storeLogoUrl)
        express_checkout.on("paymentOk", this.redirectToCheckoutSuccess)
        express_checkout.on("paymentKo", () => { console.log("PAYMENT KO") })
        if(this.currentProfile === "m2_product_page"){
          express_checkout.on("validationFunc", this.validateFunction.bind(this))
        }else{
          express_checkout.on("validationFunc", function(){return true;});
        }
        express_checkout.currency(quote.currency)
        express_checkout.groupId(quote.group_id)
        express_checkout.amount(quote.amount)
      };

      this.redirectToCheckoutSuccess = function() {
        var redirectUrl = url.build("checkout/onepage/success", {});
        window.location.href = redirectUrl;
      };

      this.loadStylesInHead = function() {
        // css include
        if(this.hasLoadedStyle){
          return;
        }
        var l = document.createElement("link");
        l.rel = "stylesheet";
        l.href = this.config.cssUrl;
        this.hasLoadedStyle = true;
        $("head").append(l);
      };

      this.initExpressCheckout = function() {
        //if first #app, save configuration for later
        this.loadStylesInHead();
        this.containerPosition();
        $.ajax({
          url: this.config.quoteInfoUrl,
          dataType: "json",
          data: {},
          timeout: 30000,
          success: function (data) {
            this._init(this.config, this.element, data);
          }.bind(this)
        });
      };

      this.containerPosition = function(){
        var app_element = this.element;
        var insert = "";

        $('#app').remove();
        if (Object.keys(this.config.positionConfig).length > 0) {
          $('#sipay_ec_app').remove();
          //Prepare container and insert in position selected
          var app_container = "<div id=sipay_ec_app></div>";
          this.insertInPosition(this.config.positionConfig, this.element, app_container);
          app_element = "#sipay_ec_app";
          if (this.config.positionStyleConfig) {
            $(app_element).css(this.config.positionStyleConfig);
          }
          this.element = app_element;
          insert = this.config.positionConfig.insertion;
        }
        //CREATE CONTAINER WITH LEGEND AND ALL THAT HAPPY STUFF
        if (Object.keys(this.config.containerStyle).length > 0) {
          if (this.config.containerStyle.header_title != null) {
            var fieldset = '<fieldset id="sipay_ec_container"><legend style="padding: 0px 16px;" align="center">' + this.config.containerStyle.header_title + '</legend ></fieldset>';
          } else {
            var fieldset = '<fieldset id="sipay_ec_container"><fieldset>';
          }
          if (this.config.positionStyleConfig) {
            $("#sipay_ec_container").css(this.config.positionStyleConfig);
          }
          if (insert === "into") {
            this.insertInPosition(this.config.positionConfig, $(this.element).parent(), fieldset);
          } else {
            this.insertInPosition(this.config.positionConfig, this.element, fieldset);
          }
          if (this.config.containerStyle.descriptive_text != null && this.config.containerStyle.descriptive_text != "") {
            $("fieldset#sipay_ec_container > legend").bind({
              mousemove: this.changeTooltipPosition,
              mouseenter: this.showTooltip.bind(this),
              mouseleave: this.hideTooltip
            });
          }

          var elementj = $(app_element).detach();

          if (this.config.containerStyle.color != "#") {
            $('#sipay_ec_container').css('border', '1px solid ' + this.config.containerStyle.color);
          } else {
            $('#sipay_ec_container').css('border', '1px solid ' + this.config.containerStyle.custom_color);
          }
          if (this.config.containerStyle.header_title_typo != null) {
            $('#sipay_ec_container').css('font-family', this.config.containerStyle.header_title_typo);
          }
          $('#sipay_ec_container').append(elementj);
        }
      }

      this.removeFieldset = function(app_element){
        var $fieldset = $('#sipay_ec_container');
        if ($fieldset.length && !this.config.positionConfig.length){
          var $app_element = $(app_element).detach();
          this.elementParent.append($app_element);
        }
        $fieldset.remove();
      }

      this.validateFunction = function() {
        var $form = $("#product_addtocart_form");
        if ($form.length > 0) {
          $('body').trigger('processStart');
          var validateRes = $form.validation('isValid');
          if (validateRes === true) {
            this.ajaxSyncSubmit($form);
          }
          $('body').trigger('processStop');
          return validateRes;
        } else {
          return true;
        }
      };

      this.ajaxSyncSubmit = function(form) {
        var formData = new FormData(form[0]);
        $.ajax({
          url: form.attr('action'),
          data: formData,
          type: 'post',
          dataType: 'json',
          cache: false,
          contentType: false,
          processData: false,
          async: false
        });
      };

      this.insertInPosition = function(configPosition, element, app_container) {
        if (configPosition.insertion === "into") {
          $(element).append(app_container);
        } else if (configPosition.insertion === "before") {
          $(app_container).insertBefore(element);
        } else {
          $(app_container).insertAfter(element);
        }
      };

      this.showTooltip = function (event) {
        $('div.tooltip').remove();
        $('<div class="tooltip">' + this.config.containerStyle.descriptive_text + '</div>')
          .appendTo('body').css({
            "margin": "8px",
            "padding": "8px",
            "border": "1px solid grey",
            "position": "absolute",
            "background-color": "#FFFFFF",
            "z-index": "1000"
          });
        this.changeTooltipPosition(event);
      };

      this.changeTooltipPosition = function (event) {
        var tooltipX = event.pageX - 8;
        var tooltipY = event.pageY + 8;
        $('div.tooltip').css({ top: tooltipY, left: tooltipX });
      };

      this.hideTooltip = function () {
        $('div.tooltip').remove();
      };
    }
    return new sipayExpressCheckoutState();
  });