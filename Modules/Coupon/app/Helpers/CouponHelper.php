<?php

namespace Modules\Coupon\app\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CouponHelper {
    public static function getAvailableCoupons($sourceCategoryId, $sourceSubcategoryId, $serviceId)
    {   
        $currentDate = Carbon::today();
        $couponData = DB::table('coupons')
            ->select(
                'id',
                'code',
                'coupon_type',
                'coupon_value',
                'quantity',
                'quantity_value'
            )
            ->where('status', 1)
            ->whereDate('start_date', '<=', $currentDate)
            ->whereDate('end_date', '>=', $currentDate)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($sourceCategoryId, $sourceSubcategoryId, $serviceId) {
                $query->where(function ($q) use ($sourceCategoryId, $sourceSubcategoryId, $serviceId) {
                    $q->orWhereRaw("FIND_IN_SET(?, category_id)", [$sourceCategoryId])
                    ->orWhereRaw("FIND_IN_SET(?, subcategory_id)", [$sourceSubcategoryId])
                    ->orWhereRaw("FIND_IN_SET(?, product_id)", [$serviceId]);
                })
                ->orWhere('product_type', 'all');
            })
            ->orderBy('id', 'desc')
            ->get();

        return $couponData;
    }
}
