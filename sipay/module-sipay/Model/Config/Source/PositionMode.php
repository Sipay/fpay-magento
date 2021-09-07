<?php
namespace Sipay\Sipay\Model\Config\Source;

class PositionMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
      return array(
          array('value'=> '0','label'=>__('Automatic')),
          array('value'=> '1',  'label'=>__('Manual'))
      );
    }
}
