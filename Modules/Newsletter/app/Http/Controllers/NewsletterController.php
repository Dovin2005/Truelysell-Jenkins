<?php

namespace Modules\Newsletter\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Newsletter\app\Http\Requests\EmailSubscriptionRequest;
use Modules\Newsletter\app\Repositories\Contracts\NewsletterRepositoryInterface;

class NewsletterController extends Controller
{
    protected NewsletterRepositoryInterface $newsletterRepo;

    public function __construct(NewsletterRepositoryInterface $newsletterRepo)
    {
        $this->newsletterRepo = $newsletterRepo;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->newsletterRepo->index($request);
            return response()->json([
                'code' => 200,
                'message' => __('Email Subscription details retrieved successfully.'),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while retrieving Email Subscriptions.'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function store(EmailSubscriptionRequest $request): JsonResponse
    {
        try {
            $result = $this->newsletterRepo->store($request);

            if ($result['error']) {
                return response()->json([
                    'success' => false,
                    'code' => 422,
                    'errors' => $result['messages']
                ], 422);
            }

            return response()->json([
                'code' => 200,
                'message' => __('You have successfully subscribed to our newsletter.'),
                'data' => $result['template']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while processing your subscription. Please try again later.')
            ]);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $this->newsletterRepo->destroy($request);
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => __('email_subscriber_delete_success', [], $request->input('language_code', 'en'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => __('Failed to delete subscriber.')
            ]);
        }
    }

    public function subscriberStatusChange(Request $request): JsonResponse
    {
        try {
            $this->newsletterRepo->subscriberStatusChange($request);
            return response()->json([
                'code' => 200,
                'message' => __('email_subscriber_status_success', [], $request->input('language_code', 'en'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while changing subscriber status')
            ]);
        }
    }

    public function getNewsletterTemplate(Request $request): JsonResponse
    {
        try {
            $template = $this->newsletterRepo->getNewsletterTemplate($request);
            return response()->json([
                'code' => 200,
                'message' => __('Newsletter template retrieved successfully.'),
                'data' => $template
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('Error! while getting newsletter template')
            ]);
        }
    }
}
