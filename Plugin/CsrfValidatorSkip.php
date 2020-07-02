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
        if ('dibsflexwin' == $request->getModuleName() && in_array($request->getActionName(), ['callback', 'cancel'])) {
            return;
        }
        $proceed($request, $action);
    }
    
}
