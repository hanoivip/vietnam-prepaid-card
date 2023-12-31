<?php
namespace Hanoivip\VietnamPrepaidCard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Hanoivip\PaymentContract\Facades\PaymentFacade;
use Hanoivip\VietnamPrepaidCard\Models\WebtopupLogs;
use Hanoivip\VietnamPrepaidCard\Services\WebtopupRepository;
use Hanoivip\Payment\Facades\BalanceFacade;
use Hanoivip\Events\Gate\UserTopup;
use Hanoivip\VietnamPrepaidCard\Jobs\CheckPendingReceipt;

/**
 *
 * @author hanoivip
 */
class AdminController extends Controller
{

    protected $logs;
    
    protected $stats;
    
    protected $service;
    
    protected $request;

    public function __construct(
        WebtopupRepository $logs)
    {
        $this->logs = $logs;
    }

    public function webtopupHistory(Request $request)
    {
        $page = 0;
        if ($request->has('page'))
            $page = $request->input('page');
        $tid = $request->input('tid');
        $history = $this->logs->list($tid, $page);
        return view('hanoivip.vpcard::admin.webtopup-history', [
            'submits' => $history[0],
            'total_page' => $history[1],
            'current_page' => $history[2],
            'tid' => $tid
        ]);
    }
    
    public function retry(Request $request)
    {
        $receipt = $request->input('receipt');
        $log = WebtopupLogs::where('trans_id', $receipt)->first();
        if (empty($log))
        {
            return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => 'Receipt not found']);
        }
        //if (!empty($log->callback))
        //{
        //    return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => 'Receipt was done']);
        //}
        $tid = $log->user_id;
        $log->callback = true;
        $log->by_admin = true;
        $log->save();
        try
        {
            $result = PaymentFacade::query($receipt);
            if (gettype($result) == 'string')
            {
                if ($request->ajax())
                {
                    return ['error' => 1, 'message' => $result, 'data' => []];
                }
                else
                {
                    return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => $result]);
                }
            }
            else
            {
                if ($result->isPending())
                {
                    dispatch(new CheckPendingReceipt($tid, $receipt));
                    if ($request->ajax())
                    {
                        return ['error' => 0, 'message' => 'pending', 'data' => ['trans' => $receipt]];
                    }
                    else
                    {
                        return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => "OK. Thẻ trễ, đợi.."]);
                    }
                }
                else if ($result->isFailure())
                {
                    if ($request->ajax())
                    {
                        return ['error' => 2, 'message' => $result->getDetail(), 'data' => []];
                    }
                    else
                    {
                        return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => 'Err:' . $result->getDetail()]);
                    }
                }
                else
                {
                    event(new UserTopup($tid, 0, $result->getAmount(), $receipt));
                    BalanceFacade::add($tid, $result->getAmount(), "WebTopup:" . $receipt);
                    if ($request->ajax())
                    {
                        return ['error' => 0, 'message' => 'success', 'data' => []];
                    }
                    else
                    {
                        return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => "Thành công."]);
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error("WebTopup  callback exception:" . $ex->getMessage());
            if ($request->ajax())
            {
                return ['error' => 99, 'message' => $ex->getMessage(), 'data' => []];
            }
            else
            {
                return view('hanoivip.vpcard::webtopup-failure', ['message' => $ex->getMessage()]);
            }
        }
    }
    
    public function check(Request $request)
    {
        $receipt = $request->input('receipt');
        $log = WebtopupLogs::where('trans_id', $receipt)->first();
        if (empty($log))
        {
            return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => 'Receipt not found']);
        }
        $tid = $log->user_id;
        $log->callback = true;
        $log->by_admin = true;
        $log->save();
        try
        {
            $resultCache = PaymentFacade::query($receipt);
            if (gettype($resultCache) == 'string')
            {
                return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => $resultCache]);
            }
            else
            {
                if ($resultCache->isPending() || $resultCache->isSuccess())
                {
                    return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => 'No thing to do']);
                }
                else 
                {
                    $resultForce = PaymentFacade::query($receipt, true);
                    if (gettype($resultForce) == 'string')
                    {
                        return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => $resultForce]);
                    }
                    else 
                    {
                        if ($resultForce->isFailure())
                        {
                            return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => $resultForce->getDetail()]);
                        }
                        else if ($resultForce->isPending())
                        {
                            dispatch(new CheckPendingReceipt($tid, $receipt));
                            return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => "OK. Still pending. Wait more."]);
                        }
                        else
                        {
                            event(new UserTopup($tid, 0, $resultForce->getAmount(), $receipt));
                            BalanceFacade::add($tid, $resultForce->getAmount(), "WebTopup:" . $receipt);
                            return view('hanoivip.vpcard::admin.webtopup-retry-result', ['message' => "OK. Credit added."]);
                        }
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            Log::error("WebTopup admin check exception:" . $ex->getMessage());
            if ($request->ajax())
            {
                return ['error' => 99, 'message' => $ex->getMessage(), 'data' => []];
            }
            else
            {
                return view('hanoivip.vpcard::webtopup-failure', ['message' => $ex->getMessage()]);
            }
        }
    }
    
    public function findUserByOrder(Request $request)
    {
        $message = "";
        $error = "";
        if ($request->getMethod() == 'POST')
        {
            $order = $request->input('order');
            $log = WebtopupLogs::where('trans_id', $order)->first();
            if (!empty($log))
            {
                return redirect()->route('user-detail', ['tid' => $log->user_id]);
            }
            else
            {
                $error = "Order not found!";
            }
        }
        return view('hanoivip.vpcard::admin.webtopup-find-user', ['message' => $message, 'error_message' => $error]);
    }
}