<?php
namespace Modules\GlobalSetting\app\Repositories\Contracts;

use Illuminate\Http\Request;

interface SocialLinkRepositoryInterface
{
    public function store(Request $request);
    public function getAll(Request $request);
    public function find($id);
    public function delete(Request $request);
}
