<?php

namespace Modules\Installer\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Installer\app\Enums\InstallerInfo;
use Modules\Installer\app\Models\Configuration;

class PuchaseVerificationController extends Controller
{
    public function __construct()
    {
        set_time_limit(8000000);
        // Start session if not already started
        if (!Session::isStarted()) {
            Session::start();
        }
    }

    public function index()
    {
        return view('installer::index');
    }

    public function validatePurchase(Request $request)
    {
        // Clear existing session data for fresh start
        Session::flush();

        $request->validate([
            'purchase_code' => 'required|string',
        ]);

        try {
            $response = Http::asForm()->post(InstallerInfo::VERIFICATION_URL->value, [
                'purchase_code' => $request->purchase_code,
            ]);
            
            $data = $response->json();
            
            if ($data['status'] == true) {
                // Set multiple session variables at once
                Session::put([
                    'step-1-complete' => true,
                    'requirements-complete' => false,
                    'step-2-complete' => false,
                    'step-3-complete' => false,
                    'step-4-complete' => false,
                    'step-5-complete' => false,
                    'step-6-complete' => false,
                ]);
                
                Configuration::updateStep(2);

                return response()->json([
                    'success' => true, 
                    'message' => "Purchase Code Verified Successfully"
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $data['message'] ?? 'Purchase Code is Invalid'
                ], 200);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Server Error'
            ], 500);
        }
    }
}