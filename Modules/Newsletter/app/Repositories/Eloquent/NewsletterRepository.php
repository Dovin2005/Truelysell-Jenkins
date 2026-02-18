<?php

namespace Modules\Newsletter\app\Repositories\Eloquent;

use Illuminate\Http\Request;
use Modules\GlobalSetting\app\Models\Templates;
use Modules\Newsletter\app\Models\EmailSubscription;
use Modules\Newsletter\app\Repositories\Contracts\NewsletterRepositoryInterface;
use Illuminate\Support\Facades\Validator;

class NewsletterRepository implements NewsletterRepositoryInterface
{
    public function index(Request $request)
    {
        $orderBy = $request->order_by ?? 'desc';
        return EmailSubscription::orderBy('id', $orderBy)->get();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscriber_email' => 'required|email|unique:email_subscriptions,email',
        ], [
            'subscriber_email.required' => __('The email field is required.'),
            'subscriber_email.email' => __('Please provide a valid email address.'),
            'subscriber_email.unique' => __('This email is already subscribed.'),
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'messages' => $validator->messages()];
        }

        $data = [
            'email' => $request->subscriber_email,
            'status' => 1,
        ];

        EmailSubscription::create($data);

        $template = Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', 9)
            ->first();

        return ['error' => false, 'template' => $template];
    }

    public function destroy(Request $request)
    {
        return EmailSubscription::where('id', $request->id)->delete();
    }

    public function subscriberStatusChange(Request $request)
    {
        return EmailSubscription::where('id', $request->id)->update([
            'status' => $request->status
        ]);
    }

    public function getNewsletterTemplate(Request $request)
    {
        return Templates::select('subject', 'content')
            ->where('type', 1)
            ->where('notification_type', 9)
            ->first();
    }
}
