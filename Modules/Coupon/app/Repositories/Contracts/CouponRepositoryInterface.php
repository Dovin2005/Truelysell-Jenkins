<?php
namespace Modules\Coupon\app\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Coupon\app\Models\Coupon;
use Modules\Coupon\app\Http\Requests\CouponListRequest;
use Modules\Coupon\app\Http\Requests\CouponStoreRequest;
use Modules\Coupon\app\Http\Requests\CouponStatusRequest;
use Modules\Coupon\app\Http\Requests\CouponUniqueRequest;

interface CouponRepositoryInterface
{
    public function getCouponsByUser($userId, $isValid, $orderBy);
    public function findCoupon($id);
    public function createOrUpdateCoupon(array $data, $id = null);
    public function deleteCoupon($id);
    public function updateCouponStatus($id, $status);
    public function checkCodeUnique($code, $id = null);

}