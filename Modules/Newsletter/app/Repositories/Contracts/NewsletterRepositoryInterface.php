<?php

namespace Modules\Newsletter\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface NewsletterRepositoryInterface
{
    public function index(Request $request);
    public function store(Request $request);
    public function destroy(Request $request);
    public function subscriberStatusChange(Request $request);
    public function getNewsletterTemplate(Request $request);
}
