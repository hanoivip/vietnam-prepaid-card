<?php

namespace Hanoivip\VietnamPrepaidCard\Services;

use Hanoivip\PaymentMethodContract\IPaymentDone;

class CardToGame implements IPaymentDone
{
    use CheckResult;
    
    protected $delivery = 'game';
    
}