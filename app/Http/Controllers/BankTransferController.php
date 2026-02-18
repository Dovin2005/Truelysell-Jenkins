<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Product\app\Models\Product;
use App\Models\Bookings;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Log;
use Modules\GlobalSetting\Entities\GlobalSetting;

class BankTransferController extends Controller
{
    // The API version for Cashfree PG
    private $x_api_version = "2023-08-01";

    /**
     * Create a Cashfree order and return a redirect URL.
     */
    public function handlePayment(Request $request, $formattedBookingDate, $authId, $additionalServiceData, $fromTime, $toTime)
    {
        $receiptPath = null;

        if ($request->hasFile('payment_receipt')) {
            $file = $request->file('payment_receipt');
            $filename = 'receipt_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $receiptPath = $file->storeAs('uploads/receipts', $filename, 'public');
        }

        $data = [
            "product_id" => $request->input('service_id'),
            "branch_id" => $request->input('branch_id') ?? 0,
            "staff_id" => $request->input('staff_id') ?? 0,
            "slot_id" => $request->input('slot_id') ?? 0,
            "booking_date" => $formattedBookingDate,
            "from_time" => $request->input('from_time') ?? $fromTime,
            "to_time" => $request->input('to_time') ?? $toTime,
            "booking_status" => 1,
            "amount_tax" => $request->input('tax_amount'),
            "user_id" => $authId,
            "first_name" => $request->input('first_name'),
            "last_name" => $request->input('last_name'),
            "user_email" => $request->input('email'),
            "user_phone" => $request->input('phone_number'),
            "user_city" => $request->input('city'),
            "user_state" => $request->input('state'),
            "user_address" => $request->input('address'),
            "notes" => $request->input('note'),
            "user_postal" => $request->input('postal'),
            "payment_type" => 4,
            "payment_status" => 1,
            "service_qty" => 1,
            "service_amount" => $request->input('sub_amount'),
            "total_amount" => $request->input('total_amount'),
            "bank_transfer_proof" => $receiptPath,
        ];

        if ($additionalServiceData) {
            $data['additional_services'] = $additionalServiceData;
        }

        $save = Bookings::create($data);

        if ($save) {
            $orderId = getBookingOrderId($save->id);
            $save->update(['order_id' => $orderId]);
            sendBookingNotification($save->id);
        }

        if ($save && $request->filled('coupon_id')) {
            FacadesDB::table('coupon_logs')->insert([
                'user_id' => $save->user_id,
                'booking_id' => $save->id,
                'coupon_id' => $request->input('coupon_id'),
                'coupon_code' => $request->input('coupon_code'),
                'coupon_value' => $request->input('coupon_value'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $provider_id = Product::select('created_by')->where('id', $request->input('service_id'))->first();
        try {
            $pdfPath = \App\Helpers\InvoiceHelper::generateInvoice(
                $save->id,
                $request->input('total_amount'),
                '2',
                $provider_id->created_by,
            );
        } catch (\Exception $e) {
            Log::error("Failed to generate invoice: " . $e->getMessage());
        }

        if (request()->has('is_mobile') && request()->get('is_mobile') === "yes") {
            return response()->json(['code' => "200", 'message' => __('Booking successfully created.'), 'data' => $data], 200);
        }

        if ($save) {
            return response()->json(['code' => 200, 'message' => 'Booking successfully created!', 'data' => ['order_id' => $save->order_id]], 200);
        } else {
            return response()->json(['error' => 'Failed to create booking'], 500);
        }
    }
}
