<?php

namespace Dibs\Flexwin\Controller;

use \Dibs\Flexwin\Model\Method as Method;

abstract class Index extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     *
     * @var \Dibs\Flexwin\Model\Method
     */
    protected $method;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Dibs\Flexwin\Model\Method $method,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->method = $method;
        $this->logger = $logger;
    }

    public function checkPost()
    {
      if(!$this->getRequest()->getParam(Method::KEY_ORDERID_NAME)) {
            $message = __('Missing orderid');
            $this->logger->critical($message);
       }
       return $this->getRequest()->isPost()
              && $this->getRequest()->getParam(Method::KEY_ORDERID_NAME);
    }

    public function checkProtectCode()
    {
        return $this->getRequest()->getParam(Method::KEY_ORDER_PROTECT_CODE)
              && $this->method->checkProtectCode($this->getRequest()->getParam(Method::KEY_ORDERID_NAME),
                 $this->getRequest()->getParam(Method::KEY_ORDER_PROTECT_CODE));
    }
}
