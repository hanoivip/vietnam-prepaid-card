<?php

namespace Hanoivip\VietnamPrepaidCard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\VietnamPrepaidCard\Services\WebtopupRepository;
/**
 *
 * Flow 2: Web topup with prepaid card
 * - Target to game char
 * @author hanoivip
 *
 */
class CardToGameFlow extends Controller
{
    private $logs;
    
    public function __construct(
        WebtopupRepository $logs)
    {
        $this->logs = $logs;
    }
    
    public function index(Request $request)
    {
        $method = config('vpcard.payment_method_id', '');
        $userId = Auth::user()->getAuthIdentifier();
        $order = "CardToWebFlow@" . Str::random(6);
        $next = "CardToGame";
        try
        {
            $result = PaymentFacade::preparePayment($order, $method, $next);
            if ($this->logs->saveLog($userId, $result->getTransId()))
            {
                if ($request->ajax())
                {
                    return ['error' => 0, 'message' => '',
                        'data' => ['trans' => $result->getTransId(), 'guide' => $result->getGuide(), 'data' => $result->getData()]];
                }
                else
                {
                    return PaymentFacade::paymentPage($result->getTransId(), $result->getGuide(), $result->getData());
                }
            }
            else
            {
                return view('hanoivip.vpcard::failure', ['error_message' => __('hanoivip.payment::webtopup.log-fail')]);
            }
        }
        catch (Exception $ex)
        {
            Log::error("Webtopup index exception:" + $ex->getMessage());
            //report($ex);
            return view('hanoivip.vpcard::failure', ['error_message' => __('hanoivip.payment::webtopup.exception')]);
        }
    }
}