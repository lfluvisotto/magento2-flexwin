<?php

namespace Dibs\Flexwin\Plugin\Data\Form\FormKey;

/**
 * Description of Validator
 *
 * @author mabe
 */
class Validator {

    public function aroundValidate(\Magento\Framework\Data\Form\FormKey\Validator $subject, 
                                    \Closure $proceed, 
                                    \Magento\Framework\App\RequestInterface $request) {
        if('dibsflexwin' == $request->getModuleName() && 'callback' == $request->getActionName()) {
            return true;
        }
        $returnValue = $proceed($request);
        return $returnValue;
    }
}
