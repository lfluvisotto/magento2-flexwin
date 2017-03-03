<?php

namespace Dibs\Flexwin\Observer;

use Magento\Framework\Event\ObserverInterface;

class availabilityValidator implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $methodInstance = $observer['method_instance'];
        if($methodInstance->getCode() == \Dibs\Flexwin\Model\ConfigProvider::METHOD_CODE) {
            $paytypes = array(
                        $methodInstance->getConfigData('card_visa'),
                        $methodInstance->getConfigData('card_master'),
                        $methodInstance->getConfigData('card_amex'),
                        $methodInstance->getConfigData('card_diners'),
                        $methodInstance->getConfigData('card_dankort'),
                        $methodInstance->getConfigData('mobilepay'));
            if(!in_array('1', $paytypes)) {
              $checkResult = $observer['result'];
              $checkResult->setData('is_available', false);
            }
        }
    }
}