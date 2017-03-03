<?php

namespace Dibs\Flexwin\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\Message\ManagerInterface;

class Refund extends ResponseHandler implements HandlerInterface {
    
    public function handle(array $handlingSubject, array $response) {
        $this->preHandleValidate($handlingSubject);
        if($response['status'] == \Dibs\Flexwin\Model\Method::API_OPERATION_FAILURE) {
           $message = __('Refund declined');
           $message .= $this->prepareErrorMessage($response['error']);
           $this->message->addError($message);
           throw new \Exception($message);
        }
        if($response['status'] == \Dibs\Flexwin\Model\Method::API_OPERATION_SUCCESS) {
            $this->handleTransaction($handlingSubject['payment']);
            $this->message->addSuccess(__('Transaction was refunded'));
        }
    }
}
