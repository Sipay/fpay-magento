<?php

namespace Sipay\Sipay\Block\ExpressCheckout;

class Shortcut extends \Sipay\Sipay\Block\ExpressCheckout\Button implements \Magento\Catalog\Block\ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Sipay_Sipay::express_checkout/minicart.phtml';

    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }

    public function setIsShoppingCart($isShoppingCart)
    {
        $this->isShoppingCart = $isShoppingCart;
    }

    /**
     * Is Should Rendered
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function shouldRender()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$payment = $objectManager->create('Magento\Payment\Model\MethodInterface');
        $session = $objectManager->create('Magento\Checkout\Model\Session');

        if ($this->getIsCart()) {
            return false;
        }

        return /*$this->expressHelper->getStoreConfig('payment/stripe_payments_express/cart_button', $session->getQuote()->getStoreId())
               &&*/ $this->isMiniCart;
    }

    /**
     * Render the block if needed
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }
}
