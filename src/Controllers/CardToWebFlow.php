<?php

namespace Hanoivip\VietnamPrepaidCard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\Payment\Facades\BalanceFacade;
use Hanoivip\VietnamPrepaidCard\Services\WebtopupRepository;

/**
 *
 * Flow 1: Web topup with prepaid card
 * - Target to web balance
 * - Transfer to game
 * @author hanoivip
 *
 */
class CardToWebFlow extends Controller
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
        $next = 'CardToWeb';
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
                if ($request->ajax())
                {
                    return ['error' => 2, 'message' => '', 'data' => [] ];
                }
                else
                {
                    return view('hanoivip.vpcard::failure', ['error_message' => __('hanoivip.payment::webtopup.log-fail')]);
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error("Webtopup index exception:" + $ex->getMessage());
            report($ex);
            return view('hanoivip.vpcard::failure', ['error_message' => __('hanoivip.payment::webtopup.exception')]);
        }
    }
    
    public function query(Request $request)
    {
        try
        {
            $trans = $request->input('trans');
            $result = PaymentFacade::query($trans);
            if ($request->ajax())
            {
                return ['error' => 0, 'message' => '', 'data' => $result->toArray()];
            }
            else
            {
                return view('hanoivip.vpcard::result', ['data' => $result, 'trans' => $trans]);
            }
        }
        catch (Exception $ex)
        {
            Log::error("NewTopup query exception: " . $ex->getMessage());
            if ($request->ajax())
            {
                return ['error' => 99, 'message' => $ex->getMessage(), 'data' => []];
            }
            else
            {
                return view('hanoivip.vpcard::failure', ['message' => $ex->getMessage()]);
            }
        }
    }
    
    public function history(Request $request)
    {
        try
        {
            $userId = Auth::user()->getAuthIdentifier();
            $submits = $this->logs->list($userId);
            $mods = BalanceFacade::getHistory($userId);
            return view('hanoivip.vpcard::history',
                ['submits' => $submits[0], 'total_submits' => $submits[1],
                    'mods' => $mods[0], 'total_mods' => $mods[1]]);
        }
        catch (Exception $ex)
        {
            Log::error("Webtopup history exception " . $ex->getMessage());
            
        }
    }
}