<?php

namespace Dibs\Flexwin\Model;

interface MethodConfig {
    
    const KEY_CURRENCY_NAME    = 'currency';

    const KEY_MERCHANT_NAME    = 'merchant';

    const KEY_AMOUNT_NAME      = 'amount';

    const KEY_ACCEPTURL_NAME   = 'accepturl';

    const KEY_CANCELURL_NAME   = 'cancelurl';

    const KEY_PAYTYPE_NAME     = 'paytype';

    const KEY_CALLBACKURL_NAME = 'callbackurl';

    const KEY_ORDERID_NAME     = 'orderid';

    const KEY_DECORATOR_NAME   = 'decorator';

    const KEY_LANG_NAME        = 'lang';

    const KEY_TESTMODE_NAME    = 'testmode';

    const KEY_MD5KEY_NAME      = 'md5key';

    const KEY_CAPTURENOW_NAME  = 'capturenow';

    const KEY_MD5_KEY1_NAME    = 'md5key1';

    const KEY_MD5_KEY2_NAME    = 'md5key2';

    const KEY_CALCFEE_NAME     = 'calcfee';

    const KEY_ORDER_PROTECT_CODE  = 'x_protect_code';

    const RETURN_CONTEXT_ACCEPT   = 'accept';
    
    const RETURN_CONTEXT_CALLBACK = 'callback';

    const API_OPERATION_SUCCESS = 'ACCEPTED';
    
    const API_OPERATION_FAILURE = 'DECLINED';

    const CAPTURE_URL        = 'https://payment.architrade.com/cgi-bin/capture.cgi';
    
    const REFUND_URL_PATTERN = 'https://login:password@payment.architrade.com/cgi-adm/refund.cgi';
    
    const CANCEL_URL_PATTERN = 'https://login:password@payment.architrade.com/cgi-adm/cancel.cgi';
    
}
