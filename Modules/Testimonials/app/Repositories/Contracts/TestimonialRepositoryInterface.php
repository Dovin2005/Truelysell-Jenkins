<?php

namespace Modules\Testimonials\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface TestimonialRepositoryInterface
{
    public function getAll(Request $request);
    public function store(Request $request);
    public function destroy(Request $request);
    public function statusChange(Request $request);
}
