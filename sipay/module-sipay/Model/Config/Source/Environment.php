<?php
namespace Sipay\Sipay\Model\Config\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
      return array(
          array('value'=> 'sandbox','label'=>__('sandbox')),
          array('value'=> 'live',  'label'=>__('live')),
          array('value'=> 'develop','label'=>__('develop'))
      );
    }
}
