<?php

namespace Dibs\Flexwin\Block;

/**
 * Description of Redirect
 *
 * @author mabe
 */
class Redirect extends \Magento\Framework\View\Element\Template {
    
    private $method;
    
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = array(), \Dibs\Flexwin\Model\Method $method) {
         parent::__construct($context, $data);
         $this->method = $method;
         
    }
    
    public function getRequestParams() {
        return $this->method->collectRequestParams();
        
    }
    
}
