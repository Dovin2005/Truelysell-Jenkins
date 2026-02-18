<?php

namespace Modules\Faq\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface FaqRepositoryInterface
{
    public function getAll(Request $request);
    public function store(Request $request);
    public function update(Request $request);
    public function delete(Request $request);
    public function getById(Request $request);
}
