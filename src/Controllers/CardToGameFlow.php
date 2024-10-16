<?php

namespace Hanoivip\VietnamPrepaidCard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Hanoivip\Game\Facades\GameHelper;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\Shop\Facades\OrderFacade;
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
        $next = "CardToGame";
        $svid = $request->input('svid');
        $role = $request->input('role');
        try
        {
            if (!empty($svid) && !empty($role))
            {
                GameHelper::saveUserDefaultRole($userId, "s$svid", $role);
            }
            $order = OrderFacade::dummyOrder($userId);
            $result = PaymentFacade::preparePayment($order->serial, $method, $next);
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