<?php

namespace Hanoivip\VietnamPrepaidCard\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Hanoivip\VietnamPrepaidCard\Services\WebtopupRepository;

class AppHistory extends Controller
{
    private $logs;
    
    public function __construct(
        WebtopupRepository $logs)
    {
        $this->logs = $logs;
    }
    
    public function cards(Request $request)
    {
        $page = 0;
        if ($request->has('page'))
            $page = $request->input('page');
        try
        {
            $userId = Auth::user()->getAuthIdentifier();
            $submits = $this->logs->list($userId, $page);
            return ['error' => 0, 'message' => 0, 'data' => 
                ['submits' => $submits[0], 'total_submits' => $submits[1], 'current_page' => $submits[2]]];
        }
        catch (Exception $ex)
        {
            Log::error("Vietname card history exception " . $ex->getMessage());
            return ['error' => 0, 'message' => 'exception', 'data' => []];
        }
    }
}