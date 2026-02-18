@if(!empty($couponData) && count($couponData) > 0)
<div class="available-coupons border p-2 rounded bg-light">
    <h6 class="fs-14 fw-bold mb-2">{{ __('Available Coupons') }}</h6>
    <ul class="list-unstyled mb-0">
        @foreach($couponData as $coupon)
            <li class="mb-1">
                <span class="badge bg-success badge-no-transform">{{ $coupon->code }}</span>
                <span class="text-danger ms-1">
                    @if ($coupon->coupon_type == 'percentage')
                        {{ $coupon->coupon_value }}%
                    @else
                        {{ $currecy_details->symbol }}{{ $coupon->coupon_value }}
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
</div>
@endif
<div class="coupon">
    <label for="coupon_code" class="fs-13 text-dark fw-bold">{{ __('Apply Coupon') }}</label>
    <div class="input-group input-group-sm">
        <input type="text" class="form-control form-control-sm" id="coupon_code" name="coupon_code" placeholder="{{ __('Enter coupon code') }}">
        <button type="button" class="input-group-text btn btn-dark fw-medium d-block btn-sm" id="coupon_btn">{{ __('ADD') }}</button>
        <button type="button" class="input-group-text btn btn-danger fw-medium d-none btn-sm" id="coupon_remove_btn">{{ __('Remove') }}</button>
    </div>
    <span class="fs-10" id="coupon_code_error"></span>
</div>
