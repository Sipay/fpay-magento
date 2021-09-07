<?php
namespace Sipay\Sipay\Model\Config\Source;

class Typographies implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
      return array(
          array('value'=> '-', 'label'=>__('Without custom font')),
          array('value'=> 'Arial, Arial, Helvetica','label'=>'Arial'),
          array('value'=> 'Arial Black, Arial Black, Gadget',  'label'=>'Arial Black'),
          array('value'=> 'Comic Sans MS','label'=>'Comic Sans MS'),
          array('value'=> 'Courier New','label'=>'Courier New'),
          array('value'=> 'Georgia','label'=>'Georgia'),
          array('value'=> 'Impact, Impact, Charcoal','label'=>'Impact'),
          array('value'=> 'Lucida Console, Monaco','label'=> 'Lucida Console'),
          array('value'=> 'Lucida Sans Unicode, Lucida Grande','label'=> 'Lucida Sans Unicode'),
          array('value'=> 'Palatino Linotype, Book Antiqua, Palatino','label'=> 'Palatino'),
          array('value'=> 'Tahoma, Geneva','label'=> 'Tahoma'),
          array('value'=> 'Trebuchet MS','label'=> 'Trebuchet MS'),
          array('value'=> 'Verdana, Verdana, Geneva','label'=> 'Verdana'),
          array('value'=> 'Symbol','label'=> 'Symbol'),
          array('value'=> 'Webdings','label'=> 'Webdings'),
          array('value'=> 'Wingdings, Zapf Dingbats','label'=> 'Wingdings'),
          array('value'=> 'MS Sans Serif, Geneva','label'=> 'MS Sans Serif'),
          array('value'=> 'MS Serif, New York','label'=> 'MS Serif')
      );
    }
}
