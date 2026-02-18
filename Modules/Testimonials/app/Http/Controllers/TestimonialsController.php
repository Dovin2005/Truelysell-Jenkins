<?php

namespace Modules\Testimonials\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Testimonials\app\Http\Requests\TestimonialRequest;
use Modules\Testimonials\app\Repositories\Contracts\TestimonialRepositoryInterface;

class TestimonialsController extends Controller
{
    protected TestimonialRepositoryInterface $testimonialRepo;

    public function __construct(TestimonialRepositoryInterface $testimonialRepo)
    {
        $this->testimonialRepo = $testimonialRepo;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $data = $this->testimonialRepo->getAll($request);
            return response()->json([
                'code' => 200,
                'message' => __('Testimonials details retrieved successfully.'),
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('An error occurred while retrieving testimonials.'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function store(TestimonialRequest $request): JsonResponse
    {
        try {
            $result = $this->testimonialRepo->store($request);
            return response()->json([
                'code' => 200,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('testimonial_save_error', [], $request->input('language_code', 'en')),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        try {
            $result = $this->testimonialRepo->destroy($request);
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => __('testimonial_delete_error', [], $request->input('language_code', 'en'))
            ]);
        }
    }

    public function statusChange(Request $request): JsonResponse
    {
        try {
            $result = $this->testimonialRepo->statusChange($request);
            return response()->json([
                'code' => 200,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => __('testimonial_status_error', [], $request->input('language_code', 'en'))
            ]);
        }
    }
}
