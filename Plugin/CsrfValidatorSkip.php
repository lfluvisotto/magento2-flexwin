<?php

namespace Dibs\Flexwin\Plugin;
/**
 * Description of CsrfValidatorSkip
 *
 * @author mabe
 */
class CsrfValidatorSkip {
    
    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ActionInterface $action
    ) {
        if ('dibsflexwin' == $request->getModuleName() && 'callback' == $request->getActionName()) {
            return;
        }
        $proceed($request, $action);
    }
    
}
