<?php

namespace Hanoivip\VietnamPrepaidCard\Services;

use Hanoivip\PaymentMethodContract\IPaymentDone;

class CardToOrder implements IPaymentDone
{
    use CheckResult;
    
    protected $delivery = 'order';
    
}