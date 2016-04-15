<?php

namespace Dibs\Flexwin\Model;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface {
    
    protected $method;
    
    protected $urlInterface;
    
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
                'dibsFlexwin'           => [
                    'paytype' => [
                       'visa'              => $this->method->getConfigData('card_visa'),
                       'master'            => $this->method->getConfigData('card_master'),
                       'amex'              => $this->method->getConfigData('card_amex'),
                       'diners'            => $this->method->getConfigData('card_diners'),
                       'dankort'           => $this->method->getConfigData('card_dankort'),
                       'mobilepay'         => $this->method->getConfigData('mobilepay'),
                    ],
                    'getPlaceOrderUrl'  => $this->urlInterface->getDirectUrl($this->method->getConfigData('place_order_url')),
                    'formActionUrl'     => $this->method->getConfigData('form_action_url'),
                    'cdnUrlLogoPrefix'  => $this->method->getConfigData('cdn_url_logo_prefix'),
                    'logoWith'          => $this->method->getConfigData('logo_with')
                ]
            ]
        ];
        return $config;
    }
    
    public function getCards() {
        return $this->method->isCardsActive();
    }

}