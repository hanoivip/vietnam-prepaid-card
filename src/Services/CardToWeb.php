<?php

namespace Hanoivip\VietnamPrepaidCard\Services;

use Hanoivip\PaymentMethodContract\IPaymentDone;

class CardToWeb implements IPaymentDone
{
    use CheckResult;
    
    protected $delivery = 'web';
    
}