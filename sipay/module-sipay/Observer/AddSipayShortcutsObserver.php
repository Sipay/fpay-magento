<?php

namespace Sipay\Sipay\Observer;

class AddSipayShortcutsObserver implements \Magento\Framework\Event\ObserverInterface
{

  protected $shortcutFactory;

  public function __construct(
    \Magento\Paypal\Helper\Shortcut\Factory $shortcutFactory
  ) {
    $this->shortcutFactory = $shortcutFactory;
  }

  /**
   * Add Sipay shortcut button
   *
   * @param EventObserver $observer
   * @return void
   */
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
    /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
    $shortcutButtons = $observer->getEvent()->getContainer();

    $shortcut = $shortcutButtons->getLayout()->createBlock(
      \Sipay\Sipay\Block\ExpressCheckout\Shortcut::class,
      '',
      []
    );

    $shortcut->setIsInCatalogProduct(
      $observer->getEvent()->getIsCatalogProduct()
    )->setShowOrPosition(
      $observer->getEvent()->getOrPosition()
    );

    $shortcut->setIsShoppingCart($observer->getEvent()->getIsShoppingCart());

    $shortcut->setIsCart(get_class($shortcutButtons) == \Magento\Checkout\Block\QuoteShortcutButtons::class);
    
    $shortcutButtons->addShortcut($shortcut);
  }
}
