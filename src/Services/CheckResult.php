<?php

namespace Hanoivip\VietnamPrepaidCard\Services;

use Hanoivip\VietnamPrepaidCard\Jobs\CheckPendingReceipt;
use Illuminate\Support\Facades\Log;
use Exception;

trait CheckResult
{
    public function onTopupDone($userId, $receipt, $result)
    {
        $expectJson = request()->expectsJson();
        try
        {
            if ($result->isPending())
            {
                dispatch(new CheckPendingReceipt($userId, $receipt, $this->delivery))->delay(60);
                if ($expectJson)
                {
                    return ['error' => 0, 'message' => 'pending transaction', 'data' => $result->toArray()];
                }
                return view('hanoivip.vpcard::pending', ['trans' => $receipt]);
            }
            else if ($result->isFailure())
            {
                if ($expectJson)
                {
                    return ['error' => 2, 'message' => $result->getDetail()];
                }
                return view('hanoivip.vpcard::failure', ['message' => $result->getDetail()]);
            }
            else
            {
                dispatch(new CheckPendingReceipt($userId, $receipt, $this->delivery));
                if ($expectJson)
                {
                    return ['error' => 0, 'message' => 'success', 'data' => $result->toArray()];
                }
                return view('hanoivip.vpcard::success');
            }
        }
        catch (Exception $ex)
        {
            Log::error("WebTopup payment callback exception:" . $ex->getMessage());
            dispatch(new CheckPendingReceipt($userId, $receipt, 'game'))->delay(60);
            if ($expectJson)
            {
                return ['error' => 0, 'message' => 'We are trying our best to finish your payment', 'data' => $result->toArray()];
            }
            return view('hanoivip.vpcard::failure', ['message' => 'We are trying our best to finish your payment.']);
        }
    }
}

