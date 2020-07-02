<?php

namespace Dibs\Flexwin\Model;

use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const METHOD_CODE = 'dibs_flexwin';

    /*
     * @var \Dibs\Flexwin\Model\Method
     */
    protected $method;

    /*
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /*
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\View\Asset\Repository $assetRepo

    ) {
        $this->method = $paymentHelper->getMethodInstance(self::METHOD_CODE);
        $this->urlInterface = $urlInterface;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'dibsFlexwin' => [
                    'paytype'       => [
                        'visa'      => ['enabled' => $this->method->getConfigData('card_visa'),
                                        'logo'=>$this->getLogo('visa'),
                                         'id' => 'dibs_flexwin_cards_visa',
                                         'title' => __('Visa'),
                                         'paytype' => 'VISA'],
                        'master'    => ['enabled' => $this->method->getConfigData('card_master'),
                                        'logo'=>$this->getLogo('mastercard'),
                                         'id'=> 'dibs_flexwin_cards_master',
                                         'title'=> __('Mastercard'),
                                         'paytype'=> 'MC'],
                        'visa_debit' => ['enabled' => $this->method->getConfigData('card_visa_debit'),
                                        'logo'=>$this->getLogo('visa'),
                                        'id' => 'dibs_flexwin_cards_visa_debit',
                                        'title' => __('Visa debit'),
                                        'paytype' => 'VISA'],
                        'master_debit'    => ['enabled' => $this->method->getConfigData('card_master_debit'),
                                        'logo'=>$this->getLogo('mastercard'),
                                        'id'=> 'dibs_flexwin_cards_master_debit',
                                        'title'=> __('Mastercard debit'),
                                        'paytype'=> 'MC'],
                        'visa_credit' => ['enabled' => $this->method->getConfigData('card_visa_credit'),
                                        'logo'=>$this->getLogo('visa'),
                                        'id' => 'dibs_flexwin_cards_visa_credit',
                                        'title' => __('Visa credit'),
                                        'paytype' => 'VISA'],
                        'master_credit'  => ['enabled' => $this->method->getConfigData('card_master_credit'),
                                        'logo'=>$this->getLogo('mastercard'),
                                        'id'=> 'dibs_flexwin_cards_master_credit',
                                        'title'=> __('Mastercard credit'),
                                        'paytype'=> 'MC'],
                        'amex'      => ['enabled' => $this->method->getConfigData('card_amex'),
                                        'logo'=>$this->getLogo('amex'),
                                         'id'=> 'dibs_flexwin_cards_amex',
                                         'title'=> __('American Express'),
                                         'paytype' => 'AMEX'],
                        'diners'    => ['enabled' => $this->method->getConfigData('card_diners'),
                                        'logo'=>$this->getLogo('diners'),
                                        'id' => 'dibs_flexwin_cards_diners',
                                        'title'=> __('Diners'),
                                        'paytype'=> 'DIN'],
                        'dankort'   => ['enabled' => $this->method->getConfigData('card_dankort'),
                                        'logo'=>$this->getLogo('dankort'),
                                         'id' => 'dibs_flexwin_cards_dankort',
                                         'title' => __('Dankort'),
                                         'paytype' => 'DK'],
                        'visa_electron' => ['enabled' => $this->method->getConfigData('card_visa_electron'),
                                        'logo' => $this->getLogo('visa_electron'),
                                        'id' => 'dibs_flexwin_card_visa_electron',
                                        'title' => __('VISA Electron'),
                                        'paytype' => 'ELEC'],
                        'maestro' => ['enabled' => $this->method->getConfigData('card_maestro'),
                                        'logo' => $this->getLogo('maestro'),
                                        'id' => 'dibs_flexwin_card_maestro',
                                        'title' => __('Maestro'),
                                        'paytype' => 'MTRO'],
                       'forbrugsforeningen' => ['enabled' => $this->method->getConfigData('card_forbrugsforeningen'),
                                        'logo' => $this->getLogo('forbrugerforening'),
                                        'id' => 'dibs_flexwin_card_forbrugsforeningen',
                                        'title' => __('Forbrugsforeningen'),
                                        'paytype' => 'FFK'],
                        'mobilepay' => ['enabled' => $this->method->getConfigData('mobilepay'),
                                        'logo'=>$this->getLogo('mobilepay'),
                                         'id'=> 'dibs_flexwin_mobilepay',
                                         'title'=> __('MobilePay'),
                                         'paytype'=> $this->method->getConfigData('testmode') == 1 ? 'MPO_Nets_T' : 'MPO_Nets'],
                        'dkw' => ['enabled' => $this->method->getConfigData('dkw'),
                                        'logo'=>$this->getLogo('dankort'),
                                         'id'=> 'dibs_flexwin_dkw',
                                         'title'=> __('DKW'),
                                         'paytype'=> 'DKW'],
                        'swedbank'  => ['enabled' => $this->method->getConfigData('swedbank'),
                                        'logo'=>$this->getLogo('swedbank'),
                                         'id'=> 'dibs_flexwin_fsb',
                                         'title'=> __('Swedbank'),
                                         'paytype'=> $this->method->getConfigData('testmode') == 1 ? 'swd_atest' : 'swd_a'],
                        'seb'       => ['enabled' => $this->method->getConfigData('seb'),
                                        'logo'=>$this->getLogo('seb'),
                                         'id' => 'dibs_flexwin_seb',
                                         'title' => __('SEB Bank'),
                                         'paytype' => $this->method->getConfigData('testmode') == 1 ? 'seb_atest' : 'seb'],
                        'paypal'    => ['enabled' => $this->method->getConfigData('paypal'),
                                        'logo'=>$this->getLogo('paypal'),
                                         'id' => 'dibs_flexwin_paypal',
                                         'title' => __('Paypal'),
                                         'paytype' => $this->method->getConfigData('testmode') == 1 ?'paypaltest':'paypal'],
                        'nordea'    => ['enabled' => $this->method->getConfigData('nordea'),
                                        'logo' => $this->getLogo('nordea'),
                                        'id' => 'dibs_flexwin_nordea',
                                        'title' => __('Nordea'),
                                        'paytype' => 'ndb']],

                        'getPlaceOrderUrl' => $this->urlInterface->getDirectUrl($this->method->getConfigData('place_order_url')),
                        'formActionUrl'    => $this->method->getConfigData('form_action_url'),
                        'logoWith'         => $this->method->getConfigData('logo_with'),
                        'test'             => $this->method->getConfigData('testmode')
                ]
            ]
        ];
        return $config;
    }

    protected function getLogo($imgName) {
        $imgPath = 'Dibs_Flexwin::images/'.$imgName.'.png';
        return $this->assetRepo->getUrlWithParams($imgPath,[]);
    }
}
