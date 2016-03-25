<?php

namespace Dibs\Flexwin\Controller\index;
class Cancel extends \Dibs\Flexwin\Controller\Index {
   
    public function execute() {
       if($this->checkPost()) {
            $order = $this->order->loadByIncrementId($this->getRequest()->getParam(\Dibs\Flexwin\Model\Method::KEY_ORDERID_NAME));
            $quote = $this->quote->loadByIdWithoutStore($order->getQuoteId());
            $quote->setIsActive(1)->setReservedOrderId(NULL)->save();
            $this->checkoutSession->replaceQuote($quote);
            $this->checkoutSession->unsLastRealOrderId();
       }
       $this->_redirect('checkout');
    }
}