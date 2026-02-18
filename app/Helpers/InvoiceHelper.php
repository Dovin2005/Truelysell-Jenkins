<?php

namespace App\Helpers;

use Modules\GlobalSetting\Entities\GlobalSetting;
use Modules\GlobalSetting\app\Models\InvoiceTemplate;
use Modules\Leads\app\Models\UserFormInput;
use App\Models\UserDetail;
use App\Models\User;
use App\Models\Bookings;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\Communication\app\Http\Controllers\EmailController;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\PayoutHistory;
use Modules\Categories\app\Models\Categories;
use Modules\Product\app\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class InvoiceHelper
{
    public static function generateInvoice($providerFormInputId, $amount, $type, $providerid)
    {
        if($type == 1){
            $providerFormInput = UserFormInput::find($providerFormInputId);
            $defaultInvoiceTemplate = InvoiceTemplate::where('invoice_type', 'Leads')->first();
            $category = Categories::where('id', $providerFormInput->category_id)->value('name');
        } else if($type == 2){
            $providerFormInput = Bookings::find($providerFormInputId);
            $defaultInvoiceTemplate = InvoiceTemplate::where('invoice_type', 'Booking')->first();
            $category = Product::where('id', $providerFormInput->product_id)->value('source_name');
            $ratedetails = Product::where('id', $providerFormInput->product_id)->select('source_price', 'duration')->first();
        }
        else if($type == 3){
            $providerFormInput = PayoutHistory::find($providerFormInputId);
            $defaultInvoiceTemplate = InvoiceTemplate::where('invoice_type', 'Payout')->first();
        }
        
        if (!$providerFormInput) {
            throw new \Exception('Provider form input not found.');
        }
        if(!$defaultInvoiceTemplate){
            $defaultInvoiceTemplate = InvoiceTemplate::where('is_default', true)->first();
        }

        if (!$defaultInvoiceTemplate) {
            throw new \Exception('Invoice template not found.');
        }

        // Fetch global settings values or use default values
        $companyName = GlobalSetting::where('key', 'company_name')->value('value') ?? 'TruelySell';
        $companyAddress = GlobalSetting::where('key', 'site_address')->value('value') ?? '589 5th Ave, NY 10024, USA';
        $companyEmail = GlobalSetting::where('key', 'site_email')->value('value') ?? 'contact@truelysell.com';
        $companyPhone = GlobalSetting::where('key', 'phone_no')->value('value') ?? '(123) 456-7890';
        $privderImageStatus = GlobalSetting::where('key', 'providerlogo')->value('value');
        $companyImage = GlobalSetting::where('key', 'invoice_company_logo')->value('value');
        $invoicePrefix = GlobalSetting::where('key', 'invoice_prefix')->value('value') ?? '';
        $invoiceStarts = GlobalSetting::where('key', 'invoice_starts')->value('value') ?? '';
        $companyWebsite = GlobalSetting::where('key', 'website')->value('value') ?? '';
        $contact = $companyEmail . ' | ' . $companyPhone;
        $invoicePrefix = $invoicePrefix . $invoiceStarts;
        $orderId = str_pad($providerFormInputId ?? 0, 4, '0', STR_PAD_LEFT);
        $invoiceId = $invoicePrefix . $orderId;

        // Fetch provider details
        $providerDetails = UserDetail::where('user_id', $providerid)->first();
        $providerInfo = User::where('id', $providerid)->first();

        // Check if provider-specific values are enabled and available
        if ($privderImageStatus == 1 && $providerDetails && $providerInfo) {
            $companyName = $providerDetails->company_name ?? $companyName;
            $companyAddress = $providerDetails->company_address ?? $companyAddress;
            $companyEmail = $providerInfo->email ?? $companyEmail;
            $companyPhone = $providerInfo->phone_number ?? $companyPhone;

            // Set provider image URL if available
            if (!empty($providerDetails->company_image)) {
                $imageUrl = public_path('storage/company-image/' . $providerDetails->company_image);
            } else {
                $imageUrl = public_path('/front/img/logo.svg');
            }
        } elseif ($companyImage) {
            $imageUrl = public_path('storage/' . $companyImage);
        } else {
            $imageUrl = public_path('/front/img/logo.svg');
        }

        // Override image URL for a specific type
        if ($type == 3 && $companyImage) {
            $imageUrl = public_path('storage/' . $companyImage);
            $companyName = GlobalSetting::where('key', 'company_name')->value('value') ?? 'TruelySell';
            $companyAddress = GlobalSetting::where('key', 'site_address')->value('value') ?? '589 5th Ave, NY 10024, USA';
            $companyEmail = GlobalSetting::where('key', 'site_email')->value('value') ?? 'contact@truelysell.com';
            $companyPhone = GlobalSetting::where('key', 'phone_no')->value('value') ?? '(123) 456-7890';
        }

        $imageData = base64_encode(file_get_contents($imageUrl));
        $imageSrc = 'data:image;base64,' . $imageData;

        $userDetails = UserDetail::select(
            'first_name',
            'last_name',
            'address',
            'postal_code'
        )->where('user_id', $providerFormInput->user_id)
          ->first();

        $userName = $userDetails->first_name . ' ' . $userDetails->last_name ?? 'Demo User';
        $userAddress = $userDetails->address ?? 'No Address';
        $userCity = $userDetails->city ?? 'No City';
        $userState = $userDetails->state ?? 'No State';
        $userPostal = $userDetails->postal_code ?? 'No Postal Code';
        $user_email = User::select('email','phone_number')->where('id',$providerFormInput->user_id)->first();

        switch ($type) {
            case 1:
                $description = 'Leads';
                break;
            case 2:
                $description = 'Booking';
                break;
            case 3:
                $description = 'Payout';
                break;
            default:
                $description = 'Unknown';
                break;
        }

        $service_amount = $amount - $providerFormInput->amount_tax;
        $invoiceContent = str_replace(
            ['{{company_name}}', '{{company_address}}', '{{company_email}}', '{{company_phone}}',
             '{{user_name}}', '{{user_address}}', '{{user_city}}', '{{user_state}}', '{{user_postal}}',
             '{{user_email}}', '{{user_phone}}', '{{quote}}', '{{description}}', '{{logo}}','{{category}}',
             '{{service_amount}}', '{{amount_tax}}' , '{{id}}' , '{{date}}' ,'{{ratehour}}', '{{first_name}}', '{{last_name}}', '{{website_link}}', '{{contact}}'],
            [
                $companyName,
                $companyAddress,
                $companyEmail,
                $companyPhone,
                $userName,
                $userAddress,
                $userCity,
                $userState,
                $userPostal,
                $user_email->email,
                $user_email->phone_number,
                $amount,
                $description,
                $imageSrc,
                $category,
                $service_amount,
                $providerFormInput->amount_tax ?? '-',
                $invoiceId ?? '-',
                $providerFormInput->created_at ?? '-',
                ($ratedetails->duration ?? null) ? ($ratedetails->source_price ?? '-') . '/' . ($ratedetails->duration ?? '- ') : '-',
                $userDetails->first_name ?? '', 
                $userDetails->last_name ?? '',
                $companyWebsite,
                $contact
            ],
            $defaultInvoiceTemplate->template_content
        );

        $dompdf = new Dompdf();
        $dompdf->loadHtml($invoiceContent);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf->setOptions($options);

        $dompdf->render();

        $pdfPath = 'invoices/invoice_' . $invoiceId . '.pdf';

        if (!\Storage::disk('public')->exists('invoices')) {
            \Storage::disk('public')->makeDirectory('invoices');
        }

        \Storage::disk('public')->put($pdfPath, $dompdf->output());

        $pdfUrl = \Storage::disk('public')->url($pdfPath);

        if($type == 1){
            $notificationType = 30;
            $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
            $subject = $template->subject;
            $content = $template->content;
            $user_email = User::select('email','name')->where('id',$providerFormInput->user_id)->first();
            $content = str_replace('{{user_name}}', $user_email->name, $content);
            $content = str_replace('{{company_name}}',  $companyName, $content);
        }else if($type == 2){
            $notificationType = 28;
            $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
            $subject = $template->subject;
            $content = $template->content;
            $user_email = User::select('email','name')->where('id',$providerFormInput->user_id)->first();
            $content = str_replace('{{user_name}}', $user_email->name, $content);
            $content = str_replace('{{company_name}}',  $companyName, $content);
        }
        else if($type == 3){
            $notificationType = 29;
            $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', $notificationType)
            ->first();
            $subject = $template->subject;
            $content = $template->content;
            $user_email = User::select('email','name')->where('id',$providerFormInput->user_id)->first();
            $content = str_replace('{{user_name}}', $user_email->name, $content);
            $content = str_replace('{{company_name}}',  $companyName, $content);
        }
        $emailData = [
            'to_email' => $user_email->email,
            'subject' => $subject,
            'content' => $content,
            'attachment' => public_path('storage/' . $pdfPath),
        ];

        $emailRequest = new Request($emailData);
        $emailController = new EmailController();
        $emailController->sendEmail($emailRequest);

        return $pdfPath;
    }
}
