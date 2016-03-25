<?php

namespace Dibs\Flexwin\Model;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {
    
    protected $method;
    
    protected $urlInterface;
    
    const FORM_ACTION_URL = "https://payment.architrade.com/paymentweb/start.action";
    
    const METHOD_CODE = 'dibs_flexwin';
    
    /**
     * @var Escaper
     */
    protected $escaper;
    
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance(self::METHOD_CODE);
        $this->urlInterface = $urlInterface;
    }
    
    public function getConfig() {
          $config = [
            'payment' => [
                'dibsFlexwin' => [
                    'visa'   => $this->method->getConfigData('card_visa'),
                    'master' => $this->method->getConfigData('card_master'),
                    'amex'   => $this->method->getConfigData('card_amex'),
                    'diners'   => $this->method->getConfigData('card_diners'),
                    'dankort'   => $this->method->getConfigData('card_dankort'),
                    'getParamsUrl' => $this->urlInterface->getDirectUrl('dibsflexwin/index/request'),
                    'formActionUrl' => self::FORM_ACTION_URL
                    
                ]
            ]
        ];
        return $config;
    }
    
    public function getCards() {
        return $this->method->isCardsActive();
    }

}