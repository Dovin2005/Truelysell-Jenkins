<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Support\Facades\Redirect;
use Modules\Product\app\Models\Book;
use App\Models\PackageTrx;
use App\Models\Bookings;
use Auth;
use DB;
use Session;
use Modules\Leads\app\Models\Payments;
use Modules\Communication\app\Http\Controllers\EmailController;
use Modules\Communication\app\Models\Templates;
use Illuminate\Support\Str;
use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Models\Currency;
use Modules\Communication\app\Http\Controllers\NotificationController;
use Illuminate\Support\Carbon;
use Modules\Product\app\Models\Product;
use App\Models\WalletHistory;

class MollieController extends Controller
{
    public function preparePayment(Request $request)
    {

        $added_amount = 0;
        $service_offer = "";
        if (isset($request->service_offer)) {
            $service_offer = serialize($request->service_offer);
            foreach ($request->service_offer as $service_offerdValues) {
                $actualvalue = explode("_", $service_offerdValues);
                $added_amount = $added_amount + $actualvalue[1];
            }
        }
        $purchase_units = [];
        $book_details = Bookings::orderBy('id', 'DESC')->first();
        //  print_r($book_details); exit();
        $total_amount = $request->service_amount * $request->service_qty + ($added_amount) + ($request->amount_tax);
        $defaultCurrency = getDefaultCurrencyCode();

        $payment = Mollie::api()->payments->create([
            "amount" => [
                "currency" => $defaultCurrency,
                "value" => number_format($total_amount, 2) // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            "description" => "truelysell service",
            "redirectUrl" => route('make.molliesucess'),
            // "webhookUrl" => route('webhooks.mollie'),
            "metadata" => [
                "order_id" => "12345",
            ],
        ]);
        // Session->put('paymentid',$payment->id);
        session(['paymentid' => $payment->id]);

        $data_insert = [
            'product_id' => $request->product_id,
            'service_qty' => $request->service_qty,
            'payment_type' => 7,
            'amount_tax' => $request->amount_tax,
            'first_name' => $request->first_name,
            'user_id' => Auth::user()->id,
            'last_name' => $request->last_name,
            'user_email' => $request->user_email,
            'total_amount' => $total_amount,
            'user_phone' => $request->user_phone,
            'service_offer' => $service_offer,
            'tranaction' =>  $payment->id,
            'user_address' => $request->user_address,
            'user_city' => $request->user_city,
            'user_state' => $request->user_state,
            'user_postal' => $request->user_postalcode,
            'service_amount' => $request->service_amount,
            'notes' => $request->user_notes,
        ];
        DB::table('bookings')->insert($data_insert);

        // redirect customer to Mollie checkout page
        return redirect($payment->getCheckoutUrl(), 303);
    }

    public function handleWebhookNotification(Request $request)
    {
        Bookings::where('tranaction', Session('paymentid'))->update(['payment_status' => 2]);
        return view('bookingsuccess');
    }

    public function molliepayment(Request $request)
    {
        $defaultCurrency = getDefaultCurrencyCode();
        if (request()->has('type') && request()->get('type') === "user") {
            $total_amount = $request->input('service_amount');
            $trx_id = $request->input('trx_id');

            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => $defaultCurrency,
                    "value" => number_format($total_amount, 2, '.', '')
                ],
                "description" => "TruelySell Service",
                "redirectUrl" => route('make.molliepayment.leads'),
                "metadata" => [
                    "order_id" => "12345",
                ],
            ]);

            session(['paymentid' => $payment->id]);

            Payments::where('id', $trx_id)->update(['transaction_id' => $payment->id]);

            $mollieURL = $payment->getCheckoutUrl();

            return response()->json([
                'message' => 'Order created successfully.',
                'url' => $mollieURL
            ]);
        } else {
            $total_amount = $request->input('service_amount');
            $trx_id = $request->input('trx_id');

            $payment = Mollie::api()->payments->create([
                "amount" => [
                    "currency" => $defaultCurrency,
                    "value" => number_format($total_amount, 2, '.', '')
                ],
                "description" => "TruelySell Service",
                "redirectUrl" => route('make.molliepayment'),
                "metadata" => [
                    "order_id" => "12345",
                ],
            ]);

            session(['paymentid' => $payment->id]);

            PackageTrx::where('id', $trx_id)->update(['transaction_id' => $payment->id]);

            $mollieURL = $payment->getCheckoutUrl();

            return response()->json([
                'message' => 'Order created successfully.',
                'url' => $mollieURL
            ]);
        }
    }

    public function handleMolliepayment(Request $request)
    {
        PackageTrx::where('transaction_id', Session('paymentid'))->update(['payment_status' => 2]);
        return view('provider.subscription.payment_success');
    }

    public function handleMolliepaymentLeads(Request $request)
    {
        Payments::where('transaction_id', Session('paymentid'))->update(['status' => 2]);
        return view('user.userpaymentsuccess');
    }
}
