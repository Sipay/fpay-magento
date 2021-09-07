define(
  [
    'mage/translate',
    'mage/url',
    'jquery',
    'sipayExpressCheckoutState',
    'domReady!'
  ],
  function (
    $t,
    url,
    $,
    sipayExpressCheckoutState
  ) {
    'use strict';

    var sipayExpressCheckout = function(config, element){
      if(config.profile != "m2_minicart"){
        sipayExpressCheckoutState.setCurrentProfile(config, element);     
      }else{
        $('[data-block=\'minicart\']').on('dropdowndialogopen', toggleMinicart(config, element));
        $('[data-block=\'minicart\']').on('dropdowndialogclose', toggleMinicart(config, element, 'close'));
        // $(document).on('click.outsideDropdown', toggleMinicart(config, element, 'close'));
        // $('#btn-minicart-close').on('click', toggleMinicart(config, element, 'close'));
        // $('[data-block="minicart"]').on('click', '[data-action="close"]', toggleMinicart(config, element, 'close'));
      }
      
      function toggleMinicart(config, element, action)
      {
        return function(){

          if (!action && $("#minicart-content-wrapper:visible").length || action !== 'close') {
            sipayExpressCheckoutState.setCurrentProfile(config, element);
          } else {
            sipayExpressCheckoutState.revertProfile();
          }
        }
      }
    }

    return sipayExpressCheckout
  });