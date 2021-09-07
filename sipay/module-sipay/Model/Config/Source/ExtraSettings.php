<?php
/**
 * Copyright © 2016 Sipay. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Sipay\Sipay\Model\Config\Source;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ExtraSettings extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Sipay_Sipay::system/config/settings.phtml';
    protected $config;
    protected $url_builder;
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Url $urlHelper,
        array $data = []
    ) {
        $this->config       = $context->getScopeConfig();
        $this->url_builder  = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'launch_settings',
                'label' => __('Configure payment methods')
            ]
        );

        return $button->toHtml();
    }


    public function getBackendUrl()
    {
      return $this->url_builder->getUrl('sipay/backend/index', [ '_nosid' => true ]);
    }

    public function getCssUrl()
    {
        if(array_key_exists($this->config->getValue('payment/sipay_sipay/environment'), \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS)){
            return \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS[$this->config->getValue('payment/sipay_sipay/environment')] . "pwall_app/css/app.css";      
        }         
    }

    public function getSdkUrl()
    {
        if (array_key_exists($this->config->getValue('payment/sipay_sipay/environment'), \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS)) {
            return \Sipay\Sipay\Helper\Constants::ENVIROMENTS_URLS[$this->config->getValue('payment/sipay_sipay/environment')] . "pwall_sdk/pwall_sdk.bundle.js";
        } 
    }

    public function getSdkJsUrl()
    {
      return \Sipay\Sipay\Helper\Constants::SDK_JS_URL;
    }

    public function getEnvironment(){
        return $this->config->getValue('payment/sipay_sipay/environment');
    }
}
?>
