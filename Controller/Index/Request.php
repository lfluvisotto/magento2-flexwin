<?php

namespace Dibs\Flexwin\Controller\Index;

use Magento\Framework\DataObject;

class Request extends \Dibs\Flexwin\Controller\Index
{

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    protected $_pageFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Sales\Model\OrderFactory $orderFactory,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Dibs\Flexwin\Model\Method $method,
                                \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
                                \Psr\Log\LoggerInterface $logger,
                                \Magento\Framework\View\Result\PageFactory $pageFactory
            )
    {
        parent::__construct($context, $orderFactory, $checkoutSession, $method, $logger);
        $this->formKeyValidator = $formKeyValidator;
        $this->_pageFactory = $pageFactory;
    }

    public function execute()
    {
        return $this->_pageFactory->create();
    }
}
