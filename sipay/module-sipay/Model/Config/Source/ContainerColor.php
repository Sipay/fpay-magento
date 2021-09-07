<?php
namespace Sipay\Sipay\Model\Config\Source;

class ContainerColor implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
      return array(
          array('value'=> '#FFFFFF','label'=>__('Light')),
          array('value'=> '#000000',  'label'=>__('Dark')),
          array('value'=> '#','label'=>__('Custom'))
      );
    }
}
