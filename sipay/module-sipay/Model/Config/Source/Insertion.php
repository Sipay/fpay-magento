<?php
namespace Sipay\Sipay\Model\Config\Source;

class Insertion implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
      return array(
          array('value'=> 'before','label'=>__('Before')),
          array('value'=> 'into',  'label'=>__('Into')),
          array('value'=> 'after','label'=>__('After'))
      );
    }
}
