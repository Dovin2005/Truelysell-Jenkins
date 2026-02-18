<?php

namespace Modules\Coupon\app\Repositories\Eloquent;

use Modules\Coupon\app\Models\Coupon;
use Carbon\Carbon;
use Modules\Coupon\app\Repositories\Contracts\CouponRepositoryInterface;

class CouponRepository implements CouponRepositoryInterface
{
    public function getCouponsByUser($userId, $isValid, $orderBy)
    {
        $condition = $isValid == 1 ? '>=' : '<';
        $currentDate = Carbon::today();

        return Coupon::where(['created_by' => $userId])
            ->where('end_date', $condition, $currentDate)
            ->orderBy('id', $orderBy)
            ->get()
            ->map(function ($coupon) {
                if ($coupon->product_id) {
                    $coupon->product_id = explode(',', $coupon->product_id);
                }
                if ($coupon->category_id) {
                    $coupon->category_id = explode(',', $coupon->category_id);
                }
                if ($coupon->subcategory_id) {
                    $coupon->subcategory_id = explode(',', $coupon->subcategory_id);
                }
                return $coupon;
            });
    }

    public function findCoupon($id)
    {
        $coupon = Coupon::where('id', $id)->first();

        if ($coupon) {
            $coupon->product_id = $coupon->product_id ? explode(',', $coupon->product_id) : [];
            $coupon->category_id = $coupon->category_id ? explode(',', $coupon->category_id) : [];
            $coupon->subcategory_id = $coupon->subcategory_id ? explode(',', $coupon->subcategory_id) : [];
        }

        return $coupon;
    }

    public function createOrUpdateCoupon(array $data, $id = null)
    {
        return Coupon::updateOrCreate(['id' => $id], $data);
    }

    public function deleteCoupon($id)
    {
        return Coupon::where('id', $id)->delete();
    }

    public function updateCouponStatus($id, $status)
    {
        return Coupon::where('id', $id)->update(['status' => $status]);
    }

    public function checkCodeUnique($code, $id = null)
    {
        return Coupon::where('code', $code)
            ->when($id, function ($query) use ($id) {
                return $query->where('id', '!=', $id);
            })
            ->doesntExist();
    }
}