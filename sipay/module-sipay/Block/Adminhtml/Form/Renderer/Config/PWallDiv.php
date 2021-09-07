<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sipay\Sipay\Block\Adminhtml\Form\Renderer\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class PWallDiv extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context, 
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Retrieve Element HTML fragment
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        //$html = $this->layout->createBlock('Vendor\Module\Block\File')->setData($data)->setTemplate('Vendor_Module::file.phtml')->toHtml();
        $html = '<div id="' . $element->getHtmlId() . '" ></div>'; //You can add here your html code
        return $html;
    }
}