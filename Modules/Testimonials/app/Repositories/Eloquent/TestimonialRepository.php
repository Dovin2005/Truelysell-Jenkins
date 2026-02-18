<?php

namespace Modules\Testimonials\app\Repositories\Eloquent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Testimonials\app\Models\Testimonial;
use Modules\Testimonials\app\Repositories\Contracts\TestimonialRepositoryInterface;

class TestimonialRepository implements TestimonialRepositoryInterface
{
    public function getAll($request)
    {
        $orderBy = $request->input('order_by', 'desc');
        $sortBy = $request->input('sort_by', 'order_by');

        return Testimonial::orderBy($sortBy, $orderBy)
            ->get()
            ->map(function ($testimonial) {
                $testimonial->client_image = $testimonial->client_image
                    ? $testimonial->file($testimonial->client_image)
                    : url('assets/img/user-default.jpg');
                return $testimonial;
            });
    }

    public function store(Request $request)
    {
        $data = $request->only(['client_name', 'position', 'description', 'status']);
        $langCode = $request->input('language_code', 'en');

        if ($request->method === 'add') {
            $last = Testimonial::latest('order_by')->first();
            $data['order_by'] = $last ? $last->order_by + 1 : 1;

            if ($request->hasFile('client_image')) {
                $data['client_image'] = $this->uploadImage($request->file('client_image'));
            }

            Testimonial::create($data);
            return ['message' => __('testimonial_create_success', [], $langCode)];
        } else {
            $id = $request->input('id');
            $testimonial = Testimonial::find($id);
            if ($testimonial && $request->hasFile('client_image')) {
                $this->deleteImage($testimonial->client_image);
                $data['client_image'] = $this->uploadImage($request->file('client_image'));
            }
            Testimonial::where('id', $id)->update($data);
            return ['message' => __('testimonial_update_success', [], $langCode)];
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $langCode = $request->input('language_code', 'en');

        $testimonial = Testimonial::findOrFail($id);
        $this->deleteImage($testimonial->client_image);
        $testimonial->delete();

        return ['message' => __('testimonial_delete_success', [], $langCode)];
    }

    public function statusChange(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $langCode = $request->input('language_code', 'en');

        Testimonial::where('id', $id)->update(['status' => $status]);

        return ['message' => __('testimonial_status_success', [], $langCode)];
    }

    protected function uploadImage($file): string
    {
        $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('testimonials', $filename, 'public');
        return $filename;
    }

    protected function deleteImage(?string $filename): void
    {
        if ($filename && Storage::disk('public')->exists('testimonials/' . $filename)) {
            Storage::disk('public')->delete('testimonials/' . $filename);
        }
    }
}
