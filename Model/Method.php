<?php

namespace Dibs\Flexwin\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Invoice;

class Method implements \Dibs\Flexwin\Model\MethodConfig
{
    /**
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     *
     * @var Magento\Payment\Model\MethodInterface
     */
    protected $methodObj;

    /**
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     *
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     *
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface
     */
    protected $_transactionBuilder;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        PaymentHelper $paymentHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\ObjectManagerInterface $_objectManager,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->urlInterface = $urlInterface;
        $this->paymentHelper = $paymentHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->methodObj = $this->paymentHelper->getMethodInstance(ConfigProvider::METHOD_CODE);
        $this->request = $request;
        $this->orderFactory = $orderFactory;
        $this->orderSender = $orderSender;
        $this->scopeConfig = $scopeConfig;
        $this->invoiceService = $invoiceService;
        $this->_objectManager = $_objectManager;
        $this->_transactionBuilder = $transactionBuilder;
        $this->logger = $logger;
    }

    /**
     * Collect params that will be
     * sended to DIBS Payment gateway
     *
     * @return array
     */
    public function collectRequestParams()
    {
        $order = $this->orderFactory->create()->load($this->request->getParam(self::KEY_ORDERID_NAME));
        if ($order->getId()) {
            $orderId = $order->getIncrementId();
            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $this->setCustomOrderStatus('order_status_pending', $order);
            $order->save();
            $requestParams['result'] = 'success';
            $requestParams['params'] = array(
                self::KEY_MERCHANT_NAME    => trim($this->methodObj->getConfigData(self::KEY_MERCHANT_NAME)),
                self::KEY_ORDERID_NAME     => $orderId,
                self::KEY_CURRENCY_NAME    => $this->getCurrencyNumber($order->getOrderCurrencyCode()),
                self::KEY_AMOUNT_NAME      => $this->api_dibs_round($order->getGrandTotal()),
                self::KEY_ACCEPTURL_NAME   => $this->urlInterface->getDirectUrl('dibsflexwin/index/accept'),
                self::KEY_CANCELURL_NAME   => $this->urlInterface->getDirectUrl('dibsflexwin/index/cancel'),
                self::KEY_PAYTYPE_NAME     => $this->request->getParam(self::KEY_PAYTYPE_NAME),
                // https://github.com/magento/magento2/commit/b475068f894b95674454cc08dc327a45d218a72f
                // compatibility with version 2.3 and backward compatibility
                self::KEY_CALLBACKURL_NAME => $this->urlInterface->getDirectUrl('dibsflexwin/index/callback/ajax/1')
            );
            if ($this->methodObj->getConfigData(self::KEY_TESTMODE_NAME) == 1) {
                $requestParams['params']['test'] = 1;
            }
            if ($this->methodObj->getConfigData(self::KEY_DECORATOR_NAME)) {
                $requestParams['params'][self::KEY_DECORATOR_NAME] = $this->methodObj->getConfigData(self::KEY_DECORATOR_NAME);
            }
            if ($this->methodObj->getConfigData(self::KEY_LANG_NAME)) {
                $langCode = $this->scopeConfig->getValue('payment/' . ConfigProvider::METHOD_CODE . 
                        '/' . self::KEY_LANG_NAME, \Magento\Store\Model\ScopeInterface::SCOPE_STORES);
                $requestParams['params'][self::KEY_LANG_NAME] = $langCode;
            }
            if ($this->methodObj->getConfigData(self::KEY_CALCFEE_NAME) == 1) {
                $requestParams['params'][self::KEY_CALCFEE_NAME] = 'yes';
            }
            $macCodeParams = array(
                self::KEY_MERCHANT_NAME => $requestParams['params'][self::KEY_MERCHANT_NAME],
                self::KEY_ORDERID_NAME  => $requestParams['params'][self::KEY_ORDERID_NAME],
                self::KEY_CURRENCY_NAME => $requestParams['params'][self::KEY_CURRENCY_NAME],
                self::KEY_AMOUNT_NAME   => $requestParams['params'][self::KEY_AMOUNT_NAME]
            );
            if ($md5Key = $this->calcMd5Code($macCodeParams)) {
                $requestParams['params'][self::KEY_MD5KEY_NAME] = $md5Key;
            }
            if ($this->methodObj->getConfigData(self::KEY_CAPTURENOW_NAME) == '1') {
                $requestParams['params'][self::KEY_CAPTURENOW_NAME] = 1;
            }
            // Billing info
            $billingAddress = $order->getBillingAddress();
            $requestParams['params']['delivery01.Billing'] = 'Billing Address';
            $requestParams['params']['delivery02.Firstname'] = $billingAddress->getFirstname();
            $requestParams['params']['delivery03.Lastname'] = $billingAddress->getLastname();
            $requestParams['params']['delivery04.Street'] = $billingAddress->getStreet();
            $requestParams['params']['delivery05.Postcode'] = $billingAddress->getPostcode();
            $requestParams['params']['delivery06.City'] = $billingAddress->getCity();
            $requestParams['params']['delivery07.Region'] = $billingAddress->getRegionId();
            $requestParams['params']['delivery08.Country'] = $billingAddress->getCountryId();
            $requestParams['params']['delivery09.Telephone'] = $billingAddress->getTelephone();
            $requestParams['params']['delivery10.E-mail'] = $billingAddress->getEmail();

            // Shipping info
            $shippigAddress = $order->getShippingAddress();
            // Virtual products doesn't have shipping address
            if($shippigAddress) {
            	$requestParams['params']['delivery11.Delivery'] = 'Shipping Address';
            	$requestParams['params']['delivery12.Firstname'] = $shippigAddress->getFirstname();
            	$requestParams['params']['delivery13.Lastname'] = $shippigAddress->getLastname();
            	$requestParams['params']['delivery14.Street'] = $shippigAddress->getStreet();
            	$requestParams['params']['delivery15.Postcode'] = $shippigAddress->getPostcode();
            	$requestParams['params']['delivery16.City'] = $shippigAddress->getCity();
            	$requestParams['params']['delivery17.Region'] = $shippigAddress->getRegionId();
            	$requestParams['params']['delivery18.Country'] = $shippigAddress->getCountryId();
            	$requestParams['params']['delivery19.Telephone'] = $shippigAddress->getTelephone();
            	$requestParams['params']['delivery10.E-mail'] = $shippigAddress->getEmail();
	    }

            $requestParams['params']['x_protect_code'] = $order->getProtectCode();

        } else {
            $requestParams['result'] = 'error';
            $requestParams['message'] = 'error occured';
        }
        return $requestParams;
    }

    /**
     * Calculate md5key from given params
     *
     * @param array $params
     *
     * @return string|null
     */
    protected function calcMd5Code(array $params)
    {
        $md5key = null;
        if ($this->checkMd5KeyCodeRequired()) {
            $key1 = trim($this->methodObj->getConfigData(self::KEY_MD5_KEY1_NAME));
            $key2 = trim($this->methodObj->getConfigData(self::KEY_MD5_KEY2_NAME));
            $parameter_string = '';
            $parameter_string .= self::KEY_MERCHANT_NAME . '=' . $params[self::KEY_MERCHANT_NAME];
            $parameter_string .= '&' . self::KEY_ORDERID_NAME . '=' . $params[self::KEY_ORDERID_NAME];
            $parameter_string .= '&' . self::KEY_CURRENCY_NAME . '=' . $params[self::KEY_CURRENCY_NAME];
            $parameter_string .= '&' . self::KEY_AMOUNT_NAME . '=' . $params[self::KEY_AMOUNT_NAME];
            $md5key = md5($key2 . md5($key1 . $parameter_string));
        }
        return $md5key;
    }

    /**
     * Set returned params to order for
     * success return and callback, add
     * comment for order for successreturn/callback cases
     *
     * @param string $context
     *
     * @return \Magento\Sales\Model\Order $order
     */
    protected function setReturnedParamsToOrder($context = self::RETURN_CONTEXT_ACCEPT)
    {
        $orderComment = '';
        if ($context == self::RETURN_CONTEXT_ACCEPT) {
            $orderComment = __('Customer successfully returned from DIBS');
        }
        if ($context == self::RETURN_CONTEXT_CALLBACK) {
            $orderComment = __('Callback was received from DIBS');
        }
        $orderIncrementId = $this->request->getParam(self::KEY_ORDERID_NAME);
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        $transactId = $this->request->getParam('transact');

        $this->authorizeTransaction($order, ['id' => $transactId]);
        if ($order->getId()) {
            $order->addStatusHistoryComment($orderComment);
            $order->save();
            $payment = $order->getPayment();
            $payment->setLastTransId($transactId);
            if($cardnomask = $this->request->getParam('cardnomask')) {
                 $payment->setCcLast4(substr($cardnomask, strlen($cardnomask)-4));
                 $payment->setCcType($this->request->getParam('paytype'));
                 $payment->setCcNumberEnc($cardnomask);
                 $expdate = $this->request->getParam('cardexpdate');
                 $payment->setCcExpYear(substr($expdate, 0, 2));
                 $payment->setCcExpMonth(substr($expdate, 2));
            }
            $payment->save();
        }
        return $order;
    }

    /**
     * Complete checkout and set new status to order
     * \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
     * Send Email comfirmation
     *
     * @param type $context
     *
     * @return type
     */
    public function completeCheckout($context = self::RETURN_CONTEXT_ACCEPT)
    {
        if ($this->checkMd5KeyCodeRequired()) {
            $returnedParams = array(
                self::KEY_MERCHANT_NAME => $this->request->getParam(self::KEY_MERCHANT_NAME),
                self::KEY_ORDERID_NAME  => $this->request->getParam(self::KEY_ORDERID_NAME),
                self::KEY_CURRENCY_NAME => $this->request->getParam(self::KEY_CURRENCY_NAME),
                self::KEY_AMOUNT_NAME   => $this->request->getParam(self::KEY_AMOUNT_NAME),
                self::KEY_MD5KEY_NAME   => $this->request->getParam(self::KEY_MD5KEY_NAME),
            );
            if (!$this->checkMacCode($returnedParams)) {
                 $this->logger->alert('mac code error');
                return;
            }
        }
        $order = $this->setReturnedParamsToOrder($context);
        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
        $this->setCustomOrderStatus('order_status', $order);
        $payment = $order->getPayment();
        // save fee to order
        if($this->request->getParam('fee') > 0) {
            $order->setDibsFee($this->request->getParam('fee'));
            $order->setBaseDibsFee($this->request->getParam('fee'));
            $order->setGrandTotal($order->getGrandTotal() + $this->request->getParam('fee') / 100);
        }
        if($this->shouldMakeInvoice() && $order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $this->capture($invoice, $payment);
        } 
        $order->setIsNotified(false);
        $order->save();
        if (!$order->getEmailSent()) {
            $this->orderSender->send($order);
        }

    }

    public static function api_dibs_round($fNum, $iPrec = 2)
    {
        return empty($fNum) ? (int) 0 : (int) (string)
                (round($fNum, $iPrec) * pow(10, $iPrec));
    }

    /**
     *  Compare calculated mac
     *  code based on returned params
     *  and returned mac code
     *
     * @param type $returnedParams
     * @return boolean
     */
    protected function checkMacCode(array $returnedParams)
    {
       return $this->calcMd5Code($returnedParams) == $returnedParams[self::KEY_MD5KEY_NAME];
    }

    /**
     *
     * @param string $incrementId
     */
    public function setOrderCancelled($incrementId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            $order->cancel();
            $this->setCustomOrderStatus('order_status_cancel', $order);
            $order->addStatusHistoryComment(__('Customer has cancelled payment'));
            $order->save();
        }
    }

    /**
     *
     * @param string $incrementId
     */
    public function restoreQuoteFromOrder($incrementId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getId()) {
            $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
            $quote->setIsActive(1)->setReservedOrderId(null)->save();
            $this->_checkoutSession->replaceQuote($quote);
        }
    }

    /**
     * Check if we have to check md5key only
     * in case if we set up md5key1 md5key2 in admin
     *
     * @return bool
     */
    protected function checkMd5KeyCodeRequired()
    {
        return trim($this->methodObj->getConfigData(self::KEY_MD5_KEY1_NAME))
               && trim($this->methodObj->getConfigData(self::KEY_MD5_KEY2_NAME));
    }

    /**
     * Set custom order staus for DIBS for states:
     *  pending_payment, processing, cancelled
     *
     * @param string $statusConfName
     *
     * @return type
     */
    protected function setCustomOrderStatus($statusConfName, \Magento\Sales\Model\Order $order)
    {
        $orderStatus = $this->methodObj->getConfigData($statusConfName);
        if ($orderStatus) {
            $order->setStatus($orderStatus);
        }
    }

    protected function capture(\Magento\Sales\Model\Order\Invoice $invoice, $payment) {
         try {
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                $invoice->setTransactionId($payment->getLastTransId());
                $invoice->register();
                $transactionSave = $this->_objectManager->create(
                    'Magento\Framework\DB\Transaction'
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
            } catch(\Exception $e) {
                 $this->logger->critical($e->getMessage());
            }
    }

    protected function shouldMakeInvoice() {
        return (null !== $this->request->getParam('capturenow'))
                && ($this->request->getParam('capturenow') == 1)
                && ($this->methodObj->getConfigData('makeinvoice') == 1);
    }

    protected function getCurrencyNumber($code) {
        $aCurrency = array ('ADP' => '020','AED' => 784,'AFA' => '004','ALL' => '008',
                            'AMD' => '051','ANG' => 532,'AOA' => 973,'ARS' => '032',
                            'AUD' => '036','AWG' => 533,'AZM' => '031','BAM' => 977,
                            'BBD' => '052','BDT' => '050','BGL' => 100,'BGN' => 975,
                            'BHD' => '048','BIF' => 108,'BMD' => '060','BND' => '096',
                            'BOB' => '068','BOV' => 984,'BRL' => 986,'BSD' => '044',
                            'BTN' => '064','BWP' => '072','BYR' => 974,'BZD' => '084',
                            'CAD' => 124,'CDF' => 976,'CHF' => 756,'CLF' => 990,
                            'CLP' => 152,'CNY' => 156,'COP' => 170,'CRC' => 188,
                            'CUP' => 192,'CVE' => 132,'CYP' => 196,'CZK' => 203,
                            'DJF' => 262,'DKK' => 208,'DOP' => 214,'DZD' => '012',
                            'ECS' => 218,'ECV' => 983,'EEK' => 233,'EGP' => 818,
                            'ERN' => 232,'ETB' => 230,'EUR' => 978,'FJD' => 242,
                            'FKP' => 238,'GBP' => 826,'GEL' => 981,'GHC' => 288,
                            'GIP' => 292,'GMD' => 270,'GNF' => 324,'GTQ' => 320,
                            'GWP' => 624,'GYD' => 328,'HKD' => 344,'HNL' => 340,
                            'HRK' => 191,'HTG' => 332,'HUF' => 348,'IDR' => 360,
                            'ILS' => 376,'INR' => 356,'IQD' => 368,'IRR' => 364,
                            'ISK' => 352,'JMD' => 388,'JOD' => 400,'JPY' => 392,
                            'KES' => 404,'KGS' => 417,'KHR' => 116,'KMF' => 174,
                            'KPW' => 408,'KRW' => 410,'KWD' => 414,'KYD' => 136,
                            'KZT' => 398,'LAK' => 418,'LBP' => 422,'LKR' => 144,
                            'LRD' => 430,'LSL' => 426,'LTL' => 440,'LVL' => 428,
                            'LYD' => 434,'MAD' => 504,'MDL' => 498,'MGF' => 450,
                            'MKD' => 807,'MMK' => 104,'MNT' => 496,'MOP' => 446,
                            'MRO' => 478,'MTL' => 470,'MUR' => 480,'MVR' => 462,
                            'MWK' => 454,'MXN' => 484,'MXV' => 979,'MYR' => 458,
                            'MZM' => 508,'NAD' => 516,'NGN' => 566,'NIO' => 558,
                            'NOK' => 578,'NPR' => 524,'NZD' => 554,'OMR' => 512,
                            'PAB' => 590,'PEN' => 604,'PGK' => 598,'PHP' => 608,
                            'PKR' => 586,'PLN' => 985,'PYG' => 600,'QAR' => 634,
                            'ROL' => 642,'RUB' => 643,'RUR' => 810,'RWF' => 646,
                            'SAR' => 682,'SBD' =>'090','SCR' => 690,'SDD' => 736,
                            'SEK' => 752,'SGD' => 702,'SHP' => 654,'SIT' => 705,
                            'SKK' => 703,'SLL' => 694,'SOS' => 706,'SRG' => 740,
                            'STD' => 678,'SVC' => 222,'SYP' => 760,'SZL' => 748,
                            'THB' => 764,'TJS' => 972,'TMM' => 795,'TND' => 788,
                            'TOP' => 776,'TPE' => 626,'TRL' => 792,'TRY' => 949,
                            'TTD' => 780,'TWD' => 901,'TZS' => 834,'UAH' => 980,
                            'UGX' => 800,'USD' => 840,'UYU' => 858,'UZS' => 860,
                            'VEB' => 862,'VND' => 704,'VUV' => 548,'XAF' => 950,
                            'XCD' => 951,'XOF' => 952,'XPF' => 953,'YER' => 886,
                            'YUM' => 891,'ZAR' => 710,'ZMK' => 894,'ZWD' => 716,
        );
        return isset($aCurrency[$code]) ? $aCurrency[$code] : 0;
    }

    public function authorizeTransaction(\Magento\Sales\Model\Order $order, array $paymentData)
    {
        try {
            //get payment object from order object
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData['id']);
            $payment->setTransactionId($paymentData['id']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
            );
            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal() + $this->request->getParam('fee') / 100
            );
            $message = __('The authorized amount is %1.', $formatedPrice);
            //get the object of builder class
            $trans = $this->_transactionBuilder;
            $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData['id'])
            ->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $paymentData]
            )
            ->setFailSafe(true)
            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $transaction->setParentTxnId($paymentData['id'])
            ->setIsClosed(0); 
            if($this->shouldMakeInvoice()){
                $transaction->setIsClosed(1);
            }
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
            return  $transaction->save()->getTransactionId();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Compare returned order protect_code with current order protect code
     *
     * @param string $incrementId
     * @param string $protectCode
     * @return bool
     */
    public function checkProtectCode($incrementId, $protectCode)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        return $order->getProtectCode() == $protectCode ? true: false;
    }
}
