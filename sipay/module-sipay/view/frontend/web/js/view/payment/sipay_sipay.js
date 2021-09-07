define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'mage/url'
    ],
    function (
        Component,
        rendererList,
        url
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'sipay_sipay',
                component: 'Sipay_Sipay/js/view/payment/method-renderer/sipay_sipay'
            }
        );
        if (parseUrlParams('request_id') && parseUrlParams('method')) {
            var actual_url          = location.protocol + '//' + location.host + location.pathname;
            var magento_checkout    = url.build("checkout", {});
            if (actual_url === magento_checkout){
                window.location.hash = "payment";
            }
        }
        // Add view logic here if needed
        function parseUrlParams(name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            if (results == null) {
                return null;
            }
            else {
                return decodeURI(results[1]) || 0;
            }
        }
        return Component.extend({});
    }
    
);
