<?php
namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use DB;
use Illuminate\Http\Request;
use Modules\Communication\app\Models\NotificationSettings;
use Illuminate\Http\JsonResponse;
use Modules\GlobalSetting\app\Models\Currency;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    public function getdefaultcurrency(Request $request) : JsonResponse
    {
        try {
            $getcurrency = Currency::select('name','symbol')->where('is_default',1)->where('status',1)->Where('deleted_at', NULL)->first();
        
            return response()->json([
                'code' => 200,
                'message' => __('Currency retrieved successfully.'),
                'data' => $getcurrency,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while getting details'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getnotificationsettings(int $type,string $source) : mixed
    {
        $getdata=NotificationSettings::where('type',$type)->where('source',$source)->where('is_flag',1)->first();
        $status=0;
        if(isset($getdata)){
            $status=1;
        }
        return $status;
    }
}
