<?php

namespace Dibs\Flexwin\Controller\index;
class Callback extends \Dibs\Flexwin\Controller\Index 
{
    public function execute() {
        if($this->checkPost()) {
            $this->method->completeCheckout('callback');
        }
    }

}