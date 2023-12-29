<?php

namespace Hanoivip\VietnamPrepaidCard\Services;

use Hanoivip\VietnamPrepaidCard\Jobs\CheckPendingReceipt;
use Illuminate\Support\Facades\Log;
use Exception;

trait CheckResult
{
    public function onTopupDone($userId, $receipt, $result)
    {
        Log::debug("1111111111111");
        try
        {
            if ($result->isPending())
            {
                dispatch(new CheckPendingReceipt($userId, $receipt, $this->delivery))->delay(60);
                return view('hanoivip.vpcard::pending', ['trans' => $receipt]);
            }
            else if ($result->isFailure())
            {
                return view('hanoivip.vpcard::failure', ['message' => $result->getDetail()]);
            }
            else
            {
                dispatch(new CheckPendingReceipt($userId, $receipt, $this->delivery));
                return view('hanoivip.vpcard::success');
            }
        }
        catch (Exception $ex)
        {
            Log::error("WebTopup payment callback exception:" . $ex->getMessage());
            dispatch(new CheckPendingReceipt($userId, $receipt, 'game'))->delay(60);
            return view('hanoivip.vpcard::failure', ['message' => 'We are trying our best to finish your payment.']);
        }
    }
}

