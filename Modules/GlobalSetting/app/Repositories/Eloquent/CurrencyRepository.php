<?php

namespace Modules\GlobalSetting\app\Repositories\Eloquent;

use Modules\GlobalSetting\app\Models\Currency;
use Modules\GlobalSetting\app\Repositories\Contracts\CurrencyInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CurrencyRepository implements CurrencyInterface
{
    public function index(array $filters)
    {
        $orderBy = $filters['order_by'] ?? 'desc';
        $countPerPage = $filters['count_per_page'] ?? 10;
        $sortBy = $filters['sort_by'] ?? 'id';
        $search = $filters['search'] ?? null;

        $query = Currency::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
        }

        return $query->orderBy($sortBy, $orderBy)->paginate($countPerPage);
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $currency = Currency::create($data);

            if ($data['is_default'] ?? false) {
                $this->setDefault($currency->id);
            }

            Cache::forget('currecy_details');
            DB::commit();

            return $currency;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function setDefault(int $id)
    {
        DB::beginTransaction();
        try {
            Currency::where('is_default', true)->update(['is_default' => false]);
            Currency::where('id', $id)->update(['is_default' => true]);
            
            Cache::forget('currecy_details');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function changeStatus(int $id, bool $status)
    {
        $currency = Currency::findOrFail($id);
        $currency->update(['status' => $status]);
        
        Cache::forget('currecy_details');
        return $currency;
    }

    public function destroy(int $id)
    {
        $currency = Currency::findOrFail($id);
        
        if ($currency->is_default) {
            throw new \Exception('Default currency cannot be deleted.');
        }
        
        $currency->delete();
        Cache::forget('currecy_details');
        
        return $currency;
    }

    public function checkUnique(string $field, string $value)
    {
        return Currency::where($field, $value)->exists();
    }
}